<?php

namespace Tests\Feature;

use App\Models\AdminLog;
use App\Models\Permissao;
use App\Models\Pessoa;
use App\Services\SyntheticAdminProvisioner;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class SyntheticAdminProvisioningTest extends TestCase
{
    private const PASSWORD = 'synthetic-local-only-password-123';

    protected function setUp(): void
    {
        parent::setUp();
        // Never touch the configured development/MySQL database.
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        config()->set('database.connections.sqlite.url', null);
        DB::purge('sqlite');
        foreach (['2026_05_05_224105_pessoa.php', '2026_10_08_000000_create_governance_tables.php', '2026_05_09_223409_create_personal_access_tokens_table.php'] as $file) {
            (require database_path('migrations/'.$file))->up();
        }
    }

    public function test_default_and_unapplied_commands_are_read_only(): void
    {
        $this->artisan('homologacao:admin')->assertSuccessful();
        $this->artisan('homologacao:admin', ['action' => 'provision'])->assertSuccessful();
        $this->artisan('homologacao:admin', ['action' => 'revoke'])->assertSuccessful();
        $this->assertSame(0, Pessoa::count());
        $this->assertSame(0, AdminLog::count());
    }

    public function test_provision_is_idempotent_and_grants_only_existing_permission_names(): void
    {
        $service = app(SyntheticAdminProvisioner::class);
        $this->assertSame('created', $service->provision(self::PASSWORD, 'local-ticket-1'));
        $admin = Pessoa::sole();
        $hash = $admin->senha_pessoa;
        $this->assertTrue(Hash::check(self::PASSWORD, $hash));
        $this->assertFalse($admin->admin_master);
        foreach (SyntheticAdminProvisioner::PERMISSIONS as $permission) {
            $this->assertTrue($admin->hasPermission($permission));
        }
        $this->assertFalse($admin->hasPermission('xp.campanhas.gerenciar'));
        $this->assertFalse($admin->hasPermission('lgpd.visualizar'));
        $this->assertSame('unchanged', $service->provision('ignored-no-rotation', 'local-ticket-1'));
        $this->assertSame($hash, $admin->fresh()->senha_pessoa);
        $this->assertSame(1, Pessoa::count());
        $this->assertSame(1, AdminLog::count());
        $this->assertStringNotContainsString(self::PASSWORD, AdminLog::sole()->toJson());
        $this->assertStringNotContainsString($hash, AdminLog::sole()->toJson());
    }

    public function test_revoke_removes_privileges_tokens_and_password_access_without_deletion(): void
    {
        $service = app(SyntheticAdminProvisioner::class);
        $service->provision(self::PASSWORD, 'local-ticket-1');
        $admin = Pessoa::sole();
        $admin->createToken('local-synthetic-token', ['pessoa']);
        $this->assertSame('revoked', $service->revoke('local-ticket-1'));
        $admin->refresh();
        $this->assertFalse(Hash::check(self::PASSWORD, $admin->senha_pessoa));
        $this->assertFalse($admin->admin_master);
        $this->assertFalse($admin->hasPermission('usuarios.validar'));
        $this->assertSame(0, $admin->tokens()->count());
        $this->assertSame(0, $admin->permissoes()->count());
        $this->assertFalse($admin->exclusao_pendente);
        $this->assertNull($admin->deletar_em);
        $this->assertSame('revoked', $service->inspect());
        $this->assertSame('unchanged', $service->revoke('local-ticket-1'));
        $this->assertSame(2, AdminLog::count());
        $this->expectException(DomainException::class);
        $service->provision(self::PASSWORD, 'local-ticket-1');
    }

    public function test_existing_person_is_never_adopted_or_promoted(): void
    {
        $person = $this->createPessoa('voluntario', ['login_pessoa' => SyntheticAdminProvisioner::LOGIN]);
        try {
            app(SyntheticAdminProvisioner::class)->provision(self::PASSWORD, 'local-ticket-1');
            $this->fail('Existing person must not be adopted.');
        } catch (DomainException) {
            $this->assertSame('voluntario', $person->fresh()->role_pessoa);
            $this->assertSame(0, AdminLog::count());
        }
    }

    public function test_privilege_drift_is_refused_including_master_escalation(): void
    {
        $service = app(SyntheticAdminProvisioner::class);
        $service->provision(self::PASSWORD, 'local-ticket-1');
        Pessoa::sole()->forceFill(['admin_master' => true])->save();
        $this->expectException(DomainException::class);
        $service->revoke('local-ticket-1');
    }

    public function test_cli_validates_batch_and_prompts_without_outputting_password(): void
    {
        $this->artisan('homologacao:admin', ['action' => 'provision', '--apply' => true, '--confirm-batch' => 'WRONG', '--operator' => 'local-ticket-1'])
            ->assertFailed();
        $this->assertSame(0, Pessoa::count());
        $this->artisan('homologacao:admin', ['action' => 'provision', '--apply' => true, '--confirm-batch' => SyntheticAdminProvisioner::BATCH, '--operator' => 'local-ticket-1'])
            ->expectsQuestion('Senha exclusiva (20–72 bytes)', self::PASSWORD)
            ->expectsQuestion('Confirme a senha', self::PASSWORD)
            ->doesntExpectOutputToContain(self::PASSWORD)
            ->assertSuccessful();
        $this->assertSame('active', app(SyntheticAdminProvisioner::class)->inspect());
        $this->artisan('homologacao:admin', ['action' => 'revoke', '--apply' => true, '--confirm-batch' => SyntheticAdminProvisioner::BATCH, '--operator' => 'local-ticket-1'])
            ->assertSuccessful();
    }

    public function test_invalid_password_or_operator_never_creates_accounts_or_audit_rows(): void
    {
        $service = app(SyntheticAdminProvisioner::class);
        foreach ([['short', 'local-ticket-1'], [str_repeat('a', 73), 'local-ticket-1'], [self::PASSWORD, 'invalid operator']] as [$password, $operator]) {
            try {
                $service->provision($password, $operator);
                $this->fail('Invalid input accepted.');
            } catch (DomainException) {
                $this->assertSame(0, Pessoa::count());
                $this->assertSame(0, AdminLog::count());
            }
        }
    }

    public function test_public_registration_cannot_mass_assign_master_or_role(): void
    {
        $person = new Pessoa;
        $person->fill(['role_pessoa' => 'administrador', 'admin_master' => true]);
        $this->assertNull($person->role_pessoa);
        $this->assertNull($person->admin_master);
    }

    public function test_audit_failure_rolls_back_account_and_permissions(): void
    {
        AdminLog::creating(fn () => throw new RuntimeException('Synthetic local failure'));
        try {
            app(SyntheticAdminProvisioner::class)->provision(self::PASSWORD, 'local-ticket-1');
            $this->fail('Audit failure must abort the transaction.');
        } catch (RuntimeException) {
            $this->assertSame(0, Pessoa::count());
            $this->assertSame(0, Permissao::count());
            $this->assertSame(0, AdminLog::count());
        }
    }

    public function test_noninteractive_provision_cannot_create_an_account(): void
    {
        $this->artisan('homologacao:admin', ['action' => 'provision', '--apply' => true,
            '--confirm-batch' => SyntheticAdminProvisioner::BATCH, '--operator' => 'local-ticket-1', '--no-interaction' => true])
            ->assertFailed();
        $this->assertSame(0, Pessoa::count());
        $this->assertSame(0, AdminLog::count());
    }
}

<?php

namespace Tests\Feature;

use App\Models\DocumentoPessoa;
use App\Models\Ong;
use App\Models\Pessoa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExclusaoContasPendentesSchedulerTest extends TestCase
{
    use RefreshDatabase;

    private function pessoa(int $number, string $role = 'voluntario', string $status = 'aguardando_validacao', bool $pending = true, bool $due = true): Pessoa
    {
        $pessoa = $this->createPessoa($role, [
            'cpf_pessoa' => str_pad((string) $number, 11, '0', STR_PAD_LEFT),
            'email_pessoa' => "pessoa{$number}@example.test",
            'login_pessoa' => "pessoa{$number}",
            'status_validacao' => $status,
        ]);
        $pessoa->forceFill([
            'exclusao_pendente' => $pending,
            'deletar_em' => $due ? now()->subDay() : now()->addDay(),
        ])->save();

        return $pessoa;
    }

    private function ong(int $number, string $status = 'aguardando aprovação', bool $pending = true, bool $due = true): Ong
    {
        $ong = $this->createOng($status, [
            'cnpj_ong' => str_pad((string) $number, 14, '0', STR_PAD_LEFT),
            'login_ong' => "ong{$number}",
            'email_ong' => "ong{$number}@example.test",
        ]);
        $ong->forceFill([
            'exclusao_pendente' => $pending,
            'deletar_em' => $due ? now()->subDay() : now()->addDay(),
        ])->save();

        return $ong;
    }

    public function test_default_and_dry_run_preserve_account_token_document_file_terms_and_notification(): void
    {
        Storage::fake('local');
        $pessoa = $this->pessoa(1);
        $token = $pessoa->createToken('teste')->accessToken;
        $pessoa->assinaturas()->create([
            'tipo_termo' => 'termos_de_uso', 'versao_termo' => '1.0',
            'aceito_em' => now(),
        ]);
        $pessoa->notificacoes()->create([
            'tipo_notificacao' => 'teste', 'mensagem_notificacao' => 'Registro fictício',
        ]);
        Storage::disk('local')->put('documentos/sintetico.pdf', 'PDF-SINTETICO');
        $documento = DocumentoPessoa::create([
            'pessoa_id' => $pessoa->pessoa_id, 'tipo' => 'documento_identidade',
            'storage_disk' => 'local', 'storage_path' => 'documentos/sintetico.pdf',
            'mime_type' => 'application/pdf', 'size_bytes' => 14,
            'uploaded_at' => now(),
        ]);

        foreach ([[], ['--dry-run' => true]] as $options) {
            $this->assertSame(0, Artisan::call('app:excluir-contas-pendentes', $options));
            $output = Artisan::output();
            $this->assertStringContainsString('1 Pessoas e 0 ONGs candidatas', $output);
            $this->assertStringNotContainsString($pessoa->cpf_pessoa, $output);
            $this->assertStringNotContainsString('documentos/sintetico.pdf', $output);
        }

        $this->assertNotNull($pessoa->fresh());
        $this->assertNotNull($token->fresh());
        $this->assertNotNull($documento->fresh());
        $this->assertSame(1, $pessoa->assinaturas()->count());
        $this->assertSame(1, $pessoa->notificacoes()->count());
        Storage::disk('local')->assertExists('documentos/sintetico.pdf');
    }

    public function test_preview_excludes_approved_organizer_admin_in_review_cancelled_and_not_yet_due(): void
    {
        $candidate = $this->pessoa(1);
        $this->pessoa(2, 'voluntario', 'aprovado');
        $this->pessoa(3, 'organizador');
        $this->pessoa(4, 'administrador');
        $this->pessoa(5, 'voluntario', 'em_analise');
        $this->pessoa(6, 'voluntario', 'aguardando_validacao', false);
        $this->pessoa(7, 'voluntario', 'aguardando_validacao', true, false);
        $this->ong(1);
        $this->ong(2, 'ativo');
        $this->ong(3, 'aguardando aprovação', false);
        $this->ong(4, 'aguardando aprovação', true, false);

        $this->assertSame(0, Artisan::call('app:excluir-contas-pendentes --dry-run'));
        $this->assertStringContainsString('1 Pessoas e 1 ONGs candidatas', Artisan::output());
        $this->assertSame(7, Pessoa::count());
        $this->assertSame(4, Ong::count());
        $this->assertNotNull($candidate->fresh());
    }

    public function test_execute_is_suspended_even_for_candidate_and_after_state_changes(): void
    {
        $pessoa = $this->pessoa(1);
        $ong = $this->ong(1);
        $this->assertSame(0, Artisan::call('app:excluir-contas-pendentes --dry-run'));

        $pessoa->forceFill(['status_validacao' => 'aprovado'])->save();
        $this->assertSame(1, Artisan::call('app:excluir-contas-pendentes --execute'));
        $this->assertStringContainsString('Exclusão efetiva suspensa', Artisan::output());
        $this->assertSame(1, Artisan::call('app:excluir-contas-pendentes --execute'));
        $this->assertNotNull($pessoa->fresh());
        $this->assertNotNull($ong->fresh());
        $this->assertSame('aprovado', $pessoa->fresh()->status_validacao);
    }

    public function test_schedule_listing_is_dry_run_and_does_not_query_mysql_when_cache_is_array(): void
    {
        config()->set('cache.default', 'array');
        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->sql;
        });

        $this->assertSame(0, Artisan::call('schedule:list'));
        $output = Artisan::output();
        $this->assertStringContainsString('app:excluir-contas-pendentes --dry-run', $output);
        $this->assertStringContainsString('documentos:excluir-retidos --dry-run --limit=100', $output);
        $this->assertStringNotContainsString('imagens:reconciliar', $output);
        $this->assertSame([], $queries);
    }
}

<?php

namespace App\Services;

use App\Models\AdminLog;
use App\Models\Permissao;
use App\Models\Pessoa;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SyntheticAdminProvisioner
{
    public const BATCH = 'HML-V1-20261020-F82A530';

    public const LOGIN = 'hmlv1_20261020_admin';

    public const PERMISSIONS = ['eventos.aprovar', 'ongs.validar', 'usuarios.validar'];

    private const NAME = '[HML - NAO REAL] Admin F82A530';

    private const EMAIL = 'hmlv1_20261020_admin@example.test';

    private const CPF = '00000008419';

    public function inspect(): string
    {
        $pessoa = Pessoa::where('login_pessoa', self::LOGIN)->first();

        return $pessoa ? $this->state($pessoa) : 'absent';
    }

    public function provision(string $password, string $operator): string
    {
        $this->validateOperator($operator);

        return DB::transaction(function () use ($password, $operator) {
            $pessoa = Pessoa::where('login_pessoa', self::LOGIN)->lockForUpdate()->first();
            if ($pessoa) {
                if ($this->state($pessoa) !== 'active') {
                    throw new DomainException('Conta revogada não pode ser reativada por este procedimento.');
                }

                return 'unchanged';
            }
            if (strlen($password) < 20 || strlen($password) > 72) {
                throw new DomainException('A senha deve ter entre 20 e 72 bytes.');
            }
            // Never adopt a pre-existing person, even when its login differs.
            if (Pessoa::where('email_pessoa', self::EMAIL)->orWhere('cpf_pessoa', self::CPF)->exists()) {
                throw new DomainException('Identidade sintética em conflito; nenhum registro foi adotado.');
            }
            $pessoa = new Pessoa;
            $pessoa->forceFill([
                'nm_pessoa' => self::NAME, 'login_pessoa' => self::LOGIN,
                'email_pessoa' => self::EMAIL, 'cpf_pessoa' => self::CPF,
                'genero_pessoa' => 'prefiro não dizer', 'dt_nasc' => '1995-01-01',
                'rg_pessoa' => 'HML-NAO-REAL', 'tele_pessoa' => '11000000000',
                'cep_pessoa' => '00000000', 'logradouro_pessoa' => 'Rua Ficticia HML',
                'compl_pessoa' => self::BATCH, 'cidade_pessoa' => 'Cidade Sintetica',
                'bairro_pessoa' => 'Bairro Teste', 'uf_pessoa' => 'SP',
                'pfp_pessoa_link' => 'https://example.test/hml/admin.png',
                'senha_pessoa' => Hash::make($password), 'role_pessoa' => 'administrador',
                'admin_master' => false, 'status_validacao' => 'aprovado', 'aprovado_em' => now(),
            ])->save();
            $ids = [];
            foreach (self::PERMISSIONS as $name) {
                $ids[] = Permissao::firstOrCreate(['nome' => $name])->getKey();
            }
            $pessoa->permissoes()->sync($ids);
            $this->audit($pessoa, 'provisionado', $operator);

            return 'created';
        });
    }

    public function revoke(string $operator): string
    {
        $this->validateOperator($operator);

        return DB::transaction(function () use ($operator) {
            $pessoa = Pessoa::where('login_pessoa', self::LOGIN)->lockForUpdate()->first();
            if (! $pessoa) {
                return 'absent';
            }
            $state = $this->state($pessoa);
            if ($state === 'revoked') {
                return 'unchanged';
            }
            $pessoa->tokens()->delete();
            $pessoa->permissoes()->detach();
            $pessoa->forceFill([
                'role_pessoa' => 'voluntario', 'admin_master' => false,
                'status_validacao' => 'reprovado_definitivamente',
                'reprovado_definitivamente_em' => now(),
                // Pessoa login does not check validation status: rotate the password too.
                'senha_pessoa' => Hash::make(Str::random(64)),
            ])->save();
            $this->audit($pessoa, 'revogado', $operator);

            return 'revoked';
        });
    }

    private function validateOperator(string $operator): void
    {
        if (! preg_match('/^[A-Za-z0-9][A-Za-z0-9_.:-]{2,79}$/D', $operator)) {
            throw new DomainException('Identificador não secreto de operador/ticket inválido.');
        }
    }

    private function state(Pessoa $pessoa): string
    {
        $provenance = AdminLog::where('acao', 'homologacao.admin.provisionado')
            ->where('recurso_type', Pessoa::class)->where('recurso_id', $pessoa->getKey())
            ->get()->contains(fn (AdminLog $log) => ($log->detalhes['lote'] ?? null) === self::BATCH);
        if (! $provenance || $pessoa->nm_pessoa !== self::NAME || $pessoa->email_pessoa !== self::EMAIL
            || $pessoa->cpf_pessoa !== self::CPF || $pessoa->admin_master || $pessoa->exclusao_pendente || $pessoa->deletar_em) {
            throw new DomainException('Identidade, proveniência ou estado divergente; intervenção recusada.');
        }
        $revoked = AdminLog::where('acao', 'homologacao.admin.revogado')
            ->where('recurso_type', Pessoa::class)->where('recurso_id', $pessoa->getKey())->exists();
        $permissions = $pessoa->permissoes()->orderBy('nome')->pluck('nome')->all();
        if ($revoked && $pessoa->role_pessoa === 'voluntario' && $pessoa->status_validacao === 'reprovado_definitivamente'
            && $permissions === [] && ! $pessoa->tokens()->exists()) {
            return 'revoked';
        }
        if (! $revoked && $pessoa->role_pessoa === 'administrador' && $pessoa->status_validacao === 'aprovado'
            && $permissions === self::PERMISSIONS) {
            return 'active';
        }
        throw new DomainException('Privilégios divergentes; nenhuma reconciliação automática foi aplicada.');
    }

    private function audit(Pessoa $pessoa, string $action, string $operator): void
    {
        AdminLog::create([
            // The initiating actor is the OS operator, not an authenticated API user.
            'pessoa_id' => null, 'acao' => 'homologacao.admin.'.$action,
            'recurso_type' => Pessoa::class, 'recurso_id' => $pessoa->getKey(),
            'detalhes' => ['lote' => self::BATCH, 'operador' => $operator, 'origem' => 'artisan',
                'admin_master' => false, 'permissoes' => self::PERMISSIONS],
        ]);
    }
}

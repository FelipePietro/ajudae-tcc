<?php

namespace App\Console\Commands;

use App\Services\SyntheticAdminProvisioner;
use DomainException;
use Illuminate\Console\Command;
use Throwable;

class HomologacaoAdmin extends Command
{
    protected $signature = 'homologacao:admin {action=plan : plan, provision ou revoke}
        {--apply : Autoriza explicitamente a escrita}
        {--confirm-batch= : Deve coincidir com o lote fixo}
        {--operator= : Identificador não secreto do operador/ticket}';

    protected $description = 'Provisiona/revoga somente o administrador sintético do lote, sem Admin Master';

    public function handle(SyntheticAdminProvisioner $provisioner): int
    {
        $action = $this->argument('action');
        if (! in_array($action, ['plan', 'provision', 'revoke'], true)) {
            $this->error('Ação inválida.');

            return self::FAILURE;
        }
        try {
            $state = $provisioner->inspect();
            $this->line('Lote: '.SyntheticAdminProvisioner::BATCH.'; estado: '.$state.'; Admin Master: não.');
            if ($action === 'plan' || ! $this->option('apply')) {
                $this->line('Somente leitura. Nenhuma conta, permissão, token ou log foi alterado.');

                return self::SUCCESS;
            }
            $operator = (string) $this->option('operator');
            if ($this->option('confirm-batch') !== SyntheticAdminProvisioner::BATCH
                || ! preg_match('/^[A-Za-z0-9][A-Za-z0-9_.:-]{2,79}$/D', $operator)) {
                throw new DomainException('Informe lote exato e identificador não secreto de operador/ticket.');
            }
            if ($action === 'revoke') {
                $result = $provisioner->revoke($operator);
            } elseif ($state === 'active') {
                $result = $provisioner->provision('', $operator);
            } else {
                if ($state === 'revoked') {
                    throw new DomainException('Conta revogada não pode ser reativada.');
                }
                if (! $this->input->isInteractive()) {
                    throw new DomainException('Provisionamento novo exige terminal interativo com senha oculta.');
                }
                // Disable fallback: never echo a password when hidden input is unavailable.
                $password = $this->secret('Senha exclusiva (20–72 bytes)', false);
                $confirmation = $this->secret('Confirme a senha', false);
                if (! is_string($password) || ! is_string($confirmation) || ! hash_equals($password, $confirmation)) {
                    throw new DomainException('Confirmação de senha inválida.');
                }
                $result = $provisioner->provision($password, $operator);
                unset($password, $confirmation);
            }
            $this->info('Resultado: '.$result.'. Nenhuma credencial foi emitida.');

            return self::SUCCESS;
        } catch (DomainException $exception) {
            $this->error($exception->getMessage());
        } catch (Throwable) {
            // Do not report query bindings or stack traces containing secrets.
            $this->error('Falha operacional; transação revertida. Consulte o estado sem exportar credenciais.');
        }

        return self::FAILURE;
    }
}

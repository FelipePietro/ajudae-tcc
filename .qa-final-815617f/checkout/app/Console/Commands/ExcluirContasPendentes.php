<?php

namespace App\Console\Commands;

use App\Models\Ong;
use App\Models\Pessoa;
use Illuminate\Console\Command;

class ExcluirContasPendentes extends Command
{
    protected $signature = 'app:excluir-contas-pendentes
        {--dry-run : Mostra apenas contagens, sem excluir contas ou arquivos}
        {--execute : Solicita execução efetiva (suspensa até definição de retenção e LGPD)}';

    protected $description = 'Pré-visualiza contas com exclusão solicitada e prazo vencido; exclusão efetiva suspensa';

    public function handle(): int
    {
        if ($this->option('execute')) {
            $this->error('Exclusão efetiva suspensa: definir retenção de aceites, notificações, LGPD e histórico antes de habilitá-la.');

            return self::FAILURE;
        }

        // A ausência de --execute é deliberadamente equivalente a --dry-run.
        // Essas contagens são candidatas, não autorização para hard-delete:
        // cadastros normais já possuem aceite de termos e podem ter outros vínculos.
        $pessoas = Pessoa::query()
            ->where('exclusao_pendente', true)
            ->where('deletar_em', '<=', now())
            ->where('role_pessoa', 'voluntario')
            ->where('status_validacao', 'aguardando_validacao')
            ->count();
        $ongs = Ong::query()
            ->where('exclusao_pendente', true)
            ->where('deletar_em', '<=', now())
            ->where('status_ong', 'aguardando aprovação')
            ->count();

        $this->info("Pré-visualização: {$pessoas} Pessoas e {$ongs} ONGs candidatas. Nenhuma conta, token, notificação ou arquivo foi excluído.");

        return self::SUCCESS;
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class LegacyCertificateMigrationTest extends TestCase
{
    public function test_legacy_schema_with_rows_stops_safely_and_empty_schema_upgrades(): void
    {
        if (getenv('RUN_CERT_UPGRADE_TEST') !== '1') {
            $this->markTestSkipped('Exige banco MySQL descartável separado e RUN_CERT_UPGRADE_TEST=1.');
        }
        $current = config('database.connections.mysql');
        if (config('database.default') !== 'mysql'
            || $current['database'] !== 'ajudae_tcc_testing'
            || (string) $current['port'] !== '3307'
            || $current['host'] !== '127.0.0.1') {
            throw new RuntimeException('Teste de upgrade recusado: conexão MySQL temporária inesperada.');
        }
        config()->set('database.connections.cert_upgrade', array_merge($current, [
            'database' => 'ajudae_tcc_upgrade_testing',
        ]));
        $db = DB::connection('cert_upgrade');
        if ($db->getDatabaseName() !== 'ajudae_tcc_upgrade_testing') {
            throw new RuntimeException('Banco de upgrade inesperado.');
        }
        $schema = Schema::connection('cert_upgrade');
        // Banco descartável dedicado: remover também tabelas auxiliares de uma
        // execução anterior, que podem conter FKs para estas quatro tabelas.
        $schema->dropAllTables();
        $schema->create('pessoa', fn (Blueprint $table) => $table->id('pessoa_id'));
        $schema->create('evento', fn (Blueprint $table) => $table->id('evento_id'));
        $schema->create('p_ag_inscreve', fn (Blueprint $table) => $table->id('inscricao_id'));
        // Estrutura copiada do ZIP operacional de 2026-10-12, antes da edição retroativa.
        $schema->create('certificado', function (Blueprint $table) {
            $table->id('certificado_id');
            $table->foreignId('inscricao_id')->unique()->constrained('p_ag_inscreve', 'inscricao_id')->restrictOnDelete();
            $table->string('codigo', 64)->unique();
            $table->string('arquivo_path', 255)->nullable();
            $table->timestamp('disponibilizado_em')->nullable();
            $table->timestamps();
        });
        $db->table('pessoa')->insert(['pessoa_id' => 1]);
        $db->table('evento')->insert(['evento_id' => 1]);
        $db->table('p_ag_inscreve')->insert(['inscricao_id' => 1]);
        $db->table('certificado')->insert([
            'inscricao_id' => 1, 'codigo' => 'LEGACY-SYNTHETIC-001',
            'arquivo_path' => 'legacy/synthetic.pdf', 'disponibilizado_em' => now(),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $previous = DB::getDefaultConnection();
        DB::setDefaultConnection('cert_upgrade');
        try {
            $migration = require database_path('migrations/2026_10_15_000000_upgrade_legacy_certificado_schema.php');
            try {
                $migration->up();
                $this->fail('A migration deveria interromper certificados legados emitidos.');
            } catch (RuntimeException $e) {
                $this->assertStringContainsString('certificados legados', $e->getMessage());
            }
            $this->assertFalse($schema->hasColumn('certificado', 'evento_id'));
            $this->assertSame('LEGACY-SYNTHETIC-001', $db->table('certificado')->value('codigo'));
            $this->assertSame('legacy/synthetic.pdf', $db->table('certificado')->value('arquivo_path'));

            // Caminho de upgrade sem certificados antigos: mantém colunas legadas,
            // acrescenta o contrato novo e aceita uma emissão nova.
            $db->table('certificado')->delete();
            $migration->up();
            $this->assertTrue($schema->hasColumn('certificado', 'codigo_verificacao'));
            $this->assertTrue($schema->hasColumn('certificado', 'arquivo_path'));
            $db->table('certificado')->insert([
                'evento_id' => 1, 'pessoa_id' => 1,
                'codigo_verificacao' => 'AJD-SYNTHETIC-NEW', 'total_minutos' => 60,
                'nome_participante' => 'Pessoa fictícia', 'nome_evento' => 'Evento fictício',
                'nome_responsavel' => 'ONG fictícia', 'emitido_em' => now(),
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $this->assertSame(60, $db->table('certificado')->value('total_minutos'));
            $migration->up(); // idempotência no schema atual
            $this->assertSame(1, $db->table('certificado')->count());
        } finally {
            DB::setDefaultConnection($previous);
        }
    }
}

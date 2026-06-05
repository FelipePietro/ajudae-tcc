<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ong', function (Blueprint $table) {
            $table->id('ong_id');
            $table->string('nome_fantasia', 100);
            $table->char('cnpj_ong', 14) ->unique();
            $table->string('descricao_ong', 256);
            $table->string('pfp_ong_link', 255);
            $table->string('email_ong', 100);
            $table->string('tel_ong', 15);
            $table->string('logradouro_ong', 100);
            $table->string('cidade_ong', 100);
            $table->char('uf_ong', 2);
            $table->char('cep_ong', 8);
            $table->string('bairro_ong', 100);
            $table->string('site_url_ong', 255);
            $table->enum('status_ong', ['ativo', 'aguardando aprovação', 'reprovado']);
            $table->string('nome_responsavel_ong', 100);
            $table->string('tel_responsavel_ong', 15);
            $table->string('email_responsavel_ong', 100);
            $table->char('cpf_responsavel_ong', 11) ->unique();
            $table->string('rg_responsavel_ong_link', 255);
            $table->string('login_ong', 64) ->unique();
            $table->char('senha_ong', 64);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ong');
    }
};

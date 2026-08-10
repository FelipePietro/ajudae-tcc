<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pessoa', function (Blueprint $table) {
            $table->id('pessoa_id');
            $table->string('nm_pessoa', 64);
            $table->enum('genero_pessoa', ['masculino', 'feminino', 'outro', 'prefiro não dizer']);
            $table->char('cpf_pessoa', 11) ->unique();
            $table->date('dt_nasc');
            $table->string('email_pessoa', 128);
            $table->char('tele_pessoa', 11);
            $table->char('cep_pessoa', 8);
            $table->string('logradouro_pessoa', 64);
            $table->string('compl_pessoa', 64);
            $table->string('cidade_pessoa', 64);
            $table->string('bairro_pessoa', 64);
            $table->char('uf_pessoa', 2);
            $table->enum('role_pessoa', ['voluntario', 'organizador', 'administrador']);
            $table->integer('xp_pessoa')->default(0);
            $table->decimal('avaliacao_pessoa', 3, 2)->default(0);
            $table->string('rg_pessoa', 20);
            $table->string('antecedentes_pessoa_link', 255);
            $table->string('cnh_pessoa_link', 255);
            $table->string('login_pessoa', 64)->unique();
            $table->char('senha_pessoa', 64);
            $table->string('pfp_pessoa_link', 255);
            $table->string('rg_pessoa_link', 255);
            $table->timestamps();
            $table->timestamp('deletar_em')->nullable();
            $table->boolean('exclusao_pendente')->default(false);
            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pessoa');
    }
};

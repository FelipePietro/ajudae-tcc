<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class Pessoa extends Authenticatable
{
    use HasApiTokens, HasFactory;

    /**
     * Nome da tabela no banco.
     */
    protected $table = 'pessoa';

    /**
     * Chave primária da tabela.
     */
    protected $primaryKey = 'id_pessoa';


    public $timestamps = true;
    /**
     * Tipo da chave primária.
     * 
     */
    protected $keyType = 'int';

    /**
     * A chave primária é auto incremento.
     */
    public $incrementing = true;

    /**
     * Campos que podem ser preenchidos em massa.
     */
    protected $fillable = [
    'nm_pessoa',
    'genero_pessoa',
    'cpf_pessoa',
    'dt_nasc',
    'email_pessoa',
    'tele_pessoa',
    'cep_pessoa',
    'logradouro_pessoa',
    'compl_pessoa',
    'cidade_pessoa',
    'bairro_pessoa',
    'uf_pessoa',
    'role_pessoa',
    'xp_pessoa',
    'avaliacao_pessoa',
    'rg_pessoa',
    'antecedentes_pessoa_link',
    'cnh_pessoa_link',
    'login_pessoa',      
    'senha_pessoa',
    'pfp_pessoa_link',
    'rg_pessoa_link',
];

    /**
     * Campos ocultos em respostas JSON.
     */
    protected $hidden = [
        'senha_pessoa',
        'remember_token',
    ];

    /**
     * Conversão automática de tipos.
     */
    protected $casts = [
        'dt_nasc' => 'date',
        'dt_asc' => 'date',
    ];

    /**
     * Informa ao Laravel qual coluna contém a senha.
     */
    public function getAuthPassword()
    {
        return $this->senha_pessoa;
    }
}
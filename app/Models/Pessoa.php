<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    protected $primaryKey = 'pessoa_id';


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
    'exclusao_pendente',
    'deletar_em',
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
        'exclusao_pendente' => 'boolean',
        'deletar_em' => 'datetime',
    ];

    /**
     * Informa ao Laravel qual coluna contém a senha.
     */
    public function getAuthPassword()
    {
        return $this->senha_pessoa;
    }


    public function notificacoes(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function habilidades(): BelongsToMany
    {
        return $this->belongsToMany(
            Habilidade::class,
            'habilidade_pessoa',
            'pessoa_id',
            'habilidade_id'
        )->withPivot('nivel_habilidade');
    }

    public function causas(): BelongsToMany
    {
        return $this->belongsToMany(
            Causa::class,
            'causa_pessoa',
            'pessoa_id',
            'causa_id'
        );
    }

    public function recursos(): BelongsToMany
    {
        return $this->belongsToMany(
            Recurso::class,
            'pessoa_recurso',
            'pessoa_id',
            'recurso_id'
        )->withPivot('detalhes_recurso');
    }

    public function inscricoes(): HasMany
    {
        return $this->hasMany(
            Inscricao::class,
            'pessoa_id',
            'pessoa_id'
        );
    }
    }

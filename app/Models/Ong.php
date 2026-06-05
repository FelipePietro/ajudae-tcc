<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Ong extends Model
{
    use HasApiTokens, Notifiable;

    protected $table = 'ong';

    protected $primaryKey = 'id_ong';

    public $timestamps = false;

    protected $fillable = [
        'login_ong',
        'nome_fantasia',
        'cnpj_ong',
        'email_ong',
        'senha_ong',
        'tel_ong',
        'cep_ong',
        'logradouro_ong',
        'bairro_ong',
        'cidade_ong',
        'uf_ong',
        'descricao_ong',
        'site_url_ong',
        'pfp_ong_link',
        'nome_responsavel_ong',
        'cpf_responsavel_ong',
        'rg_responsavel_ong_link',
        'email_responsavel_ong',
        'tel_responsavel_ong',
        
    ];


    protected $hidden = [
        'senha_ong',
    ];

    public function eventos()
    {
        return $this->hasMany(Evento::class);
    }   

    public function assinatura()
    {
        return $this->hasOne(Assinatura::class);
    }
}
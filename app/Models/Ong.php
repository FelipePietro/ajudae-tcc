<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Ong extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'ong';

    protected $primaryKey = 'ong_id';

    public $timestamps = true;

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
        'cnpj_ong',
        'cpf_responsavel_ong',
        'rg_responsavel_ong_link',
        'email_responsavel_ong',
        'tel_responsavel_ong',
        'exclusao_pendente',
        'deletar_em',
        'managed_image_disk',
        'managed_image_path',
        'managed_image_mime',
        'managed_image_size',
    ];

    protected $casts = [
        'exclusao_pendente' => 'boolean',
        'deletar_em' => 'datetime',
    ];

    public function getAuthPassword(): string
    {
        return $this->senha_ong;
    }

    public function eventos()
    {
        return $this->hasMany(Evento::class, 'ong_id', 'ong_id');
    }

    public function vinculosOrganizadores()
    {
        return $this->hasMany(VinculoOngOrganizador::class, 'ong_id', 'ong_id');
    }

    public function assinaturas()
    {
        return $this->morphMany(Assinatura::class, 'assinante');
    }

    public function notificacoes()
    {
        return $this->morphMany(Notification::class, 'destinatario');
    }
}

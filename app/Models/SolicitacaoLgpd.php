<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitacaoLgpd extends Model
{
    protected $table = 'solicitacao_lgpd';

    protected $primaryKey = 'solicitacao_lgpd_id';

    protected $fillable = [
        'solicitante_type',
        'solicitante_id',
        'tipo',
        'status',
        'descricao',
        'resposta',
        'respondida_por',
        'respondida_em',
    ];

    protected $casts = ['respondida_em' => 'datetime'];

    public function solicitante()
    {
        return $this->morphTo();
    }
}

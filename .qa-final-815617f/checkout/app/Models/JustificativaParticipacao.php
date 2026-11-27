<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JustificativaParticipacao extends Model
{
    protected $table = 'justificativa_participacao';

    protected $primaryKey = 'justificativa_id';

    protected $fillable = ['inscricao_id', 'motivo'];

    protected $hidden = ['motivo', 'motivo_decisao', 'decidido_por_tipo', 'decidido_por_id'];

    protected $casts = ['decidido_em' => 'datetime'];
}

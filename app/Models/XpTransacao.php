<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XpTransacao extends Model
{
    protected $table = 'xp_transacao';

    protected $primaryKey = 'xp_transacao_id';

    protected $guarded = ['*'];

    protected $casts = [
        'campanhas_aplicadas' => 'array',
        'multiplicador_duracao' => 'decimal:6',
        'multiplicador_tematico' => 'decimal:6',
        'concedido_em' => 'datetime',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VinculoOngOrganizador extends Model
{
    protected $table = 'vinculo_ong_organizador';

    protected $primaryKey = 'vinculo_ong_organizador_id';

    protected $fillable = [
        'ong_id',
        'pessoa_id',
        'status',
        'convidado_em',
        'respondido_em',
        'encerrado_em',
    ];

    protected $casts = [
        'convidado_em' => 'datetime',
        'respondido_em' => 'datetime',
        'encerrado_em' => 'datetime',
    ];

    public function ong(): BelongsTo
    {
        return $this->belongsTo(Ong::class, 'ong_id', 'ong_id');
    }

    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id', 'pessoa_id');
    }
}

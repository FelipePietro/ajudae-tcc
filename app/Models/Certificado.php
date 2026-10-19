<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificado extends Model
{
    protected $table = 'certificado';

    protected $primaryKey = 'certificado_id';

    protected $fillable = [
        'evento_id', 'pessoa_id', 'codigo_verificacao', 'total_minutos',
        'nome_participante', 'nome_evento', 'nome_responsavel', 'emitido_em',
    ];

    protected $casts = ['total_minutos' => 'integer', 'emitido_em' => 'datetime'];

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class, 'evento_id', 'evento_id');
    }

    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id', 'pessoa_id');
    }
}

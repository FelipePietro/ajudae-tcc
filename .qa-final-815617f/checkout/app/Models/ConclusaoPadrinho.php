<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConclusaoPadrinho extends Model
{
    protected $table = 'conclusao_padrinho';

    protected $primaryKey = 'conclusao_padrinho_id';

    protected $fillable = [
        'acompanhamento_id', 'apadrinhado_id', 'status', 'solicitada_em',
        'admin_id', 'decidida_em', 'motivo_decisao',
    ];

    protected $casts = ['solicitada_em' => 'datetime', 'decidida_em' => 'datetime'];

    public function acompanhamento(): BelongsTo
    {
        return $this->belongsTo(Acompanhamento::class, 'acompanhamento_id', 'acompanhamento_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitacaoPadrinho extends Model
{
    protected $table = 'solicitacao_padrinho';

    protected $primaryKey = 'solicitacao_padrinho_id';

    protected $fillable = [
        'padrinho_id', 'apadrinhado_id', 'status', 'solicitada_em',
        'decidida_em', 'acompanhamento_id',
    ];

    protected $casts = ['solicitada_em' => 'datetime', 'decidida_em' => 'datetime'];

    public function padrinho(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'padrinho_id', 'pessoa_id');
    }

    public function apadrinhado(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'apadrinhado_id', 'pessoa_id');
    }

    public function acompanhamento(): BelongsTo
    {
        return $this->belongsTo(Acompanhamento::class, 'acompanhamento_id', 'acompanhamento_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitacaoOrganizador extends Model
{
    protected $table = 'solicitacao_organizador';

    protected $primaryKey = 'solicitacao_organizador_id';

    protected $fillable = [
        'pessoa_id',
        'status',
        'justificativa',
        'admin_id',
        'motivo_decisao',
        'solicitada_em',
        'analise_iniciada_em',
        'decidida_em',
    ];

    protected $casts = [
        'solicitada_em' => 'datetime',
        'analise_iniciada_em' => 'datetime',
        'decidida_em' => 'datetime',
    ];

    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id', 'pessoa_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'admin_id', 'pessoa_id');
    }
}

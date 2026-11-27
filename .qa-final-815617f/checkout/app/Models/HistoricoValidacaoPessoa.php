<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoricoValidacaoPessoa extends Model
{
    protected $table = 'historico_validacao_pessoa';

    protected $primaryKey = 'historico_validacao_pessoa_id';

    protected $fillable = [
        'pessoa_id',
        'admin_id',
        'status_anterior',
        'status_novo',
        'motivo',
        'numero_tentativa',
        'iniciada_em',
        'concluida_em',
    ];

    protected $casts = [
        'iniciada_em' => 'datetime',
        'concluida_em' => 'datetime',
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

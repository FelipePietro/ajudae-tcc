<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvaliacaoOng extends Model
{
    protected $table = 'avaliacao_ong';

    protected $primaryKey = 'avaliacao_ong_id';

    protected $fillable = ['evento_id', 'pessoa_id', 'nota'];

    protected $casts = ['nota' => 'integer'];

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class, 'evento_id', 'evento_id');
    }

    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id', 'pessoa_id');
    }
}

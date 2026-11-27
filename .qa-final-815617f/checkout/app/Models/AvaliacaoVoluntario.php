<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvaliacaoVoluntario extends Model
{
    protected $table = 'avaliacao_voluntario';

    protected $primaryKey = 'avaliacao_voluntario_id';

    protected $fillable = ['evento_id', 'pessoa_id', 'nota', 'avaliador_tipo', 'avaliador_id'];

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

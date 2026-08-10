<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inscricao extends Model
{
    protected $table = 'p_ag_inscreve';

    protected $primaryKey = 'inscricao_id';

    public $timestamps = false;

    protected $fillable = [
        'agenda_id',
        'pessoa_id',
        'status_inscricao',
        'dt_inscricao',
    ];

    protected $casts = [
        'dt_inscricao' => 'datetime',
    ];

    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(
            Pessoa::class,
            'pessoa_id',
            'pessoa_id'
        );
    }

    public function agenda(): BelongsTo
    {
        return $this->belongsTo(
            Agenda::class,
            'agenda_id',
            'agenda_id'
        );
    }
}
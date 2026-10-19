<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inscricao extends Model
{
    protected $table = 'p_ag_inscreve';

    protected $primaryKey = 'inscricao_id';

    public $timestamps = true;

    protected $fillable = [
        'agenda_id',
        'pessoa_id',
        'status_inscricao',
        'dt_inscricao',
        'desistiu_em',
        'desistencia_tardia',
        'desistencia_sem_penalidade',
        'desistencia_sem_penalidade_ate',
        'presenca_confirmada_em',
        'presenca',
        'carga_horaria',
    ];

    protected $casts = [
        'dt_inscricao' => 'datetime',
        'desistiu_em' => 'datetime',
        'desistencia_tardia' => 'boolean',
        'desistencia_sem_penalidade' => 'boolean',
        'desistencia_sem_penalidade_ate' => 'datetime',
        'presenca_confirmada_em' => 'datetime',
        'presenca' => 'boolean',
        'carga_horaria' => 'decimal:2',
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

    public function monitoria()
    {
        return $this->hasOne(Monitoria::class, 'inscricao_id', 'inscricao_id');
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Monitoria extends Model
{
    protected $table = 'monitoria';

    protected $primaryKey = 'monitoria_id';

    protected $fillable = ['inscricao_id', 'monitor_id', 'presenca', 'carga_horaria', 'validado_em', 'observacao'];

    protected $casts = [
        'presenca' => 'boolean',
        'carga_horaria' => 'decimal:2',
        'validado_em' => 'datetime',
    ];
}

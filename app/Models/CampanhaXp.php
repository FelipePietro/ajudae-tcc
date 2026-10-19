<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampanhaXp extends Model
{
    protected $table = 'campanha_xp';

    protected $primaryKey = 'campanha_xp_id';

    protected $fillable = ['nome', 'multiplicador', 'inicio', 'fim', 'ativa', 'cat_evento_id'];

    protected $casts = [
        'multiplicador' => 'decimal:6',
        'inicio' => 'datetime',
        'fim' => 'datetime',
        'ativa' => 'boolean',
    ];
}

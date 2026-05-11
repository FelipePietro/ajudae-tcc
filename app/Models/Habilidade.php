<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Habilidade extends Model
{
    use HasFactory;
    protected $table = 'habilidade';

    protected $fillable = [
        'nome_Habilidade',
        'descricao_Habilidade'
    ];
}
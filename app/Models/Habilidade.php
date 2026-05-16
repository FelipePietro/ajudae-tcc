<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Habilidade extends Model
{
    use HasFactory;
    protected $table = 'habilidade';
    protected $primaryKey = 'id_habilidade';
    public $timestamps = false;

    protected $fillable = [
        'nome_habilidade',
        'descricao_habilidade'
    ];
}
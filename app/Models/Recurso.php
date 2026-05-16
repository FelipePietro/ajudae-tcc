<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recurso extends Model
{
    use HasFactory;
    protected $table = 'recurso';
    protected $primaryKey = 'id_recurso';
    public $timestamps = false;

    protected $fillable = [
        'nome_recurso',
        'descricao_recurso'
    ];
}
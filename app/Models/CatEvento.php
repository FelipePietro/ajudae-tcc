<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatEvento extends Model
{
    use HasFactory;
    protected $table = 'cat_evento';

    protected $fillable = [
        'nome_CatEvento',
    ];
}
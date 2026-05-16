<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Causa extends Model
{
    use HasFactory;
    protected $table = 'causa';
    protected $primaryKey = 'id_causa';
    public $timestamps = false;

    protected $fillable = [
        'nome_causa',
        'descricao_causa'
    ];
}
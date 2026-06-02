<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class cat_evento extends Model
{
    use HasFactory;
    protected $table = 'cat_evento';
    protected $primaryKey = 'cat_evento_id';
    public $timestamps = false;

    protected $fillable = [
        'nome_categoria'
    ];


    
}

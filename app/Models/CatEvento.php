<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatEvento extends Model
{
    use HasFactory;

    protected $table = 'cat_evento';

    protected $primaryKey = 'cat_evento_id';

    public $timestamps = false;

    protected $fillable = [
        'nome_categoria',
    ];

    public function eventos(): HasMany
    {
        return $this->hasMany(
            Evento::class,
            'cat_evento_id',
            'cat_evento_id'
        );
    }
}
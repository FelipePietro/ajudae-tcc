<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Recurso extends Model
{
    use HasFactory;
    protected $table = 'recurso';
    protected $primaryKey = 'recurso_id';
    public $timestamps = false;

    protected $fillable = [
        'nome_recurso',
        'descricao_recurso'
    ];

    public function pessoas(): BelongsToMany
    {
        return $this->belongsToMany(Pessoa::class)
            ->withPivot('detalhes_recurso');
    }
}

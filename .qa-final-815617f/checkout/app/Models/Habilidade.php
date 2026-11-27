<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Habilidade extends Model
{
    protected $table = 'habilidade';

    protected $primaryKey = 'habilidade_id';

    public $timestamps = false;

    protected $fillable = [
        'nome_habilidade',
        'descricao_habilidade',
    ];

    public function pessoas(): BelongsToMany
    {
        return $this->belongsToMany(
            Pessoa::class,
            'habilidade_pessoa',
            'habilidade_id',
            'pessoa_id'
        )->withPivot('nivel_habilidade');
    }

    public function eventos()
    {
        return $this->belongsToMany(
            Evento::class,
            'evento_habilidade',
            'habilidade_id',
            'evento_id'
        );
    }
}

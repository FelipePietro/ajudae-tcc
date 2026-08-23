<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Evento extends Model
{
    protected $table = 'evento';

    protected $primaryKey = 'evento_id';

    public $timestamps = true;

    protected $fillable = [
        'logradouro_evento',
        'cep_evento',
        'cidade_evento',
        'bairro_evento',
        'uf_evento',

        'compl_evento',

        'nm_evento',
        'descricao_evento',
        'vagas_evento',
        'modalidade_evento',
        'status_evento',
        'imagem_evento_link',

        'ong_id',
        'pessoa_id',
        'cat_evento_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function ong(): BelongsTo
    {
        return $this->belongsTo(
            Ong::class,
            'ong_id',
            'ong_id'
        );
    }

    public function pessoa()
    {
        return $this->belongsTo(
            Pessoa::class,
            'pessoa_id',
            'pessoa_id'
        );
}

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(
            CatEvento::class,
            'cat_evento_id',
            'cat_evento_id'
        );
    }

    public function habilidades()
    {
        return $this->belongsToMany(
            Habilidade::class,
            'evento_habilidade',
            'evento_id',
            'habilidade_id'
        );
    }
}
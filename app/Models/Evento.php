<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'nm_evento',
        'descricao_evento',
        'status_evento',
        'imagem_evento_link',
    ];

    protected $casts = [
        'status_evento' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function ong()
    {
        return $this->belongsTo(Ong::class, 'ong_id');
    }

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }
}

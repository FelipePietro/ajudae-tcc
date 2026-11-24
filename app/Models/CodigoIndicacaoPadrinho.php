<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CodigoIndicacaoPadrinho extends Model
{
    protected $table = 'codigo_indicacao_padrinho';

    public $timestamps = false;

    protected $fillable = ['padrinho_id', 'digest', 'codigo', 'emitido_em', 'revogado_em'];

    protected $hidden = ['digest', 'codigo'];

    protected $casts = [
        'codigo' => 'encrypted',
        'emitido_em' => 'datetime',
        'revogado_em' => 'datetime',
    ];
}

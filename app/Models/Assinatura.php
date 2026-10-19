<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assinatura extends Model
{
    protected $table = 'assinatura';

    protected $primaryKey = 'assinatura_id';

    public $timestamps = true;

    protected $fillable = [
        'assinante_type',
        'assinante_id',
        'tipo_termo',
        'versao_termo',
        'aceito_em',
        'ip_assinatura',
        'documento_hash',
        'user_agent_assinatura',
    ];

    protected $casts = [
        'aceito_em' => 'datetime',
    ];

    public function assinante()
    {
        return $this->morphTo();
    }
}

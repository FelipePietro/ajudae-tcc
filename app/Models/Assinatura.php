<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assinatura extends Model
{
    protected $table = 'assinatura';

    protected $primaryKey = 'sq_assinatura';

    public $timestamps = true;

    protected $fillable = [
        'dispositivo',
        'ip_assinatura',
        'documento_url',
        'documento_hash',
        'user_agent_assinatura'
    ];
}

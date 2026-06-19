<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Assinatura extends Model
{
    protected $table = 'assinatura';

    protected $primaryKey = 'id_assinatura';

    protected $fillable = [
        'dispositivo',
        'ip_assinatura',
        'user_agent_assinatura',
        'documento_url',
        'documento_hash',
        'geoloc_assinatura',
    ];

    public function pessoas(): BelongsToMany
    {
        return $this->belongsToMany(
            Pessoa::class,
            'assinatura_pessoa',
            'id_assinatura',
            'id_pessoa',
            'id_assinatura',
            'id_pessoa'
        );
    }

    public function ongs(): BelongsToMany
    {
        return $this->belongsToMany(
            Ong::class,
            'aassinatura_ong',
            'id_assinatura',
            'id_ong',
            'id_assinatura',
            'id_ong'
        );
    }
}

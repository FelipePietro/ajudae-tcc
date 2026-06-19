<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ong extends Model
{
    protected $table = 'ong';

    protected $primaryKey = 'id_ong';

    public function assinaturas(): BelongsToMany
    {
        return $this->belongsToMany(
            Assinatura::class,
            'aassinatura_ong',
            'id_ong',
            'id_assinatura',
            'id_ong',
            'id_assinatura'
        );
    }
}

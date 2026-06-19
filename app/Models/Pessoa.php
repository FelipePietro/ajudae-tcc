<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Pessoa extends Model
{
    protected $table = 'pessoa';

    protected $primaryKey = 'id_pessoa';

    public function assinaturas(): BelongsToMany
    {
        return $this->belongsToMany(
            Assinatura::class,
            'assinatura_pessoa',
            'id_pessoa',
            'id_assinatura',
            'id_pessoa',
            'id_assinatura'
        );
    }
}

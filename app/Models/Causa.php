<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Causa extends Model
{
    use HasFactory;

    protected $table = 'causa';

    protected $primaryKey = 'causa_id';

    public $timestamps = false;

    protected $fillable = [
        'nome_causa',
        'descricao_causa',
    ];

    public function pessoas(): BelongsToMany
    {
        return $this->belongsToMany(
            Pessoa::class,
            'causa_pessoa',
            'causa_id',
            'pessoa_id'
        );
    }
}

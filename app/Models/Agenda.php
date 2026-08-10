<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agenda extends Model
{
    protected $table = 'agenda';

    protected $primaryKey = 'agenda_id';

    public $timestamps = true;

    protected $fillable = [
        'status_ativo',
        'data_inicio',
        'data_fim',
        'evento_id'
    ];

    protected $casts = [
        'status_ativo' => 'string',
        'data_inicio' => 'datetime',
        'data_fim' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function evento()
    {
        return $this->belongsTo(Evento::class, 'evento_id');
    }

    /**
     * Ativar a agenda (alterar status para 'ativo')
     */
    public function ativar()
    {
        $this->update(['status_ativo' => 'ativo']);
        return $this;
    }

    /**
     * Finalizar a agenda (alterar status para 'finalizado')
     */
    public function finalizar()
    {
        $this->update(['status_ativo' => 'finalizado']);
        return $this;
    }

    /**
     * Verificar se está ativo
     */
    public function isAtivo()
    {
        return $this->status_ativo === 'ativo';
    }

    /**
     * Verificar se está finalizado
     */
    public function isFinalizado()
    {
        return $this->status_ativo === 'finalizado';
    }

    public function inscricoes(): HasMany
    {
        return $this->hasMany(
            Inscricao::class,
            'agenda_id',
            'agenda_id'
        );
    }
}

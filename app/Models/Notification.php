<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notificacao';

    protected $primaryKey = 'notificacao_id';

    public $timestamps = true;

    protected $fillable = [
        'status_confirmacao',
        'mensagem_notificacao',
        'tipo_notificacao',
        'destinatario_type',
        'destinatario_id',
        'agenda_id',
        'evento_id',
    ];

    public function destinatario()
    {
        return $this->morphTo();
    }

    public function agenda()
    {
        return $this->belongsTo(
            Agenda::class,
            'agenda_id',
            'agenda_id'
        );
    }

    public function evento()
    {
        return $this->belongsTo(Evento::class, 'evento_id', 'evento_id');
    }
}

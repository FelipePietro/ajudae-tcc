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
        'agenda_id',
        'pessoa_id',
    ];

    public function pessoa()
    {
        return $this->belongsTo(
            Pessoa::class,
            'pessoa_id',
            'pessoa_id'
        );
    }

    public function agenda()
    {
        return $this->belongsTo(
            Agenda::class,
            'agenda_id',
            'agenda_id'
        );
    }
}
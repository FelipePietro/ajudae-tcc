<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notificacao';

    protected $primaryKey = 'sq_notificacao';

    public $timestamps = false;

    protected $fillable = [
        'status_confirmacao',
        'mensagem_notificacao',
    ];

    // public function agenda()
    //{
    // return $this->belongsTo(Agenda::class, 'agenda_id');
    //}

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }
}
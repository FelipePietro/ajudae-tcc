<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminLog extends Model
{
    protected $table = 'admin_log';

    protected $primaryKey = 'admin_log_id';

    protected $fillable = ['pessoa_id', 'acao', 'recurso_type', 'recurso_id', 'detalhes'];

    protected $casts = [
        'detalhes' => 'array',
    ];
}

<?php

namespace Laikmosh\Plog\Models;

use Illuminate\Database\Eloquent\Model;

class PlogRequest extends Model
{
    protected $connection = 'plog';
    protected $table = 'plog_requests';

    protected $fillable = [
        'request_id',
        'method',
        'url',
        'headers',
        'body',
        'query_params',
        'cookies',
        'ip_address',
        'user_agent',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'headers' => 'array',
        'body' => 'array',
        'query_params' => 'array',
        'cookies' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function logEntries()
    {
        return $this->hasMany(PlogEntry::class, 'request_id', 'request_id');
    }
}
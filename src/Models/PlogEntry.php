<?php

namespace Laikmosh\Plog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PlogEntry extends Model
{
    protected $connection = 'plog';
    protected $table = 'plog_entries';
    public $timestamps = false;

    protected $fillable = [
        'level',
        'message',
        'context',
        'user_id',
        'session_id',
        'request_id',
        'environment',
        'endpoint',
        'file',
        'line',
        'class',
        'method',
        'tags',
        'stack_trace',
        'retention_group',
        'response_time',
    ];

    protected $casts = [
        'context' => 'array',
        'tags' => 'array',
        'stack_trace' => 'array',
        'created_at' => 'datetime',
        'response_time' => 'float',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = config('plog.database.connection', 'plog');
        $this->table = config('plog.database.table', 'plog_entries');
    }

    public function scopeLevel(Builder $query, $level)
    {
        return $query->where('level', $level);
    }

    public function scopeUser(Builder $query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRequest(Builder $query, $requestId)
    {
        return $query->where('request_id', $requestId);
    }

    public function scopeSession(Builder $query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopeEnvironment(Builder $query, $environment)
    {
        return $query->where('environment', $environment);
    }

    public function scopeEndpoint(Builder $query, $endpoint)
    {
        return $query->where('endpoint', $endpoint);
    }

    public function scopeWithTag(Builder $query, $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }

    public function scopeWithAnyTag(Builder $query, array $tags)
    {
        return $query->where(function ($q) use ($tags) {
            foreach ($tags as $tag) {
                $q->orWhereJsonContains('tags', $tag);
            }
        });
    }

    public function scopeWithAllTags(Builder $query, array $tags)
    {
        foreach ($tags as $tag) {
            $query->whereJsonContains('tags', $tag);
        }
        return $query;
    }

    public function request()
    {
        return $this->belongsTo(PlogRequest::class, 'request_id', 'request_id');
    }

    public function scopeSearch(Builder $query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('message', 'like', '%' . $search . '%')
              ->orWhere('context', 'like', '%' . $search . '%')
              ->orWhere('endpoint', 'like', '%' . $search . '%')
              ->orWhere('class', 'like', '%' . $search . '%')
              ->orWhere('method', 'like', '%' . $search . '%');
        });
    }

    public function scopeBetweenDates(Builder $query, $start, $end)
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    public function scopeRecent(Builder $query, $minutes = 60)
    {
        return $query->where('created_at', '>=', now()->subMinutes($minutes));
    }

    public function getShortFileAttribute()
    {
        if (!$this->file) {
            return null;
        }

        $parts = explode('/', $this->file);
        return end($parts);
    }

    public function getFormattedLocationAttribute()
    {
        $location = '';

        if ($this->class) {
            $location = $this->class;
            if ($this->method) {
                $location .= '::' . $this->method;
            }
        } elseif ($this->file) {
            $location = $this->short_file;
            if ($this->line) {
                $location .= ':' . $this->line;
            }
        }

        return $location;
    }

    public function getLevelColorAttribute()
    {
        return match($this->level) {
            'emergency', 'alert', 'critical', 'error' => 'red',
            'warning' => 'yellow',
            'notice', 'info' => 'blue',
            'debug' => 'gray',
            default => 'gray',
        };
    }

    public function getLevelIconAttribute()
    {
        return match($this->level) {
            'emergency', 'alert', 'critical', 'error' => '❌',
            'warning' => '⚠️',
            'notice', 'info' => 'ℹ️',
            'debug' => '🐛',
            default => '📝',
        };
    }
}
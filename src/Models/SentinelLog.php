<?php

namespace Abdelhmed\SentinelAi\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SentinelLog extends Model
{
    use MassPrunable;

    protected $guarded = ['id'];

    protected $casts = [
        'code_snippet' => 'array',
        'occurrences'  => 'integer',
        'line'         => 'integer',
        'last_seen_at' => 'datetime',
        'resolved_at'  => 'datetime',
    ];

    /* ---------------------------------------------------------------- Scopes */

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNull('resolved_at');
    }

    public function scopeResolved(Builder $query): Builder
    {
        return $query->whereNotNull('resolved_at');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $like = '%' . $term . '%';

        return $query->where(function (Builder $q) use ($like) {
            $q->where('message', 'like', $like)
              ->orWhere('exception_class', 'like', $like)
              ->orWhere('file', 'like', $like);
        });
    }

    /* ------------------------------------------------------------- Accessors */

    public function getIsResolvedAttribute(): bool
    {
        return $this->resolved_at !== null;
    }

    /** File path relative to the project root, when possible. */
    public function getShortFileAttribute(): string
    {
        return Str::after($this->file, base_path() . DIRECTORY_SEPARATOR);
    }

    public function getShortClassAttribute(): string
    {
        return class_basename($this->exception_class);
    }

    public function getAnalysisHtmlAttribute(): string
    {
        return $this->markdown($this->ai_analysis);
    }

    public function getTestHtmlAttribute(): string
    {
        return $this->markdown($this->ai_test);
    }

    /** AI output is untrusted: raw HTML is stripped before it reaches the page. */
    protected function markdown(?string $text): string
    {
        if (blank($text)) {
            return '';
        }

        return Str::markdown($text, [
            'html_input'         => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    /* --------------------------------------------------------------- Pruning */

    public function prunable(): Builder
    {
        $days = (int) config('sentinel.prune_after_days', 30);

        return static::query()->where('last_seen_at', '<', now()->subDays($days));
    }
}

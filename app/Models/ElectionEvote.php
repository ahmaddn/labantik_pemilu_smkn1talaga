<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ElectionEvote extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'elections_evote';

    protected $fillable = [
        'created_by',
        'title',
        'description',
        'type',
        'target_voter',
        'academic_year',
        'class_id',
        'start_at',
        'end_at',
        'is_published',
        'is_multi_stage',
        'max_votes_per_voter',
        'current_stage',
        'total_stages',
        'stage_schedules',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'is_published' => 'boolean',
        'is_multi_stage' => 'boolean',
        'max_votes_per_voter' => 'integer',
        'current_stage' => 'integer',
        'total_stages' => 'integer',
        'stage_schedules' => 'array',
    ];

    protected $appends = ['status'];

    /**
     * Compute dynamic election status based on timestamps.
     */
    public function getStatusAttribute(): string
    {
        $now = now();

        if ($now->lt($this->start_at)) {
            return 'upcoming';
        }

        if ($now->gte($this->start_at) && $now->lte($this->end_at)) {
            return 'ongoing';
        }

        return 'finished';
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function targetClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id', 'id');
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(CandidateEvote::class, 'election_id', 'id')->orderBy('candidate_number');
    }

    public function voterAccesses(): HasMany
    {
        return $this->hasMany(VoterAccessEvote::class, 'election_id', 'id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(VoteEvote::class, 'election_id', 'id');
    }
}

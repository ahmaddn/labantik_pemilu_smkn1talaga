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
        'is_simulation',
        'simulation_start_at',
        'simulation_end_at',
        'max_votes_per_voter',
        'vote_selection_mode',
        'current_stage',
        'total_stages',
        'stage_schedules',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'simulation_start_at' => 'datetime',
        'simulation_end_at' => 'datetime',
        'is_published' => 'boolean',
        'is_multi_stage' => 'boolean',
        'is_simulation' => 'boolean',
        'max_votes_per_voter' => 'integer',
        'current_stage' => 'integer',
        'total_stages' => 'integer',
        'stage_schedules' => 'array',
    ];

    protected $appends = ['status', 'simulation_status'];

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

    /**
     * Get maximum votes allowed per voter for a specific stage or the active stage.
     */
    public function getMaxVotesForStage(?int $stage = null): int
    {
        $targetStage = $stage ?? ($this->current_stage ?: 1);

        if ($this->is_multi_stage && is_array($this->stage_schedules)) {
            $stageKey = (string) $targetStage;
            if (isset($this->stage_schedules[$stageKey]['max_votes'])) {
                return max(1, (int) $this->stage_schedules[$stageKey]['max_votes']);
            }
            if (isset($this->stage_schedules[$targetStage]['max_votes'])) {
                return max(1, (int) $this->stage_schedules[$targetStage]['max_votes']);
            }
        }

        return max(1, (int) ($this->max_votes_per_voter ?? 1));
    }

    /**
     * Get vote selection mode ('max' = fleksibel hingga N, 'exact' = wajib tepat pas N)
     */
    public function getVoteSelectionModeForStage(?int $stage = null): string
    {
        $targetStage = $stage ?? ($this->current_stage ?: 1);

        if ($this->is_multi_stage && is_array($this->stage_schedules)) {
            $stageKey = (string) $targetStage;
            if (isset($this->stage_schedules[$stageKey]['selection_mode'])) {
                return $this->stage_schedules[$stageKey]['selection_mode'] === 'exact' ? 'exact' : 'max';
            }
            if (isset($this->stage_schedules[$targetStage]['selection_mode'])) {
                return $this->stage_schedules[$targetStage]['selection_mode'] === 'exact' ? 'exact' : 'max';
            }
        }

        return ($this->vote_selection_mode ?? 'max') === 'exact' ? 'exact' : 'max';
    }

    /**
     * Compute dynamic simulation status based on simulation timestamps.
     */
    public function getSimulationStatusAttribute(): string
    {
        if (! $this->is_simulation) {
            return 'inactive';
        }

        $now = now();

        if (! $this->simulation_start_at || ! $this->simulation_end_at) {
            return 'ongoing';
        }

        if ($now->lt($this->simulation_start_at)) {
            return 'upcoming';
        }

        if ($now->gte($this->simulation_start_at) && $now->lte($this->simulation_end_at)) {
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

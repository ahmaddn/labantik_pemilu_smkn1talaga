<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CandidateEvote extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'candidates_evote';

    protected $fillable = [
        'election_id',
        'candidate_number',
        'chairman_name',
        'vice_chairman_name',
        'photo',
        'vision_mission',
        'is_qualified',
        'eliminated_at_stage',
    ];

    protected $casts = [
        'is_qualified' => 'boolean',
        'eliminated_at_stage' => 'integer',
    ];

    public function election(): BelongsTo
    {
        return $this->belongsTo(ElectionEvote::class, 'election_id', 'id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(VoteEvote::class, 'candidate_id', 'id');
    }
}

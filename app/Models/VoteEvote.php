<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoteEvote extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'votes_evote';

    public $timestamps = false;

    protected $fillable = [
        'election_id',
        'candidate_id',
        'stage_number',
        'created_at',
    ];

    protected $casts = [
        'stage_number' => 'integer',
        'created_at' => 'datetime',
    ];

    public function election(): BelongsTo
    {
        return $this->belongsTo(ElectionEvote::class, 'election_id', 'id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(CandidateEvote::class, 'candidate_id', 'id');
    }
}

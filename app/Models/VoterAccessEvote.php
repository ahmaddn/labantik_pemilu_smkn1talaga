<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoterAccessEvote extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'voter_accesses_evote';

    protected $fillable = [
        'user_id',
        'election_id',
        'stage_number',
        'is_voted',
        'voted_at',
    ];

    protected $casts = [
        'stage_number' => 'integer',
        'is_voted' => 'boolean',
        'voted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function election(): BelongsTo
    {
        return $this->belongsTo(ElectionEvote::class, 'election_id', 'id');
    }
}

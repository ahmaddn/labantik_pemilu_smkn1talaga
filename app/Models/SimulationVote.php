<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SimulationVote extends Model
{
    protected $fillable = [
        'election_id',
        'user_id',
        'candidate_id',
        'stage',
        'voter_alias',
    ];

    public function election()
    {
        return $this->belongsTo(ElectionEvote::class, 'election_id', 'id');
    }

    public function candidate()
    {
        return $this->belongsTo(CandidateEvote::class, 'candidate_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}

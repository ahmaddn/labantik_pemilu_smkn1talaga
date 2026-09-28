<?php

namespace Tests\Feature;

use App\Models\CandidateEvote;
use App\Models\ElectionEvote;
use App\Models\User;
use App\Models\VoteEvote;
use App\Models\VoterAccessEvote;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EvoteSystemTest extends TestCase
{
    /**
     * Test election model creation and dynamic status accessor.
     */
    public function test_election_model_status_accessor(): void
    {
        $user = User::first();
        $this->assertNotNull($user, 'User should exist in eage_dev database');

        $election = new ElectionEvote([
            'created_by' => $user->id,
            'title' => 'Test Pemilihan OSIS',
            'type' => 'osis',
            'start_at' => now()->subHour(),
            'end_at' => now()->addHour(),
        ]);

        $this->assertEquals('ongoing', $election->status);
    }

    /**
     * Test atomic SQL update and single vote constraint logic.
     */
    public function test_atomic_single_vote_prevention(): void
    {
        $user = User::first();
        $this->assertNotNull($user);

        // Create temporary test election
        $election = ElectionEvote::create([
            'created_by' => $user->id,
            'title' => 'Test Concurrency Election',
            'type' => 'osis',
            'start_at' => now()->subHour(),
            'end_at' => now()->addHour(),
        ]);

        $candidate = CandidateEvote::create([
            'election_id' => $election->id,
            'candidate_number' => 1,
            'chairman_name' => 'Kandidat Uji',
        ]);

        $access = VoterAccessEvote::create([
            'election_id' => $election->id,
            'user_id' => $user->id,
            'is_voted' => false,
        ]);

        // 1st Vote Attempt -> Should succeed (affected = 1)
        $now = now();
        $affected1 = DB::update('
            UPDATE voter_accesses_evote 
            SET is_voted = 1, voted_at = ? 
            WHERE user_id = ? AND election_id = ? AND is_voted = 0
        ', [$now, $user->id, $election->id]);

        $this->assertEquals(1, $affected1);

        VoteEvote::create([
            'election_id' => $election->id,
            'candidate_id' => $candidate->id,
        ]);

        // 2nd Vote Attempt -> Must fail (affected = 0)
        $affected2 = DB::update('
            UPDATE voter_accesses_evote 
            SET is_voted = 1, voted_at = ? 
            WHERE user_id = ? AND election_id = ? AND is_voted = 0
        ', [$now, $user->id, $election->id]);

        $this->assertEquals(0, $affected2);

        // Clean up test records
        $election->delete();
    }
}

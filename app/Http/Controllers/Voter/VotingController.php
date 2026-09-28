<?php

namespace App\Http\Controllers\Voter;

use App\Http\Controllers\Controller;
use App\Models\CandidateEvote;
use App\Models\ElectionEvote;
use App\Models\VoteEvote;
use App\Models\VoterAccessEvote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class VotingController extends Controller
{
    /**
     * Show voting ballot screen for an election.
     */
    public function show(string $electionId): Response|RedirectResponse
    {
        $user = auth()->user();

        $election = ElectionEvote::with(['candidates' => function ($query) {
            $query->where('is_qualified', true)->orderBy('candidate_number', 'asc');
        }])->findOrFail($electionId);

        if ($election->status !== 'ongoing') {
            return redirect()->route('voter.dashboard')->with('error', 'Pemilihan ini belum dimulai atau telah berakhir.');
        }

        $currentStage = $election->current_stage;

        // Verify base access
        $hasBaseAccess = VoterAccessEvote::where('user_id', $user->id)
            ->where('election_id', $electionId)
            ->exists();

        if (! $hasBaseAccess) {
            return redirect()->route('voter.dashboard')->with('error', 'Anda tidak memiliki hak pilih dalam pemilihan ini.');
        }

        // Get or create voter access for current stage
        $access = VoterAccessEvote::firstOrCreate([
            'user_id' => $user->id,
            'election_id' => $electionId,
            'stage_number' => $currentStage,
        ], [
            'is_voted' => false,
            'voted_at' => null,
        ]);

        if ($access->is_voted) {
            $stageInfo = $election->is_multi_stage ? " (Tahap {$currentStage})" : '';

            return redirect()->route('voter.dashboard')->with('info', "Anda sudah memberikan suara dalam pemilihan ini{$stageInfo}.");
        }

        return Inertia::render('Voter/VoteWizard', [
            'election' => [
                'id' => $election->id,
                'title' => $election->title,
                'description' => $election->description,
                'type' => $election->type,
                'is_multi_stage' => $election->is_multi_stage,
                'current_stage' => $election->current_stage,
                'total_stages' => $election->total_stages,
                'end_at' => $election->end_at->toIso8601String(),
                'candidates' => $election->candidates->map(function ($candidate) {
                    return [
                        'id' => $candidate->id,
                        'candidate_number' => $candidate->candidate_number,
                        'chairman_name' => $candidate->chairman_name,
                        'vice_chairman_name' => $candidate->vice_chairman_name,
                        'photo' => $candidate->photo,
                        'vision_mission' => $candidate->vision_mission,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Submit vote using atomic SQL update to prevent race conditions.
     */
    public function vote(Request $request, string $electionId): RedirectResponse
    {
        $request->validate([
            'candidate_id' => ['required', 'string', 'exists:candidates_evote,id'],
        ]);

        $userId = auth()->id();
        $candidateId = $request->input('candidate_id');

        $election = ElectionEvote::findOrFail($electionId);
        if ($election->status !== 'ongoing') {
            return back()->withErrors(['vote' => 'Pemilihan sudah berakhir atau belum dimulai.']);
        }

        $currentStage = $election->current_stage;

        // Verify candidate belongs to this election and is qualified
        $candidate = CandidateEvote::where('id', $candidateId)
            ->where('election_id', $electionId)
            ->where('is_qualified', true)
            ->first();

        if (! $candidate) {
            return back()->withErrors(['vote' => 'Kandidat yang dipilih tidak valid atau sudah tereliminasi.']);
        }

        // Ensure voter access record exists for current stage
        VoterAccessEvote::firstOrCreate([
            'user_id' => $userId,
            'election_id' => $electionId,
            'stage_number' => $currentStage,
        ], [
            'is_voted' => false,
            'voted_at' => null,
        ]);

        // ATOMIC CONDITIONAL SQL UPDATE (High Concurrency Protection per Stage)
        $now = now();
        $affectedRows = DB::update('
            UPDATE voter_accesses_evote 
            SET is_voted = 1, voted_at = ? 
            WHERE user_id = ? AND election_id = ? AND stage_number = ? AND is_voted = 0
        ', [$now, $userId, $electionId, $currentStage]);

        if ($affectedRows === 0) {
            return redirect()->route('voter.dashboard')->with('error', "Hak pilih Anda untuk Tahap {$currentStage} ini sudah digunakan.");
        }

        // Insert secret vote into votes_evote (No user_id stored)
        VoteEvote::create([
            'election_id' => $electionId,
            'candidate_id' => $candidateId,
            'stage_number' => $currentStage,
            'created_at' => $now,
        ]);

        $stageMsg = $election->is_multi_stage ? " (Tahap {$currentStage})" : '';

        return redirect()->route('voter.dashboard')->with('success', "Suara Anda{$stageMsg} berhasil disimpan! Terima kasih telah berpartisipasi.");
    }
}

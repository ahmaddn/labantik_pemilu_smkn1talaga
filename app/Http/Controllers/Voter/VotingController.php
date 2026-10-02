<?php

namespace App\Http\Controllers\Voter;

use App\Http\Controllers\Controller;
use App\Models\CandidateEvote;
use App\Models\ElectionEvote;
use App\Models\SimulationVote;
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

        $isSimulationMode = (bool) $election->is_simulation;

        if ($isSimulationMode) {
            if ($election->simulation_status === 'upcoming') {
                return redirect()->route('voter.dashboard')->with('error', 'Sesi simulasi pemilihan ini belum dimulai.');
            }
            if ($election->simulation_status === 'finished') {
                return redirect()->route('voter.dashboard')->with('error', 'Sesi simulasi pemilihan ini telah berakhir.');
            }
        } else {
            if ($election->status !== 'ongoing') {
                return redirect()->route('voter.dashboard')->with('error', 'Pemilihan ini belum dimulai atau telah berakhir.');
            }
        }

        $currentStage = $election->current_stage;

        // Verify base access
        $hasBaseAccess = VoterAccessEvote::where('user_id', $user->id)
            ->where('election_id', $electionId)
            ->exists();

        if (! $hasBaseAccess) {
            return redirect()->route('voter.dashboard')->with('error', 'Anda tidak memiliki hak pilih dalam pemilihan ini.');
        }

        // Check if voter already voted (Simulation votes or Real votes)
        if ($isSimulationMode) {
            $hasVotedSimulation = SimulationVote::where('election_id', $electionId)
                ->where('user_id', $user->id)
                ->where('stage', $currentStage)
                ->exists();

            if ($hasVotedSimulation) {
                $stageInfo = $election->is_multi_stage ? " (Tahap {$currentStage})" : '';

                return redirect()->route('voter.dashboard')->with('info', "Anda sudah memberikan suara simulasi dalam uji coba ini{$stageInfo}.");
            }
        } else {
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
        }

        // Candidate data mapping with masking if simulation mode is active
        $alphabet = range('A', 'Z');
        $candidates = $election->candidates->values()->map(function ($candidate, $index) use ($isSimulationMode, $alphabet) {
            $aliasLetter = $alphabet[$index % count($alphabet)];

            return [
                'id' => $candidate->id,
                'candidate_number' => $candidate->candidate_number,
                'chairman_name' => $isSimulationMode ? "Kandidat {$aliasLetter} (Simulasi)" : $candidate->chairman_name,
                'vice_chairman_name' => $isSimulationMode ? ($candidate->vice_chairman_name ? "Wakil {$aliasLetter} (Simulasi)" : null) : $candidate->vice_chairman_name,
                'photo' => $isSimulationMode ? null : $candidate->photo,
                'vision_mission' => $isSimulationMode ? 'Visi & Misi disamarkan untuk keperluan gladi/simulasi sistem pemilihan.' : $candidate->vision_mission,
            ];
        });

        $effectiveEndTime = $isSimulationMode && $election->simulation_end_at
            ? $election->simulation_end_at->toIso8601String()
            : $election->end_at->toIso8601String();

        return Inertia::render('Voter/VoteWizard', [
            'election' => [
                'id' => $election->id,
                'title' => $isSimulationMode ? "[SIMULASI] {$election->title}" : $election->title,
                'description' => $isSimulationMode
                    ? 'Ini adalah mode gladi / simulasi. Nama dan identitas paslon disamarkan untuk menguji kelancaran sistem pemilihan.'
                    : $election->description,
                'type' => $election->type,
                'is_multi_stage' => $election->is_multi_stage,
                'is_simulation' => $isSimulationMode,
                'max_votes_per_voter' => $election->max_votes_per_voter ?? 1,
                'current_stage' => $election->current_stage,
                'total_stages' => $election->total_stages,
                'end_at' => $effectiveEndTime,
                'candidates' => $candidates,
            ],
        ]);
    }

    /**
     * Submit vote using atomic SQL update or simulation vote sandbox.
     */
    public function vote(Request $request, string $electionId): RedirectResponse
    {
        $election = ElectionEvote::findOrFail($electionId);
        $isSimulationMode = (bool) $election->is_simulation;

        if ($isSimulationMode) {
            if ($election->simulation_status !== 'ongoing') {
                return back()->withErrors(['vote' => 'Sesi simulasi pemilihan sedang tidak aktif atau sudah berakhir.']);
            }
        } else {
            if ($election->status !== 'ongoing') {
                return back()->withErrors(['vote' => 'Pemilihan sudah berakhir atau belum dimulai.']);
            }
        }

        $maxVotes = $election->max_votes_per_voter ?? 1;

        $validated = $request->validate([
            'candidate_id' => ['required_without:candidate_ids', 'nullable', 'string', 'exists:candidates_evote,id'],
            'candidate_ids' => ['required_without:candidate_id', 'nullable', 'array', 'min:1', "max:{$maxVotes}"],
            'candidate_ids.*' => ['string', 'exists:candidates_evote,id'],
        ]);

        $userId = auth()->id();
        $candidateIds = [];

        if (! empty($validated['candidate_ids'])) {
            $candidateIds = array_unique($validated['candidate_ids']);
        } elseif (! empty($validated['candidate_id'])) {
            $candidateIds = [$validated['candidate_id']];
        }

        if (empty($candidateIds)) {
            return back()->withErrors(['vote' => 'Silakan pilih setidaknya 1 kandidat.']);
        }

        if (count($candidateIds) > $maxVotes) {
            return back()->withErrors(['vote' => "Anda hanya diperbolehkan memilih maksimal {$maxVotes} kandidat."]);
        }

        $currentStage = $election->current_stage;

        // Verify all candidates belong to this election and are qualified
        $validCandidatesCount = CandidateEvote::whereIn('id', $candidateIds)
            ->where('election_id', $electionId)
            ->where('is_qualified', true)
            ->count();

        if ($validCandidatesCount !== count($candidateIds)) {
            return back()->withErrors(['vote' => 'Satu atau lebih kandidat yang dipilih tidak valid atau sudah tereliminasi.']);
        }

        // SIMULATION MODE VOTING
        if ($isSimulationMode) {
            $hasVotedSimulation = SimulationVote::where('election_id', $electionId)
                ->where('user_id', $userId)
                ->where('stage', $currentStage)
                ->exists();

            if ($hasVotedSimulation) {
                return redirect()->route('voter.dashboard')->with('error', "Hak suara simulasi Anda untuk Tahap {$currentStage} ini sudah digunakan.");
            }

            foreach ($candidateIds as $candId) {
                SimulationVote::create([
                    'election_id' => $electionId,
                    'user_id' => $userId,
                    'candidate_id' => $candId,
                    'stage' => $currentStage,
                    'voter_alias' => auth()->user()->name ?? 'Pemilih Simulasi',
                ]);
            }

            $stageMsg = $election->is_multi_stage ? " (Tahap {$currentStage})" : '';

            return redirect()->route('voter.dashboard')->with('success', "Suara simulasi Anda{$stageMsg} berhasil dicatat! Data simulasi tidak memengaruhi hasil resmi.");
        }

        // OFFICIAL VOTING (PRODUCTION)
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

        // Insert secret vote(s) into votes_evote (No user_id stored)
        foreach ($candidateIds as $candId) {
            VoteEvote::create([
                'election_id' => $electionId,
                'candidate_id' => $candId,
                'stage_number' => $currentStage,
                'created_at' => $now,
            ]);
        }

        $stageMsg = $election->is_multi_stage ? " (Tahap {$currentStage})" : '';

        return redirect()->route('voter.dashboard')->with('success', "Suara Anda{$stageMsg} berhasil disimpan! Terima kasih telah berpartisipasi.");
    }
}

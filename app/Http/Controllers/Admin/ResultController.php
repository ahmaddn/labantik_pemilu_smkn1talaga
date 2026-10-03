<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CandidateEvote;
use App\Models\ElectionEvote;
use App\Models\VoteEvote;
use App\Models\VoterAccessEvote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ResultController extends Controller
{
    public function show(string $electionId, Request $request): Response
    {
        $election = ElectionEvote::with(['candidates'])
            ->findOrFail($electionId);

        $selectedStage = (int) $request->input('stage', $election->current_stage);
        if ($selectedStage < 1) {
            $selectedStage = 1;
        }

        // Total votes in the selected stage
        $totalVotes = VoteEvote::where('election_id', $electionId)
            ->where('stage_number', $selectedStage)
            ->count();

        // Total registered voters in the selected stage
        $totalVoters = VoterAccessEvote::where('election_id', $electionId)
            ->where('stage_number', $selectedStage)
            ->count();
        if ($totalVoters === 0) {
            $totalVoters = VoterAccessEvote::where('election_id', $electionId)->count();
        }

        $turnoutPercentage = $totalVoters > 0 ? round(($totalVotes / $totalVoters) * 100, 1) : 0;

        // Group votes per candidate for selected stage
        $votesMap = VoteEvote::where('election_id', $electionId)
            ->where('stage_number', $selectedStage)
            ->selectRaw('candidate_id, count(*) as count')
            ->groupBy('candidate_id')
            ->pluck('count', 'candidate_id');

        $results = $election->candidates->map(function ($candidate) use ($votesMap, $totalVotes) {
            $count = (int) ($votesMap[$candidate->id] ?? 0);
            $percentage = $totalVotes > 0 ? round(($count / $totalVotes) * 100, 1) : 0;

            return [
                'id' => $candidate->id,
                'candidate_number' => $candidate->candidate_number,
                'chairman_name' => $candidate->chairman_name,
                'vice_chairman_name' => $candidate->vice_chairman_name,
                'photo' => $candidate->photo,
                'is_qualified' => $candidate->is_qualified,
                'eliminated_at_stage' => $candidate->eliminated_at_stage,
                'votes_count' => $count,
                'percentage' => $percentage,
            ];
        });

        // Determine leading candidate for selected stage
        $leadingCandidate = $results->sortByDesc('votes_count')->first();

        // Active candidates count for current stage
        $activeQualifiedCount = $election->candidates->where('is_qualified', true)->count();

        return Inertia::render('Admin/Elections/Results', [
            'election' => [
                'id' => $election->id,
                'title' => $election->title,
                'description' => $election->description,
                'type' => $election->type,
                'target_voter' => $election->target_voter,
                'academic_year' => $election->academic_year,
                'start_at' => $election->start_at->toIso8601String(),
                'end_at' => $election->end_at->toIso8601String(),
                'is_published' => $election->is_published,
                'is_multi_stage' => $election->is_multi_stage,
                'current_stage' => $election->current_stage,
                'total_stages' => $election->total_stages,
                'status' => $election->status,
                'total_votes' => $totalVotes,
                'total_voters' => $totalVoters,
                'turnout_percentage' => $turnoutPercentage,
            ],
            'selectedStage' => $selectedStage,
            'activeQualifiedCount' => $activeQualifiedCount,
            'results' => $results->values(),
            'leadingCandidate' => $leadingCandidate && $leadingCandidate['votes_count'] > 0 ? $leadingCandidate : null,
        ]);
    }

    /**
     * Automatically advance to next stage by promoting Top N candidates based on real vote count or manual candidate selection.
     */
    public function advanceStage(Request $request, string $electionId): RedirectResponse
    {
        $election = ElectionEvote::findOrFail($electionId);

        $validated = $request->validate([
            'mode' => ['nullable', 'in:auto,manual'],
            'qualifiers_count' => ['required_if:mode,auto', 'nullable', 'integer', 'min:1'],
            'selected_candidate_ids' => ['required_if:mode,manual', 'nullable', 'array'],
            'selected_candidate_ids.*' => ['string', 'exists:candidates_evote,id'],
            'schedule_option' => ['nullable', 'in:now,custom'],
            'start_at' => ['required_if:schedule_option,custom', 'nullable', 'date'],
            'end_at' => ['nullable', 'date'],
        ]);

        $mode = $validated['mode'] ?? 'auto';
        $currentStage = $election->current_stage;

        if ($currentStage >= $election->total_stages) {
            return back()->with('error', "Pemilihan ini telah mencapai tahap akhir (Tahap {$election->total_stages}). Tidak dapat melanjutkan ke tahap berikutnya.");
        }

        $activeCandidates = CandidateEvote::where('election_id', $electionId)
            ->where('is_qualified', true)
            ->get();

        if ($mode === 'manual' && ! empty($validated['selected_candidate_ids'])) {
            $selectedIds = $validated['selected_candidate_ids'];
            $qualifiersCount = count($selectedIds);

            foreach ($activeCandidates as $cand) {
                if (in_array($cand->id, $selectedIds, true)) {
                    $cand->is_qualified = true;
                } else {
                    $cand->is_qualified = false;
                    $cand->eliminated_at_stage = $currentStage;
                }
                $cand->save();
            }
        } else {
            $qualifiersCount = (int) ($validated['qualifiers_count'] ?? 1);
            if ($activeCandidates->count() <= $qualifiersCount) {
                return back()->with('error', "Jumlah kandidat aktif ({$activeCandidates->count()}) tidak lebih dari kuota target ({$qualifiersCount}). Tidak dapat melanjutkan penyaringan.");
            }

            // Count votes per active candidate in current stage
            $votesMap = VoteEvote::where('election_id', $electionId)
                ->where('stage_number', $currentStage)
                ->selectRaw('candidate_id, count(*) as count')
                ->groupBy('candidate_id')
                ->pluck('count', 'candidate_id');

            // Sort active candidates descending by real votes
            $sortedCandidates = $activeCandidates->sortByDesc(function ($candidate) use ($votesMap) {
                return (int) ($votesMap[$candidate->id] ?? 0);
            })->values();

            // Promote Top N and eliminate the rest
            $qualifiers = $sortedCandidates->take($qualifiersCount);
            $eliminated = $sortedCandidates->slice($qualifiersCount);

            foreach ($qualifiers as $cand) {
                $cand->is_qualified = true;
                $cand->save();
            }

            foreach ($eliminated as $cand) {
                $cand->is_qualified = false;
                $cand->eliminated_at_stage = $currentStage;
                $cand->save();
            }
        }

        // Advance stage & update scheduling if specified
        $nextStage = $currentStage + 1;
        $election->current_stage = $nextStage;
        if ($nextStage > $election->total_stages) {
            $election->total_stages = $nextStage;
        }

        $scheduleOption = $validated['schedule_option'] ?? 'now';
        if ($scheduleOption === 'now') {
            $election->start_at = now();
            if (! empty($validated['end_at'])) {
                $election->end_at = Carbon::parse($validated['end_at']);
            }
        } else {
            if (! empty($validated['start_at'])) {
                $election->start_at = Carbon::parse($validated['start_at']);
            }
            if (! empty($validated['end_at'])) {
                $election->end_at = Carbon::parse($validated['end_at']);
            }
        }

        $election->save();

        // Siapkan hak akses pemilih untuk tahap baru dari semua pemilih yang memiliki hak pilih dasar
        $baseVoterUserIds = VoterAccessEvote::where('election_id', $electionId)
            ->pluck('user_id')
            ->unique();

        $existingStageUserIds = VoterAccessEvote::where('election_id', $electionId)
            ->where('stage_number', $nextStage)
            ->pluck('user_id')
            ->toArray();

        $missingUserIds = $baseVoterUserIds->diff($existingStageUserIds);

        if ($missingUserIds->isNotEmpty()) {
            $records = [];
            $nowTimestamp = now();
            foreach ($missingUserIds as $uId) {
                $records[] = [
                    'id' => (string) Str::uuid(),
                    'election_id' => $electionId,
                    'user_id' => $uId,
                    'stage_number' => $nextStage,
                    'is_voted' => false,
                    'voted_at' => null,
                    'created_at' => $nowTimestamp,
                    'updated_at' => $nowTimestamp,
                ];
            }
            foreach (array_chunk($records, 500) as $chunk) {
                VoterAccessEvote::insert($chunk);
            }
        }

        return redirect("/admin/pemilihan/{$electionId}/hasil?stage={$nextStage}")
            ->with('success', "Berhasil menyaring {$qualifiersCount} kandidat ke Tahap {$nextStage} dan memperbarui jadwal waktu!");
    }

    /**
     * Reset all stages back to Stage 1 and restore all candidates.
     */
    public function resetStages(string $electionId): RedirectResponse
    {
        $election = ElectionEvote::findOrFail($electionId);

        $election->current_stage = 1;
        $election->save();

        CandidateEvote::where('election_id', $electionId)->update([
            'is_qualified' => true,
            'eliminated_at_stage' => null,
        ]);

        return redirect("/admin/pemilihan/{$electionId}/hasil?stage=1")
            ->with('success', 'Semua kandidat berhasil dikembalikan dan tahap dipulihkan ke Tahap 1!');
    }
}

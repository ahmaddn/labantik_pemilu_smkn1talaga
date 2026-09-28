<?php

namespace App\Http\Controllers\Voter;

use App\Http\Controllers\Controller;
use App\Models\ElectionEvote;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class HasilController extends Controller
{
    public function show(string $electionId): Response|RedirectResponse
    {
        $election = ElectionEvote::with(['candidates' => function ($query) {
            $query->withCount('votes')->orderBy('candidate_number', 'asc');
        }])
            ->withCount(['votes', 'voterAccesses'])
            ->findOrFail($electionId);

        if (! $election->is_published) {
            return redirect()->route('voter.dashboard')->with('error', 'Hasil pemilihan ini belum dipublikasikan oleh panitia.');
        }

        $totalVotes = $election->votes_count;

        $results = $election->candidates->map(function ($candidate) use ($totalVotes) {
            $count = $candidate->votes_count;
            $percentage = $totalVotes > 0 ? round(($count / $totalVotes) * 100, 1) : 0;

            return [
                'id' => $candidate->id,
                'candidate_number' => $candidate->candidate_number,
                'chairman_name' => $candidate->chairman_name,
                'vice_chairman_name' => $candidate->vice_chairman_name,
                'photo' => $candidate->photo,
                'votes_count' => $count,
                'percentage' => $percentage,
            ];
        });

        return Inertia::render('Voter/Results', [
            'election' => [
                'id' => $election->id,
                'title' => $election->title,
                'description' => $election->description,
                'type' => $election->type,
                'start_at' => $election->start_at->toIso8601String(),
                'end_at' => $election->end_at->toIso8601String(),
                'total_votes' => $totalVotes,
                'total_voters' => $election->voter_accesses_count,
            ],
            'results' => $results,
        ]);
    }
}

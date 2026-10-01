<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ElectionEvote;
use App\Models\SimulationVote;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SimulationController extends Controller
{
    public function show(ElectionEvote $election)
    {
        $election->load(['candidates']);

        $candidates = $election->candidates->map(function ($c) use ($election) {
            $totalSimulationVotes = SimulationVote::where('election_id', $election->id)
                ->where('candidate_id', $c->id)
                ->where('stage', $election->current_stage)
                ->count();

            return [
                'id' => $c->id,
                'candidate_number' => $c->candidate_number,
                'chairman_name' => $c->chairman_name,
                'vice_chairman_name' => $c->vice_chairman_name,
                'photo' => $c->photo,
                'vision_mission' => $c->vision_mission,
                'is_qualified' => $c->is_qualified,
                'eliminated_at_stage' => $c->eliminated_at_stage,
                'simulation_votes_count' => $totalSimulationVotes,
            ];
        });

        $totalVotesInCurrentStage = SimulationVote::where('election_id', $election->id)
            ->where('stage', $election->current_stage)
            ->count();

        return Inertia::render('Admin/Elections/Simulation', [
            'election' => $election,
            'candidates' => $candidates,
            'totalSimulationVotes' => $totalVotesInCurrentStage,
        ]);
    }

    public function vote(Request $request, ElectionEvote $election)
    {
        $validated = $request->validate([
            'candidate_id' => 'required|exists:candidates_evote,id',
            'voter_alias' => 'nullable|string|max:100',
        ]);

        SimulationVote::create([
            'election_id' => $election->id,
            'candidate_id' => $validated['candidate_id'],
            'stage' => $election->current_stage ?? 1,
            'voter_alias' => $validated['voter_alias'] ?? ('Simulasi Pemilih #'.(SimulationVote::where('election_id', $election->id)->count() + 1)),
        ]);

        return back()->with('success', 'Suara simulasi berhasil ditambahkan!');
    }

    public function advanceStage(Request $request, ElectionEvote $election)
    {
        if (! $election->is_multi_stage) {
            return back()->with('error', 'Pemilihan ini bukan jenis multi-putaran.');
        }

        if ($election->current_stage >= $election->total_stages) {
            return back()->with('error', 'Sudah mencapai putaran maksimal.');
        }

        $election->increment('current_stage');

        return back()->with('success', 'Simulasi berhasil dilanjutkan ke Putaran '.$election->current_stage);
    }

    public function reset(ElectionEvote $election)
    {
        SimulationVote::where('election_id', $election->id)->delete();
        $election->update(['current_stage' => 1]);

        return back()->with('success', 'Data simulasi telah di-reset dari awal.');
    }
}

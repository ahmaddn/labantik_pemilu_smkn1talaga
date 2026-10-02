<?php

namespace App\Http\Controllers;

use App\Models\ElectionEvote;
use Inertia\Inertia;
use Inertia\Response;

class LandingController extends Controller
{
    public function index(): Response
    {
        $now = now();

        $activeElections = ElectionEvote::withCount(['candidates', 'voterAccesses'])
            ->get()
            ->filter(function ($election) use ($now) {
                if ($election->is_simulation) {
                    return $election->simulation_status === 'ongoing';
                }

                return $now->gte($election->start_at) && $now->lte($election->end_at);
            })
            ->values()
            ->map(function ($election) {
                $isSimulation = (bool) $election->is_simulation;
                $start = $isSimulation && $election->simulation_start_at ? $election->simulation_start_at : $election->start_at;
                $end = $isSimulation && $election->simulation_end_at ? $election->simulation_end_at : $election->end_at;

                return [
                    'id' => $election->id,
                    'title' => $isSimulation ? "[SIMULASI] {$election->title}" : $election->title,
                    'description' => $isSimulation
                        ? 'Sesi simulasi / gladi voting sedang dibuka. Nama kandidat disamarkan.'
                        : $election->description,
                    'type' => $election->type,
                    'is_simulation' => $isSimulation,
                    'simulation_status' => $election->simulation_status,
                    'start_at' => $start->toIso8601String(),
                    'end_at' => $end->toIso8601String(),
                    'candidates_count' => $election->candidates_count,
                    'voters_count' => $election->voter_accesses_count,
                    'status' => $isSimulation ? 'ongoing' : $election->status,
                ];
            });

        $upcomingElections = ElectionEvote::withCount(['candidates', 'voterAccesses'])
            ->get()
            ->filter(function ($election) use ($now) {
                if ($election->is_simulation) {
                    return $election->simulation_status === 'upcoming';
                }

                return $now->lt($election->start_at);
            })
            ->values()
            ->map(function ($election) {
                $isSimulation = (bool) $election->is_simulation;
                $start = $isSimulation && $election->simulation_start_at ? $election->simulation_start_at : $election->start_at;
                $end = $isSimulation && $election->simulation_end_at ? $election->simulation_end_at : $election->end_at;

                return [
                    'id' => $election->id,
                    'title' => $isSimulation ? "[SIMULASI] {$election->title}" : $election->title,
                    'description' => $isSimulation
                        ? 'Jadwal gladi / simulasi pemilihan dengan kandidat tersamar.'
                        : $election->description,
                    'type' => $election->type,
                    'is_simulation' => $isSimulation,
                    'simulation_status' => $election->simulation_status,
                    'start_at' => $start->toIso8601String(),
                    'end_at' => $end->toIso8601String(),
                    'candidates_count' => $election->candidates_count,
                    'voters_count' => $election->voter_accesses_count,
                    'status' => 'upcoming',
                ];
            });

        return Inertia::render('Welcome', [
            'activeElections' => $activeElections,
            'upcomingElections' => $upcomingElections,
            'user' => auth()->user() ? [
                'id' => auth()->user()->id,
                'name' => auth()->user()->name,
                'role' => auth()->user()->role,
            ] : null,
        ]);
    }
}

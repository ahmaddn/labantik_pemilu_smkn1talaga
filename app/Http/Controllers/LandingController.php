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
            ->where('start_at', '<=', $now)
            ->where('end_at', '>=', $now)
            ->orderBy('start_at', 'asc')
            ->get()
            ->map(function ($election) {
                return [
                    'id' => $election->id,
                    'title' => $election->title,
                    'description' => $election->description,
                    'type' => $election->type,
                    'start_at' => $election->start_at->toIso8601String(),
                    'end_at' => $election->end_at->toIso8601String(),
                    'candidates_count' => $election->candidates_count,
                    'voters_count' => $election->voter_accesses_count,
                    'status' => $election->status,
                ];
            });

        $upcomingElections = ElectionEvote::withCount(['candidates', 'voterAccesses'])
            ->where('start_at', '>', $now)
            ->orderBy('start_at', 'asc')
            ->get()
            ->map(function ($election) {
                return [
                    'id' => $election->id,
                    'title' => $election->title,
                    'description' => $election->description,
                    'type' => $election->type,
                    'start_at' => $election->start_at->toIso8601String(),
                    'end_at' => $election->end_at->toIso8601String(),
                    'candidates_count' => $election->candidates_count,
                    'voters_count' => $election->voter_accesses_count,
                    'status' => $election->status,
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

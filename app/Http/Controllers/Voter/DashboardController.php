<?php

namespace App\Http\Controllers\Voter;

use App\Http\Controllers\Controller;
use App\Models\CandidateEvote;
use App\Models\VoterAccessEvote;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        // Load all voter access records for logged in user with election details
        $voterAccesses = VoterAccessEvote::with(['election' => function ($query) {
            $query->withCount('candidates');
        }])
            ->where('user_id', $user->id)
            ->get()
            ->map(function ($access) {
                $election = $access->election;

                return [
                    'access_id' => $access->id,
                    'is_voted' => $access->is_voted,
                    'voted_at' => $access->voted_at ? $access->voted_at->toIso8601String() : null,
                    'election' => [
                        'id' => $election->id,
                        'title' => $election->title,
                        'description' => $election->description,
                        'type' => $election->type,
                        'start_at' => $election->start_at->toIso8601String(),
                        'end_at' => $election->end_at->toIso8601String(),
                        'is_published' => $election->is_published,
                        'is_multi_stage' => $election->is_multi_stage,
                        'current_stage' => $election->current_stage,
                        'total_stages' => $election->total_stages,
                        'stage_schedules' => $election->stage_schedules ?? [],
                        'status' => $election->status,
                        'candidates_count' => $election->candidates_count,
                        'previous_qualifiers' => $election->is_multi_stage && $election->current_stage > 1
                            ? CandidateEvote::where('election_id', $election->id)
                                ->where('is_qualified', true)
                                ->get()
                                ->map(fn ($c) => [
                                    'id' => $c->id,
                                    'candidate_number' => $c->candidate_number,
                                    'chairman_name' => $c->chairman_name,
                                    'vice_chairman_name' => $c->vice_chairman_name,
                                    'photo' => $c->photo,
                                ])
                            : [],
                    ],
                ];
            });

        // User detail string (e.g. Class for student or NIP for teacher)
        $userSubtext = '';
        if ($user->isStudent() && $user->student) {
            $activeYear = $user->student->activeAcademicYear();
            if ($activeYear && $activeYear->schoolClass) {
                $userSubtext = 'Kelas: '.$activeYear->schoolClass->name;
            } else {
                $userSubtext = 'NIS: '.$user->student->student_number;
            }
        } elseif ($user->isTeacher() && $user->employee) {
            $userSubtext = 'NIP: '.($user->employee->nip ?? '-');
        }

        return Inertia::render('Voter/Dashboard', [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'subtext' => $userSubtext,
            ],
            'voterAccesses' => $voterAccesses,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Voter;

use App\Http\Controllers\Controller;
use App\Models\CandidateEvote;
use App\Models\SimulationVote;
use App\Models\VoterAccessEvote;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        // Load voter access records for logged in user (1 kartu per pemilihan sesuai stage aktif/terakhir)
        $voterAccesses = VoterAccessEvote::with(['election' => function ($query) {
            $query->withCount('candidates');
        }])
            ->where('user_id', $user->id)
            ->orderBy('stage_number', 'desc')
            ->get()
            ->unique('election_id')
            ->map(function ($access) use ($user) {
                $election = $access->election;

                $isSimulationVoted = false;
                if ($election->is_simulation) {
                    $isSimulationVoted = SimulationVote::where('election_id', $election->id)
                        ->where('user_id', $user->id)
                        ->where('stage', $election->current_stage)
                        ->exists();
                }

                return [
                    'access_id' => $access->id,
                    'is_voted' => $access->is_voted,
                    'voted_at' => $access->voted_at ? $access->voted_at->toIso8601String() : null,
                    'is_simulation_voted' => $isSimulationVoted,
                    'election' => [
                        'id' => $election->id,
                        'title' => $election->title,
                        'description' => $election->description,
                        'type' => $election->type,
                        'start_at' => $election->start_at->toIso8601String(),
                        'end_at' => $election->end_at->toIso8601String(),
                        'is_published' => $election->is_published,
                        'is_multi_stage' => $election->is_multi_stage,
                        'is_simulation' => (bool) $election->is_simulation,
                        'simulation_start_at' => $election->simulation_start_at ? $election->simulation_start_at->toIso8601String() : null,
                        'simulation_end_at' => $election->simulation_end_at ? $election->simulation_end_at->toIso8601String() : null,
                        'simulation_status' => $election->simulation_status,
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
        } elseif ($user->isTeacher()) {
            if ($user->employee && ! empty($user->employee->nip)) {
                $userSubtext = 'NIP: '.$user->employee->nip;
            } elseif ($user->email) {
                $userSubtext = 'Email: '.$user->email;
            } else {
                $userSubtext = 'Guru / Staf SMKN 1 Talaga';
            }
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

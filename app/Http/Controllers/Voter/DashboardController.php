<?php

namespace App\Http\Controllers\Voter;

use App\Http\Controllers\Controller;
use App\Models\ElectionEvote;
use App\Models\SimulationVote;
use App\Models\VoterAccessEvote;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        // Ambil semua pemilihan unik yang pemilih memiliki hak pilih (base access)
        $accessibleElectionIds = VoterAccessEvote::where('user_id', $user->id)
            ->pluck('election_id')
            ->unique();

        $elections = ElectionEvote::withCount('candidates')
            ->whereIn('id', $accessibleElectionIds)
            ->get();

        $voterAccesses = $elections->map(function ($election) use ($user) {
            $currentStage = $election->current_stage ?? 1;

            // Cari akses hak pilih khusus untuk tahap yang sedang berlangsung
            $stageAccess = VoterAccessEvote::where('user_id', $user->id)
                ->where('election_id', $election->id)
                ->where('stage_number', $currentStage)
                ->first();

            $isSimulationVoted = false;
            if ($election->is_simulation) {
                $isSimulationVoted = SimulationVote::where('election_id', $election->id)
                    ->where('user_id', $user->id)
                    ->where('stage', $currentStage)
                    ->exists();
            }

            return [
                'access_id' => $stageAccess ? $stageAccess->id : "pending-stage-{$election->id}-{$currentStage}",
                'is_voted' => $stageAccess ? (bool) $stageAccess->is_voted : false,
                'voted_at' => $stageAccess && $stageAccess->voted_at ? $stageAccess->voted_at->toIso8601String() : null,
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
                ],
            ];
        });

        // User detail string (e.g. Class for student or NIP for teacher)
        $userSubtext = '';
        if ($user->isStudent() && $user->student) {
            $activeYear = DB::table('ref_student_academic_years')
                ->join('ref_classes', 'ref_student_academic_years.class_id', '=', 'ref_classes.id')
                ->where('ref_student_academic_years.student_id', $user->student->id)
                ->whereNull('ref_student_academic_years.mutation_date')
                ->orderBy('ref_student_academic_years.academic_year', 'desc')
                ->select('ref_classes.academic_level', 'ref_classes.name as class_name')
                ->first();

            $levelPrefix = $activeYear && $activeYear->academic_level ? "Kelas {$activeYear->academic_level} " : 'Kelas ';
            $classText = $activeYear ? trim($levelPrefix.$activeYear->class_name) : null;
            $nisText = $user->student->student_number ? "NIS: {$user->student->student_number}" : null;

            if ($classText && $nisText) {
                $userSubtext = "{$classText} | {$nisText}";
            } elseif ($classText) {
                $userSubtext = $classText;
            } else {
                $userSubtext = $nisText ?? 'Siswa';
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

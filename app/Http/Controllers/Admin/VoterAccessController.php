<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ElectionEvote;
use App\Models\StudentAcademicYear;
use App\Models\User;
use App\Models\VoterAccessEvote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class VoterAccessController extends Controller
{
    public function index(string $electionId): Response
    {
        $election = ElectionEvote::with(['targetClass'])->findOrFail($electionId);

        $voterAccesses = VoterAccessEvote::with(['user.student', 'user.employee'])
            ->where('election_id', $electionId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($access) {
                $user = $access->user;
                $subtext = '-';

                if ($user && $user->isStudent() && $user->student) {
                    $subtext = 'NIS: '.$user->student->student_number;
                } elseif ($user && $user->isTeacher() && $user->employee) {
                    $subtext = 'NIP: '.($user->employee->nip ?? '-');
                }

                return [
                    'id' => $access->id,
                    'user_id' => $access->user_id,
                    'user_name' => $user ? $user->name : 'User Unknown',
                    'user_email' => $user ? $user->email : '-',
                    'user_role' => $user ? $user->role : '-',
                    'user_subtext' => $subtext,
                    'is_voted' => $access->is_voted,
                    'voted_at' => $access->voted_at ? $access->voted_at->toIso8601String() : null,
                ];
            });

        $stats = [
            'total_access' => $voterAccesses->count(),
            'total_voted' => $voterAccesses->where('is_voted', true)->count(),
        ];

        return Inertia::render('Admin/Voters/Index', [
            'election' => [
                'id' => $election->id,
                'title' => $election->title,
                'target_voter' => $election->target_voter,
                'academic_year' => $election->academic_year,
                'class_name' => $election->targetClass ? $election->targetClass->name : null,
            ],
            'voterAccesses' => $voterAccesses,
            'stats' => $stats,
        ]);
    }

    /**
     * Batch generate voter accesses automatically based on target & active academic year.
     */
    public function generateBatch(Request $request, string $electionId): RedirectResponse
    {
        $election = ElectionEvote::findOrFail($electionId);

        $targetVoter = $election->target_voter;
        $academicYear = $election->academic_year;
        $classId = $election->class_id;

        $userIds = [];

        // 1. Fetch eligible student user_ids
        if (in_array($targetVoter, ['all', 'student'], true)) {
            $studentQuery = StudentAcademicYear::query()
                ->where('status', 'Active')
                ->join('ref_students', 'ref_student_academic_years.student_id', '=', 'ref_students.id')
                ->whereNotNull('ref_students.user_id');

            if ($academicYear) {
                $studentQuery->where('ref_student_academic_years.academic_year', $academicYear);
            }

            if ($classId) {
                $studentQuery->where('ref_student_academic_years.class_id', $classId);
            }

            $studentUserIds = $studentQuery->pluck('ref_students.user_id')->toArray();
            $userIds = array_merge($userIds, $studentUserIds);
        }

        // 2. Fetch eligible teacher user_ids
        if (in_array($targetVoter, ['all', 'teacher'], true)) {
            $teacherUserIds = User::where('role', 'guru')
                ->where('is_active', true)
                ->pluck('id')
                ->toArray();
            $userIds = array_merge($userIds, $teacherUserIds);
        }

        $userIds = array_unique(array_filter($userIds));

        if (empty($userIds)) {
            return back()->with('error', 'Tidak ditemukan pemilih yang memenuhi kriteria (Siswa Aktif / Guru).');
        }

        $now = now();
        $records = [];
        foreach ($userIds as $userId) {
            $records[] = [
                'election_id' => $electionId,
                'user_id' => $userId,
                'is_voted' => false,
                'voted_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Chunk insert using insertOrIgnore to prevent duplicates
        $countInserted = 0;
        foreach (array_chunk($records, 500) as $chunk) {
            DB::table('voter_accesses_evote')->insertOrIgnore($chunk);
            $countInserted += count($chunk);
        }

        return back()->with('success', "Berhasil memproses hak akses pemilih untuk {$countInserted} akun!");
    }

    /**
     * Delete individual voter access.
     */
    public function destroy(string $electionId, string $accessId): RedirectResponse
    {
        $access = VoterAccessEvote::where('election_id', $electionId)->findOrFail($accessId);
        $access->delete();

        return back()->with('success', 'Akses pemilih berhasil dihapus.');
    }
}

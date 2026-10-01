<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ElectionEvote;
use App\Models\Employee;
use App\Models\StudentAcademicYear;
use App\Models\VoterAccessEvote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class VoterAccessController extends Controller
{
    public function index(Request $request, string $electionId): Response
    {
        $election = ElectionEvote::with(['targetClass'])->findOrFail($electionId);
        $search = trim($request->input('search', ''));

        $query = VoterAccessEvote::with([
            'user' => function ($q) {
                $q->select('id', 'name', 'email', 'role');
            },
            'user.student' => function ($q) {
                $q->select('id', 'user_id', 'student_number');
            },
            'user.employee' => function ($q) {
                $q->select('id', 'user_id', 'nip');
            },
        ])
            ->where('election_id', $electionId);

        if (! empty($search)) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($sq) use ($search) {
                        $sq->where('student_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('employee', function ($eq) use ($search) {
                        $eq->where('nip', 'like', "%{$search}%");
                    });
            });
        }

        $voterAccesses = $query->orderBy('created_at', 'desc')
            ->paginate(25)
            ->withQueryString()
            ->through(function ($access) {
                $user = $access->user;
                $subtext = '-';

                if ($user && ($user->isStudent() || $user->student)) {
                    $subtext = $user->student && ! empty($user->student->student_number)
                        ? 'NIS: '.$user->student->student_number
                        : ($user->email ? 'Email: '.$user->email : 'Siswa');
                } elseif ($user && ($user->isTeacher() || $user->employee)) {
                    $subtext = ($user->employee && ! empty($user->employee->nip))
                        ? 'NIP: '.$user->employee->nip
                        : ($user->email ? 'Email: '.$user->email : 'Guru / Staf');
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
            'total_access' => VoterAccessEvote::where('election_id', $electionId)->count(),
            'total_voted' => VoterAccessEvote::where('election_id', $electionId)->where('is_voted', true)->count(),
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
            'filters' => [
                'search' => $search,
            ],
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
            // Ambil siswa yang AKTIF pada Tahun Ajaran yang disetting di pemilihan (tidak mutasi, tidak lulus)
            $studentQuery = StudentAcademicYear::query()
                ->whereIn('ref_student_academic_years.status', ['Active', 'Naik Kelas'])
                ->whereNotIn('ref_student_academic_years.status', ['Mutasi Out', 'Lulus'])
                ->join('ref_students', 'ref_student_academic_years.student_id', '=', 'ref_students.id')
                ->join('core_users', 'ref_students.user_id', '=', 'core_users.id')
                ->where('core_users.is_active', true)
                ->whereNotNull('ref_students.user_id');

            if ($academicYear) {
                $studentQuery->where('ref_student_academic_years.academic_year', $academicYear);
            }

            if ($classId) {
                $studentQuery->where('ref_student_academic_years.class_id', $classId);
            }

            // Pastikan tidak mengambil siswa yang tercatat mutasi keluar pada tahun ajaran tersebut
            if ($academicYear) {
                $mutatedStudentIds = DB::table('ref_student_academic_years')
                    ->where('academic_year', $academicYear)
                    ->where('status', 'Mutasi Out')
                    ->pluck('student_id')
                    ->toArray();

                if (! empty($mutatedStudentIds)) {
                    $studentQuery->whereNotIn('ref_student_academic_years.student_id', $mutatedStudentIds);
                }
            }

            $studentUserIds = $studentQuery->pluck('ref_students.user_id')->toArray();
            $userIds = array_merge($userIds, $studentUserIds);
        }

        // 2. Fetch eligible teacher user_ids (from core_employees, assoc_user_roles, and core_users role = 'guru')
        if (in_array($targetVoter, ['all', 'teacher'], true)) {
            $teacherUserIdsEmp = Employee::whereNotNull('user_id')
                ->pluck('user_id')
                ->toArray();

            $teacherUserIdsAssoc = DB::table('assoc_user_roles')
                ->join('core_roles', 'assoc_user_roles.role_id', '=', 'core_roles.id')
                ->where(function ($query) {
                    $query->where('core_roles.name', 'LIKE', '%Guru%')
                        ->orWhere('core_roles.name', 'LIKE', '%Wali Kelas%')
                        ->orWhere('core_roles.name', 'LIKE', '%Kurikulum%')
                        ->orWhere('core_roles.name', 'LIKE', '%Kepala Sekolah%')
                        ->orWhere('core_roles.name', 'LIKE', '%Kesiswaan%')
                        ->orWhere('core_roles.name', 'LIKE', '%Tenaga Kependidikan%')
                        ->orWhere('core_roles.name', 'LIKE', '%Pembina%')
                        ->orWhere('core_roles.name', 'LIKE', '%Kaprog%');
                })
                ->pluck('assoc_user_roles.user_id')
                ->toArray();

            $teacherUserIdsRole = DB::table('core_users')
                ->where('role', 'guru')
                ->where('is_active', true)
                ->pluck('id')
                ->toArray();

            $teacherUserIds = array_unique(array_merge($teacherUserIdsEmp, $teacherUserIdsAssoc, $teacherUserIdsRole));
            $userIds = array_merge($userIds, $teacherUserIds);
        }

        $userIds = array_unique(array_filter($userIds));

        if (empty($userIds)) {
            return back()->with('error', 'Tidak ditemukan pemilih yang memenuhi kriteria (Siswa Aktif / Guru).');
        }

        // BERSIHKAN hak akses lama yang BELUM memilih tetapi sudah tidak masuk dalam target pemilihan yang baru
        // (Contoh: awalnya siswa lalu diubah ke guru saja, maka akses siswa yang belum memilih dihapus otomatis)
        VoterAccessEvote::where('election_id', $electionId)
            ->where('is_voted', false)
            ->whereNotIn('user_id', $userIds)
            ->delete();

        $now = now();
        $records = [];
        foreach ($userIds as $userId) {
            $records[] = [
                'id' => (string) Str::uuid(),
                'election_id' => $electionId,
                'user_id' => $userId,
                'stage_number' => $election->current_stage ?? 1,
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

        return back()->with('success', "Berhasil menyinkronkan hak akses pemilih untuk {$countInserted} akun!");
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

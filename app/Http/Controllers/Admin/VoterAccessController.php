<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ElectionEvote;
use App\Models\VoterAccessEvote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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

        $sortBy = $request->input('sort_by', 'name'); // 'name', 'class'
        $classFilter = trim((string) $request->input('class', ''));

        $academicYear = $election->academic_year;

        // Subquery/join untuk kelas siswa agar sorting dan filtering berdasarkan kelas bekerja di level DB
        $query->join('core_users', 'voter_accesses_evote.user_id', '=', 'core_users.id')
            ->leftJoin('ref_students', 'core_users.id', '=', 'ref_students.user_id')
            ->leftJoin('ref_student_academic_years', function ($j) use ($academicYear) {
                $j->on('ref_students.id', '=', 'ref_student_academic_years.student_id')
                    ->whereNull('ref_student_academic_years.mutation_date');
                if ($academicYear) {
                    $j->where('ref_student_academic_years.academic_year', $academicYear);
                }
            })
            ->leftJoin('ref_classes', 'ref_student_academic_years.class_id', '=', 'ref_classes.id');

        if (! empty($classFilter)) {
            $query->where('ref_classes.name', $classFilter);
        }

        $query->select(
            'voter_accesses_evote.*',
            'ref_classes.academic_level as student_academic_level',
            'ref_classes.name as student_class_name'
        );

        if ($sortBy === 'class') {
            // Urutkan berdasarkan tingkat kelas, nama kelas, lalu nama pemilih
            $query->orderByRaw('CASE WHEN ref_classes.id IS NULL THEN 1 ELSE 0 END')
                ->orderBy('ref_classes.academic_level', 'asc')
                ->orderBy('ref_classes.name', 'asc')
                ->orderBy('core_users.name', 'asc');
        } else {
            $query->orderBy('core_users.name', 'asc')
                ->orderBy('voter_accesses_evote.id', 'asc');
        }

        $voterAccesses = $query->paginate(25)
            ->withQueryString()
            ->through(function ($access) {
                $user = $access->user;
                $classInfo = null;
                $identifier = '-';

                if ($access->student_class_name) {
                    $levelStr = $access->student_academic_level ? "Kelas {$access->student_academic_level} " : 'Kelas ';
                    $classInfo = trim($levelStr.$access->student_class_name);
                }

                if ($user && ($user->isStudent() || $user->student)) {
                    $identifier = $user->student && ! empty($user->student->student_number)
                        ? 'NIS: '.$user->student->student_number
                        : ($user->email ? $user->email : 'Siswa');
                } elseif ($user && ($user->isTeacher() || $user->employee)) {
                    $identifier = ($user->employee && ! empty($user->employee->nip))
                        ? 'NIP: '.$user->employee->nip
                        : ($user->email ? $user->email : 'Guru / Staf');
                }

                return [
                    'id' => $access->id,
                    'user_id' => $access->user_id,
                    'user_name' => $user ? $user->name : 'User Unknown',
                    'user_email' => $user ? $user->email : '-',
                    'user_role' => $user ? $user->role : '-',
                    'user_class' => $classInfo,
                    'user_identifier' => $identifier,
                    'is_voted' => $access->is_voted,
                    'voted_at' => $access->voted_at ? $access->voted_at->toIso8601String() : null,
                ];
            });

        $stats = [
            'total_access' => VoterAccessEvote::where('election_id', $electionId)->count(),
            'total_voted' => VoterAccessEvote::where('election_id', $electionId)->where('is_voted', true)->count(),
        ];

        // Daftar nama kelas untuk pilihan filter
        $availableClasses = DB::table('ref_classes')
            ->orderBy('academic_level')
            ->orderBy('name')
            ->pluck('name')
            ->unique()
            ->values()
            ->toArray();

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
                'sort_by' => $sortBy,
                'class' => $classFilter,
            ],
            'availableClasses' => $availableClasses,
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

        // 1. Fetch eligible student user_ids (Persis sama dengan metode SIMS: whereNull mutation_date)
        if (in_array($targetVoter, ['all', 'student'], true)) {
            $studentQuery = DB::table('ref_student_academic_years')
                ->whereNull('ref_student_academic_years.mutation_date')
                ->join('ref_students', 'ref_student_academic_years.student_id', '=', 'ref_students.id');

            if ($academicYear) {
                $studentQuery->where('ref_student_academic_years.academic_year', $academicYear);
            }

            if ($classId) {
                $studentQuery->where('ref_student_academic_years.class_id', $classId);
            }

            $studentsList = $studentQuery->select('ref_students.id', 'ref_students.user_id', 'ref_students.student_number', 'ref_students.full_name')->get();

            $studentUserIds = [];
            $now = now();
            foreach ($studentsList as $stu) {
                $uid = $stu->user_id;

                // Jika siswa belum memiliki user_id atau user_id tidak valid, hubungkan atau buatkan akun
                if (! $uid) {
                    $nis = trim((string) $stu->student_number);
                    $email = ! empty($nis) ? "{$nis}@smkn1talaga.sch.id" : null;

                    // Cari apakah user sudah ada di core_users berdasarkan email atau NIS
                    $existingUser = null;
                    if ($email) {
                        $existingUser = DB::table('core_users')->where('email', $email)->first();
                    }

                    if ($existingUser) {
                        $uid = $existingUser->id;
                    } else {
                        $uid = (string) Str::uuid();
                        DB::table('core_users')->insert([
                            'id' => $uid,
                            'name' => $stu->full_name,
                            'email' => $email ?? ($uid.'@smkn1talaga.sch.id'),
                            'password' => Hash::make('12345678'),
                            'is_active' => true,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);

                        // Assign role Siswa
                        $siswaRole = DB::table('core_roles')->where('name', 'Siswa')->orWhere('code', 'siswa')->first();
                        if ($siswaRole) {
                            DB::table('assoc_user_roles')->insert([
                                'id' => (string) Str::uuid(),
                                'user_id' => $uid,
                                'role_id' => $siswaRole->id,
                                'app_type' => 'STUDENT_APPLICATION',
                                'created_at' => $now,
                                'updated_at' => $now,
                            ]);
                        }
                    }

                    // Update ref_students agar user_id tersimpan
                    DB::table('ref_students')->where('id', $stu->id)->update(['user_id' => $uid]);
                }

                if ($uid) {
                    $studentUserIds[] = $uid;
                }
            }

            $userIds = array_merge($userIds, $studentUserIds);
        }

        // 2. Fetch eligible teacher user_ids (MURNI hanya role 'Guru' aktif, dan BUKAN tendik, kepsek, kurikulum, kesiswaan, atau siswa)
        if (in_array($targetVoter, ['all', 'teacher'], true)) {
            $excludedRoleNames = ['Super Admin', 'Kesiswaan', 'Tenaga Kependidikan', 'Kepala Sekolah', 'Kurikulum', 'Siswa'];

            $excludedUserIds = DB::table('assoc_user_roles')
                ->join('core_roles', 'assoc_user_roles.role_id', '=', 'core_roles.id')
                ->whereIn('core_roles.name', $excludedRoleNames)
                ->pluck('assoc_user_roles.user_id')
                ->unique()
                ->toArray();

            $studentIdsToExclude = DB::table('ref_students')
                ->whereNotNull('user_id')
                ->pluck('user_id')
                ->toArray();

            $allExcludes = array_unique(array_merge($excludedUserIds, $studentIdsToExclude));

            $teacherUserIds = DB::table('assoc_user_roles')
                ->join('core_roles', 'assoc_user_roles.role_id', '=', 'core_roles.id')
                ->join('core_users', 'assoc_user_roles.user_id', '=', 'core_users.id')
                ->where('core_roles.name', 'Guru')
                ->where('core_users.is_active', true)
                ->whereNotIn('assoc_user_roles.user_id', $allExcludes)
                ->distinct()
                ->pluck('assoc_user_roles.user_id')
                ->toArray();

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

        // Ambil user_id yang SUDAH terdaftar di pemilihan ini agar tidak dibuat ulang
        $existingUserIds = VoterAccessEvote::where('election_id', $electionId)
            ->pluck('user_id')
            ->toArray();

        $newUserIds = array_diff($userIds, $existingUserIds);

        $now = now();
        $records = [];
        foreach ($newUserIds as $userId) {
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
     * Delete all voter accesses for an election that haven't voted yet (or all if confirmed).
     */
    public function destroyAll(string $electionId): RedirectResponse
    {
        // Hapus pemilih yang belum memilih untuk menjaga integritas suara yang sudah masuk
        $deletedCount = VoterAccessEvote::where('election_id', $electionId)
            ->where('is_voted', false)
            ->delete();

        return back()->with('success', "Berhasil menghapus {$deletedCount} data hak pilih pemilih.");
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

    /**
     * Search eligible users (students or teachers) that are not yet added to this election.
     */
    public function searchUsers(Request $request, string $electionId)
    {
        $election = ElectionEvote::findOrFail($electionId);
        $search = trim((string) $request->input('q', ''));

        // Ambil ID user yang sudah terdaftar
        $existingUserIds = VoterAccessEvote::where('election_id', $electionId)->pluck('user_id')->toArray();

        $targetVoter = $election->target_voter;

        $usersQuery = DB::table('core_users')
            ->leftJoin('ref_students', 'core_users.id', '=', 'ref_students.user_id')
            ->leftJoin('core_employees', 'core_users.id', '=', 'core_employees.user_id')
            ->whereNotIn('core_users.id', $existingUserIds)
            ->where('core_users.is_active', true)
            ->select(
                'core_users.id',
                'core_users.name',
                'core_users.email',
                'ref_students.student_number',
                'core_employees.nip'
            );

        // Filter sesuai target_voter:
        if ($targetVoter === 'student') {
            // Hanya siswa (memiliki catatan siswa di ref_students)
            $usersQuery->whereNotNull('ref_students.id');
        } elseif ($targetVoter === 'teacher') {
            // Hanya guru murni (memiliki role Guru dan bukan siswa/tendik/admin lainnya)
            $excludedRoleNames = ['Super Admin', 'Kesiswaan', 'Tenaga Kependidikan', 'Kepala Sekolah', 'Kurikulum', 'Siswa'];

            $excludedUserIds = DB::table('assoc_user_roles')
                ->join('core_roles', 'assoc_user_roles.role_id', '=', 'core_roles.id')
                ->whereIn('core_roles.name', $excludedRoleNames)
                ->pluck('assoc_user_roles.user_id')
                ->unique()
                ->toArray();

            $studentUserIds = DB::table('ref_students')->whereNotNull('user_id')->pluck('user_id')->toArray();
            $allTeacherExcludes = array_unique(array_merge($excludedUserIds, $studentUserIds));

            $teacherUserIds = DB::table('assoc_user_roles')
                ->join('core_roles', 'assoc_user_roles.role_id', '=', 'core_roles.id')
                ->where('core_roles.name', 'Guru')
                ->whereNotIn('assoc_user_roles.user_id', $allTeacherExcludes)
                ->pluck('assoc_user_roles.user_id')
                ->unique()
                ->toArray();

            $usersQuery->whereIn('core_users.id', $teacherUserIds);
        } else {
            // 'all': Guru atau Siswa
            $studentUserIds = DB::table('ref_students')->whereNotNull('user_id')->pluck('user_id')->toArray();

            $excludedRoleNames = ['Super Admin', 'Kesiswaan', 'Tenaga Kependidikan', 'Kepala Sekolah', 'Kurikulum', 'Siswa'];
            $excludedUserIds = DB::table('assoc_user_roles')
                ->join('core_roles', 'assoc_user_roles.role_id', '=', 'core_roles.id')
                ->whereIn('core_roles.name', $excludedRoleNames)
                ->pluck('assoc_user_roles.user_id')
                ->unique()
                ->toArray();

            $allTeacherExcludes = array_unique(array_merge($excludedUserIds, $studentUserIds));
            $teacherUserIds = DB::table('assoc_user_roles')
                ->join('core_roles', 'assoc_user_roles.role_id', '=', 'core_roles.id')
                ->where('core_roles.name', 'Guru')
                ->whereNotIn('assoc_user_roles.user_id', $allTeacherExcludes)
                ->pluck('assoc_user_roles.user_id')
                ->unique()
                ->toArray();

            $allowedUserIds = array_unique(array_merge($studentUserIds, $teacherUserIds));
            $usersQuery->whereIn('core_users.id', $allowedUserIds);
        }

        if (! empty($search)) {
            $usersQuery->where(function ($q) use ($search) {
                $q->where('core_users.name', 'like', "%{$search}%")
                    ->orWhere('core_users.email', 'like', "%{$search}%")
                    ->orWhere('ref_students.student_number', 'like', "%{$search}%")
                    ->orWhere('core_employees.nip', 'like', "%{$search}%");
            });
        }

        $usersList = $usersQuery->limit(20)->get();

        $studentUserIds = $usersList->pluck('id')->toArray();
        $classMap = [];
        if (! empty($studentUserIds)) {
            $classQuery = DB::table('ref_students')
                ->join('ref_student_academic_years', 'ref_students.id', '=', 'ref_student_academic_years.student_id')
                ->join('ref_classes', 'ref_student_academic_years.class_id', '=', 'ref_classes.id')
                ->whereIn('ref_students.user_id', $studentUserIds)
                ->whereNull('ref_student_academic_years.mutation_date');

            if ($election->academic_year) {
                $classQuery->where('ref_student_academic_years.academic_year', $election->academic_year);
            }

            $classRows = $classQuery->select(
                'ref_students.user_id',
                'ref_classes.academic_level',
                'ref_classes.name as class_name'
            )->get();

            foreach ($classRows as $row) {
                $levelStr = $row->academic_level ? "Kelas {$row->academic_level} " : 'Kelas ';
                $classMap[$row->user_id] = trim($levelStr.$row->class_name);
            }
        }

        $results = $usersList->map(function ($u) use ($classMap) {
            $classInfo = $classMap[$u->id] ?? null;
            $identifierParts = [];

            if ($classInfo) {
                $identifierParts[] = $classInfo;
            }
            if ($u->student_number) {
                $identifierParts[] = "NIS: {$u->student_number}";
            } elseif ($u->nip) {
                $identifierParts[] = "NIP: {$u->nip}";
            }

            $identifier = ! empty($identifierParts) ? implode(' | ', $identifierParts) : $u->email;

            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'identifier' => $identifier,
            ];
        });

        return response()->json($results);
    }

    /**
     * Store selected voter access based on explicitly chosen user IDs.
     */
    public function storeSelected(Request $request, string $electionId): RedirectResponse
    {
        $election = ElectionEvote::findOrFail($electionId);

        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'string', 'exists:core_users,id'],
        ]);

        $existingUserIds = VoterAccessEvote::where('election_id', $electionId)->pluck('user_id')->toArray();
        $newUserIds = array_diff(array_unique($validated['user_ids']), $existingUserIds);

        if (empty($newUserIds)) {
            return back()->with('info', 'Semua pengguna yang dipilih sudah terdaftar sebagai pemilih.');
        }

        $now = now();
        $records = [];
        foreach ($newUserIds as $userId) {
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

        $count = count($records);
        foreach (array_chunk($records, 500) as $chunk) {
            DB::table('voter_accesses_evote')->insertOrIgnore($chunk);
        }

        return back()->with('success', "Berhasil menambahkan {$count} pemilih terpilih ke acara pemilihan!");
    }
}

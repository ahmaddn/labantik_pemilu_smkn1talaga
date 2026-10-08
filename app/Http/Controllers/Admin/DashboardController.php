<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSettingEvote;
use App\Models\ElectionEvote;
use App\Models\VoteEvote;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $totalElections = ElectionEvote::count();

        // 1. Hitung Siswa Aktif (persis sama dengan metode SIMS: ref_student_academic_years dengan academic_year aktif & whereNull mutation_date)
        $activeAcademicYear = AppSettingEvote::getValue('active_academic_year', '2026/2027');
        $totalStudents = DB::table('ref_student_academic_years')
            ->where('academic_year', $activeAcademicYear)
            ->whereNull('mutation_date')
            ->count();

        // 2. Hitung Guru (HANYA role 'Guru' yang aktif, tanpa tendik, kepsek, kurikulum, kesiswaan, superadmin, atau siswa)
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

        $totalTeachers = DB::table('assoc_user_roles')
            ->join('core_roles', 'assoc_user_roles.role_id', '=', 'core_roles.id')
            ->join('core_users', 'assoc_user_roles.user_id', '=', 'core_users.id')
            ->where('core_roles.name', 'Guru')
            ->where('core_users.is_active', true)
            ->whereNotIn('assoc_user_roles.user_id', $allExcludes)
            ->distinct()
            ->count('assoc_user_roles.user_id');

        $totalVotesCast = VoteEvote::count();

        $recentElections = ElectionEvote::withCount(['candidates', 'voterAccesses', 'votes'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($election) {
                return [
                    'id' => $election->id,
                    'title' => $election->title,
                    'type' => $election->type,
                    'academic_year' => $election->academic_year,
                    'start_at' => $election->start_at->toIso8601String(),
                    'end_at' => $election->end_at->toIso8601String(),
                    'is_published' => $election->is_published,
                    'status' => $election->status,
                    'candidates_count' => $election->candidates_count,
                    'voters_count' => $election->voter_accesses_count,
                    'votes_count' => $election->votes_count,
                ];
            });

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_elections' => $totalElections,
                'total_students' => $totalStudents,
                'total_teachers' => $totalTeachers,
                'total_votes_cast' => $totalVotesCast,
            ],
            'recentElections' => $recentElections,
        ]);
    }
}

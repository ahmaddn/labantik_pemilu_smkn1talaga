<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ElectionEvote;
use App\Models\Student;
use App\Models\User;
use App\Models\VoteEvote;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $totalElections = ElectionEvote::count();
        $totalStudents = Student::count();
        $totalTeachers = User::where('role', 'guru')->count();
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

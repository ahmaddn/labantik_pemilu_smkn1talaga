<?php

namespace App\Http\Controllers\Voter;

use App\Http\Controllers\Controller;
use App\Models\ElectionEvote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class HasilController extends Controller
{
    public function show(string $electionId): Response|RedirectResponse
    {
        $election = ElectionEvote::with(['candidates' => function ($query) {
            $query->withCount('votes')->orderBy('candidate_number', 'asc');
        }])
            ->withCount(['votes', 'voterAccesses'])
            ->findOrFail($electionId);

        if (! $election->is_published) {
            return redirect()->route('voter.dashboard')->with('error', 'Hasil pemilihan ini belum dipublikasikan oleh panitia.');
        }

        $totalVotes = $election->votes_count;

        $candidateNames = [];
        foreach ($election->candidates as $c) {
            $candidateNames[] = trim($c->chairman_name);
            if ($c->vice_chairman_name) {
                $candidateNames[] = trim($c->vice_chairman_name);
            }
        }
        $candidateNames = array_unique(array_filter($candidateNames));

        $candidateClassMap = [];
        if (! empty($candidateNames)) {
            $candClassRows = DB::table('ref_students')
                ->join('ref_student_academic_years', 'ref_students.id', '=', 'ref_student_academic_years.student_id')
                ->join('ref_classes', 'ref_student_academic_years.class_id', '=', 'ref_classes.id')
                ->whereIn('ref_students.full_name', $candidateNames)
                ->whereNull('ref_student_academic_years.mutation_date');

            if ($election->academic_year) {
                $candClassRows->where('ref_student_academic_years.academic_year', $election->academic_year);
            }

            $candClassRows = $candClassRows->select(
                'ref_students.full_name',
                'ref_classes.academic_level',
                'ref_classes.name as class_name'
            )->get();

            foreach ($candClassRows as $row) {
                $levelStr = $row->academic_level ? "Kelas {$row->academic_level} " : 'Kelas ';
                $candidateClassMap[trim($row->full_name)] = trim($levelStr.$row->class_name);
            }
        }

        $results = $election->candidates->map(function ($candidate) use ($totalVotes, $candidateClassMap) {
            $count = $candidate->votes_count;
            $percentage = $totalVotes > 0 ? round(($count / $totalVotes) * 100, 1) : 0;

            return [
                'id' => $candidate->id,
                'candidate_number' => $candidate->candidate_number,
                'chairman_name' => $candidate->chairman_name,
                'chairman_class' => $candidateClassMap[trim($candidate->chairman_name)] ?? null,
                'vice_chairman_name' => $candidate->vice_chairman_name,
                'vice_chairman_class' => $candidate->vice_chairman_name ? ($candidateClassMap[trim($candidate->vice_chairman_name)] ?? null) : null,
                'photo' => $candidate->photo,
                'votes_count' => $count,
                'percentage' => $percentage,
            ];
        });

        return Inertia::render('Voter/Results', [
            'election' => [
                'id' => $election->id,
                'title' => $election->title,
                'description' => $election->description,
                'type' => $election->type,
                'start_at' => $election->start_at->toIso8601String(),
                'end_at' => $election->end_at->toIso8601String(),
                'total_votes' => $totalVotes,
                'total_voters' => $election->voter_accesses_count,
            ],
            'results' => $results,
        ]);
    }
}

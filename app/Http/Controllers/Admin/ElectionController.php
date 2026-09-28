<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ElectionEvote;
use App\Models\SchoolClass;
use App\Models\StudentAcademicYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ElectionController extends Controller
{
    public function index(): Response
    {
        $elections = ElectionEvote::with(['targetClass', 'creator'])
            ->withCount(['candidates', 'voterAccesses', 'votes'])
            ->orderBy('created_at', 'desc')
            ->paginate(9)
            ->through(function ($election) {
                return [
                    'id' => $election->id,
                    'title' => $election->title,
                    'description' => $election->description,
                    'type' => $election->type,
                    'target_voter' => $election->target_voter,
                    'academic_year' => $election->academic_year,
                    'class_id' => $election->class_id,
                    'class_name' => $election->targetClass ? $election->targetClass->name : null,
                    'start_at' => $election->start_at->toIso8601String(),
                    'end_at' => $election->end_at->toIso8601String(),
                    'is_published' => $election->is_published,
                    'is_multi_stage' => $election->is_multi_stage,
                    'max_votes_per_voter' => $election->max_votes_per_voter ?? 1,
                    'current_stage' => $election->current_stage,
                    'total_stages' => $election->total_stages,
                    'status' => $election->status,
                    'candidates_count' => $election->candidates_count,
                    'voters_count' => $election->voter_accesses_count,
                    'votes_count' => $election->votes_count,
                ];
            });

        // Available academic years from ref_student_academic_years
        $academicYears = StudentAcademicYear::select('academic_year')
            ->distinct()
            ->orderBy('academic_year', 'desc')
            ->pluck('academic_year');

        // Classes list for class president elections
        $classes = SchoolClass::orderBy('name', 'asc')
            ->get(['id', 'name', 'academic_year']);

        return Inertia::render('Admin/Elections/Index', [
            'elections' => $elections,
            'academicYears' => $academicYears,
            'classes' => $classes,
        ]);
    }

    public function create(): Response
    {
        $academicYears = StudentAcademicYear::select('academic_year')
            ->distinct()
            ->orderBy('academic_year', 'desc')
            ->pluck('academic_year');

        $classes = SchoolClass::orderBy('name', 'asc')
            ->get(['id', 'name', 'academic_year']);

        return Inertia::render('Admin/Elections/Create', [
            'academicYears' => $academicYears,
            'classes' => $classes,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:osis,vice_principal,class_president,other'],
            'target_voter' => ['required', 'in:all,student,teacher'],
            'academic_year' => ['nullable', 'string', 'max:10'],
            'class_id' => ['nullable', 'string', 'exists:ref_classes,id'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'is_multi_stage' => ['boolean'],
            'max_votes_per_voter' => ['nullable', 'integer', 'min:1', 'max:20'],
            'total_stages' => ['nullable', 'integer', 'min:1', 'max:5'],
            'stage_schedules' => ['nullable', 'array'],
        ]);

        $validated['created_by'] = auth()->id();
        $validated['is_multi_stage'] = $request->boolean('is_multi_stage');
        $validated['max_votes_per_voter'] = (int) ($validated['max_votes_per_voter'] ?? 1);
        $validated['total_stages'] = $validated['is_multi_stage'] ? ($validated['total_stages'] ?? 2) : 1;
        $validated['current_stage'] = 1;

        ElectionEvote::create($validated);

        return redirect('/admin/pemilihan')->with('success', 'Pemilihan baru berhasil dibuat!');
    }

    public function edit(string $id): Response
    {
        $election = ElectionEvote::findOrFail($id);

        $academicYears = StudentAcademicYear::select('academic_year')
            ->distinct()
            ->orderBy('academic_year', 'desc')
            ->pluck('academic_year');

        $classes = SchoolClass::orderBy('name', 'asc')
            ->get(['id', 'name', 'academic_year']);

        return Inertia::render('Admin/Elections/Edit', [
            'election' => [
                'id' => $election->id,
                'title' => $election->title,
                'description' => $election->description,
                'type' => $election->type,
                'target_voter' => $election->target_voter,
                'academic_year' => $election->academic_year,
                'class_id' => $election->class_id,
                'start_at' => $election->start_at ? $election->start_at->format('Y-m-d\TH:i') : '',
                'end_at' => $election->end_at ? $election->end_at->format('Y-m-d\TH:i') : '',
                'is_multi_stage' => $election->is_multi_stage,
                'max_votes_per_voter' => $election->max_votes_per_voter ?? 1,
                'current_stage' => $election->current_stage,
                'total_stages' => $election->total_stages,
                'stage_schedules' => $election->stage_schedules ?? [],
            ],
            'academicYears' => $academicYears,
            'classes' => $classes,
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $election = ElectionEvote::findOrFail($id);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:osis,vice_principal,class_president,other'],
            'target_voter' => ['required', 'in:all,student,teacher'],
            'academic_year' => ['nullable', 'string', 'max:10'],
            'class_id' => ['nullable', 'string', 'exists:ref_classes,id'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'is_multi_stage' => ['boolean'],
            'max_votes_per_voter' => ['nullable', 'integer', 'min:1', 'max:20'],
            'total_stages' => ['nullable', 'integer', 'min:1', 'max:5'],
            'stage_schedules' => ['nullable', 'array'],
        ]);

        $validated['is_multi_stage'] = $request->boolean('is_multi_stage');
        $validated['max_votes_per_voter'] = (int) ($validated['max_votes_per_voter'] ?? 1);
        if ($validated['is_multi_stage']) {
            $validated['total_stages'] = $validated['total_stages'] ?? 2;
        } else {
            $validated['total_stages'] = 1;
            $validated['current_stage'] = 1;
        }

        $election->update($validated);

        return redirect('/admin/pemilihan')->with('success', 'Data pemilihan berhasil diperbarui!');
    }

    public function destroy(string $id): RedirectResponse
    {
        $election = ElectionEvote::findOrFail($id);
        $election->delete();

        return back()->with('success', 'Pemilihan berhasil dihapus!');
    }

    public function togglePublish(string $id): RedirectResponse
    {
        $election = ElectionEvote::findOrFail($id);
        $election->is_published = ! $election->is_published;
        $election->save();

        $statusStr = $election->is_published ? 'dipublikasikan' : 'disembunyikan';

        return back()->with('success', "Hasil pemilihan berhasil {$statusStr}!");
    }
}

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
                    'is_simulation' => $election->is_simulation,
                    'simulation_start_at' => $election->simulation_start_at ? $election->simulation_start_at->toIso8601String() : null,
                    'simulation_end_at' => $election->simulation_end_at ? $election->simulation_end_at->toIso8601String() : null,
                    'simulation_status' => $election->simulation_status,
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
            'is_simulation' => ['boolean'],
            'simulation_start_at' => ['nullable', 'date'],
            'simulation_end_at' => ['nullable', 'date', 'after:simulation_start_at'],
            'max_votes_per_voter' => ['nullable', 'integer', 'min:1', 'max:20'],
            'vote_selection_mode' => ['nullable', 'in:max,exact'],
            'total_stages' => ['nullable', 'integer', 'min:1', 'max:5'],
            'stage_schedules' => ['nullable', 'array'],
        ]);

        $validated['created_by'] = auth()->id();
        $validated['is_multi_stage'] = $request->boolean('is_multi_stage');
        $validated['is_simulation'] = $request->boolean('is_simulation');
        $validated['max_votes_per_voter'] = (int) ($validated['max_votes_per_voter'] ?? 1);
        $validated['vote_selection_mode'] = $validated['vote_selection_mode'] ?? 'max';
        $validated['total_stages'] = $validated['is_multi_stage'] ? ($validated['total_stages'] ?? 2) : 1;
        $validated['current_stage'] = 1;

        if ($validated['is_multi_stage'] && ! empty($validated['stage_schedules'])) {
            $schedules = $validated['stage_schedules'];
            if (isset($schedules['1']['max_votes'])) {
                $validated['max_votes_per_voter'] = (int) $schedules['1']['max_votes'];
            }
            if (isset($schedules['1']['selection_mode'])) {
                $validated['vote_selection_mode'] = $schedules['1']['selection_mode'];
            }
        }

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
                'is_simulation' => $election->is_simulation,
                'simulation_start_at' => $election->simulation_start_at ? $election->simulation_start_at->format('Y-m-d\TH:i') : '',
                'simulation_end_at' => $election->simulation_end_at ? $election->simulation_end_at->format('Y-m-d\TH:i') : '',
                'max_votes_per_voter' => $election->max_votes_per_voter ?? 1,
                'vote_selection_mode' => $election->vote_selection_mode ?? 'max',
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
            'is_simulation' => ['boolean'],
            'simulation_start_at' => ['nullable', 'date'],
            'simulation_end_at' => ['nullable', 'date', 'after:simulation_start_at'],
            'max_votes_per_voter' => ['nullable', 'integer', 'min:1', 'max:20'],
            'vote_selection_mode' => ['nullable', 'in:max,exact'],
            'total_stages' => ['nullable', 'integer', 'min:1', 'max:5'],
            'stage_schedules' => ['nullable', 'array'],
        ]);

        $validated['is_multi_stage'] = $request->boolean('is_multi_stage');
        $validated['is_simulation'] = $request->boolean('is_simulation');
        $validated['max_votes_per_voter'] = (int) ($validated['max_votes_per_voter'] ?? 1);
        $validated['vote_selection_mode'] = $validated['vote_selection_mode'] ?? 'max';
        $validated['total_stages'] = $validated['is_multi_stage'] ? ($validated['total_stages'] ?? 2) : 1;

        if ($validated['is_multi_stage'] && ! empty($validated['stage_schedules'])) {
            $schedules = $validated['stage_schedules'];
            $currentStageKey = (string) ($election->current_stage ?: 1);
            if (isset($schedules[$currentStageKey]['max_votes'])) {
                $validated['max_votes_per_voter'] = (int) $schedules[$currentStageKey]['max_votes'];
            }
            if (isset($schedules[$currentStageKey]['selection_mode'])) {
                $validated['vote_selection_mode'] = $schedules[$currentStageKey]['selection_mode'];
            }
        }

        $election->update($validated);

        return redirect('/admin/pemilihan')->with('success', 'Data pemilihan berhasil diperbarui!');
    }

    public function toggleSimulation(ElectionEvote $election): RedirectResponse
    {
        $election->update([
            'is_simulation' => ! $election->is_simulation,
        ]);

        $status = $election->is_simulation ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Mode simulasi pemilihan berhasil {$status}.");
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

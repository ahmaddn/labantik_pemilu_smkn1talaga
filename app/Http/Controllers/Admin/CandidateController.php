<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CandidateEvote;
use App\Models\ElectionEvote;
use App\Models\Employee;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CandidateController extends Controller
{
    private function getPeopleList(): array
    {
        $employeeNames = [];
        $employees = Employee::select('full_name', 'nip')
            ->orderBy('full_name')
            ->get()
            ->map(function ($e) use (&$employeeNames) {
                $employeeNames[] = strtolower(trim($e->full_name));

                return [
                    'name' => $e->full_name,
                    'type' => 'Guru / Staf',
                    'info' => $e->nip ? "NIP: {$e->nip}" : 'Guru / Staf SMKN 1 Talaga',
                ];
            });

        // Also fetch teachers from core_users that might not be in core_employees
        $additionalTeachers = User::select('name', 'email')
            ->where('role', 'guru')
            ->where('is_active', true)
            ->get()
            ->reject(fn ($u) => in_array(strtolower(trim($u->name)), $employeeNames, true))
            ->map(fn ($u) => [
                'name' => $u->name,
                'type' => 'Guru / Staf',
                'info' => $u->email ? "Email: {$u->email}" : 'Guru / Staf SMKN 1 Talaga',
            ]);

        $students = Student::select('full_name', 'national_student_number')
            ->orderBy('full_name')
            ->get()
            ->map(fn ($s) => [
                'name' => $s->full_name,
                'type' => 'Siswa',
                'info' => $s->national_student_number ? "NISN: {$s->national_student_number}" : 'Siswa SMKN 1 Talaga',
            ]);

        return $employees->concat($additionalTeachers)->concat($students)->values()->toArray();
    }

    public function index(string $electionId): Response
    {
        $election = ElectionEvote::with(['candidates' => function ($query) {
            $query->orderBy('candidate_number', 'asc');
        }])->findOrFail($electionId);

        return Inertia::render('Admin/Candidates/Index', [
            'election' => [
                'id' => $election->id,
                'title' => $election->title,
                'type' => $election->type,
            ],
            'candidates' => $election->candidates->map(function ($candidate) {
                return [
                    'id' => $candidate->id,
                    'candidate_number' => $candidate->candidate_number,
                    'chairman_name' => $candidate->chairman_name,
                    'vice_chairman_name' => $candidate->vice_chairman_name,
                    'photo' => $candidate->photo,
                    'vision_mission' => $candidate->vision_mission,
                ];
            }),
        ]);
    }

    public function create(string $electionId): Response
    {
        $election = ElectionEvote::withCount('candidates')->findOrFail($electionId);

        return Inertia::render('Admin/Candidates/Create', [
            'election' => [
                'id' => $election->id,
                'title' => $election->title,
                'type' => $election->type,
                'next_number' => $election->candidates_count + 1,
            ],
            'people' => $this->getPeopleList(),
        ]);
    }

    public function store(Request $request, string $electionId): RedirectResponse
    {
        ElectionEvote::findOrFail($electionId);

        $validated = $request->validate([
            'candidate_number' => ['required', 'integer', 'min:1'],
            'chairman_name' => ['required', 'string', 'max:255'],
            'vice_chairman_name' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'max:2048'], // 2MB max
            'vision_mission' => ['nullable', 'string'],
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('candidates', 'public');
            $photoPath = '/storage/'.$path;
        }

        CandidateEvote::create([
            'election_id' => $electionId,
            'candidate_number' => $validated['candidate_number'],
            'chairman_name' => $validated['chairman_name'],
            'vice_chairman_name' => $validated['vice_chairman_name'] ?? null,
            'photo' => $photoPath,
            'vision_mission' => $validated['vision_mission'] ?? null,
        ]);

        return redirect("/admin/pemilihan/{$electionId}/kandidat")->with('success', 'Kandidat baru berhasil ditambahkan!');
    }

    public function edit(string $electionId, string $candidateId): Response
    {
        $election = ElectionEvote::findOrFail($electionId);
        $candidate = CandidateEvote::where('election_id', $electionId)->findOrFail($candidateId);

        return Inertia::render('Admin/Candidates/Edit', [
            'election' => [
                'id' => $election->id,
                'title' => $election->title,
                'type' => $election->type,
            ],
            'candidate' => [
                'id' => $candidate->id,
                'candidate_number' => $candidate->candidate_number,
                'chairman_name' => $candidate->chairman_name,
                'vice_chairman_name' => $candidate->vice_chairman_name,
                'photo' => $candidate->photo,
                'vision_mission' => $candidate->vision_mission,
            ],
            'people' => $this->getPeopleList(),
        ]);
    }

    public function update(Request $request, string $electionId, string $candidateId): RedirectResponse
    {
        $candidate = CandidateEvote::where('election_id', $electionId)->findOrFail($candidateId);

        $validated = $request->validate([
            'candidate_number' => ['required', 'integer', 'min:1'],
            'chairman_name' => ['required', 'string', 'max:255'],
            'vice_chairman_name' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'vision_mission' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('candidates', 'public');
            $candidate->photo = '/storage/'.$path;
        }

        $candidate->candidate_number = $validated['candidate_number'];
        $candidate->chairman_name = $validated['chairman_name'];
        $candidate->vice_chairman_name = $validated['vice_chairman_name'] ?? null;
        $candidate->vision_mission = $validated['vision_mission'] ?? null;
        $candidate->save();

        return redirect("/admin/pemilihan/{$electionId}/kandidat")->with('success', 'Data kandidat berhasil diperbarui!');
    }

    public function destroy(string $electionId, string $candidateId): RedirectResponse
    {
        $candidate = CandidateEvote::where('election_id', $electionId)->findOrFail($candidateId);
        $candidate->delete();

        return back()->with('success', 'Kandidat berhasil dihapus!');
    }
}

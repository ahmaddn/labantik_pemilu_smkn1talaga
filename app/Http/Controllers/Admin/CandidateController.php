<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CandidateEvote;
use App\Models\ElectionEvote;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CandidateController extends Controller
{
    private function getPeopleList(?string $academicYear = null): array
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

        $studentQuery = DB::table('ref_students')
            ->join('ref_student_academic_years', function ($j) use ($academicYear) {
                $j->on('ref_students.id', '=', 'ref_student_academic_years.student_id')
                    ->whereNull('ref_student_academic_years.mutation_date');
                if ($academicYear) {
                    $j->where('ref_student_academic_years.academic_year', $academicYear);
                }
            })
            ->join('ref_classes', 'ref_student_academic_years.class_id', '=', 'ref_classes.id')
            ->select(
                'ref_students.full_name',
                'ref_students.student_number',
                'ref_classes.academic_level',
                'ref_classes.name as class_name'
            )
            ->orderBy('ref_students.full_name');

        $students = $studentQuery->get()
            ->unique('full_name')
            ->map(function ($s) {
                $classInfo = $s->class_name ? ($s->academic_level ? "Kelas {$s->academic_level} {$s->class_name}" : "Kelas {$s->class_name}") : null;
                $nisInfo = $s->student_number ? "NIS: {$s->student_number}" : null;
                $info = $classInfo && $nisInfo ? "{$classInfo} | {$nisInfo}" : ($classInfo ?? ($nisInfo ?? 'Siswa SMKN 1 Talaga'));

                return [
                    'name' => $s->full_name,
                    'type' => 'Siswa',
                    'info' => $info,
                ];
            });

        return $employees->concat($additionalTeachers)->concat($students)->values()->toArray();
    }

    public function index(string $electionId): Response
    {
        $election = ElectionEvote::with(['candidates' => function ($query) {
            $query->orderBy('candidate_number', 'asc');
        }])->findOrFail($electionId);

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

        return Inertia::render('Admin/Candidates/Index', [
            'election' => [
                'id' => $election->id,
                'title' => $election->title,
                'type' => $election->type,
            ],
            'candidates' => $election->candidates->map(function ($candidate) use ($candidateClassMap) {
                return [
                    'id' => $candidate->id,
                    'candidate_number' => $candidate->candidate_number,
                    'chairman_name' => $candidate->chairman_name,
                    'chairman_class' => $candidateClassMap[trim($candidate->chairman_name)] ?? null,
                    'vice_chairman_name' => $candidate->vice_chairman_name,
                    'vice_chairman_class' => $candidate->vice_chairman_name ? ($candidateClassMap[trim($candidate->vice_chairman_name)] ?? null) : null,
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
            'people' => $this->getPeopleList($election->academic_year),
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
            'people' => $this->getPeopleList($election->academic_year),
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

    /**
     * Download CSV template for candidate import.
     */
    public function downloadTemplate(string $electionId): StreamedResponse
    {
        $election = ElectionEvote::findOrFail($electionId);
        $cleanTitle = Str::slug($election->title, '_');
        $fileName = "template_import_kandidat_{$cleanTitle}.csv";

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM so Excel opens it with proper encoding
            fwrite($handle, "\xEF\xBB\xBF");

            // Header row
            fputcsv($handle, ['Nomor Urut', 'Nama Ketua', 'Nama Wakil', 'Visi & Misi']);

            // Example rows
            fputcsv($handle, [1, 'Ahmad Fauzi', 'Siti Rahmawati', 'Visi: Mewujudkan sekolah inovatif. Misi: 1. Kolaborasi siswa, 2. Fasilitas terbuka.']);
            fputcsv($handle, [2, 'Budi Santoso', 'Dewi Lestari', 'Visi: Disiplin dan berprestasi. Misi: Mengembangkan bakat minat siswa.']);

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Import candidate list from CSV / Excel file.
     */
    public function import(Request $request, string $electionId): RedirectResponse
    {
        $election = ElectionEvote::findOrFail($electionId);

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:5120'], // 5MB max
        ]);

        $uploadedFile = $request->file('file');
        $path = $uploadedFile->getRealPath();

        $rows = [];
        if (($handle = fopen($path, 'r')) !== false) {
            // Strip possible UTF-8 BOM
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            while (($data = fgetcsv($handle, 4096, ',')) !== false) {
                // If single column detected, try semicolon delimiter
                if (count($data) === 1 && str_contains($data[0], ';')) {
                    $data = str_getcsv($data[0], ';');
                }
                $rows[] = $data;
            }
            fclose($handle);
        }

        if (empty($rows)) {
            return back()->with('error', 'File yang diunggah kosong atau format data tidak dapat dibaca.');
        }

        // Header detection (skip first row if it contains headers like 'nomor', 'ketua', etc.)
        $startIndex = 0;
        $firstRowText = strtolower(implode(' ', $rows[0]));
        if (str_contains($firstRowText, 'nomor') || str_contains($firstRowText, 'ketua') || str_contains($firstRowText, 'nama')) {
            $startIndex = 1;
        }

        $importedCount = 0;
        $errors = [];

        for ($i = $startIndex; $i < count($rows); $i++) {
            $row = $rows[$i];
            // Filter out empty rows
            $cleanRow = array_filter(array_map('trim', $row));
            if (empty($cleanRow)) {
                continue;
            }

            $candidateNumber = isset($row[0]) && is_numeric(trim($row[0])) ? (int) trim($row[0]) : null;
            $chairmanName = isset($row[1]) ? trim($row[1]) : '';
            $viceChairmanName = isset($row[2]) ? trim($row[2]) : null;
            $visionMission = isset($row[3]) ? trim($row[3]) : null;

            if (! $candidateNumber || empty($chairmanName)) {
                $lineNum = $i + 1;
                $errors[] = "Baris ke-{$lineNum}: Nomor urut dan Nama Ketua wajib diisi.";

                continue;
            }

            // Upsert or create candidate by election_id + candidate_number
            CandidateEvote::updateOrCreate(
                [
                    'election_id' => $electionId,
                    'candidate_number' => $candidateNumber,
                ],
                [
                    'chairman_name' => $chairmanName,
                    'vice_chairman_name' => ! empty($viceChairmanName) ? $viceChairmanName : null,
                    'vision_mission' => ! empty($visionMission) ? nl2br(e($visionMission)) : null,
                    'is_qualified' => true,
                ]
            );

            $importedCount++;
        }

        if ($importedCount === 0 && ! empty($errors)) {
            return back()->with('error', 'Gagal mengimpor kandidat. '.implode(' ', array_slice($errors, 0, 3)));
        }

        $message = "Berhasil mengimpor {$importedCount} kandidat.";
        if (! empty($errors)) {
            $message .= ' Beberapa baris dilewati karena data tidak lengkap.';
        }

        return redirect("/admin/pemilihan/{$electionId}/kandidat")->with('success', $message);
    }
}

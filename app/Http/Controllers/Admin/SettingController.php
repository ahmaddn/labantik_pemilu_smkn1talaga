<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSettingEvote;
use App\Models\StudentAcademicYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function index(): Response
    {
        $settings = [
            'app_name' => AppSettingEvote::getValue('app_name', 'LabAntik Pemilu SMKN 1 Talaga'),
            'app_description' => AppSettingEvote::getValue('app_description', 'Sistem Pemilihan Umum E-Voting SMKN 1 Talaga'),
            'active_academic_year' => AppSettingEvote::getValue('active_academic_year', '2025/2026'),
            'app_logo' => AppSettingEvote::getValue('app_logo'),
            'app_favicon' => AppSettingEvote::getValue('app_favicon'),
        ];

        $academicYears = StudentAcademicYear::select('academic_year')
            ->distinct()
            ->orderBy('academic_year', 'desc')
            ->pluck('academic_year');

        return Inertia::render('Admin/Settings/Index', [
            'settings' => $settings,
            'academicYears' => $academicYears,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_name' => ['required', 'string', 'max:255'],
            'app_description' => ['nullable', 'string'],
            'active_academic_year' => ['required', 'string', 'max:20'],
            'app_logo' => ['nullable', 'image', 'max:2048'],
            'app_favicon' => ['nullable', 'file', 'mimes:ico,png,jpg,jpeg,svg', 'max:1024'],
        ]);

        AppSettingEvote::setValue('app_name', $validated['app_name']);
        AppSettingEvote::setValue('app_description', $validated['app_description'] ?? '');
        AppSettingEvote::setValue('active_academic_year', $validated['active_academic_year']);

        if ($request->hasFile('app_logo')) {
            $path = $request->file('app_logo')->store('settings', 'public');
            AppSettingEvote::setValue('app_logo', '/storage/'.$path);
        }

        if ($request->hasFile('app_favicon')) {
            $path = $request->file('app_favicon')->store('settings', 'public');
            $faviconPath = '/storage/'.$path;

            AppSettingEvote::setValue('app_favicon', $faviconPath);

            // Copy to root public/favicon.ico for browser default requests
            @copy(storage_path('app/public/'.$path), public_path('favicon.ico'));
        }

        return back()->with('success', 'Pengaturan aplikasi berhasil disimpan!');
    }
}

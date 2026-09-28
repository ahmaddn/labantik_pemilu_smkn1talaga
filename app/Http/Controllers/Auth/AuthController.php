<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    /**
     * Display the login view.
     */
    public function showLogin(Request $request): Response|RedirectResponse
    {
        // 1. Check if user is already logged in
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->isCommittee() || $user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('voter.dashboard');
        }

        // 2. Check for superapp shared session / cookie / token (SSO auto-login)
        $superappUserId = $request->session()->get('user_id')
            ?? $request->session()->get('superapp_user_id')
            ?? $request->cookie('superapp_user_id')
            ?? $request->input('sso_user_id');

        if ($superappUserId) {
            $user = User::find($superappUserId);
            if ($user && $user->is_active) {
                Auth::login($user);
                $request->session()->regenerate();

                if ($user->isCommittee() || $user->isAdmin()) {
                    return redirect()->route('admin.dashboard');
                }

                return redirect()->route('voter.dashboard');
            }
        }

        return Inertia::render('Auth/Login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'identifier' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $identifier = trim($credentials['identifier']);
        $password = $credentials['password'];

        $user = null;

        // 1. Try finding student by NIS (student_number) or NISN
        $student = Student::where('student_number', $identifier)
            ->orWhere('national_student_number', $identifier)
            ->first();

        if ($student && $student->user_id) {
            $user = User::find($student->user_id);
        }

        // 2. If not found, try finding employee/teacher by NIP
        if (! $user) {
            $employee = Employee::where('nip', $identifier)->first();
            if ($employee && $employee->user_id) {
                $user = User::find($employee->user_id);
            }
        }

        // 3. If still not found, try finding by email
        if (! $user) {
            $user = User::where('email', $identifier)->first();
        }

        // Validate user existence and password
        if (! $user || ! Hash::check($password, $user->password)) {
            return back()->withErrors([
                'identifier' => 'NIS/NIP/Email atau password yang Anda masukkan salah.',
            ])->onlyInput('identifier');
        }

        if (! $user->is_active) {
            return back()->withErrors([
                'identifier' => 'Akun Anda sedang tidak aktif. Silahkan hubungi panitia.',
            ])->onlyInput('identifier');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if ($user->isCommittee() || $user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('voter.dashboard'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

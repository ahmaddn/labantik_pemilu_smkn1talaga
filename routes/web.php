<?php

use App\Http\Controllers\Admin\CandidateController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ElectionController;
use App\Http\Controllers\Admin\GuideController;
use App\Http\Controllers\Admin\ResultController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SimulationController;
use App\Http\Controllers\Admin\VoterAccessController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Voter\DashboardController as VoterDashboardController;
use App\Http\Controllers\Voter\HasilController;
use App\Http\Controllers\Voter\VotingController;
use Illuminate\Support\Facades\Route;

// Public Landing Page
Route::get('/', [LandingController::class, 'index'])->name('home');

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Voter Interface Routes
    Route::get('/dashboard', [VoterDashboardController::class, 'index'])->name('voter.dashboard');
    Route::get('/vote/{election}', [VotingController::class, 'show'])->name('voter.vote.show');
    Route::post('/vote/{election}', [VotingController::class, 'vote'])->name('voter.vote.store');
    Route::get('/hasil/{election}', [HasilController::class, 'show'])->name('voter.results');

    // Admin & Committee Interface Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Elections CRUD
        Route::get('/pemilihan', [ElectionController::class, 'index'])->name('elections.index');
        Route::get('/pemilihan/create', [ElectionController::class, 'create'])->name('elections.create');
        Route::post('/pemilihan', [ElectionController::class, 'store'])->name('elections.store');
        Route::get('/pemilihan/{election}/edit', [ElectionController::class, 'edit'])->name('elections.edit');
        Route::put('/pemilihan/{election}', [ElectionController::class, 'update'])->name('elections.update');
        Route::delete('/pemilihan/{election}', [ElectionController::class, 'destroy'])->name('elections.destroy');
        Route::post('/pemilihan/{election}/toggle-publish', [ElectionController::class, 'togglePublish'])->name('elections.toggle-publish');
        Route::post('/pemilihan/{election}/toggle-simulation', [ElectionController::class, 'toggleSimulation'])->name('elections.toggle-simulation');
        Route::get('/pemilihan/{election}/hasil', [ResultController::class, 'show'])->name('elections.results');
        Route::post('/pemilihan/{election}/advance-stage', [ResultController::class, 'advanceStage'])->name('elections.advance-stage');
        Route::post('/pemilihan/{election}/reset-stages', [ResultController::class, 'resetStages'])->name('elections.reset-stages');

        // Simulation Routes
        Route::get('/pemilihan/{election}/simulasi', [SimulationController::class, 'show'])->name('elections.simulation.show');
        Route::post('/pemilihan/{election}/simulasi/vote', [SimulationController::class, 'vote'])->name('elections.simulation.vote');
        Route::post('/pemilihan/{election}/simulasi/advance-stage', [SimulationController::class, 'advanceStage'])->name('elections.simulation.advance');
        Route::delete('/pemilihan/{election}/simulasi/reset', [SimulationController::class, 'reset'])->name('elections.simulation.reset');

        // Candidate Management
        Route::get('/pemilihan/{election}/kandidat', [CandidateController::class, 'index'])->name('candidates.index');
        Route::get('/pemilihan/{election}/kandidat/create', [CandidateController::class, 'create'])->name('candidates.create');
        Route::post('/pemilihan/{election}/kandidat', [CandidateController::class, 'store'])->name('candidates.store');
        Route::get('/pemilihan/{election}/kandidat/{candidate}/edit', [CandidateController::class, 'edit'])->name('candidates.edit');
        Route::post('/pemilihan/{election}/kandidat/{candidate}', [CandidateController::class, 'update'])->name('candidates.update');
        Route::delete('/pemilihan/{election}/kandidat/{candidate}', [CandidateController::class, 'destroy'])->name('candidates.destroy');

        // Voter Access Management
        Route::get('/pemilihan/{election}/pemilih', [VoterAccessController::class, 'index'])->name('voters.index');
        Route::post('/pemilihan/{election}/generate-pemilih', [VoterAccessController::class, 'generateBatch'])->name('voters.generate');
        Route::delete('/pemilihan/{election}/pemilih-semua', [VoterAccessController::class, 'destroyAll'])->name('voters.destroy-all');
        Route::delete('/pemilihan/{election}/pemilih/{access}', [VoterAccessController::class, 'destroy'])->name('voters.destroy');

        // Application Settings
        Route::get('/pengaturan', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/pengaturan', [SettingController::class, 'update'])->name('settings.update');

        // Committee Guide
        Route::get('/panduan', [GuideController::class, 'index'])->name('guide.index');
    });
});

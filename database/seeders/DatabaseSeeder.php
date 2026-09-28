<?php

namespace Database\Seeders;

use App\Models\CandidateEvote;
use App\Models\ElectionEvote;
use App\Models\User;
use App\Models\VoterAccessEvote;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $hashedPassword = Hash::make('password');

        // 1. Admin / Panitia Account
        $admin = User::firstOrCreate(
            ['email' => 'admin@smkn1talaga.sch.id'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Panitia Pemilu (Admin)',
                'password' => $hashedPassword,
            ]
        );
        $admin->password = $hashedPassword;
        $admin->save();

        // 2. Siswa Test Account
        $siswa = User::firstOrCreate(
            ['email' => '25261001@smkn1talaga.sch.id'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'ABYAD JABAR SA`BANI',
                'password' => $hashedPassword,
            ]
        );
        $siswa->password = $hashedPassword;
        $siswa->save();

        // Link ref_students record to user_id (safely create if not present)
        if (Schema::hasTable('ref_students')) {
            $studentExists = DB::table('ref_students')->where('student_number', '25261001')->exists();
            if ($studentExists) {
                DB::table('ref_students')
                    ->where('student_number', '25261001')
                    ->update(['user_id' => $siswa->id]);
            } else {
                DB::table('ref_students')->insert([
                    'id' => (string) Str::uuid(),
                    'user_id' => $siswa->id,
                    'student_number' => '25261001',
                    'full_name' => 'ABYAD JABAR SA`BANI',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 3. Guru Test Account
        $guru = User::firstOrCreate(
            ['email' => 'asepdonipradana@gmail.com'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Asep Doni Pradana, S.Kom., M.Pd., Gr.',
                'password' => $hashedPassword,
            ]
        );
        $guru->password = $hashedPassword;
        $guru->save();

        if (Schema::hasTable('core_employees')) {
            $employeeExists = DB::table('core_employees')->where('nip', '199203062022211018')->exists();
            if ($employeeExists) {
                DB::table('core_employees')
                    ->where('nip', '199203062022211018')
                    ->update(['user_id' => $guru->id]);
            } else {
                DB::table('core_employees')->insert([
                    'id' => (string) Str::uuid(),
                    'user_id' => $guru->id,
                    'nip' => '199203062022211018',
                    'full_name' => 'Asep Doni Pradana, S.Kom., M.Pd., Gr.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 4. Sample Election for Testing (UUID auto-handled by HasUuids model)
        $election = ElectionEvote::firstOrCreate(
            ['title' => 'Pemilihan Ketua OSIS Masa Bakti 2026/2027'],
            [
                'created_by' => $admin->id,
                'description' => 'Pemilihan umum terbuka untuk menentukan Ketua dan Wakil Ketua OSIS SMKN 1 Talaga.',
                'type' => 'osis',
                'target_voter' => 'all',
                'academic_year' => '2026/2027',
                'start_at' => now()->subHours(2),
                'end_at' => now()->addDays(7),
                'is_published' => true,
            ]
        );

        // Candidates (UUID auto-handled by HasUuids model)
        CandidateEvote::firstOrCreate(
            [
                'election_id' => $election->id,
                'candidate_number' => 1,
            ],
            [
                'chairman_name' => 'Ahmad Fajar',
                'vice_chairman_name' => 'Siti Nurhaliza',
                'vision_mission' => "VISI:\nMewujudkan OSIS SMKN 1 Talaga yang inovatif, berkarakter, dan berdaya saing tinggi.\n\nMISI:\n1. Meningkatkan kegiatan ekstrakurikuler berbasis teknologi.\n2. Mengadakan event tahunan skala kabupaten.",
            ]
        );

        CandidateEvote::firstOrCreate(
            [
                'election_id' => $election->id,
                'candidate_number' => 2,
            ],
            [
                'chairman_name' => 'Bintang Pratama',
                'vice_chairman_name' => 'Dwi Rahmawati',
                'vision_mission' => "VISI:\nMenjadikan siswa SMKN 1 Talaga aktif, kreatif, dan berintegritas tinggi.\n\nMISI:\n1. Memperkuat kolaborasi antar jurusaan.\n2. Optimalisasi wadah aspirasi siswa digital.",
            ]
        );

        // Grant Voter Access using Eloquent HasUuids model so UUID primary key is generated
        VoterAccessEvote::firstOrCreate(
            ['election_id' => $election->id, 'user_id' => $siswa->id],
            ['is_voted' => false]
        );

        VoterAccessEvote::firstOrCreate(
            ['election_id' => $election->id, 'user_id' => $guru->id],
            ['is_voted' => false]
        );

        VoterAccessEvote::firstOrCreate(
            ['election_id' => $election->id, 'user_id' => $admin->id],
            ['is_voted' => false]
        );
    }
}

<template>
    <AdminLayout>
        <template #header>Panduan & Operasional Panitia Pemilu</template>

        <div class="space-y-6">
            <!-- Header Title Card -->
            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="shrink-0 rounded-xl bg-blue-50 p-3 text-blue-600 dark:bg-blue-950 dark:text-blue-400"
                    >
                        <BookOpen class="h-6 w-6" />
                    </div>
                    <div>
                        <h2
                            class="text-base font-extrabold text-slate-900 dark:text-white"
                        >
                            Panduan Operasional Panitia E-Voting
                        </h2>
                        <p
                            class="text-xs text-slate-500 dark:text-slate-400"
                        >
                            Pilih modul alur kerja di sebelah kiri untuk melihat petunjuk pelaksanaan lengkap.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Master-Detail Side-by-Side Grid Layout -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-12 items-start">
                <!-- Left Sidebar Menu (4 Cols) -->
                <div class="space-y-2 md:col-span-4 lg:col-span-3">
                    <button
                        v-for="(section, idx) in guideSections"
                        :key="idx"
                        @click="activeSection = idx"
                        :class="[
                            'w-full flex items-center justify-between text-left rounded-xl p-3.5 border transition-all cursor-pointer',
                            activeSection === idx
                                ? 'border-blue-600 bg-blue-50/70 text-blue-900 shadow-sm dark:border-blue-500 dark:bg-blue-950/50 dark:text-white'
                                : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/60',
                        ]"
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            <span
                                :class="[
                                    'flex h-6 w-6 shrink-0 items-center justify-center rounded-lg text-[11px] font-black',
                                    activeSection === idx
                                        ? 'bg-blue-600 text-white'
                                        : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400',
                                ]"
                            >
                                0{{ idx + 1 }}
                            </span>
                            <div class="truncate">
                                <h4 class="truncate text-xs font-extrabold">
                                    {{ section.shortTitle }}
                                </h4>
                            </div>
                        </div>
                        <component
                            :is="section.icon"
                            :class="[
                                'h-4 w-4 shrink-0',
                                activeSection === idx
                                    ? 'text-blue-600 dark:text-blue-400'
                                    : 'text-slate-400',
                            ]"
                        />
                    </button>
                </div>

                <!-- Right Detail Panel (8 Cols) -->
                <div
                    class="space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:col-span-8 lg:col-span-9 dark:border-slate-800 dark:bg-slate-900"
                >
                    <!-- Detail Header -->
                    <div
                        class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4 dark:border-slate-800"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="rounded-xl bg-blue-50 p-2.5 text-blue-600 dark:bg-blue-950 dark:text-blue-400"
                            >
                                <component
                                    :is="guideSections[activeSection].icon"
                                    class="h-5 w-5"
                                />
                            </div>
                            <div>
                                <span
                                    class="text-[10px] font-extrabold tracking-wider text-blue-600 uppercase dark:text-blue-400"
                                >
                                    Tahap {{ activeSection + 1 }} dari
                                    {{ guideSections.length }}
                                </span>
                                <h3
                                    class="text-base font-extrabold text-slate-900 dark:text-white"
                                >
                                    {{ guideSections[activeSection].title }}
                                </h3>
                            </div>
                        </div>

                        <!-- Step Indicator Badge -->
                        <span
                            class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                        >
                            Modul 0{{ activeSection + 1 }}
                        </span>
                    </div>

                    <!-- Description -->
                    <p
                        class="text-xs leading-relaxed text-slate-600 dark:text-slate-400"
                    >
                        {{ guideSections[activeSection].description }}
                    </p>

                    <!-- Steps Cards Grid -->
                    <div class="grid grid-cols-1 gap-3.5 lg:grid-cols-3">
                        <div
                            v-for="(
                                step, sIdx
                            ) in guideSections[activeSection].steps"
                            :key="sIdx"
                            class="space-y-1.5 rounded-xl border border-slate-100 bg-slate-50 p-4 dark:border-slate-800/80 dark:bg-slate-900/60"
                        >
                            <div class="flex items-center gap-2">
                                <span
                                    class="flex h-5 w-5 items-center justify-center rounded-md bg-slate-200 text-[10px] font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-300"
                                >
                                    {{ sIdx + 1 }}
                                </span>
                                <h4
                                    class="text-xs font-extrabold text-slate-900 dark:text-white"
                                >
                                    {{ step.title }}
                                </h4>
                            </div>
                            <p
                                class="text-xs leading-relaxed text-slate-600 dark:text-slate-400"
                            >
                                {{ step.detail }}
                            </p>
                        </div>
                    </div>

                    <!-- Tips Section -->
                    <div
                        v-if="guideSections[activeSection].tips"
                        class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-3.5 text-xs dark:border-amber-900/60 dark:bg-amber-950/40"
                    >
                        <Info
                            class="mt-0.5 h-4 w-4 shrink-0 text-amber-600 dark:text-amber-400"
                        />
                        <div class="space-y-0.5">
                            <strong
                                class="font-extrabold text-amber-900 dark:text-amber-200"
                                >Catatan Penting:</strong
                            >
                            <p class="text-amber-800 dark:text-amber-300">
                                {{ guideSections[activeSection].tips }}
                            </p>
                        </div>
                    </div>

                    <!-- Bottom Prev/Next Controls -->
                    <div
                        class="flex items-center justify-between border-t border-slate-100 pt-4 dark:border-slate-800"
                    >
                        <button
                            type="button"
                            :disabled="activeSection === 0"
                            @click="activeSection--"
                            class="flex cursor-pointer items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 transition-colors hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                        >
                            <ArrowLeft class="h-3.5 w-3.5" />
                            <span>Sebelumnya</span>
                        </button>

                        <span
                            class="text-xs font-bold text-slate-500 dark:text-slate-400"
                        >
                            {{ activeSection + 1 }} dari
                            {{ guideSections.length }} Modul
                        </span>

                        <button
                            type="button"
                            :disabled="
                                activeSection === guideSections.length - 1
                            "
                            @click="activeSection++"
                            class="flex cursor-pointer items-center gap-1.5 rounded-xl bg-blue-600 px-3.5 py-2 text-xs font-bold text-white shadow-sm transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-40"
                        >
                            <span>Selanjutnya</span>
                            <ArrowRight class="h-3.5 w-3.5" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import {
    BookOpen,
    Vote,
    Users,
    UserCheck,
    Layers,
    BarChart3,
    Info,
    Sliders,
    ArrowLeft,
    ArrowRight,
} from '@lucide/vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const activeSection = ref(0);

const guideSections = [
    {
        title: 'Pembuatan Acara Pemilihan',
        shortTitle: '1. Buat Acara Pemilihan',
        icon: Vote,
        description:
            'Langkah awal untuk mengonfigurasi kegiatan pemilihan umum sekolah, target pemilih, kuota suara per pemilih, dan mode tahap.',
        steps: [
            {
                title: 'Buka Form Pemilihan Baru',
                detail: 'Klik tombol "+ Buat Pemilihan Baru" pada Dashboard Admin atau halaman Kelola Pemilihan.',
            },
            {
                title: 'Kategori & Target Pemilih',
                detail: 'Pilih jenis acara (Ketua OSIS, Wakasek, Ketua Kelas, Umum) dan target pemilih (Semua, Siswa, Guru, Per Kelas).',
            },
            {
                title: 'Multi-Choice & Multi-Stage',
                detail: 'Atur "Jumlah Suara per Pemilih" (misal 1 untuk single choice, atau 4 untuk memilih 4 paslon sekaligus) & centang Multi-Stage jika bertahap.',
            },
        ],
        tips: 'Fitur Multi-Choice memungkinkan 1 akun pemilih memberikan hingga N suara kandidat sekaligus secara dinamis. Timezone terkonfigurasi WIB presisi.',
    },
    {
        title: 'Pengelolaan Pasangan Calon (Kandidat)',
        shortTitle: '2. Kelola Pasangan Calon',
        icon: Users,
        description:
            'Mendaftarkan kandidat atau pasangan calon beserta informasi identitas, foto profil, dan visi-misi.',
        steps: [
            {
                title: 'Identitas & Nomor Urut',
                detail: 'Pilih nama calon ketua dan wakil dari daftar pegawai/siswa terdaftar. Tentukan nomor urut paslon.',
            },
            {
                title: 'Visi & Misi Rich Text',
                detail: 'Gunakan editor teks Quill untuk menyusun visi dan misi lengkap dengan poin-poin (bullet/number) dan penekanan teks.',
            },
            {
                title: 'Upload Foto Paslon',
                detail: 'Unggah foto resmi paslon (JPG/PNG, max 2MB). Foto disimpan di direktori storage symlink secara aman.',
            },
        ],
        tips: 'Tampilan Visi & Misi Quill disajikan persis di modal pemilih lengkap dengan penomoran dan pembagian paragraf.',
    },
    {
        title: 'Alokasi Hak Pilih (Voter Access)',
        shortTitle: '3. Alokasi Hak Pilih',
        icon: UserCheck,
        description:
            'Mendaftarkan pemilih yang berhak memberikan suara sesuai kategori acara pemilihan.',
        steps: [
            {
                title: 'Buka Kelola Hak Pilih',
                detail: 'Klik tombol "Hak Pilih" pada kartu pemilihan yang diinginkan di halaman Kelola Pemilihan.',
            },
            {
                title: 'Generate Pemilih Otomatis',
                detail: 'Klik "Generate Pemilih Otomatis". Sistem mengambil akun siswa aktif / guru sesuai kriteria target.',
            },
            {
                title: 'Verifikasi & Pengendalian',
                detail: 'Panitia dapat memeriksa daftar pemilih terdaftar dan menghapus hak pilih akun tertentu bila diperlukan.',
            },
        ],
        tips: 'Hak pilih terlindungi dengan sistem proteksi atomic SQL update. Setiap akun hanya dapat mencoblos 1 kali per tahap.',
    },
    {
        title: 'Pelaksanaan & Pemantauan Voting',
        shortTitle: '4. Pelaksanaan Voting',
        icon: Sliders,
        description:
            'Memantau jalannya proses pemungutan suara secara real-time.',
        steps: [
            {
                title: 'Status Otomatis Sesuai Jam',
                detail: 'Status "Belum Mulai" sebelum waktu mulai, "Berlangsung" saat jam aktif, dan "Selesai" setelah melewati waktu akhir.',
            },
            {
                title: 'Kerahasiaan Suara (Anonymous)',
                detail: 'Suara yang dicoblos disimpan secara anonim tanpa mencatat identitas pemilih pada tabel suara.',
            },
            {
                title: 'Pantau Partisipasi',
                detail: 'Panitia dapat melihat jumlah suara masuk dan persentase partisipasi secara langsung dari Dashboard Admin.',
            },
        ],
        tips: 'Pemilih yang sudah memberikan suara pada Tahap 1 tidak akan bisa memilih ulang di tahap yang sama.',
    },
    {
        title: 'Penyaringan Bertahap (Multi-Stage)',
        shortTitle: '5. Saring Babak / Tahap',
        icon: Layers,
        description:
            'Menyaring kandidat terbaik untuk melaju ke tahap babak selanjutnya (Tahap 1 ke Tahap 2 dst).',
        steps: [
            {
                title: 'Buka Hasil Pemilihan',
                detail: 'Klik tombol "Lihat Hasil" lalu pilih "Saring Top Paslon & Lanjutkan Tahap".',
            },
            {
                title: 'Mode Otomatis / Manual',
                detail: 'Gunakan Mode Otomatis (ketik kuota misal Top 10/Top 5) atau Mode Manual (centang sendiri paslon yang lolos).',
            },
            {
                title: 'Jadwal Waktu Tahap Baru',
                detail: 'Pilih untuk memulai tahap baru "Sekarang (Detik ini)" atau tentukan tanggal & jam mulai/selesai yang baru.',
            },
        ],
        tips: 'Kandidat yang tidak tereliminasi di tahap sebelumnya akan tetap aktif dan dapat dipilih kembali di tahap baru.',
    },
    {
        title: 'Publikasi & Cetak Rekapitulasi',
        shortTitle: '6. Publikasi & Cetak',
        icon: BarChart3,
        description:
            'Mempublikasikan hasil perolehan suara resmi dan mencetak dokumen laporan.',
        steps: [
            {
                title: 'Publikasikan Hasil ke Pemilih',
                detail: 'Klik tombol "Publikasikan Hasil" agar perolehan suara dapat dilihat oleh pemilih.',
            },
            {
                title: 'Sembunyikan Hasil',
                detail: 'Panitia dapat menyembunyikan kembali hasil perolehan suara apabila proses penghitungan belum selesai.',
            },
            {
                title: 'Cetak Dokumen Rekap',
                detail: 'Gunakan tombol "Cetak Rekap" untuk mencetak dokumen fisik rekapitulasi perolehan suara resmi.',
            },
        ],
        tips: 'Hasil suara yang tersembunyi tidak dapat diakses oleh akun pemilih hingga panitia mengaktifkan tombol publikasi.',
    },
];
</script>

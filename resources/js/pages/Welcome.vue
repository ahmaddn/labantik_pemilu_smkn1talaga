<template>
    <VoterLayout>
        <div class="space-y-16 py-4 lg:space-y-20">
            <!-- 1. HERO SECTION (2-Column Hero with Card on the Right) -->
            <section
                id="pemilihan"
                class="grid scroll-mt-20 grid-cols-1 items-center gap-8 py-4 sm:py-8 lg:grid-cols-12 lg:gap-12"
            >
                <!-- Left Column: Title, Intro & CTAs -->
                <div class="space-y-6 text-left lg:col-span-7">
                    <h1
                        class="text-4xl leading-[1.15] font-black tracking-tight text-slate-900 sm:text-5xl lg:text-5xl dark:text-white"
                    >
                        {{
                            appSettings.app_name ||
                            'Portal Pemilihan Digital SMKN 1 Talaga'
                        }}
                    </h1>

                    <p
                        class="text-base leading-relaxed font-normal text-slate-600 sm:text-lg dark:text-slate-400"
                    >
                        {{
                            appSettings.app_description ||
                            'Salurkan hak suara Anda dalam Pemilu Sekolah SMKN 1 Talaga secara cepat, transparan, dan rahasia berlandaskan asas LUBER JURDIL.'
                        }}
                    </p>

                    <!-- CTAs -->
                    <div
                        class="flex flex-col items-center gap-3.5 pt-2 sm:flex-row"
                    >
                        <Link
                            v-if="!user"
                            href="/login"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-3.5 text-sm font-bold text-white shadow-sm transition-colors hover:bg-blue-700 sm:w-auto"
                        >
                            <LogIn class="h-4 w-4" />
                            <span>Masuk E-Voting</span>
                        </Link>
                        <Link
                            v-else
                            href="/dashboard"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-3.5 text-sm font-bold text-white shadow-sm transition-colors hover:bg-blue-700 sm:w-auto"
                        >
                            <Vote class="h-4 w-4" />
                            <span>Dashboard Pemilih</span>
                        </Link>

                        <a
                            href="#cara-memilih"
                            class="flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-6 py-3.5 text-center text-sm font-bold text-slate-800 shadow-sm transition-colors hover:bg-slate-100 sm:w-auto dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                        >
                            <ArrowRight class="h-4 w-4 text-blue-600" />
                            <span>Cara Memilih</span>
                        </a>
                    </div>

                    <!-- Metric Highlights -->
                    <div
                        class="grid grid-cols-3 gap-4 border-t border-slate-200 pt-6 text-left dark:border-slate-800"
                    >
                        <div>
                            <span
                                class="block text-xl font-black text-slate-900 sm:text-2xl dark:text-white"
                                >2.000+</span
                            >
                            <span
                                class="text-xs font-medium text-slate-500 dark:text-slate-400"
                                >Pemilih Terdaftar</span
                            >
                        </div>
                        <div>
                            <span
                                class="block text-xl font-black text-slate-900 sm:text-2xl dark:text-white"
                                >100%</span
                            >
                            <span
                                class="text-xs font-medium text-slate-500 dark:text-slate-400"
                                >Rahasia & Valid</span
                            >
                        </div>
                        <div>
                            <span
                                class="block text-xl font-black text-slate-900 sm:text-2xl dark:text-white"
                                >Real-Time</span
                            >
                            <span
                                class="text-xs font-medium text-slate-500 dark:text-slate-400"
                                >Rekapitulasi Suara</span
                            >
                        </div>
                    </div>
                </div>

                <!-- Right Column: Hero Card Beside Hero -->
                <div class="lg:col-span-5">
                    <div
                        class="space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-7 dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800"
                        >
                            <span
                                class="text-xs font-bold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                                >Status Pemilihan</span
                            >
                            <span
                                class="rounded-md bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-300"
                                >SMKN 1 Talaga</span
                            >
                        </div>

                        <!-- Active Election Card Content -->
                        <template v-if="featuredActiveElection">
                            <div class="space-y-2">
                                <span
                                    class="rounded bg-blue-100 px-2 py-0.5 text-[10px] font-bold text-blue-800 uppercase dark:bg-blue-950 dark:text-blue-300"
                                >
                                    Sedang Berlangsung
                                </span>
                                <h3
                                    class="text-xl leading-snug font-extrabold text-slate-900 dark:text-white"
                                >
                                    {{ featuredActiveElection.title }}
                                </h3>
                                <p
                                    v-if="featuredActiveElection.description"
                                    class="line-clamp-2 text-xs text-slate-600 dark:text-slate-400"
                                >
                                    {{ featuredActiveElection.description }}
                                </p>
                            </div>

                            <!-- Quick Timer Widget -->
                            <CountdownTimer
                                :target-date="featuredActiveElection.end_at"
                                label="Sisa Waktu Pemilihan"
                                class="origin-left scale-95"
                            />

                            <div
                                class="flex items-center justify-between border-t border-slate-100 pt-3 dark:border-slate-800"
                            >
                                <div
                                    class="flex items-center gap-3 text-xs font-semibold text-slate-600 dark:text-slate-300"
                                >
                                    <span class="flex items-center gap-1">
                                        <Users
                                            class="h-3.5 w-3.5 text-blue-600"
                                        />
                                        {{
                                            featuredActiveElection.candidates_count
                                        }}
                                        Paslon
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <UserCheck
                                            class="h-3.5 w-3.5 text-emerald-600"
                                        />
                                        {{
                                            featuredActiveElection.voters_count
                                        }}
                                        Pemilih
                                    </span>
                                </div>

                                <Link
                                    v-if="user"
                                    :href="`/vote/${featuredActiveElection.id}`"
                                    class="flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition-colors hover:bg-blue-700"
                                >
                                    <Vote class="h-3.5 w-3.5" />
                                    <span>Pilih Sekarang</span>
                                </Link>
                                <Link
                                    v-else
                                    href="/login"
                                    class="flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition-colors hover:bg-blue-700"
                                >
                                    <LogIn class="h-3.5 w-3.5" />
                                    <span>Masuk Suara</span>
                                </Link>
                            </div>
                        </template>

                        <!-- Default Information Card when no active election -->
                        <template v-else>
                            <div class="space-y-3">
                                <h3
                                    class="text-lg font-extrabold text-slate-900 dark:text-white"
                                >
                                    Informasi E-Voting Sekolah
                                </h3>
                                <p
                                    class="text-xs leading-relaxed text-slate-600 dark:text-slate-400"
                                >
                                    Platform digital resmi pemilihan Ketua OSIS,
                                    Pradana, dan Ketua Komunitas SMKN 1 Talaga.
                                </p>
                            </div>

                            <div
                                class="space-y-2 text-xs font-medium text-slate-700 dark:text-slate-300"
                            >
                                <div class="flex items-center gap-2">
                                    <CheckCircle2
                                        class="h-4 w-4 shrink-0 text-emerald-600"
                                    />
                                    <span
                                        >Otentikasi Akun Resmi (Email, NIS, atau
                                        NIP)</span
                                    >
                                </div>
                                <div class="flex items-center gap-2">
                                    <CheckCircle2
                                        class="h-4 w-4 shrink-0 text-emerald-600"
                                    />
                                    <span>Kerahasiaan Suara 100% Enkripsi</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <CheckCircle2
                                        class="h-4 w-4 shrink-0 text-emerald-600"
                                    />
                                    <span
                                        >Rekapitulasi Suara Otomatis &
                                        Transparan</span
                                    >
                                </div>
                            </div>

                            <div
                                class="border-t border-slate-100 pt-3 dark:border-slate-800"
                            >
                                <Link
                                    v-if="!user"
                                    href="/login"
                                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 py-3 text-xs font-bold text-white shadow-sm transition-colors hover:bg-blue-700"
                                >
                                    <LogIn class="h-4 w-4" />
                                    <span>Masuk Akun Pemilih</span>
                                </Link>
                                <Link
                                    v-else
                                    href="/dashboard"
                                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 py-3 text-xs font-bold text-white shadow-sm transition-colors hover:bg-blue-700"
                                >
                                    <Vote class="h-4 w-4" />
                                    <span>Buka Dashboard Pemilih</span>
                                </Link>
                            </div>
                        </template>
                    </div>
                </div>
            </section>

            <!-- 2. PEMILIHAN YANG AKAN DATANG SECTION -->
            <section id="pemilihan-mendatang" class="scroll-mt-20 space-y-6">
                <div
                    class="flex flex-col justify-between gap-2 border-b border-slate-200 pb-4 sm:flex-row sm:items-end dark:border-slate-800"
                >
                    <div>
                        <div
                            class="mb-1 inline-flex items-center gap-1.5 rounded-md bg-amber-100 px-2.5 py-0.5 text-[11px] font-bold text-amber-800 uppercase dark:bg-amber-950/80 dark:text-amber-300"
                        >
                            <Clock
                                class="h-3.5 w-3.5 text-amber-600 dark:text-amber-400"
                            />
                            <span>Agenda Pemilihan</span>
                        </div>
                        <h2
                            class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl dark:text-white"
                        >
                            Pemilihan Yang Akan Datang
                        </h2>
                        <p
                            class="text-xs font-normal text-slate-600 sm:text-sm dark:text-slate-400"
                        >
                            Daftar kegiatan e-voting yang akan segera dibuka.
                            Persiapkan hak suara Anda.
                        </p>
                    </div>
                </div>

                <!-- Upcoming Elections Grid -->
                <div
                    v-if="upcomingElections && upcomingElections.length > 0"
                    class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="election in upcomingElections"
                        :key="election.id"
                        class="flex flex-col justify-between space-y-5 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition-all duration-200 hover:border-amber-300 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-amber-800"
                    >
                        <!-- Card Header -->
                        <div class="space-y-3">
                            <div
                                class="flex items-center justify-between gap-2"
                            >
                                <span
                                    class="rounded-md border border-amber-200 bg-amber-50 px-2.5 py-0.5 text-[10px] font-bold text-amber-700 uppercase dark:border-amber-800 dark:bg-amber-950/60 dark:text-amber-300"
                                >
                                    Akan Datang
                                </span>
                                <span
                                    class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                >
                                    {{ election.type }}
                                </span>
                            </div>

                            <h3
                                class="text-lg leading-snug font-extrabold text-slate-900 dark:text-white"
                            >
                                {{ election.title }}
                            </h3>

                            <p
                                v-if="election.description"
                                class="line-clamp-2 text-xs text-slate-600 dark:text-slate-400"
                            >
                                {{ election.description }}
                            </p>
                        </div>

                        <!-- Schedule & Details -->
                        <div
                            class="space-y-3 border-t border-slate-100 pt-3 dark:border-slate-800"
                        >
                            <div
                                class="flex items-center gap-2.5 rounded-2xl border border-slate-100 bg-slate-50 p-3 text-xs font-semibold text-slate-700 dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-300"
                            >
                                <Calendar
                                    class="h-4 w-4 shrink-0 text-amber-600"
                                />
                                <div class="min-w-0 truncate">
                                    <span
                                        class="block text-[10px] font-bold text-slate-400 uppercase"
                                        >Waktu Pelaksanaan</span
                                    >
                                    <span
                                        class="block truncate text-xs font-extrabold text-slate-900 dark:text-white"
                                    >
                                        {{ formatDate(election.start_at) }}
                                    </span>
                                </div>
                            </div>

                            <div
                                class="flex items-center justify-between px-1 text-xs font-semibold text-slate-600 dark:text-slate-400"
                            >
                                <span class="flex items-center gap-1.5">
                                    <Users class="h-3.5 w-3.5 text-blue-600" />
                                    {{ election.candidates_count }} Paslon
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <UserCheck
                                        class="h-3.5 w-3.5 text-emerald-600"
                                    />
                                    {{ election.voters_count || 0 }} Pemilih
                                </span>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="pt-2">
                            <div
                                class="flex w-full cursor-not-allowed items-center justify-center gap-2 rounded-xl bg-slate-100 px-4 py-2.5 text-xs font-bold text-slate-500 dark:bg-slate-800 dark:text-slate-400"
                            >
                                <Lock class="h-3.5 w-3.5" />
                                <span>Belum Dibuka (Segera Hadir)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State if no upcoming elections -->
                <div
                    v-else
                    class="space-y-3 rounded-3xl border border-slate-200 bg-white p-8 text-center dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400"
                    >
                        <Clock class="h-6 w-6" />
                    </div>
                    <h3
                        class="text-base font-extrabold text-slate-900 dark:text-white"
                    >
                        Belum Ada Agenda Pemilihan Mendatang
                    </h3>
                    <p
                        class="mx-auto max-w-md text-xs text-slate-500 dark:text-slate-400"
                    >
                        Semua agenda kegiatan e-voting sekolah sedang
                        berlangsung atau belum dijadwalkan oleh panitia.
                    </p>
                </div>
            </section>

            <!-- 2. HOW TO VOTE (ALUR MEMILIH) -->
            <section id="cara-memilih" class="scroll-mt-20 space-y-8">
                <div class="space-y-1">
                    <h2
                        class="text-2xl font-extrabold text-slate-900 sm:text-3xl dark:text-white"
                    >
                        Alur Pemilihan Suara
                    </h2>
                    <p
                        class="text-sm font-normal text-slate-600 dark:text-slate-400"
                    >
                        3 langkah mudah memberikan hak suara secara aman dan
                        terverifikasi.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <div
                        class="space-y-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-2xl bg-blue-600 text-base font-extrabold text-white shadow-sm"
                        >
                            1
                        </div>
                        <h3
                            class="text-lg font-bold text-slate-900 dark:text-white"
                        >
                            Masuk Akun
                        </h3>
                        <p
                            class="text-sm leading-relaxed font-normal text-slate-600 dark:text-slate-300"
                        >
                            Gunakan <strong>Alamat Email terdaftar</strong>,
                            NIS/NISN (Siswa), atau NIP (Guru/Staf) beserta
                            password Anda pada halaman Login.
                        </p>
                    </div>

                    <div
                        class="space-y-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-2xl bg-blue-600 text-base font-extrabold text-white shadow-sm"
                        >
                            2
                        </div>
                        <h3
                            class="text-lg font-bold text-slate-900 dark:text-white"
                        >
                            Pilih Pasangan Kandidat
                        </h3>
                        <p
                            class="text-sm leading-relaxed font-normal text-slate-600 dark:text-slate-300"
                        >
                            Buka surat suara digital, cermati profil serta
                            visi-misi pasangan calon, lalu pilih paslon pilihan
                            Anda.
                        </p>
                    </div>

                    <div
                        class="space-y-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-2xl bg-blue-600 text-base font-extrabold text-white shadow-sm"
                        >
                            3
                        </div>
                        <h3
                            class="text-lg font-bold text-slate-900 dark:text-white"
                        >
                            Konfirmasi & Simpan
                        </h3>
                        <p
                            class="text-sm leading-relaxed font-normal text-slate-600 dark:text-slate-300"
                        >
                            Konfirmasikan pilihan Anda. Suara otomatis tercatat
                            ke dalam sistem secara aman dan rahasia.
                        </p>
                    </div>
                </div>
            </section>

            <!-- 4. FAQ ACCORDION SECTION (#faq) -->
            <section id="faq" class="scroll-mt-20 space-y-8">
                <div class="space-y-1">
                    <h2
                        class="text-2xl font-extrabold text-slate-900 sm:text-3xl dark:text-white"
                    >
                        Pertanyaan Umum (FAQ)
                    </h2>
                    <p
                        class="text-sm font-normal text-slate-600 dark:text-slate-400"
                    >
                        Jawaban lengkap seputar pelaksanaan E-Voting SMKN 1
                        Talaga.
                    </p>
                </div>

                <div class="space-y-3">
                    <div
                        v-for="(faq, index) in faqs"
                        :key="index"
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-colors dark:border-slate-800 dark:bg-slate-900"
                    >
                        <button
                            type="button"
                            @click="toggleFaq(index)"
                            class="flex w-full cursor-pointer items-center justify-between gap-4 p-5 text-left text-sm font-bold text-slate-900 transition-colors hover:text-blue-600 focus:outline-none sm:text-base dark:text-white dark:hover:text-blue-400"
                        >
                            <span>{{ faq.question }}</span>
                            <ChevronDown
                                :class="[
                                    'h-5 w-5 shrink-0 text-slate-400 transition-transform duration-200',
                                    openFaqIndex === index
                                        ? 'rotate-180 text-blue-600 dark:text-blue-400'
                                        : '',
                                ]"
                            />
                        </button>

                        <div
                            v-show="openFaqIndex === index"
                            class="border-t border-slate-100 px-5 pt-3 pb-5 text-xs leading-relaxed font-normal text-slate-600 sm:text-sm dark:border-slate-800/80 dark:text-slate-300"
                        >
                            {{ faq.answer }}
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </VoterLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    LogIn,
    Vote,
    Users,
    UserCheck,
    ChevronDown,
    Sparkles,
    ShieldCheck,
    CheckCircle2,
    Calendar,
    Clock,
    Lock,
} from '@lucide/vue';
import VoterLayout from '@/Layouts/VoterLayout.vue';
import CountdownTimer from '@/Components/CountdownTimer.vue';

const props = defineProps<{
    activeElections: any[];
    upcomingElections: any[];
    user: any;
}>();

const page = usePage();
const appSettings = computed(() => (page.props.appSettings as any) || {});

const featuredActiveElection = computed(() => {
    return props.activeElections && props.activeElections.length > 0
        ? props.activeElections[0]
        : null;
});

const formatDate = (isoString: string) => {
    if (!isoString) return '-';
    const d = new Date(isoString);
    return (
        d.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        }) + ' WIB'
    );
};

const openFaqIndex = ref<number | null>(0);

const toggleFaq = (index: number) => {
    openFaqIndex.value = openFaqIndex.value === index ? null : index;
};

const faqs = [
    {
        question: 'Metode identitas apa saja yang bisa digunakan untuk login?',
        answer: 'Siswa dan Guru/Staf dapat login menggunakan Alamat Email terdaftar, Nomor Induk Siswa (NIS/NISN) untuk siswa, atau NIP untuk guru/staf beserta kata sandi terdaftar.',
    },
    {
        question:
            'Apakah orang lain atau panitia dapat melihat pilihan paslon saya?',
        answer: 'Tidak sama sekali. Sistem E-Voting SMKN 1 Talaga memisahkan catatan transaksi status pemilih (sudah memilih) dengan isi surat suara digital. Pilihan Anda 100% anonim dan rahasia.',
    },
    {
        question:
            'Apakah saya bisa mengubah suara setelah tombol simpan diklik?',
        answer: 'Tidak bisa. Demi menjaga integritas dan validitas hasil pemungutan suara, setiap pemilih hanya diberikan 1 kali kesempatan untuk menyimpan suara per acara pemilihan.',
    },
    {
        question: 'Kapan hasil perolehan suara dapat dilihat oleh pemilih?',
        answer: 'Hasil suara dapat langsung dipantau melalui tombol "Lihat Hasil" pada Dashboard Pemilih begitu panitia mengaktifkan publikasi hasil suara.',
    },
];
</script>

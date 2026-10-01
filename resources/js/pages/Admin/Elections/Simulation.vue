<template>
    <AdminLayout title="Simulasi Voting Pemilihan">
        <div class="space-y-6">
            <!-- Header Section -->
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <div class="flex items-center gap-2 text-xs font-semibold text-blue-600 dark:text-blue-400">
                        <Link href="/admin/pemilihan" class="hover:underline">Pemilihan</Link>
                        <span>/</span>
                        <span>Simulasi Voting</span>
                    </div>
                    <h1 class="mt-1 text-2xl font-black text-slate-900 dark:text-white">
                        Simulasi Bilik Suara
                    </h1>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                        Uji coba alur pencoblosan (Single Vote) & pengujian tahapan putaran (Multi-Round)
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="resetSimulation"
                        class="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-xs font-bold text-red-600 transition-all hover:bg-red-100 dark:border-red-900/40 dark:bg-red-950/30 dark:text-red-400"
                    >
                        <RotateCcw class="h-4 w-4" />
                        Reset Simulasi
                    </button>
                    <Link
                        href="/admin/pemilihan"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                    >
                        Kembali
                    </Link>
                </div>
            </div>

            <!-- Banner Warning Simulation Mode -->
            <div class="flex items-center justify-between rounded-2xl border border-amber-200 bg-amber-50/80 p-4 text-amber-900 shadow-sm dark:border-amber-900/40 dark:bg-amber-950/40 dark:text-amber-300">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/20 text-amber-600 dark:text-amber-400">
                        <FlaskConical class="h-5 w-5" />
                    </div>
                    <div>
                        <h4 class="text-sm font-bold">MODE SIMULASI AKTIF</h4>
                        <p class="text-xs text-amber-700 dark:text-amber-400">
                            Semua perolehan suara di halaman ini tersimpan di sandbox simulasi dan <strong>TIDAK BERDAMPAK</strong> pada data pemilu resmi.
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="rounded-lg bg-amber-200/80 px-2.5 py-1 text-[11px] font-black uppercase tracking-wider text-amber-900 dark:bg-amber-900/60 dark:text-amber-200">
                        Putaran {{ election.current_stage || 1 }} / {{ election.total_stages || 1 }}
                    </span>
                </div>
            </div>

            <!-- Main Content Layout Grid -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Left: Candidate Booth (Single Vote Tester) -->
                <div class="space-y-4 lg:col-span-2">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white">
                            Pilih Paslon (Simulasi Pencoblosan)
                        </h3>
                        <span class="text-xs font-semibold text-slate-500">
                            Klik kartu paslon untuk memberikan 1 suara uji coba
                        </span>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div
                            v-for="candidate in candidates"
                            :key="candidate.id"
                            class="relative flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:border-blue-500 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-blue-500"
                        >
                            <div class="space-y-4">
                                <!-- Number & Stage Badge -->
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-blue-600 text-sm font-black text-white shadow-sm">
                                        #{{ candidate.candidate_number }}
                                    </span>
                                    <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                        {{ candidate.simulation_votes_count }} Suara Simulasi
                                    </span>
                                </div>

                                <!-- Photo / Placeholder -->
                                <div class="relative h-44 overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800">
                                    <img
                                        v-if="candidate.photo"
                                        :src="candidate.photo"
                                        :alt="candidate.chairman_name"
                                        class="h-full w-full object-cover"
                                    />
                                    <div v-else class="flex h-full w-full flex-col items-center justify-center text-slate-400">
                                        <UserCheck class="h-12 w-12" />
                                    </div>
                                </div>

                                <!-- Names -->
                                <div>
                                    <h4 class="text-base font-extrabold text-slate-900 dark:text-white">
                                        {{ candidate.chairman_name }}
                                    </h4>
                                    <p v-if="candidate.vice_chairman_name" class="text-xs font-medium text-slate-500 dark:text-slate-400">
                                        Wakil: {{ candidate.vice_chairman_name }}
                                    </p>
                                </div>
                            </div>

                            <!-- Vote Action Button -->
                            <div class="mt-5 border-t border-slate-100 pt-4 dark:border-slate-800">
                                <button
                                    type="button"
                                    @click="castSimulationVote(candidate.id)"
                                    :disabled="form.processing"
                                    class="w-full rounded-xl bg-blue-600 py-2.5 text-xs font-bold text-white shadow-sm transition-all hover:bg-blue-700 focus:ring-2 focus:ring-blue-500/20 disabled:opacity-50"
                                >
                                    + Coblos Paslon ini (1 Suara)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar: Simulation Stats & Multi-Round Controls -->
                <div class="space-y-6">
                    <!-- Stat Summary -->
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">
                            Statistik Simulasi
                        </h3>
                        <p class="mt-1 text-xs text-slate-500">Hasil suara simulasi pada putaran {{ election.current_stage || 1 }}</p>

                        <div class="mt-4 flex items-center justify-between rounded-xl bg-slate-50 p-4 dark:bg-slate-800/60">
                            <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">Total Suara Simulasi</span>
                            <span class="text-2xl font-black text-blue-600 dark:text-blue-400">{{ totalSimulationVotes }}</span>
                        </div>

                        <!-- Mini Distribution List -->
                        <div class="mt-5 space-y-3">
                            <div v-for="c in candidates" :key="c.id" class="space-y-1">
                                <div class="flex items-center justify-between text-xs font-bold">
                                    <span class="text-slate-700 dark:text-slate-300">#{{ c.candidate_number }} {{ c.chairman_name }}</span>
                                    <span class="text-slate-500">{{ calculatePercentage(c.simulation_votes_count) }}% ({{ c.simulation_votes_count }})</span>
                                </div>
                                <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                    <div
                                        class="h-full bg-blue-600 transition-all duration-300"
                                        :style="{ width: calculatePercentage(c.simulation_votes_count) + '%' }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Multi-Round Control Panel (If Multi Stage enabled) -->
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="flex items-center gap-2 text-sm font-extrabold text-slate-900 dark:text-white">
                            <Layers class="h-4 w-4 text-blue-600" />
                            Kontrol Multi-Putaran
                        </div>
                        <p class="mt-1 text-xs text-slate-500">
                            Status Fitur Multi-Stage:
                            <span :class="election.is_multi_stage ? 'text-green-600 font-bold' : 'text-slate-400 font-medium'">
                                {{ election.is_multi_stage ? 'Aktif' : 'Non-Aktif (Single Round)' }}
                            </span>
                        </p>

                        <div v-if="election.is_multi_stage" class="mt-4 space-y-3">
                            <div class="text-xs text-slate-600 dark:text-slate-400">
                                Putaran Saat Ini: <strong>Putaran {{ election.current_stage }}</strong> dari {{ election.total_stages }} Putaran.
                            </div>

                            <button
                                type="button"
                                @click="advanceStage"
                                :disabled="election.current_stage >= election.total_stages"
                                class="w-full rounded-xl border border-blue-200 bg-blue-50 py-2.5 text-xs font-bold text-blue-700 transition-all hover:bg-blue-100 disabled:opacity-50 dark:border-blue-900/50 dark:bg-blue-950/40 dark:text-blue-300"
                            >
                                Lanjut ke Putaran Berikutnya &rarr;
                            </button>
                        </div>
                        <div v-else class="mt-3 text-xs text-slate-400">
                            Aktifkan opsi multi-putaran pada konfigurasi Pemilihan untuk menguji alur eliminasi dan putaran berikutnya.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { RotateCcw, FlaskConical, UserCheck, Layers } from 'lucide-vue-next';

const props = defineProps<{
    election: any;
    candidates: any[];
    totalSimulationVotes: number;
}>();

const form = useForm({
    candidate_id: '',
});

const castSimulationVote = (candidateId: string) => {
    form.candidate_id = candidateId;
    form.post(`/admin/pemilihan/${props.election.id}/simulasi/vote`, {
        preserveScroll: true,
    });
};

const advanceStage = () => {
    if (confirm('Lanjutkan simulasi ke putaran berikutnya?')) {
        router.post(`/admin/pemilihan/${props.election.id}/simulasi/advance-stage`, {}, {
            preserveScroll: true,
        });
    }
};

const resetSimulation = () => {
    if (confirm('Apakah Anda yakin ingin mereset seluruh data simulasi ini dari awal?')) {
        router.delete(`/admin/pemilihan/${props.election.id}/simulasi/reset`, {
            preserveScroll: true,
        });
    }
};

const calculatePercentage = (count: number) => {
    if (!props.totalSimulationVotes || props.totalSimulationVotes === 0) return 0;
    return Math.round((count / props.totalSimulationVotes) * 100);
};
</script>

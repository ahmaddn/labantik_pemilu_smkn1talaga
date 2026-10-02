<template>
    <VoterLayout>
        <div class="mx-auto max-w-7xl space-y-6">
            <!-- Profile Card Header -->
            <div
                class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5 dark:border-slate-700 dark:bg-slate-800"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-600 text-base font-bold text-white shadow-sm"
                    >
                        {{
                            user.name ? user.name.charAt(0).toUpperCase() : "U"
                        }}
                    </div>
                    <div>
                        <h2
                            class="text-sm leading-tight font-bold text-slate-900 sm:text-base dark:text-white"
                        >
                            {{ user.name }}
                        </h2>
                        <p
                            v-if="user.subtext"
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                        >
                            {{ user.subtext }}
                        </p>
                    </div>
                </div>
                <span
                    class="rounded-lg bg-blue-100 px-3 py-1 text-xs font-bold tracking-wider text-blue-800 uppercase dark:bg-blue-950 dark:text-blue-300"
                >
                    {{ user.role }}
                </span>
            </div>

            <!-- Section Header -->
            <div class="flex items-center justify-between">
                <h3
                    class="flex items-center gap-2 text-base font-bold text-slate-900 dark:text-white"
                >
                    <Vote class="h-5 w-5 text-blue-600" />
                    <span>Daftar Pemilihan Anda</span>
                </h3>
                <span
                    class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                >
                    {{ voterAccesses.length }} Acara
                </span>
            </div>

            <!-- Empty Access State -->
            <div
                v-if="!voterAccesses || voterAccesses.length === 0"
                class="space-y-3 rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm dark:border-slate-700 dark:bg-slate-800"
            >
                <div
                    class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-slate-400 dark:bg-slate-700"
                >
                    <Vote class="h-6 w-6" />
                </div>
                <h4
                    class="text-sm font-bold text-slate-800 dark:text-slate-200"
                >
                    Belum Ada Hak Pilih Aktif
                </h4>
                <p
                    class="mx-auto max-w-md text-xs text-slate-500 dark:text-slate-400"
                >
                    Anda belum didaftarkan dalam kegiatan pemilihan yang sedang
                    aktif. Silakan hubungi panitia sekolah jika terdapat
                    kekeliruan.
                </p>
            </div>

            <!-- Voter Access Cards Responsive Grid (1 col on Mobile, 2-3 cols on Desktop) -->
            <div
                v-else
                class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3"
            >
                <div
                    v-for="item in voterAccesses"
                    :key="item.access_id"
                    class="flex flex-col justify-between space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md dark:border-slate-700 dark:bg-slate-800"
                >
                    <div class="space-y-3">
                        <!-- Badges Bar: Multi-Stage & Simulation Indicator -->
                        <div class="flex flex-wrap items-center gap-2">
                            <div
                                v-if="item.election.is_simulation"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-amber-500/40 bg-amber-500/10 px-2.5 py-1 text-[10px] font-black text-amber-800 uppercase dark:text-amber-300"
                            >
                                <FlaskConical class="h-3 w-3" />
                                <span>SESI SIMULASI</span>
                            </div>

                            <div
                                v-if="item.election.is_multi_stage"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-blue-500/30 bg-blue-500/10 px-2.5 py-1 text-[10px] font-bold text-blue-700 uppercase dark:text-blue-400"
                            >
                                <Layers class="h-3 w-3" />
                                <span
                                    >Tahap
                                    {{ item.election.current_stage }} dari
                                    {{ item.election.total_stages }}</span
                                >
                            </div>
                        </div>

                        <!-- Header & Status Badge -->
                        <div class="flex items-start justify-between gap-3">
                            <h4
                                class="text-base leading-snug font-bold text-slate-900 dark:text-white"
                            >
                                <span
                                    v-if="item.election.is_simulation"
                                    class="text-amber-600 dark:text-amber-400 font-extrabold"
                                    >[SIMULASI]
                                </span>
                                {{ item.election.title }}
                            </h4>

                            <!-- Dynamic Status Badges -->
                            <!-- Simulation Active Logic -->
                            <template v-if="item.election.is_simulation">
                                <span
                                    v-if="item.is_simulation_voted"
                                    class="flex shrink-0 items-center gap-1 rounded-lg bg-emerald-100 px-2.5 py-1 text-[10px] font-bold text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300"
                                >
                                    <CheckCircle2
                                        class="h-3 w-3 text-emerald-600"
                                    />
                                    <span>Sudah Coba Simulasi</span>
                                </span>
                                <span
                                    v-else-if="
                                        item.election.simulation_status ===
                                        'ongoing'
                                    "
                                    class="flex shrink-0 items-center gap-1 rounded-lg bg-amber-100 px-2.5 py-1 text-[10px] font-extrabold text-amber-900 dark:bg-amber-950 dark:text-amber-300"
                                >
                                    <span
                                        class="h-2 w-2 rounded-full bg-amber-500 animate-pulse"
                                    ></span>
                                    <span>Simulasi Buka</span>
                                </span>
                                <span
                                    v-else-if="
                                        item.election.simulation_status ===
                                        'upcoming'
                                    "
                                    class="flex shrink-0 items-center gap-1 rounded-lg bg-slate-100 px-2.5 py-1 text-[10px] font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-300"
                                >
                                    <Clock class="h-3 w-3 text-slate-500" />
                                    <span>Simulasi Belum Mulai</span>
                                </span>
                                <span
                                    v-else
                                    class="shrink-0 rounded-lg bg-slate-100 px-2.5 py-1 text-[10px] font-bold text-slate-700 dark:bg-slate-700 dark:text-slate-300"
                                >
                                    Simulasi Berakhir
                                </span>
                            </template>

                            <!-- Official Election Logic -->
                            <template v-else>
                                <span
                                    v-if="item.is_voted"
                                    class="flex shrink-0 items-center gap-1 rounded-lg bg-emerald-100 px-2.5 py-1 text-[10px] font-bold text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300"
                                >
                                    <CheckCircle2
                                        class="h-3 w-3 text-emerald-600"
                                    />
                                    <span>Sudah Memilih</span>
                                </span>

                                <span
                                    v-else-if="
                                        item.election.status === 'upcoming'
                                    "
                                    class="flex shrink-0 items-center gap-1 rounded-lg bg-amber-100 px-2.5 py-1 text-[10px] font-bold text-amber-800 dark:bg-amber-950 dark:text-amber-300"
                                >
                                    <Clock class="h-3 w-3 text-amber-600" />
                                    <span>Belum Mulai</span>
                                </span>

                                <span
                                    v-else-if="
                                        item.election.status === 'ongoing'
                                    "
                                    class="flex shrink-0 items-center gap-1 rounded-lg bg-blue-100 px-2.5 py-1 text-[10px] font-bold text-blue-800 dark:bg-blue-950 dark:text-blue-300"
                                >
                                    <span
                                        class="h-2 w-2 rounded-sm bg-blue-600"
                                    ></span>
                                    <span>Berlangsung</span>
                                </span>

                                <span
                                    v-else
                                    class="shrink-0 rounded-lg bg-slate-100 px-2.5 py-1 text-[10px] font-bold text-slate-700 dark:bg-slate-700 dark:text-slate-300"
                                >
                                    Selesai
                                </span>
                            </template>
                        </div>

                        <p
                            v-if="item.election.description"
                            class="line-clamp-3 text-xs leading-relaxed text-slate-600 dark:text-slate-300"
                        >
                            {{ item.election.description }}
                        </p>

                        <!-- Previous Stage Qualifiers -->
                        <div
                            v-if="
                                item.election.is_multi_stage &&
                                item.election.previous_qualifiers &&
                                item.election.previous_qualifiers.length > 0
                            "
                            class="space-y-2 rounded-xl border border-amber-200 bg-amber-50 p-3 dark:border-amber-900/40 dark:bg-amber-950/30"
                        >
                            <div
                                class="flex items-center gap-1.5 text-xs font-bold text-amber-900 dark:text-amber-300"
                            >
                                <Trophy class="h-3.5 w-3.5 text-amber-600" />
                                <span
                                    >Lolos Tahap
                                    {{ item.election.current_stage - 1 }}:</span
                                >
                            </div>
                            <div class="space-y-1.5">
                                <div
                                    v-for="qualifier in item.election
                                        .previous_qualifiers"
                                    :key="qualifier.id"
                                    class="flex items-center gap-2 rounded-lg border border-amber-200/50 bg-white p-1.5 text-xs dark:bg-slate-800"
                                >
                                    <span
                                        class="rounded bg-blue-600 px-1.5 py-0.5 text-[10px] font-bold text-white"
                                        >No.
                                        {{ qualifier.candidate_number }}</span
                                    >
                                    <span
                                        class="truncate font-bold text-slate-900 dark:text-white"
                                        >{{
                                            item.election.is_simulation
                                                ? "Kandidat Tersamar"
                                                : qualifier.chairman_name
                                        }}</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Button Footer -->
                    <div
                        class="border-t border-slate-100 pt-3 dark:border-slate-700"
                    >
                        <!-- SIMULATION MODE ACTIONS -->
                        <template v-if="item.election.is_simulation">
                            <Link
                                v-if="
                                    item.election.simulation_status ===
                                        'ongoing' && !item.is_simulation_voted
                                "
                                :href="`/vote/${item.election.id}`"
                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-amber-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition-colors hover:bg-amber-700"
                            >
                                <FlaskConical class="h-4 w-4" />
                                <span
                                    >Ikuti Uji Coba Simulasi (Tahap
                                    {{ item.election.current_stage }})</span
                                >
                                <ArrowRight class="h-4 w-4" />
                            </Link>

                            <div
                                v-else-if="item.is_simulation_voted"
                                class="flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 p-2.5 text-xs text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300"
                            >
                                <span
                                    class="flex items-center gap-1.5 font-bold"
                                >
                                    <CheckCircle2
                                        class="h-4 w-4 shrink-0 text-emerald-600"
                                    />
                                    <span>Sudah Mengikuti Simulasi</span>
                                </span>
                            </div>

                            <div
                                v-else-if="
                                    item.election.simulation_status ===
                                    'upcoming'
                                "
                                class="flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 p-2.5 text-xs text-amber-800 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-300"
                            >
                                <Clock
                                    class="h-4 w-4 shrink-0 text-amber-600"
                                />
                                <span
                                    >Sesi simulasi belum dibuka oleh
                                    panitia.</span
                                >
                            </div>

                            <div
                                v-else
                                class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                            >
                                <Clock
                                    class="h-4 w-4 shrink-0 text-slate-400"
                                />
                                <span>Sesi simulasi telah selesai.</span>
                            </div>
                        </template>

                        <!-- REGULAR PRODUCTION ELECTIONS -->
                        <template v-else>
                            <!-- Ongoing & Not Voted -->
                            <Link
                                v-if="
                                    item.election.status === 'ongoing' &&
                                    !item.is_voted
                                "
                                :href="`/vote/${item.election.id}`"
                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition-colors hover:bg-blue-700"
                            >
                                <Vote class="h-4 w-4" />
                                <span
                                    >Pilih Sekarang (Tahap
                                    {{ item.election.current_stage }})</span
                                >
                                <ArrowRight class="h-4 w-4" />
                            </Link>

                            <!-- Already Voted -->
                            <div
                                v-else-if="item.is_voted"
                                class="flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 p-2.5 text-xs text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300"
                            >
                                <span
                                    class="flex items-center gap-1.5 font-bold"
                                >
                                    <CheckCircle2
                                        class="h-4 w-4 shrink-0 text-emerald-600"
                                    />
                                    <span>Suara Sudah Terdaftar</span>
                                </span>
                                <Link
                                    v-if="item.election.is_published"
                                    :href="`/hasil/${item.election.id}`"
                                    class="rounded-lg bg-emerald-600 px-2.5 py-1 text-xs font-bold text-white transition-colors hover:bg-emerald-700"
                                >
                                    Hasil
                                </Link>
                            </div>

                            <!-- Finished & Published -->
                            <Link
                                v-else-if="
                                    item.election.status === 'finished' &&
                                    item.election.is_published
                                "
                                :href="`/hasil/${item.election.id}`"
                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-slate-800 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition-colors hover:bg-slate-900"
                            >
                                <BarChart3 class="h-4 w-4 text-amber-400" />
                                <span>Lihat Hasil Pemilihan</span>
                            </Link>

                            <!-- Upcoming -->
                            <div
                                v-else-if="item.election.status === 'upcoming'"
                                class="flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 p-2.5 text-xs text-amber-800 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-300"
                            >
                                <Clock
                                    class="h-4 w-4 shrink-0 text-amber-600"
                                />
                                <span>Pemilihan belum dimulai.</span>
                            </div>

                            <!-- Finished & Unpublish -->
                            <div
                                v-else
                                class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                            >
                                <Clock
                                    class="h-4 w-4 shrink-0 text-slate-400"
                                />
                                <span
                                    >Pemilihan telah selesai. Menunggu hasil
                                    dipublikasikan oleh panitia.</span
                                >
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </VoterLayout>
</template>

<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import {
    Vote,
    CheckCircle2,
    Clock,
    BarChart3,
    ArrowRight,
    Layers,
    Trophy,
    FlaskConical,
} from "@lucide/vue";
import VoterLayout from "@/Layouts/VoterLayout.vue";

defineProps<{
    user: {
        name: string;
        email: string;
        role: string;
        subtext: string;
    };
    voterAccesses: any[];
}>();

const formatDate = (dateStr: string) => {
    if (!dateStr) return "";
    const d = new Date(dateStr);
    return d.toLocaleDateString("id-ID", {
        day: "numeric",
        month: "short",
        hour: "2-digit",
        minute: "2-digit",
    });
};
</script>

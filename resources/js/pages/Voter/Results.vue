<template>
    <VoterLayout>
        <div class="space-y-4">
            <!-- Back Link -->
            <div>
                <Link
                    href="/dashboard"
                    class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:underline dark:text-blue-400"
                >
                    <ArrowLeft class="h-4 w-4" />
                    <span>Kembali ke Dashboard</span>
                </Link>
            </div>

            <!-- Header Banner -->
            <div
                class="space-y-2 rounded-2xl border border-slate-200 bg-white p-5 text-slate-900 shadow-sm transition-colors dark:border-slate-800 dark:bg-slate-900 dark:text-white"
            >
                <div
                    class="flex items-center gap-2 text-xs font-bold tracking-wider text-amber-600 uppercase dark:text-amber-400"
                >
                    <BarChart3 class="h-4 w-4" />
                    <span>Hasil Perolehan Suara Resmi</span>
                </div>
                <h2 class="text-base leading-snug font-extrabold text-slate-900 dark:text-white">
                    {{ election.title }}
                </h2>
                <div
                    class="flex items-center gap-4 border-t border-slate-100 pt-2 text-xs text-slate-600 dark:border-slate-800 dark:text-slate-300"
                >
                    <span
                        >Total Suara:
                        <strong class="font-extrabold text-slate-900 dark:text-white">{{
                            election.total_votes
                        }}</strong></span
                    >
                    <span
                        >Hak Pemilih:
                        <strong class="font-extrabold text-slate-900 dark:text-white">{{
                            election.total_voters
                        }}</strong></span
                    >
                </div>
            </div>

            <!-- Results List per Candidate -->
            <div class="space-y-3">
                <div
                    v-for="(item, index) in results"
                    :key="item.id"
                    class="space-y-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800"
                >
                    <!-- Candidate Header -->
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-600 text-base font-extrabold text-white"
                            >
                                {{ item.candidate_number }}
                            </div>
                            <div>
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <h3
                                        class="text-sm font-extrabold text-slate-900 dark:text-white"
                                    >
                                        {{ item.chairman_name }}
                                    </h3>
                                    <span
                                        v-if="item.chairman_class"
                                        class="rounded-md bg-blue-100 px-2 py-0.5 text-[10px] font-bold text-blue-800 dark:bg-blue-950 dark:text-blue-300"
                                    >
                                        {{ item.chairman_class }}
                                    </span>
                                </div>
                                <div
                                    v-if="item.vice_chairman_name"
                                    class="flex flex-wrap items-center gap-1.5 text-xs font-medium text-slate-500 dark:text-slate-400"
                                >
                                    <span>Wakil: {{ item.vice_chairman_name }}</span>
                                    <span
                                        v-if="item.vice_chairman_class"
                                        class="rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                                    >
                                        {{ item.vice_chairman_class }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Total & Percentage -->
                        <div class="text-right">
                            <span
                                class="block text-lg font-black text-blue-600 dark:text-blue-400"
                            >
                                {{ item.percentage }}%
                            </span>
                            <span
                                class="text-[11px] font-medium text-slate-500 dark:text-slate-400"
                            >
                                {{ item.votes_count }} Suara
                            </span>
                        </div>
                    </div>

                    <!-- Progress Bar (Colorful Dynamic Palette) -->
                    <div
                        class="h-3 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700"
                    >
                        <div
                            :class="['h-full rounded-full transition-all duration-500 shadow-xs', getVoterColor(index)]"
                            :style="{ width: `${item.percentage}%` }"
                        ></div>
                    </div>
                </div>
            </div>
        </div>
    </VoterLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { BarChart3, ArrowLeft } from '@lucide/vue';
import VoterLayout from '@/Layouts/VoterLayout.vue';

defineProps<{
    election: {
        id: number;
        title: string;
        description: string;
        type: string;
        total_votes: number;
        total_voters: number;
    };
    results: any[];
}>();

const VOTER_COLORS = [
    'bg-gradient-to-r from-blue-600 to-indigo-600',
    'bg-gradient-to-r from-emerald-500 to-teal-600',
    'bg-gradient-to-r from-purple-600 to-fuchsia-600',
    'bg-gradient-to-r from-amber-500 to-orange-500',
    'bg-gradient-to-r from-rose-500 to-pink-600',
    'bg-gradient-to-r from-cyan-500 to-sky-600',
];

const getVoterColor = (idx: number) => {
    return VOTER_COLORS[idx % VOTER_COLORS.length];
};
</script>

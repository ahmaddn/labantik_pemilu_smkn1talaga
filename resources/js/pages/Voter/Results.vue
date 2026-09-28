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
                class="space-y-2 rounded-2xl bg-slate-900 p-4 text-white shadow-sm"
            >
                <div
                    class="flex items-center gap-2 text-xs font-bold tracking-wider text-amber-400 uppercase"
                >
                    <BarChart3 class="h-4 w-4" />
                    <span>Hasil Perolehan Suara Resmi</span>
                </div>
                <h2 class="text-base leading-snug font-extrabold">
                    {{ election.title }}
                </h2>
                <div
                    class="flex items-center gap-4 border-t border-slate-800 pt-1 text-xs text-slate-300"
                >
                    <span
                        >Total Suara:
                        <strong class="text-white">{{
                            election.total_votes
                        }}</strong></span
                    >
                    <span
                        >Hak Pemilih:
                        <strong class="text-white">{{
                            election.total_voters
                        }}</strong></span
                    >
                </div>
            </div>

            <!-- Results List per Candidate -->
            <div class="space-y-3">
                <div
                    v-for="item in results"
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
                                <h3
                                    class="text-sm font-extrabold text-slate-900 dark:text-white"
                                >
                                    {{ item.chairman_name }}
                                </h3>
                                <p
                                    v-if="item.vice_chairman_name"
                                    class="text-xs font-medium text-slate-500 dark:text-slate-400"
                                >
                                    Wakil: {{ item.vice_chairman_name }}
                                </p>
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

                    <!-- Progress Bar (Solid Colors) -->
                    <div
                        class="h-3 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700"
                    >
                        <div
                            class="h-full rounded-full bg-blue-600 transition-all duration-500"
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
</script>

<template>
    <AdminLayout>
        <template #header>Dashboard Ikhtisar Panitia</template>

        <div class="space-y-6">
            <!-- 1. Header Banner -->
            <div
                class="flex flex-col items-start justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:flex-row sm:items-center sm:p-7 dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="space-y-1">
                    <h2
                        class="text-xl font-black text-slate-900 sm:text-2xl dark:text-white"
                    >
                        Selamat Datang, {{ user?.name || 'Panitia Pemilu' }}
                    </h2>
                    <p
                        class="text-xs font-medium text-slate-600 sm:text-sm dark:text-slate-400"
                    >
                        Kelola acara pemilu sekolah, data pasangan calon, dan
                        alokasi hak pilih pemilih.
                    </p>
                </div>

                <Link
                    href="/admin/pemilihan"
                    class="flex shrink-0 items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition-colors hover:bg-blue-700"
                >
                    <Plus class="h-4 w-4" />
                    <span>Buat Pemilihan Baru</span>
                </Link>
            </div>

            <!-- 2. Metric Cards Grid -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Total Pemilihan -->
                <div
                    class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div class="space-y-1">
                        <span
                            class="text-xs font-bold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                            >Total Pemilihan</span
                        >
                        <span
                            class="block text-2xl font-black text-slate-900 dark:text-white"
                            >{{ stats.total_elections }}</span
                        >
                    </div>
                    <div
                        class="rounded-xl bg-blue-50 p-3 text-blue-600 dark:bg-blue-950 dark:text-blue-300"
                    >
                        <Vote class="h-6 w-6" />
                    </div>
                </div>

                <!-- Siswa Terdaftar -->
                <div
                    class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div class="space-y-1">
                        <span
                            class="text-xs font-bold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                            >Siswa Terdaftar</span
                        >
                        <span
                            class="block text-2xl font-black text-slate-900 dark:text-white"
                            >{{ stats.total_students }}</span
                        >
                    </div>
                    <div
                        class="rounded-xl bg-emerald-50 p-3 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-300"
                    >
                        <Users class="h-6 w-6" />
                    </div>
                </div>

                <!-- Guru Terdaftar -->
                <div
                    class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div class="space-y-1">
                        <span
                            class="text-xs font-bold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                            >Guru Terdaftar</span
                        >
                        <span
                            class="block text-2xl font-black text-slate-900 dark:text-white"
                            >{{ stats.total_teachers }}</span
                        >
                    </div>
                    <div
                        class="rounded-xl bg-purple-50 p-3 text-purple-600 dark:bg-purple-950 dark:text-purple-300"
                    >
                        <UserCheck class="h-6 w-6" />
                    </div>
                </div>

                <!-- Total Suara Masuk -->
                <div
                    class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div class="space-y-1">
                        <span
                            class="text-xs font-bold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                            >Total Suara</span
                        >
                        <span
                            class="block text-2xl font-black text-slate-900 dark:text-white"
                            >{{ stats.total_votes_cast }}</span
                        >
                    </div>
                    <div
                        class="rounded-xl bg-amber-50 p-3 text-amber-600 dark:bg-amber-950 dark:text-amber-300"
                    >
                        <BarChart3 class="h-6 w-6" />
                    </div>
                </div>
            </div>

            <!-- 3. Recent Elections Table -->
            <div
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <div
                    class="flex items-center justify-between border-b border-slate-200 p-5 dark:border-slate-800"
                >
                    <div>
                        <h3
                            class="text-base font-extrabold text-slate-900 dark:text-white"
                        >
                            Daftar Pemilihan Terbaru
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Ringkasan aktivitas pemilu sekolah
                        </p>
                    </div>

                    <Link
                        href="/admin/pemilihan"
                        class="flex items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-100 px-3.5 py-1.5 text-xs font-bold text-slate-800 transition-colors hover:bg-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                    >
                        <span>Semua Pemilihan</span>
                        <ArrowRight class="h-3.5 w-3.5" />
                    </Link>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead
                            class="border-b border-slate-200 bg-slate-50 font-bold tracking-wider text-slate-500 uppercase dark:border-slate-800 dark:bg-slate-900/80 dark:text-slate-400"
                        >
                            <tr>
                                <th class="p-4 pl-6">Nama Pemilihan</th>
                                <th class="p-4">Tipe</th>
                                <th class="p-4">Tahun Ajaran</th>
                                <th class="p-4">Status</th>
                                <th class="p-4 text-center">Paslon</th>
                                <th class="p-4 text-center">Pemilih</th>
                                <th class="p-4 text-center">Suara</th>
                                <th class="p-4 pr-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-slate-100 dark:divide-slate-800"
                        >
                            <tr
                                v-if="
                                    !recentElections ||
                                    recentElections.length === 0
                                "
                            >
                                <td
                                    colspan="8"
                                    class="p-8 text-center font-medium text-slate-500 dark:text-slate-400"
                                >
                                    Belum ada data acara pemilihan.
                                </td>
                            </tr>

                            <tr
                                v-for="item in recentElections"
                                :key="item.id"
                                class="transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/50"
                            >
                                <td
                                    class="p-4 pl-6 font-bold text-slate-900 dark:text-white"
                                >
                                    {{ item.title }}
                                </td>

                                <td
                                    class="p-4 font-semibold text-slate-600 uppercase dark:text-slate-300"
                                >
                                    {{ item.type }}
                                </td>

                                <td
                                    class="p-4 font-medium text-slate-600 dark:text-slate-300"
                                >
                                    {{ item.academic_year || '-' }}
                                </td>

                                <td class="p-4">
                                    <span
                                        v-if="item.status === 'ongoing'"
                                        class="rounded bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800 uppercase dark:bg-emerald-950 dark:text-emerald-300"
                                    >
                                        Berlangsung
                                    </span>
                                    <span
                                        v-else-if="item.status === 'upcoming'"
                                        class="rounded bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-800 uppercase dark:bg-amber-950 dark:text-amber-300"
                                    >
                                        Akan Datang
                                    </span>
                                    <span
                                        v-else
                                        class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-700 uppercase dark:bg-slate-800 dark:text-slate-300"
                                    >
                                        Selesai
                                    </span>
                                </td>

                                <td
                                    class="p-4 text-center font-bold text-slate-900 dark:text-white"
                                >
                                    {{ item.candidates_count }}
                                </td>

                                <td
                                    class="p-4 text-center font-bold text-slate-900 dark:text-white"
                                >
                                    {{ item.voters_count }}
                                </td>

                                <td
                                    class="p-4 text-center font-bold text-blue-600 dark:text-blue-400"
                                >
                                    {{ item.votes_count }}
                                </td>

                                <td class="p-4 pr-6 text-right">
                                    <div
                                        class="flex items-center justify-end gap-1"
                                    >
                                        <Link
                                            :href="`/admin/pemilihan/${item.id}/kandidat`"
                                            class="inline-flex items-center rounded-lg p-2 text-slate-500 transition-colors hover:bg-blue-50 hover:text-blue-600 dark:text-slate-400 dark:hover:bg-blue-950/40 dark:hover:text-blue-400"
                                            title="Kelola Paslon Kandidat"
                                        >
                                            <Users class="h-4 w-4" />
                                        </Link>

                                        <Link
                                            :href="`/admin/pemilihan/${item.id}/pemilih`"
                                            class="inline-flex items-center rounded-lg p-2 text-slate-500 transition-colors hover:bg-emerald-50 hover:text-emerald-600 dark:text-slate-400 dark:hover:bg-emerald-950/40 dark:hover:text-emerald-400"
                                            title="Kelola Hak Pilih"
                                        >
                                            <UserCheck class="h-4 w-4" />
                                        </Link>

                                        <Link
                                            :href="`/admin/pemilihan/${item.id}/edit`"
                                            class="inline-flex items-center rounded-lg p-2 text-slate-500 transition-colors hover:bg-amber-50 hover:text-amber-600 dark:text-slate-400 dark:hover:bg-amber-950/40 dark:hover:text-amber-400"
                                            title="Edit Pemilihan"
                                        >
                                            <Edit class="h-4 w-4" />
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import {
    Vote,
    Users,
    UserCheck,
    BarChart3,
    Plus,
    ArrowRight,
    Edit,
} from '@lucide/vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps<{
    stats: {
        total_elections: number;
        total_students: number;
        total_teachers: number;
        total_votes_cast: number;
    };
    recentElections: any[];
}>();

const page = usePage();
const user = computed(() => (page.props.auth as any)?.user || null);
</script>

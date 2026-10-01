<template>
    <AdminLayout>
        <template #header>Kelola Hak Pilih & Akses Pemilih</template>

        <div class="space-y-6">
            <!-- Top Action & Info Bar -->
            <div
                class="flex flex-col items-start justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center dark:border-slate-700 dark:bg-slate-800"
            >
                <div>
                    <Link
                        href="/admin/pemilihan"
                        class="mb-1 inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:underline dark:text-blue-400"
                    >
                        <ArrowLeft class="h-3.5 w-3.5" />
                        <span>Kembali ke Pemilihan</span>
                    </Link>
                    <h2
                        class="text-base font-extrabold text-slate-900 dark:text-white"
                    >
                        Hak Pilih: {{ election.title }}
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Target:
                        <strong
                            class="text-slate-800 uppercase dark:text-slate-200"
                            >{{ election.target_voter }}</strong
                        >
                        <span v-if="election.academic_year">
                            | Tahun Ajaran {{ election.academic_year }}</span
                        >
                        <span v-if="election.class_name">
                            | Kelas {{ election.class_name }}</span
                        >
                    </p>
                </div>

                <button
                    type="button"
                    :disabled="isGenerating"
                    @click="generateBatch"
                    class="flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition-colors hover:bg-emerald-700 disabled:opacity-50"
                >
                    <RefreshCw
                        :class="['h-4 w-4', isGenerating ? 'animate-spin' : '']"
                    />
                    <span>{{
                        isGenerating
                            ? 'Memproses Access...'
                            : 'Generate Akses Pemilih Massal'
                    }}</span>
                </button>
            </div>

            <!-- Stats Pill Bar -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-2">
                <div
                    class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800"
                >
                    <div>
                        <span
                            class="text-xs font-semibold text-slate-500 dark:text-slate-400"
                            >Total Terdaftar</span
                        >
                        <span
                            class="block text-xl font-black text-slate-900 dark:text-white"
                            >{{ stats.total_access }}</span
                        >
                    </div>
                    <Users class="h-7 w-7 text-blue-500" />
                </div>

                <div
                    class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800"
                >
                    <div>
                        <span
                            class="text-xs font-semibold text-slate-500 dark:text-slate-400"
                            >Sudah Memilih</span
                        >
                        <span
                            class="block text-xl font-black text-emerald-600 dark:text-emerald-400"
                            >{{ stats.total_voted }}</span
                        >
                    </div>
                    <CheckCircle2 class="h-7 w-7 text-emerald-500" />
                </div>
            </div>

            <!-- Voter Access Table -->
            <div
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800"
            >
                <div
                    class="flex flex-col justify-between gap-3 border-b border-slate-200 p-4 sm:flex-row sm:items-center sm:p-5 dark:border-slate-700"
                >
                    <div>
                        <h3
                            class="text-base font-extrabold text-slate-900 dark:text-white"
                        >
                            Daftar Pemilih Terdaftar
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Status penggunaan hak pilih pengguna
                        </p>
                    </div>

                    <!-- Search Bar Box -->
                    <div class="relative w-full max-w-xs">
                        <Search
                            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400"
                        />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari nama, NIS, NIP, email..."
                            @input="handleSearch"
                            class="w-full rounded-xl border border-slate-300 bg-slate-50 py-2 pr-8 pl-9 text-xs text-slate-900 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                        />
                        <button
                            v-if="searchQuery"
                            @click="clearSearch"
                            class="absolute top-1/2 right-2.5 -translate-y-1/2 cursor-pointer p-0.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                        >
                            <X class="h-3.5 w-3.5" />
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead
                            class="border-b border-slate-200 bg-slate-50 font-bold tracking-wider text-slate-600 uppercase dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-400"
                        >
                            <tr>
                                <th class="p-3.5 pl-5">Nama Pemilih</th>
                                <th class="p-3.5">NIS / NIP / Email</th>
                                <th class="p-3.5">Role</th>
                                <th class="p-3.5">Status Voting</th>
                                <th class="p-3.5">Waktu Vote</th>
                                <th class="p-3.5 pr-5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-slate-200 dark:divide-slate-700"
                        >
                            <tr
                                v-if="
                                    !voterAccesses.data ||
                                    voterAccesses.data.length === 0
                                "
                            >
                                <td
                                    colspan="6"
                                    class="p-8 text-center text-slate-500 dark:text-slate-400"
                                >
                                    <div class="space-y-1">
                                        <p class="text-sm font-bold">
                                            Tidak ada data pemilih yang
                                            ditemukan.
                                        </p>
                                        <p class="text-xs" v-if="searchQuery">
                                            Cobalah kata kunci pencarian yang
                                            berbeda.
                                        </p>
                                        <p class="text-xs" v-else>
                                            Klik tombol "Generate Akses Pemilih
                                            Massal" di atas untuk menambahkan
                                            pemilih.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            <tr
                                v-for="access in voterAccesses.data"
                                :key="access.id"
                                class="transition-colors hover:bg-slate-50 dark:hover:bg-slate-700/50"
                            >
                                <td
                                    class="p-3.5 pl-5 font-bold text-slate-900 dark:text-white"
                                >
                                    {{ access.user_name }}
                                </td>
                                <td
                                    class="p-3.5 font-medium text-slate-600 dark:text-slate-300"
                                >
                                    {{ access.user_subtext }} ({{
                                        access.user_email
                                    }})
                                </td>
                                <td class="p-3.5">
                                    <span
                                        class="rounded bg-blue-100 px-2 py-0.5 text-[10px] font-bold text-blue-800 uppercase dark:bg-blue-950 dark:text-blue-300"
                                    >
                                        {{ access.user_role }}
                                    </span>
                                </td>
                                <td class="p-3.5">
                                    <span
                                        v-if="access.is_voted"
                                        class="flex w-fit items-center gap-1 rounded bg-emerald-100 px-2.5 py-0.5 text-[10px] font-bold text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300"
                                    >
                                        <CheckCircle2
                                            class="h-3 w-3 text-emerald-600"
                                        />
                                        <span>Sudah Vote</span>
                                    </span>
                                    <span
                                        v-else
                                        class="flex w-fit items-center gap-1 rounded bg-amber-100 px-2.5 py-0.5 text-[10px] font-bold text-amber-800 dark:bg-amber-950 dark:text-amber-300"
                                    >
                                        <Clock class="h-3 w-3 text-amber-600" />
                                        <span>Belum Vote</span>
                                    </span>
                                </td>
                                <td
                                    class="p-3.5 text-slate-500 dark:text-slate-400"
                                >
                                    {{ formatDate(access.voted_at) }}
                                </td>
                                <td class="p-3.5 pr-5 text-right">
                                    <button
                                        @click="deleteAccess(access)"
                                        class="cursor-pointer rounded-lg p-1.5 text-slate-400 transition-colors hover:text-rose-600 dark:hover:text-rose-400"
                                        title="Hapus Hak Pilih"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Server-Side Pagination Bar -->
                <div
                    v-if="voterAccesses.links && voterAccesses.links.length > 3"
                    class="flex flex-col items-center justify-between gap-3 border-t border-slate-200 p-4 text-xs sm:flex-row dark:border-slate-700"
                >
                    <div class="font-medium text-slate-500 dark:text-slate-400">
                        Menampilkan {{ voterAccesses.from || 0 }} -
                        {{ voterAccesses.to || 0 }} dari {{ voterAccesses.total || 0 }} pemilih
                    </div>

                    <div class="flex flex-wrap items-center gap-1">
                        <component
                            :is="link.url ? Link : 'span'"
                            v-for="(link, idx) in voterAccesses.links"
                            :key="idx"
                            :href="link.url || undefined"
                            preserve-scroll
                            v-html="link.label"
                            :class="[
                                'rounded-lg px-3 py-1.5 font-bold transition-colors text-xs select-none',
                                link.active
                                    ? 'bg-blue-600 text-white'
                                    : link.url
                                      ? 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600 cursor-pointer'
                                      : 'text-slate-400 opacity-40 cursor-not-allowed dark:text-slate-500',
                            ]"
                        />
                    </div>
                </div>
            </div>

            <!-- Batch Generate Confirm Modal -->
            <ConfirmModal
                :show="showBatchConfirm"
                title="Generate Akses Pemilih"
                :message="`Generate hak akses pemilih untuk acara '${props.election.title}' berdasarkan kriteria target pemilih?`"
                type="info"
                confirm-text="Ya, Generate"
                cancel-text="Batal"
                @confirm="confirmBatchGenerate"
                @cancel="showBatchConfirm = false"
            />

            <!-- Delete Access Confirm Modal -->
            <ConfirmModal
                :show="showDeleteConfirm"
                title="Hapus Hak Pilih"
                :message="
                    selectedAccessToDelete
                        ? `Hapus hak pilih untuk '${selectedAccessToDelete.user_name}'?`
                        : ''
                "
                type="danger"
                confirm-text="Ya, Hapus"
                cancel-text="Batal"
                @confirm="confirmDeleteAccess"
                @cancel="showDeleteConfirm = false"
            />
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    RefreshCw,
    Users,
    CheckCircle2,
    Clock,
    Trash2,
    Search,
    X,
} from '@lucide/vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';

const props = defineProps<{
    election: {
        id: number;
        title: string;
        target_voter: string;
        academic_year: string | null;
        class_name: string | null;
    };
    voterAccesses: any;
    filters?: {
        search?: string;
    };
    stats: {
        total_access: number;
        total_voted: number;
    };
}>();

const isGenerating = ref(false);
const showBatchConfirm = ref(false);
const showDeleteConfirm = ref(false);
const selectedAccessToDelete = ref<any | null>(null);

const searchQuery = ref(props.filters?.search || '');
let searchTimer: any = null;

const handleSearch = () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        router.get(
            `/admin/pemilihan/${props.election.id}/pemilih`,
            { search: searchQuery.value },
            { preserveState: true, replace: true, preserveScroll: true }
        );
    }, 350);
};

const clearSearch = () => {
    searchQuery.value = '';
    router.get(
        `/admin/pemilihan/${props.election.id}/pemilih`,
        {},
        { preserveState: true, replace: true }
    );
};

const generateBatch = () => {
    showBatchConfirm.value = true;
};

const confirmBatchGenerate = () => {
    showBatchConfirm.value = false;
    isGenerating.value = true;
    router.post(
        `/admin/pemilihan/${props.election.id}/generate-pemilih`,
        {},
        {
            onFinish: () => {
                isGenerating.value = false;
            },
        },
    );
};

const deleteAccess = (access: any) => {
    selectedAccessToDelete.value = access;
    showDeleteConfirm.value = true;
};

const confirmDeleteAccess = () => {
    if (selectedAccessToDelete.value) {
        router.delete(
            `/admin/pemilihan/${props.election.id}/pemilih/${selectedAccessToDelete.value.id}`,
        );
    }
    showDeleteConfirm.value = false;
};

const formatDate = (dateStr: string | null) => {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

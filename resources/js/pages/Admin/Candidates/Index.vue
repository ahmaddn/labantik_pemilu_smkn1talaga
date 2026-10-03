<template>
    <AdminLayout>
        <template #header>Kelola Kandidat Paslon</template>

        <div class="space-y-6">
            <!-- Back & Info Bar -->
            <div
                class="flex flex-col items-start justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center dark:border-slate-700 dark:bg-slate-800"
            >
                <div>
                    <Link
                        href="/admin/pemilihan"
                        class="mb-1 inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:underline dark:text-blue-400"
                    >
                        <ArrowLeft class="h-3.5 w-3.5" />
                        <span>Kembali ke Daftar Pemilihan</span>
                    </Link>
                    <h2
                        class="text-base font-extrabold text-slate-900 dark:text-white"
                    >
                        Kandidat: {{ election.title }}
                    </h2>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a
                        :href="`/admin/pemilihan/${election.id}/kandidat/template`"
                        class="flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-bold text-slate-700 shadow-sm transition-colors hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 cursor-pointer"
                        title="Unduh Format Excel/CSV"
                    >
                        <Download class="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400" />
                        <span>Unduh Template</span>
                    </a>

                    <button
                        type="button"
                        @click="showImportModal = true"
                        class="flex items-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2.5 text-xs font-bold text-emerald-700 transition-colors hover:bg-emerald-100 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300 dark:hover:bg-emerald-950/70 cursor-pointer"
                        title="Import Kandidat dari file Excel/CSV"
                    >
                        <FileSpreadsheet class="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400" />
                        <span>Import Excel</span>
                    </button>

                    <Link
                        :href="`/admin/pemilihan/${election.id}/kandidat/create`"
                        class="flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition-colors hover:bg-blue-700"
                    >
                        <Plus class="h-4 w-4" />
                        <span>Tambah Kandidat</span>
                    </Link>
                </div>
            </div>

            <!-- Candidates Grid -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div
                    v-if="!candidates || candidates.length === 0"
                    class="col-span-full space-y-2 rounded-2xl border border-slate-200 bg-white p-8 text-center text-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400"
                >
                    <Users class="mx-auto h-10 w-10 text-slate-400" />
                    <h4 class="text-sm font-bold">Belum Ada Kandidat</h4>
                    <p class="text-xs">
                        Klik "Tambah Kandidat" untuk memasukkan paslon baru ke
                        pemilihan ini.
                    </p>
                </div>

                <div
                    v-for="candidate in candidates"
                    :key="candidate.id"
                    class="flex flex-col justify-between space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800"
                >
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900 text-base font-black text-amber-400"
                            >
                                {{ candidate.candidate_number }}
                            </span>
                            <div class="flex items-center gap-1">
                                <Link
                                    :href="`/admin/pemilihan/${election.id}/kandidat/${candidate.id}/edit`"
                                    class="rounded-lg p-1.5 text-slate-600 transition-colors hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400"
                                    title="Edit Kandidat"
                                >
                                    <Edit class="h-4 w-4" />
                                </Link>
                                <button
                                    @click="deleteCandidate(candidate)"
                                    class="rounded-lg p-1.5 text-slate-600 transition-colors hover:text-rose-600 dark:text-slate-400 dark:hover:text-rose-400"
                                    title="Hapus Kandidat"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center gap-3.5">
                            <div
                                class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-100 dark:border-slate-600 dark:bg-slate-700"
                            >
                                <img
                                    v-if="candidate.photo"
                                    :src="candidate.photo"
                                    :alt="candidate.chairman_name"
                                    class="h-full w-full object-cover"
                                />
                                <UserCheck
                                    v-else
                                    class="h-8 w-8 text-slate-400"
                                />
                            </div>

                            <div>
                                <h3
                                    class="text-sm font-extrabold text-slate-900 dark:text-white"
                                >
                                    {{ candidate.chairman_name }}
                                </h3>
                                <p
                                    v-if="candidate.vice_chairman_name"
                                    class="text-xs font-medium text-slate-600 dark:text-slate-300"
                                >
                                    Wakil: {{ candidate.vice_chairman_name }}
                                </p>
                            </div>
                        </div>

                        <div
                            v-if="candidate.vision_mission"
                            class="space-y-1 rounded-xl border border-slate-100 bg-slate-50 p-3 text-xs text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                        >
                            <span
                                class="block text-[11px] font-bold tracking-wider text-slate-500 uppercase"
                                >Visi & Misi:</span
                            >
                            <div
                                class="ql-editor prose prose-xs dark:prose-invert line-clamp-4 max-w-none text-xs leading-relaxed !p-0"
                                v-html="candidate.vision_mission"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delete Confirm Modal -->
            <ConfirmModal
                :show="showDeleteModal"
                title="Hapus Kandidat"
                :message="
                    selectedCandidate
                        ? `Apakah Anda yakin ingin menghapus kandidat No. ${selectedCandidate.candidate_number} (${selectedCandidate.chairman_name})?`
                        : ''
                "
                type="danger"
                confirm-text="Ya, Hapus"
                cancel-text="Batal"
                @confirm="confirmDelete"
                @cancel="showDeleteModal = false"
            />

            <!-- Import Candidates Modal -->
            <div
                v-if="showImportModal"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs"
            >
                <div
                    class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-800"
                >
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                        <div class="flex items-center gap-2">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                                <FileSpreadsheet class="h-5 w-5" />
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Import Data Kandidat</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Unggah file Excel / CSV data paslon</p>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="closeImportModal"
                            class="rounded-lg p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <form @submit.prevent="submitImport" class="mt-4 space-y-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Pilih File (CSV / Excel)</label>
                            <input
                                ref="fileInput"
                                type="file"
                                accept=".csv,.xlsx,.xls,text/csv"
                                required
                                @change="onFileSelected"
                                class="w-full cursor-pointer rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs text-slate-800 file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-blue-600 file:px-3 file:py-1 file:text-xs file:font-semibold file:text-white hover:file:bg-blue-700 focus:outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                            />
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                Format kolom: <strong>Nomor Urut, Nama Ketua, Nama Wakil, Visi & Misi</strong>.
                            </p>
                        </div>

                        <div class="flex items-center justify-between rounded-xl bg-slate-50 p-3 text-xs text-slate-600 dark:bg-slate-900/60 dark:text-slate-400">
                            <span>Belum punya formatnya?</span>
                            <a
                                :href="`/admin/pemilihan/${election.id}/kandidat/template`"
                                class="inline-flex items-center gap-1 font-bold text-blue-600 hover:underline dark:text-blue-400"
                            >
                                <Download class="h-3.5 w-3.5" />
                                <span>Unduh Template CSV</span>
                            </a>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2">
                            <button
                                type="button"
                                @click="closeImportModal"
                                class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 transition-colors hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-700"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                :disabled="isImporting || !selectedFile"
                                class="flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white shadow-sm transition-colors hover:bg-emerald-700 disabled:opacity-50 cursor-pointer"
                            >
                                <Upload class="h-4 w-4" />
                                <span>{{ isImporting ? 'Mengimpor...' : 'Mulai Import' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import {
    Users,
    Plus,
    Edit,
    Trash2,
    UserCheck,
    ArrowLeft,
    Download,
    FileSpreadsheet,
    Upload,
    X,
} from '@lucide/vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';

const props = defineProps<{
    election: {
        id: number;
        title: string;
        type: string;
    };
    candidates: any[];
}>();

const showDeleteModal = ref(false);
const selectedCandidate = ref<any | null>(null);

const showImportModal = ref(false);
const selectedFile = ref<File | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);
const isImporting = ref(false);

const deleteCandidate = (candidate: any) => {
    selectedCandidate.value = candidate;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (selectedCandidate.value) {
        router.delete(
            `/admin/pemilihan/${props.election.id}/kandidat/${selectedCandidate.value.id}`,
        );
    }
    showDeleteModal.value = false;
};

const onFileSelected = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        selectedFile.value = target.files[0];
    } else {
        selectedFile.value = null;
    }
};

const closeImportModal = () => {
    showImportModal.value = false;
    selectedFile.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const submitImport = () => {
    if (!selectedFile.value) return;

    isImporting.value = true;
    router.post(
        `/admin/pemilihan/${props.election.id}/kandidat/import`,
        {
            file: selectedFile.value,
        },
        {
            forceFormData: true,
            onFinish: () => {
                isImporting.value = false;
            },
            onSuccess: () => {
                closeImportModal();
            },
        },
    );
};
</script>

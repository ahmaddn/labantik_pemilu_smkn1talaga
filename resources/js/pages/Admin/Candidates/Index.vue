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

                <Link
                    :href="`/admin/pemilihan/${election.id}/kandidat/create`"
                    class="flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition-colors hover:bg-blue-700"
                >
                    <Plus class="h-4 w-4" />
                    <span>Tambah Kandidat</span>
                </Link>
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
                                class="prose prose-xs dark:prose-invert line-clamp-4 max-w-none text-xs leading-relaxed"
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
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { Users, Plus, Edit, Trash2, UserCheck, ArrowLeft } from '@lucide/vue';
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
</script>

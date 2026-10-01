<template>
    <AdminLayout>
        <template #header>Kelola Pemilihan Umum</template>

        <div class="space-y-6">
            <!-- Top Action Bar -->
            <div
                class="flex flex-col items-start justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:flex-row sm:items-center dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="space-y-1">
                    <h2
                        class="text-lg font-extrabold text-slate-900 dark:text-white"
                    >
                        Daftar Pemilihan Sekolah
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Atur acara pemilu, jadwal pemungutan suara, dan alokasi
                        hak pilih.
                    </p>
                </div>

                <Link
                    href="/admin/pemilihan/create"
                    class="flex shrink-0 items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-xs font-bold text-white shadow-sm transition-colors hover:bg-blue-700"
                >
                    <Plus class="h-4 w-4" />
                    <span>Buat Pemilihan Baru</span>
                </Link>
            </div>

            <!-- Elections Cards Grid -->
            <div
                v-if="!electionList || electionList.length === 0"
                class="space-y-3 rounded-2xl border border-slate-200 bg-white p-12 text-center dark:border-slate-800 dark:bg-slate-900"
            >
                <div
                    class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-950 dark:text-blue-400"
                >
                    <Vote class="h-6 w-6" />
                </div>
                <h3
                    class="text-base font-extrabold text-slate-900 dark:text-white"
                >
                    Belum Ada Acara Pemilihan
                </h3>
                <p
                    class="mx-auto max-w-sm text-xs text-slate-500 dark:text-slate-400"
                >
                    Klik tombol "Buat Pemilihan Baru" di atas untuk menambahkan
                    acara pemilu sekolah.
                </p>
            </div>

            <div v-else class="space-y-6">
                <div
                    class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="election in electionList"
                        :key="election.id"
                        class="flex flex-col justify-between space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-colors hover:border-slate-300 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-slate-700"
                    >
                        <div class="space-y-3">
                            <!-- Header Badges -->
                            <div
                                class="flex items-center justify-between gap-2"
                            >
                                <span
                                    class="rounded-md border border-blue-200 bg-blue-50 px-2.5 py-1 text-[10px] font-extrabold tracking-wider text-blue-700 uppercase dark:border-blue-900 dark:bg-blue-950 dark:text-blue-300"
                                >
                                    {{
                                        election.type === 'osis'
                                            ? 'PEMILIHAN OSIS'
                                            : election.type === 'vice_principal'
                                              ? 'WAKIL KEPALA SEKOLAH'
                                              : election.type ===
                                                  'class_president'
                                                ? 'KETUA KELAS'
                                                : 'PEMILU LAINNYA'
                                    }}
                                </span>

                                <span
                                    v-if="election.status === 'ongoing'"
                                    class="rounded-md bg-emerald-100 px-2.5 py-1 text-[10px] font-extrabold tracking-wider text-emerald-800 uppercase dark:bg-emerald-950 dark:text-emerald-300"
                                >
                                    Berlangsung
                                </span>
                                <span
                                    v-else-if="election.status === 'upcoming'"
                                    class="rounded-md bg-amber-100 px-2.5 py-1 text-[10px] font-extrabold tracking-wider text-amber-800 uppercase dark:bg-amber-950 dark:text-amber-300"
                                >
                                    Akan Datang
                                </span>
                                <span
                                    v-else
                                    class="rounded-md bg-slate-100 px-2.5 py-1 text-[10px] font-extrabold tracking-wider text-slate-700 uppercase dark:bg-slate-800 dark:text-slate-300"
                                >
                                    Selesai
                                </span>
                            </div>

                            <!-- Title & Description -->
                            <div>
                                <h3
                                    class="text-base leading-snug font-extrabold text-slate-900 dark:text-white"
                                >
                                    {{ election.title }}
                                </h3>
                                <p
                                    v-if="election.description"
                                    class="mt-1 line-clamp-2 text-xs leading-relaxed text-slate-600 dark:text-slate-400"
                                >
                                    {{ election.description }}
                                </p>
                            </div>

                            <!-- Metadata Stats Box -->
                            <div
                                class="space-y-2 rounded-xl border border-slate-100 bg-slate-50 p-3.5 text-xs dark:border-slate-800 dark:bg-slate-800/60"
                            >
                                <div
                                    class="grid grid-cols-3 gap-2 border-b border-slate-200/60 pb-2 text-center dark:border-slate-700/60"
                                >
                                    <div>
                                        <span
                                            class="block text-sm font-black text-slate-900 dark:text-white"
                                            >{{
                                                election.candidates_count
                                            }}</span
                                        >
                                        <span
                                            class="text-[10px] font-semibold text-slate-500"
                                            >Paslon</span
                                        >
                                    </div>
                                    <div>
                                        <span
                                            class="block text-sm font-black text-slate-900 dark:text-white"
                                            >{{ election.voters_count }}</span
                                        >
                                        <span
                                            class="text-[10px] font-semibold text-slate-500"
                                            >Pemilih</span
                                        >
                                    </div>
                                    <div>
                                        <span
                                            class="block text-sm font-black text-blue-600 dark:text-blue-400"
                                            >{{ election.votes_count }}</span
                                        >
                                        <span
                                            class="text-[10px] font-semibold text-slate-500"
                                            >Suara</span
                                        >
                                    </div>
                                </div>

                                <div
                                    class="flex items-center justify-between pt-0.5 text-[11px] text-slate-600 dark:text-slate-400"
                                >
                                    <span>Tahun Ajaran:</span>
                                    <strong
                                        class="font-bold text-slate-900 dark:text-white"
                                        >{{
                                            election.academic_year || 'Semua'
                                        }}</strong
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Bottom Action Buttons -->
                        <div
                            class="space-y-3 border-t border-slate-100 pt-3 dark:border-slate-800"
                        >
                            <div class="grid grid-cols-2 gap-2">
                                <Link
                                    :href="`/admin/pemilihan/${election.id}/kandidat`"
                                    class="flex items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-slate-100 px-3 py-2.5 text-xs font-bold text-slate-800 transition-colors hover:bg-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                                >
                                    <Users class="h-3.5 w-3.5 text-blue-600" />
                                    <span
                                        >Kandidat ({{
                                            election.candidates_count
                                        }})</span
                                    >
                                </Link>

                                <Link
                                    :href="`/admin/pemilihan/${election.id}/pemilih`"
                                    class="flex items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-slate-100 px-3 py-2.5 text-xs font-bold text-slate-800 transition-colors hover:bg-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                                >
                                    <UserCheck
                                        class="h-3.5 w-3.5 text-emerald-600"
                                    />
                                    <span
                                        >Hak Pilih ({{
                                            election.voters_count
                                        }})</span
                                    >
                                </Link>
                            </div>

                            <div
                                class="space-y-2.5 border-t border-slate-100 pt-3 dark:border-slate-800"
                            >
                                <!-- Baris Utama: Tombol Simulasi & Lihat Hasil -->
                                <div class="grid grid-cols-2 gap-2">
                                    <Link
                                        :href="`/admin/pemilihan/${election.id}/simulasi`"
                                        class="flex items-center justify-center gap-1.5 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-bold text-amber-800 shadow-sm transition-all hover:bg-amber-100 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-300 dark:hover:bg-amber-900/60"
                                        title="Uji Simulasi Voting & Multi-Round"
                                    >
                                        <FlaskConical class="h-3.5 w-3.5 text-amber-600 dark:text-amber-400" />
                                        <span>Simulasi</span>
                                    </Link>

                                    <Link
                                        :href="`/admin/pemilihan/${election.id}/hasil`"
                                        class="flex items-center justify-center gap-1.5 rounded-xl border border-purple-200 bg-purple-50 px-3 py-2 text-xs font-bold text-purple-800 shadow-sm transition-all hover:bg-purple-100 dark:border-purple-900/50 dark:bg-purple-950/40 dark:text-purple-300 dark:hover:bg-purple-900/60"
                                    >
                                        <BarChart3 class="h-3.5 w-3.5 text-purple-600 dark:text-purple-400" />
                                        <span>Lihat Hasil</span>
                                    </Link>
                                </div>

                                <!-- Baris Pengaturan: Toggle Publikasi & Aksi Edit/Hapus -->
                                <div class="flex items-center justify-between gap-2 pt-0.5">
                                    <button
                                        type="button"
                                        @click="togglePublish(election)"
                                        :class="[
                                            'flex flex-1 items-center justify-center gap-1.5 rounded-xl border py-2 px-3 text-xs font-bold transition-colors cursor-pointer',
                                            election.is_published
                                                ? 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300 hover:bg-emerald-100'
                                                : 'border-slate-200 bg-slate-50 text-slate-700 dark:border-slate-700 dark:bg-slate-800/80 dark:text-slate-300 hover:bg-slate-100',
                                        ]"
                                        :title="election.is_published ? 'Klik untuk sembunyikan hasil dari pemilih' : 'Klik untuk tampilkan hasil ke pemilih'"
                                    >
                                        <Eye
                                            v-if="election.is_published"
                                            class="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400"
                                        />
                                        <EyeOff v-else class="h-3.5 w-3.5 text-slate-500 dark:text-slate-400" />
                                        <span>{{
                                            election.is_published
                                                ? 'Hasil Dipublikasi'
                                                : 'Hasil Tersembunyi'
                                        }}</span>
                                    </button>

                                    <div class="flex shrink-0 items-center gap-1.5">
                                        <Link
                                            :href="`/admin/pemilihan/${election.id}/edit`"
                                            class="flex h-8 w-8 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition-colors hover:bg-slate-50 hover:text-blue-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-blue-400"
                                            title="Edit Pemilihan"
                                        >
                                            <Edit class="h-3.5 w-3.5" />
                                        </Link>

                                        <button
                                            type="button"
                                            @click="deleteElection(election)"
                                            class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-xl border border-rose-200 bg-rose-50 text-rose-600 shadow-sm transition-colors hover:bg-rose-100 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-400 dark:hover:bg-rose-900/60"
                                            title="Hapus Pemilihan"
                                        >
                                            <Trash2 class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination Bar -->
                <div
                    v-if="paginationLinks && paginationLinks.length > 3"
                    class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-4 text-xs dark:border-slate-800 dark:bg-slate-900"
                >
                    <div class="font-medium text-slate-500 dark:text-slate-400">
                        Menampilkan {{ elections.from }} -
                        {{ elections.to }} dari {{ elections.total }} pemilihan
                    </div>
                    <div class="flex gap-1">
                        <Link
                            v-for="(link, idx) in paginationLinks"
                            :key="idx"
                            :href="link.url || '#'"
                            v-html="link.label"
                            :class="[
                                'rounded-lg px-3 py-1.5 font-bold transition-colors',
                                link.active
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700',
                            ]"
                        ></Link>
                    </div>
                </div>
            </div>

            <!-- Custom Confirm Delete Modal -->
            <ConfirmModal
                :show="showDeleteConfirm"
                title="Hapus Acara Pemilihan"
                :message="`Apakah Anda yakin ingin menghapus acara pemilihan '${selectedElectionToDelete?.title}'? Data kandidat dan hasil suara terkait akan terhapus.`"
                type="danger"
                confirm-text="Ya, Hapus Pemilihan"
                cancel-text="Batal"
                @confirm="proceedDeleteElection"
                @cancel="showDeleteConfirm = false"
            />
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import {
    Plus,
    Edit,
    Trash2,
    Eye,
    EyeOff,
    Users,
    UserCheck,
    Vote,
    BarChart3,
    FlaskConical,
} from '@lucide/vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';

const props = defineProps<{
    elections: any;
    academicYears: string[];
    classes: any[];
}>();

const electionList = computed(() =>
    Array.isArray(props.elections)
        ? props.elections
        : props.elections.data || [],
);
const paginationLinks = computed(() =>
    !Array.isArray(props.elections) && props.elections?.links
        ? props.elections.links
        : [],
);

const showDeleteConfirm = ref(false);
const selectedElectionToDelete = ref<any>(null);

const deleteElection = (election: any) => {
    selectedElectionToDelete.value = election;
    showDeleteConfirm.value = true;
};

const proceedDeleteElection = () => {
    if (selectedElectionToDelete.value) {
        router.delete(`/admin/pemilihan/${selectedElectionToDelete.value.id}`, {
            onFinish: () => {
                showDeleteConfirm.value = false;
                selectedElectionToDelete.value = null;
            },
        });
    }
};

const togglePublish = (election: any) => {
    router.post(`/admin/pemilihan/${election.id}/toggle-publish`);
};
</script>

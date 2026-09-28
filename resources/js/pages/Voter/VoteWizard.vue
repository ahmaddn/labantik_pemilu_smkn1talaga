<template>
    <VoterLayout>
        <div class="mx-auto max-w-7xl space-y-5 pb-32 sm:pb-12">
            <!-- Election Header (Solid Blue, Clean Rounded-xl, No Gradients/Circles) -->
            <div
                class="space-y-2.5 rounded-2xl bg-blue-700 p-5 text-white shadow-sm sm:p-6 dark:bg-blue-800"
            >
                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="rounded-lg bg-blue-800 px-2.5 py-1 text-[11px] font-bold tracking-wider text-blue-100 uppercase"
                    >
                        Surat Suara Digital
                    </span>
                    <span
                        v-if="election.is_multi_stage"
                        class="rounded-lg bg-amber-400 px-2.5 py-1 text-[11px] font-black tracking-wider text-slate-950 uppercase"
                    >
                        Tahap {{ election.current_stage }} dari
                        {{ election.total_stages }}
                    </span>
                    <span
                        class="rounded-lg bg-blue-900/60 px-2.5 py-1 text-[11px] font-semibold text-blue-200"
                    >
                        {{ election.candidates.length }} Pasangan Kandidat
                    </span>
                </div>

                <h1 class="text-lg leading-snug font-bold sm:text-xl">
                    {{ election.title }}
                </h1>

                <p
                    v-if="election.description"
                    class="text-xs leading-relaxed text-blue-100 sm:text-sm"
                >
                    {{ election.description }}
                </p>
            </div>

            <!-- Controls & Filter Toolbar -->
            <div
                class="flex flex-col items-stretch justify-between gap-3 rounded-2xl border border-slate-200 bg-white p-3.5 shadow-sm sm:flex-row sm:items-center sm:p-4 dark:border-slate-700 dark:bg-slate-800"
            >
                <div class="flex items-center gap-2">
                    <Vote
                        class="h-4 w-4 shrink-0 text-blue-600 dark:text-blue-400"
                    />
                    <span
                        class="text-xs font-bold text-slate-700 dark:text-slate-300"
                    >
                        Pilih Salah Satu Pasangan Kandidat:
                    </span>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Search input if candidate count > 5 -->
                    <div
                        v-if="election.candidates.length > 5"
                        class="relative flex-1 sm:w-64"
                    >
                        <Search
                            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400"
                        />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari nama / no. paslon..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-1.5 pr-3 pl-9 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                        />
                    </div>

                    <span
                        class="shrink-0 text-xs font-bold text-blue-600 dark:text-blue-400"
                    >
                        {{ filteredCandidates.length }} Opsi
                    </span>
                </div>
            </div>

            <!-- Empty Filter State -->
            <div
                v-if="filteredCandidates.length === 0"
                class="space-y-3 rounded-2xl border border-slate-200 bg-white p-6 text-center dark:border-slate-700 dark:bg-slate-800"
            >
                <UserCheck class="mx-auto h-8 w-8 text-slate-400" />
                <p class="text-xs font-bold text-slate-700 dark:text-slate-300">
                    Tidak ada kandidat yang sesuai dengan pencarian.
                </p>
                <button
                    @click="searchQuery = ''"
                    class="rounded-xl bg-blue-600 px-3 py-1.5 text-xs font-bold text-white transition-colors hover:bg-blue-700"
                >
                    Reset Pencarian
                </button>
            </div>

            <!-- Candidate Cards Grid (Responsive 1 column on Mobile, 2-3 columns on Desktop) -->
            <div
                v-else
                class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3"
            >
                <div
                    v-for="candidate in paginatedCandidates"
                    :key="candidate.id"
                    @click="selectedCandidate = candidate"
                    :class="[
                        'relative cursor-pointer space-y-3 rounded-2xl border p-4 transition-all',
                        selectedCandidate?.id === candidate.id
                            ? 'border-blue-600 bg-blue-50/80 shadow-sm ring-2 ring-blue-500/40 dark:border-blue-500 dark:bg-blue-950/50'
                            : 'border-slate-200 bg-white hover:border-slate-300 dark:border-slate-700 dark:bg-slate-800',
                    ]"
                >
                    <!-- Top Row: Badge No Paslon & Square Checkbox Indicator -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-base font-bold text-white"
                            >
                                {{ candidate.candidate_number }}
                            </div>
                            <span
                                class="text-xs font-bold text-slate-700 dark:text-slate-300"
                            >
                                Paslon #{{ candidate.candidate_number }}
                            </span>
                        </div>

                        <!-- Square Checkbox Indicator (No rounded-full) -->
                        <div
                            :class="[
                                'flex h-6 w-6 items-center justify-center rounded-lg border-2 transition-colors',
                                selectedCandidate?.id === candidate.id
                                    ? 'border-blue-600 bg-blue-600 text-white'
                                    : 'border-slate-300 dark:border-slate-600',
                            ]"
                        >
                            <CheckCircle2
                                v-if="selectedCandidate?.id === candidate.id"
                                class="h-4 w-4"
                            />
                        </div>
                    </div>

                    <!-- Body Row: Photo & Details -->
                    <div class="flex items-center gap-3.5 pt-1">
                        <!-- Candidate Photo -->
                        <div
                            class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-100 sm:h-20 sm:w-20 dark:border-slate-600 dark:bg-slate-700"
                        >
                            <img
                                v-if="candidate.photo"
                                :src="candidate.photo"
                                :alt="candidate.chairman_name"
                                class="h-full w-full object-cover"
                            />
                            <UserCheck v-else class="h-8 w-8 text-slate-400" />
                        </div>

                        <!-- Candidate Names -->
                        <div class="min-w-0 flex-1 space-y-1">
                            <h3
                                class="truncate text-sm leading-tight font-bold text-slate-900 sm:text-base dark:text-white"
                            >
                                {{ candidate.chairman_name }}
                            </h3>

                            <p
                                v-if="candidate.vice_chairman_name"
                                class="truncate text-xs font-medium text-slate-600 dark:text-slate-300"
                            >
                                Wakil: {{ candidate.vice_chairman_name }}
                            </p>

                            <!-- Visi & Misi Button -->
                            <div v-if="candidate.vision_mission" class="pt-1">
                                <button
                                    type="button"
                                    @click.stop="activeVisionModal = candidate"
                                    class="inline-flex cursor-pointer items-center gap-1 text-xs font-bold text-blue-600 hover:underline dark:text-blue-400"
                                >
                                    <FileText class="h-3.5 w-3.5" />
                                    <span>Lihat Visi & Misi</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Card Footer Status -->
                    <div
                        class="flex items-center justify-between border-t border-slate-100 pt-2 text-xs dark:border-slate-700/60"
                    >
                        <span
                            class="text-[11px] font-medium text-slate-500 dark:text-slate-400"
                        >
                            {{
                                selectedCandidate?.id === candidate.id
                                    ? 'Sudah Anda pilih'
                                    : 'Klik untuk memilih'
                            }}
                        </span>
                        <span
                            :class="[
                                'rounded-lg px-2.5 py-0.5 text-[11px] font-bold',
                                selectedCandidate?.id === candidate.id
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
                            ]"
                        >
                            {{
                                selectedCandidate?.id === candidate.id
                                    ? 'Terpilih'
                                    : 'Pilih'
                            }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Pagination Bar (Active if > 5 candidates and totalPages > 1) -->
            <div
                v-if="election.candidates.length > 5 && totalPages > 1"
                class="flex flex-col items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white p-3.5 shadow-sm sm:flex-row dark:border-slate-700 dark:bg-slate-800"
            >
                <div
                    class="text-xs font-semibold text-slate-600 dark:text-slate-400"
                >
                    Halaman
                    <strong class="text-slate-900 dark:text-white">{{
                        currentPage
                    }}</strong>
                    dari
                    <strong class="text-slate-900 dark:text-white">{{
                        totalPages
                    }}</strong>
                    <span class="ml-1 text-slate-400"
                        >({{ filteredCandidates.length }} kandidat)</span
                    >
                </div>

                <div class="flex flex-wrap items-center justify-center gap-1.5">
                    <button
                        type="button"
                        :disabled="currentPage === 1"
                        @click="goToPage(currentPage - 1)"
                        :class="[
                            'flex cursor-pointer items-center gap-1 rounded-xl px-3 py-1.5 text-xs font-bold transition-colors',
                            currentPage === 1
                                ? 'cursor-not-allowed text-slate-300 dark:text-slate-600'
                                : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600',
                        ]"
                    >
                        <ChevronLeft class="h-4 w-4" />
                        <span>Sebelumnya</span>
                    </button>

                    <div class="flex items-center gap-1">
                        <button
                            v-for="p in totalPages"
                            :key="p"
                            type="button"
                            @click="goToPage(p)"
                            :class="[
                                'flex h-8 w-8 cursor-pointer items-center justify-center rounded-xl text-xs font-bold transition-colors',
                                currentPage === p
                                    ? 'bg-blue-600 text-white shadow-xs'
                                    : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600',
                            ]"
                        >
                            {{ p }}
                        </button>
                    </div>

                    <button
                        type="button"
                        :disabled="currentPage === totalPages"
                        @click="goToPage(currentPage + 1)"
                        :class="[
                            'flex cursor-pointer items-center gap-1 rounded-xl px-3 py-1.5 text-xs font-bold transition-colors',
                            currentPage === totalPages
                                ? 'cursor-not-allowed text-slate-300 dark:text-slate-600'
                                : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600',
                        ]"
                    >
                        <span>Selanjutnya</span>
                        <ChevronRight class="h-4 w-4" />
                    </button>
                </div>
            </div>

            <!-- Action Sticky Bottom Bar -->
            <div class="sticky bottom-16 z-20 pt-2 sm:bottom-4">
                <div
                    class="flex flex-col items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-xl sm:flex-row dark:border-slate-800 dark:bg-slate-900"
                >
                    <!-- Selected Candidate Preview -->
                    <div
                        class="flex w-full min-w-0 items-center gap-3 sm:w-auto"
                    >
                        <template v-if="selectedCandidate">
                            <div
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-base font-bold text-white"
                            >
                                {{ selectedCandidate.candidate_number }}
                            </div>
                            <div class="truncate">
                                <div
                                    class="text-[10px] font-bold text-blue-600 uppercase dark:text-blue-400"
                                >
                                    Paslon Pilihan:
                                </div>
                                <div
                                    class="truncate text-xs font-bold text-slate-900 sm:text-sm dark:text-white"
                                >
                                    {{ selectedCandidate.chairman_name }}
                                    <span
                                        v-if="
                                            selectedCandidate.vice_chairman_name
                                        "
                                        class="text-xs font-normal text-slate-500"
                                    >
                                        &
                                        {{
                                            selectedCandidate.vice_chairman_name
                                        }}
                                    </span>
                                </div>
                            </div>
                        </template>

                        <template v-else>
                            <div
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-400 dark:bg-slate-800"
                            >
                                <Vote class="h-5 w-5" />
                            </div>
                            <div>
                                <div
                                    class="text-xs font-bold text-slate-800 dark:text-slate-200"
                                >
                                    Belum Ada Paslon Dipilih
                                </div>
                                <div
                                    class="text-[11px] text-slate-500 dark:text-slate-400"
                                >
                                    Klik salah satu kandidat di atas
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="button"
                        :disabled="!selectedCandidate || isSubmitting"
                        @click="showConfirmModal = true"
                        :class="[
                            'flex w-full shrink-0 cursor-pointer items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-xs font-bold shadow-sm transition-colors sm:w-auto sm:text-sm',
                            selectedCandidate && !isSubmitting
                                ? 'bg-blue-600 text-white hover:bg-blue-700'
                                : 'cursor-not-allowed bg-slate-200 text-slate-400 dark:bg-slate-800 dark:text-slate-500',
                        ]"
                    >
                        <Vote class="h-4 w-4" />
                        <span>{{
                            selectedCandidate
                                ? `Kirim Suara (Paslon #${selectedCandidate.candidate_number})`
                                : 'Pilih Salah Satu Paslon'
                        }}</span>
                    </button>
                </div>
            </div>

            <!-- Confirmation Modal -->
            <ConfirmModal
                :show="showConfirmModal"
                title="Konfirmasi Pilihan Suara"
                :message="confirmMessage"
                type="warning"
                confirm-text="Ya, Simpan Suara Saya"
                cancel-text="Batal / Cek Lagi"
                @confirm="submitVote"
                @cancel="showConfirmModal = false"
            />

            <!-- Visi & Misi Modal -->
            <Teleport v-if="isMounted" to="body">
                <Transition name="modal-fade">
                    <div
                        v-if="activeVisionModal"
                        class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm"
                        @click.self="activeVisionModal = null"
                    >
                        <div
                            class="modal-card flex max-h-[80vh] w-full max-w-md flex-col space-y-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-xl dark:border-slate-800 dark:bg-slate-900"
                        >
                            <!-- Modal Header -->
                            <div
                                class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800"
                            >
                                <div class="flex items-center gap-2.5">
                                    <div
                                        class="flex h-8 w-8 items-center justify-center rounded-xl bg-blue-600 text-sm font-bold text-white"
                                    >
                                        {{ activeVisionModal.candidate_number }}
                                    </div>
                                    <div>
                                        <h3
                                            class="text-sm leading-tight font-bold text-slate-900 dark:text-white"
                                        >
                                            Visi & Misi Paslon No.
                                            {{
                                                activeVisionModal.candidate_number
                                            }}
                                        </h3>
                                        <p
                                            class="text-xs font-medium text-blue-600"
                                        >
                                            {{
                                                activeVisionModal.chairman_name
                                            }}
                                            <span
                                                v-if="
                                                    activeVisionModal.vice_chairman_name
                                                "
                                                >&
                                                {{
                                                    activeVisionModal.vice_chairman_name
                                                }}</span
                                            >
                                        </p>
                                    </div>
                                </div>

                                <button
                                    @click="activeVisionModal = null"
                                    class="cursor-pointer rounded-lg p-1 text-slate-400 transition-colors hover:text-slate-600 dark:hover:text-white"
                                >
                                    <X class="h-5 w-5" />
                                </button>
                            </div>

                            <!-- Modal Content -->
                            <div
                                class="prose prose-xs dark:prose-invert max-w-none flex-1 space-y-2 overflow-y-auto text-xs leading-relaxed text-slate-700 dark:text-slate-300"
                                v-html="activeVisionModal.vision_mission"
                            ></div>

                            <!-- Modal Footer -->
                            <div
                                class="flex justify-end border-t border-slate-100 pt-2 dark:border-slate-800"
                            >
                                <button
                                    @click="activeVisionModal = null"
                                    class="cursor-pointer rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white transition-colors hover:bg-slate-800 dark:bg-slate-800"
                                >
                                    Tutup
                                </button>
                            </div>
                        </div>
                    </div>
                </Transition>
            </Teleport>
        </div>
    </VoterLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    Vote,
    CheckCircle2,
    UserCheck,
    FileText,
    X,
    Search,
    ChevronLeft,
    ChevronRight,
} from '@lucide/vue';
import VoterLayout from '@/Layouts/VoterLayout.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';

const props = defineProps<{
    election: {
        id: string | number;
        title: string;
        description: string;
        type: string;
        is_multi_stage?: boolean;
        current_stage?: number;
        total_stages?: number;
        end_at: string;
        candidates: any[];
    };
}>();

const isMounted = ref(false);
onMounted(() => {
    isMounted.value = true;
});

const selectedCandidate = ref<any>(null);
const showConfirmModal = ref(false);
const activeVisionModal = ref<any>(null);
const isSubmitting = ref(false);

// Search & Pagination Logic
const searchQuery = ref('');
const currentPage = ref(1);
const perPage = 6;

const filteredCandidates = computed(() => {
    if (props.election.candidates.length <= 5 || !searchQuery.value.trim()) {
        return props.election.candidates;
    }
    const q = searchQuery.value.toLowerCase().trim();
    return props.election.candidates.filter(
        (c) =>
            c.chairman_name?.toLowerCase().includes(q) ||
            c.vice_chairman_name?.toLowerCase().includes(q) ||
            String(c.candidate_number) === q,
    );
});

watch(searchQuery, () => {
    currentPage.value = 1;
});

const totalPages = computed(() => {
    if (props.election.candidates.length <= 5) return 1;
    return Math.ceil(filteredCandidates.value.length / perPage) || 1;
});

const paginatedCandidates = computed(() => {
    if (props.election.candidates.length <= 5) {
        return filteredCandidates.value;
    }
    const start = (currentPage.value - 1) * perPage;
    return filteredCandidates.value.slice(start, start + perPage);
});

const goToPage = (page: number) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

const confirmMessage = computed(() => {
    if (!selectedCandidate.value) return '';
    return `Apakah Anda yakin ingin memilih Pasangan No. ${selectedCandidate.value.candidate_number} (${selectedCandidate.value.chairman_name})? Pilihan tidak dapat diubah setelah Anda menekan tombol simpan.`;
});

const submitVote = () => {
    if (!selectedCandidate.value || isSubmitting.value) return;

    isSubmitting.value = true;

    router.post(
        `/vote/${props.election.id}`,
        {
            candidate_id: selectedCandidate.value.id,
        },
        {
            onFinish: () => {
                isSubmitting.value = false;
                showConfirmModal.value = false;
            },
        },
    );
};
</script>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.25s ease;
}

.modal-fade-enter-active .modal-card,
.modal-fade-leave-active .modal-card {
    transition:
        transform 0.25s cubic-bezier(0.16, 1, 0.3, 1),
        opacity 0.25s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}

.modal-fade-enter-from .modal-card,
.modal-fade-leave-to .modal-card {
    opacity: 0;
    transform: scale(0.95) translateY(10px);
}
</style>

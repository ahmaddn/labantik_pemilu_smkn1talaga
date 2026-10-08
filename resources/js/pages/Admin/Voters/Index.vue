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

                <div class="flex flex-wrap items-center gap-2">
                    <button
                        v-if="stats.total_access > 0"
                        type="button"
                        :disabled="isDeletingAll"
                        @click="showDeleteAllConfirm = true"
                        class="flex items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2.5 text-xs font-bold text-rose-600 transition-colors hover:bg-rose-100 disabled:opacity-50 dark:border-rose-900/40 dark:bg-rose-950/30 dark:text-rose-400 dark:hover:bg-rose-950/50 cursor-pointer"
                    >
                        <Trash2 class="h-3.5 w-3.5" />
                        <span>{{ isDeletingAll ? 'Menghapus...' : 'Hapus Semua' }}</span>
                    </button>

                    <button
                        type="button"
                        @click="openSelectModal"
                        class="flex items-center gap-1.5 rounded-xl border border-blue-200 bg-blue-50 px-3.5 py-2.5 text-xs font-bold text-blue-600 transition-colors hover:bg-blue-100 dark:border-blue-900/40 dark:bg-blue-950/30 dark:text-blue-400 dark:hover:bg-blue-950/50 cursor-pointer"
                    >
                        <UserPlus class="h-3.5 w-3.5" />
                        <span>Pilih Pemilih Tertentu</span>
                    </button>

                    <button
                        type="button"
                        :disabled="isGenerating"
                        @click="generateBatch"
                        class="flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition-colors hover:bg-emerald-700 disabled:opacity-50 cursor-pointer"
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
            </div>

            <!-- Stage Selector Tabs (If Multi-Stage) -->
            <div
                v-if="election.is_multi_stage"
                class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-700 dark:bg-slate-800"
            >
                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="px-2 text-xs font-bold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                    >
                        Tahap Hak Pilih:
                    </span>
                    <button
                        v-for="stg in election.total_stages"
                        :key="stg"
                        type="button"
                        @click="changeStage(stg)"
                        :class="[
                            'cursor-pointer rounded-xl px-4 py-2 text-xs font-bold transition-all shadow-xs',
                            currentSelectedStage === stg
                                ? 'bg-blue-600 text-white ring-2 ring-blue-400 dark:ring-blue-500 shadow-md'
                                : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-700/70 dark:text-slate-300 dark:hover:bg-slate-700',
                        ]"
                    >
                        Tahap {{ stg }}
                        <span
                            v-if="stg === election.current_stage"
                            class="ml-1 rounded-full bg-emerald-500/20 px-1.5 py-0.2 text-[10px] text-emerald-700 dark:text-emerald-300 font-extrabold"
                        >
                            Aktif
                        </span>
                    </button>
                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400 pr-2">
                    Menampilkan data status hak pilih untuk: <strong class="text-blue-600 dark:text-blue-400">Tahap {{ currentSelectedStage }}</strong>
                </div>
            </div>

            <!-- Stats Pill Bar -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-2">
                <div
                    class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800"
                >
                    <div>
                        <span
                            class="text-xs font-semibold text-slate-500 dark:text-slate-400"
                            >Total Terdaftar (Tahap {{ currentSelectedStage }})</span
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
                            >Sudah Memilih (Tahap {{ currentSelectedStage }})</span
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
                            <span v-if="election.is_multi_stage" class="text-xs font-medium text-blue-600 dark:text-blue-400 ml-1.5">
                                (Tahap {{ currentSelectedStage }})
                            </span>
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Status penggunaan hak pilih pengguna
                        </p>
                    </div>

                    <!-- Filter & Search Controls -->
                    <div class="flex flex-wrap items-center gap-2.5">
                        <!-- Urutkan Berdasarkan -->
                        <div class="flex items-center gap-1.5 text-xs text-slate-600 dark:text-slate-300">
                            <span class="font-semibold text-slate-500 text-[11px] uppercase tracking-wider">Urutkan:</span>
                            <select
                                v-model="sortBy"
                                @change="handleSortChange"
                                class="rounded-xl border border-slate-300 bg-slate-50 py-1.5 px-2.5 text-xs font-semibold text-slate-800 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white cursor-pointer"
                            >
                                <option value="name">Nama Pemilih (A-Z)</option>
                                <option value="class">Kelas / Tingkat (10 - 12)</option>
                            </select>
                        </div>

                        <!-- Filter Kelas (Jika ada data kelas) -->
                        <div
                            v-if="availableClasses && availableClasses.length > 0"
                            class="flex items-center gap-1.5 text-xs text-slate-600 dark:text-slate-300"
                        >
                            <span class="font-semibold text-slate-500 text-[11px] uppercase tracking-wider">Kelas:</span>
                            <select
                                v-model="selectedClass"
                                @change="handleClassFilterChange"
                                class="rounded-xl border border-slate-300 bg-slate-50 py-1.5 px-2.5 text-xs font-semibold text-slate-800 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white cursor-pointer"
                            >
                                <option value="">Semua Kelas</option>
                                <option
                                    v-for="cls in availableClasses"
                                    :key="typeof cls === 'object' ? cls.value : cls"
                                    :value="typeof cls === 'object' ? cls.value : cls"
                                >
                                    {{ typeof cls === 'object' ? cls.label : cls }}
                                </option>
                            </select>
                        </div>

                        <!-- Search Bar Box -->
                        <div class="relative w-full sm:w-56">
                            <Search
                                class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400"
                            />
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Cari nama, NIS, NIP..."
                                @input="handleSearch"
                                class="w-full rounded-xl border border-slate-300 bg-slate-50 py-1.5 pr-8 pl-9 text-xs text-slate-900 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
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
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead
                            class="border-b border-slate-200 bg-slate-50 font-bold tracking-wider text-slate-600 uppercase dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-400"
                        >
                            <tr>
                                <th class="p-3.5 pl-5">Nama Pemilih</th>
                                <th class="p-3.5">Kelas / Rombel</th>
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
                                    colspan="7"
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
                                <td class="p-3.5">
                                    <span
                                        v-if="access.user_class"
                                        class="rounded-md bg-blue-50 px-2 py-0.5 text-xs font-bold text-blue-700 dark:bg-blue-950/60 dark:text-blue-300"
                                    >
                                        {{ access.user_class }}
                                    </span>
                                    <span
                                        v-else
                                        class="text-xs text-slate-400 dark:text-slate-500"
                                    >
                                        -
                                    </span>
                                </td>
                                <td
                                    class="p-3.5 font-medium text-slate-600 dark:text-slate-300"
                                >
                                    <div>{{ access.user_identifier }}</div>
                                    <div
                                        v-if="access.user_email && access.user_identifier !== access.user_email"
                                        class="text-[11px] text-slate-400 dark:text-slate-500"
                                    >
                                        {{ access.user_email }}
                                    </div>
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

            <!-- Delete All Voter Access Confirm Modal -->
            <ConfirmModal
                :show="showDeleteAllConfirm"
                title="Hapus Semua Hak Pilih"
                message="Apakah Anda yakin ingin menghapus SEMUA data hak pilih untuk pemilihan ini? (Pemilih yang sudah menggunakan hak suaranya tidak akan terhapus demi menjaga keabsahan data)."
                type="danger"
                confirm-text="Ya, Hapus Semua"
                cancel-text="Batal"
                @confirm="confirmDeleteAll"
                @cancel="showDeleteAllConfirm = false"
            />

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

            <!-- Select Custom Users Modal -->
            <div
                v-if="showSelectModal"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
            >
                <div
                    class="w-full max-w-lg overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900"
                >
                    <div class="flex items-center justify-between border-b border-slate-200 p-5 dark:border-slate-800">
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900 dark:text-white">
                                Tambah Pemilih Tertentu
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Cari dan pilih siswa atau guru untuk dijadikan pemilih.
                            </p>
                        </div>
                        <button
                            @click="closeSelectModal"
                            class="rounded-lg p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>

                    <div class="p-5 space-y-4">
                        <!-- Search Box & Class Filter inside Modal -->
                        <div class="flex flex-col gap-2.5 sm:flex-row sm:items-center">
                            <div class="relative flex-1">
                                <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400" />
                                <input
                                    v-model="userSearchQuery"
                                    type="text"
                                    placeholder="Ketik nama, NIS, NIP, atau email..."
                                    @input="searchEligibleUsers"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 py-2.5 pr-8 pl-9 text-xs text-slate-900 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                                />
                                <span v-if="isLoadingUsers" class="absolute top-1/2 right-3 -translate-y-1/2 text-xs text-slate-400">
                                    <RefreshCw class="h-3.5 w-3.5 animate-spin" />
                                </span>
                            </div>

                            <!-- Filter Kelas per Siswa inside Modal -->
                            <div v-if="election.target_voter !== 'teacher' && availableClasses && availableClasses.length > 0" class="sm:w-44">
                                <select
                                    v-model="modalClassFilter"
                                    @change="searchEligibleUsers"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 py-2.5 px-3 text-xs font-semibold text-slate-800 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white cursor-pointer"
                                >
                                    <option value="">Semua Kelas</option>
                                    <option
                                        v-for="cls in availableClasses"
                                        :key="typeof cls === 'object' ? cls.value : cls"
                                        :value="typeof cls === 'object' ? cls.value : cls"
                                    >
                                        {{ typeof cls === 'object' ? cls.label : cls }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- User List Box -->
                        <div class="max-h-60 overflow-y-auto divide-y divide-slate-100 rounded-xl border border-slate-200 dark:divide-slate-800 dark:border-slate-800">
                            <div
                                v-if="userCandidates.length === 0"
                                class="p-6 text-center text-xs text-slate-500 dark:text-slate-400"
                            >
                                {{ isLoadingUsers ? 'Sedang mencari data pengguna...' : 'Tidak ada pengguna ditemukan atau semua sudah terdaftar.' }}
                            </div>
                            <label
                                v-for="cand in userCandidates"
                                :key="cand.id"
                                class="flex items-center gap-3 p-3 hover:bg-slate-50 dark:hover:bg-slate-800/60 cursor-pointer transition-colors"
                            >
                                <input
                                    type="checkbox"
                                    :value="cand.id"
                                    v-model="selectedUserIds"
                                    class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                />
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-slate-900 truncate dark:text-white">
                                        {{ cand.name }}
                                    </p>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">
                                        {{ cand.identifier }} ({{ cand.email }})
                                    </p>
                                </div>
                            </label>
                        </div>

                        <!-- Selected Info -->
                        <div class="flex items-center justify-between text-xs text-slate-600 dark:text-slate-400">
                            <span>Terpilih: <strong>{{ selectedUserIds.length }}</strong> pengguna</span>
                            <button
                                v-if="selectedUserIds.length > 0"
                                type="button"
                                @click="selectedUserIds = []"
                                class="text-blue-600 hover:underline dark:text-blue-400 cursor-pointer"
                            >
                                Reset Pilihan
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 border-t border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950">
                        <button
                            type="button"
                            @click="closeSelectModal"
                            class="rounded-xl px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-200 dark:text-slate-300 dark:hover:bg-slate-800 cursor-pointer"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            :disabled="selectedUserIds.length === 0 || isSubmittingSelected"
                            @click="submitSelectedUsers"
                            class="flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-blue-700 disabled:opacity-50 cursor-pointer"
                        >
                            <RefreshCw v-if="isSubmittingSelected" class="h-3.5 w-3.5 animate-spin" />
                            <span>{{ isSubmittingSelected ? 'Menyimpan...' : 'Tambahkan Terpilih' }}</span>
                        </button>
                    </div>
                </div>
            </div>

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
    UserPlus,
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
        is_multi_stage?: boolean;
        current_stage?: number;
        total_stages?: number;
    };
    selectedStage?: number;
    voterAccesses: any;
    filters?: {
        search?: string;
        sort_by?: string;
        class?: string;
        stage?: number;
    };
    availableClasses?: string[];
    stats: {
        total_access: number;
        total_voted: number;
    };
}>();

const currentSelectedStage = ref<number>(
    props.selectedStage || props.filters?.stage || props.election.current_stage || 1
);

const isGenerating = ref(false);
const isDeletingAll = ref(false);
const showBatchConfirm = ref(false);
const showDeleteConfirm = ref(false);
const showDeleteAllConfirm = ref(false);
const selectedAccessToDelete = ref<any | null>(null);

// State untuk Modal Pilih Pemilih Tertentu
const showSelectModal = ref(false);
const userSearchQuery = ref('');
const modalClassFilter = ref('');
const userCandidates = ref<any[]>([]);
const selectedUserIds = ref<string[]>([]);
const isLoadingUsers = ref(false);
const isSubmittingSelected = ref(false);
let userSearchTimer: any = null;

const openSelectModal = () => {
    showSelectModal.value = true;
    userSearchQuery.value = '';
    modalClassFilter.value = '';
    selectedUserIds.value = [];
    searchEligibleUsers();
};

const closeSelectModal = () => {
    showSelectModal.value = false;
    userCandidates.value = [];
    selectedUserIds.value = [];
    modalClassFilter.value = '';
};

const searchEligibleUsers = () => {
    clearTimeout(userSearchTimer);
    userSearchTimer = setTimeout(async () => {
        isLoadingUsers.value = true;
        try {
            const params = new URLSearchParams({
                q: userSearchQuery.value,
                class: modalClassFilter.value,
            });
            const res = await fetch(
                `/admin/pemilihan/${props.election.id}/pemilih/search-users?${params.toString()}`
            );
            if (res.ok) {
                userCandidates.value = await res.json();
            }
        } catch (e) {
            console.error(e);
        } finally {
            isLoadingUsers.value = false;
        }
    }, 300);
};

const submitSelectedUsers = () => {
    if (selectedUserIds.value.length === 0) return;
    isSubmittingSelected.value = true;
    router.post(
        `/admin/pemilihan/${props.election.id}/pemilih-terpilih`,
        { user_ids: selectedUserIds.value },
        {
            onSuccess: () => {
                closeSelectModal();
            },
            onFinish: () => {
                isSubmittingSelected.value = false;
            },
        }
    );
};

const searchQuery = ref(props.filters?.search || '');
const sortBy = ref(props.filters?.sort_by || 'name');
const selectedClass = ref(props.filters?.class || '');
let searchTimer: any = null;

const changeStage = (stageNum: number) => {
    currentSelectedStage.value = stageNum;
    applyFilters();
};

const applyFilters = () => {
    router.get(
        `/admin/pemilihan/${props.election.id}/pemilih`,
        {
            search: searchQuery.value,
            sort_by: sortBy.value,
            class: selectedClass.value,
            stage: currentSelectedStage.value,
        },
        { preserveState: true, replace: true, preserveScroll: true }
    );
};

const handleSearch = () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        applyFilters();
    }, 350);
};

const handleSortChange = () => {
    applyFilters();
};

const handleClassFilterChange = () => {
    applyFilters();
};

const clearSearch = () => {
    searchQuery.value = '';
    applyFilters();
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

const confirmDeleteAll = () => {
    showDeleteAllConfirm.value = false;
    isDeletingAll.value = true;
    router.delete(`/admin/pemilihan/${props.election.id}/pemilih-semua`, {
        onFinish: () => {
            isDeletingAll.value = false;
        },
    });
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

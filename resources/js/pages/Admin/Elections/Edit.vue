<template>
    <AdminLayout>
        <template #header>Edit Pemilihan</template>

        <div class="mx-auto max-w-3xl space-y-6">
            <!-- Back Bar -->
            <div class="flex items-center justify-between">
                <Link
                    href="/admin/pemilihan"
                    class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 transition-colors hover:text-blue-600 dark:text-slate-300 dark:hover:text-blue-400"
                >
                    <ArrowLeft class="h-4 w-4" />
                    <span>Kembali ke Daftar Pemilihan</span>
                </Link>
            </div>

            <!-- Main Form Card -->
            <div
                class="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800"
            >
                <div>
                    <h2
                        class="text-lg font-extrabold text-slate-900 dark:text-white"
                    >
                        Form Edit Acara Pemilihan
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Perbarui detail {{ election.title }}.
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-5 text-xs">
                    <!-- Judul Pemilihan -->
                    <div class="space-y-1.5">
                        <label
                            class="block font-bold text-slate-700 dark:text-slate-300"
                        >
                            Judul Pemilihan <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="form.title"
                            type="text"
                            required
                            class="w-full rounded-xl border border-slate-300 bg-slate-50 p-3 text-xs text-slate-900 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                        />
                        <span
                            v-if="form.errors.title"
                            class="text-[11px] font-medium text-rose-500"
                            >{{ form.errors.title }}</span
                        >
                    </div>

                    <!-- Kategori & Target Pemilih Grid -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <label
                                class="block font-bold text-slate-700 dark:text-slate-300"
                            >
                                Kategori Pemilihan
                                <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="form.type"
                                required
                                class="w-full rounded-xl border border-slate-300 bg-slate-50 p-3 text-xs text-slate-900 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                            >
                                <option value="osis">
                                    Pemilihan Ketua OSIS
                                </option>
                                <option value="vice_principal">
                                    Pemilihan Wakil Kepala Sekolah
                                </option>
                                <option value="class_president">
                                    Pemilihan Ketua Kelas
                                </option>
                                <option value="other">Lainnya / Umum</option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label
                                class="block font-bold text-slate-700 dark:text-slate-300"
                            >
                                Target Pemilih
                                <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="form.target_voter"
                                required
                                class="w-full rounded-xl border border-slate-300 bg-slate-50 p-3 text-xs text-slate-900 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                            >
                                <option value="all">
                                    Semua Pengguna (Siswa & Guru)
                                </option>
                                <option value="student">Siswa Saja</option>
                                <option value="teacher">
                                    Guru / Staf Saja
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Filter Kelas (jika Pemilihan Ketua Kelas) -->
                    <div
                        v-if="form.type === 'class_president'"
                        class="space-y-1.5 rounded-xl border border-amber-200/60 bg-amber-50/50 p-4 dark:border-amber-900/40 dark:bg-amber-950/20"
                    >
                        <label
                            class="block font-bold text-amber-800 dark:text-amber-300"
                            >Pilih Kelas Target</label
                        >
                        <select
                            v-model="form.class_id"
                            class="w-full rounded-xl border border-amber-300 bg-white p-3 text-xs text-slate-900 dark:border-amber-800 dark:bg-slate-900 dark:text-white"
                        >
                            <option value="">-- Pilih Kelas --</option>
                            <option
                                v-for="c in classes"
                                :key="c.id"
                                :value="c.id"
                            >
                                {{ c.name }} ({{ c.academic_year }})
                            </option>
                        </select>
                    </div>

                    <!-- Tahun Ajaran (Opsional) -->
                    <div class="space-y-1.5">
                        <label
                            class="block font-bold text-slate-700 dark:text-slate-300"
                            >Tahun Ajaran</label
                        >
                        <select
                            v-model="form.academic_year"
                            class="w-full rounded-xl border border-slate-300 bg-slate-50 p-3 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                        >
                            <option value="">
                                -- Bebas / Semua Tahun Ajaran --
                            </option>
                            <option
                                v-for="year in academicYears"
                                :key="year"
                                :value="year"
                            >
                                Tahun Ajaran {{ year }}
                            </option>
                        </select>
                    </div>

                    <!-- Tanggal Mula & Selesai -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <label
                                class="block font-bold text-slate-700 dark:text-slate-300"
                            >
                                Waktu Mulai <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.start_at"
                                type="datetime-local"
                                required
                                class="w-full rounded-xl border border-slate-300 bg-slate-50 p-3 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                            />
                            <span
                                v-if="form.errors.start_at"
                                class="text-[11px] font-medium text-rose-500"
                                >{{ form.errors.start_at }}</span
                            >
                        </div>

                        <div class="space-y-1.5">
                            <label
                                class="block font-bold text-slate-700 dark:text-slate-300"
                            >
                                Waktu Selesai
                                <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.end_at"
                                type="datetime-local"
                                required
                                class="w-full rounded-xl border border-slate-300 bg-slate-50 p-3 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                            />
                            <span
                                v-if="form.errors.end_at"
                                class="text-[11px] font-medium text-rose-500"
                                >{{ form.errors.end_at }}</span
                            >
                        </div>
                    </div>

                    <!-- Deskripsi -->
                    <div class="space-y-1.5">
                        <label
                            class="block font-bold text-slate-700 dark:text-slate-300"
                            >Deskripsi Singkat / Catatan</label
                        >
                        <textarea
                            v-model="form.description"
                            rows="3"
                            placeholder="Tulis informasi atau catatan tambahan..."
                            class="w-full rounded-xl border border-slate-300 bg-slate-50 p-3 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                        ></textarea>
                    </div>

                    <!-- Pemilihan Bertahap (Multi-Stage) Option -->
                    <div
                        class="space-y-3 rounded-xl border border-blue-200/70 bg-blue-50/60 p-4 dark:border-blue-900/60 dark:bg-blue-950/40"
                    >
                        <label
                            class="flex cursor-pointer items-center gap-3 select-none"
                        >
                            <input
                                type="checkbox"
                                v-model="form.is_multi_stage"
                                class="h-4 w-4 cursor-pointer rounded text-blue-600 focus:ring-blue-500"
                            />
                            <div>
                                <span
                                    class="block text-xs font-bold text-slate-900 dark:text-white"
                                    >Aktifkan Pemilihan Bertahap (Multi-Round /
                                    Eliminasi Saring Paslon)</span
                                >
                                <span
                                    class="block text-[11px] text-slate-500 dark:text-slate-400"
                                    >Contoh: Tahap 1 (Penyisihan 20 paslon
                                    &rarr; saring Top 8) &rarr; Tahap 2 (Final
                                    Top 4).</span
                                >
                            </div>
                        </label>

                        <div
                            v-if="form.is_multi_stage"
                            class="flex items-center gap-4 border-t border-blue-200/50 pt-2 dark:border-blue-900/40"
                        >
                            <label
                                class="text-xs font-bold text-slate-700 dark:text-slate-300"
                                >Rencana Jumlah Tahap:</label
                            >
                            <input
                                v-model.number="form.total_stages"
                                type="number"
                                min="2"
                                max="5"
                                class="w-24 rounded-lg border border-slate-300 bg-white p-2 text-xs font-bold text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                            />
                            <span class="text-[11px] text-slate-400"
                                >Tahap aktif saat ini:
                                <strong
                                    >Tahap {{ election.current_stage }}</strong
                                ></span
                            >
                        </div>
                    </div>

                    <!-- Jumlah Suara per Pemilih (Multi-Choice) Option -->
                    <div
                        class="space-y-2 rounded-xl border border-purple-200/70 bg-purple-50/60 p-4 dark:border-purple-900/60 dark:bg-purple-950/40"
                    >
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <span
                                    class="block text-xs font-bold text-slate-900 dark:text-white"
                                    >Jumlah Maksimal Suara Diberikan per Pemilih (Pilihan Ganda / Multi-Choice)</span
                                >
                                <span
                                    class="block text-[11px] text-slate-500 dark:text-slate-400"
                                    >Isi <strong>1</strong> untuk memilih 1 paslon saja, atau ketik <strong>4</strong> (atau angka lainnya) jika 1 pemilih boleh mencoblos hingga N kandidat sekaligus.</span
                                >
                            </div>
                            <input
                                v-model.number="form.max_votes_per_voter"
                                type="number"
                                min="1"
                                max="20"
                                class="w-24 rounded-lg border border-purple-300 bg-white p-2 text-xs font-extrabold text-slate-900 outline-none focus:ring-2 focus:ring-purple-500 dark:border-purple-800 dark:bg-slate-900 dark:text-white"
                            />
                        </div>
                    </div>

                    <!-- Submit Bar -->
                    <div
                        class="flex justify-end gap-3 border-t border-slate-100 pt-4 dark:border-slate-700"
                    >
                        <Link
                            href="/admin/pemilihan"
                            class="rounded-xl bg-slate-100 px-5 py-2.5 font-bold text-slate-800 transition-colors hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="cursor-pointer rounded-xl bg-blue-600 px-6 py-2.5 font-bold text-white shadow-sm transition-colors hover:bg-blue-700 disabled:opacity-50"
                        >
                            {{
                                form.processing
                                    ? "Menyimpan..."
                                    : "Simpan Perubahan"
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { useForm, Link } from "@inertiajs/vue3";
import { ArrowLeft } from "@lucide/vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const props = defineProps<{
    election: {
        id: number;
        title: string;
        description: string | null;
        type: string;
        target_voter: string;
        academic_year: string | null;
        class_id: string | null;
        start_at: string;
        end_at: string;
        is_multi_stage?: boolean;
        max_votes_per_voter?: number;
        current_stage?: number;
        total_stages?: number;
    };
    academicYears: string[];
    classes: any[];
}>();

const form = useForm({
    title: props.election.title,
    description: props.election.description || "",
    type: props.election.type,
    target_voter: props.election.target_voter,
    academic_year: props.election.academic_year || "",
    class_id: props.election.class_id || "",
    start_at: props.election.start_at,
    end_at: props.election.end_at,
    is_multi_stage: props.election.is_multi_stage || false,
    max_votes_per_voter: props.election.max_votes_per_voter || 1,
    total_stages: props.election.total_stages || 2,
});

const submit = () => {
    form.put(`/admin/pemilihan/${props.election.id}`);
};
</script>

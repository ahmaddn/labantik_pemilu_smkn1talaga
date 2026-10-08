<template>
    <AdminLayout>
        <template #header>Buat Pemilihan Baru</template>

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
                        Form Tambah Acara Pemilihan
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Isi detail jadwal dan target pemilih untuk kegiatan
                        e-voting baru.
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
                            placeholder="Contoh: Pemilihan Ketua OSIS 2026/2027"
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
                            placeholder="Tulis informasi atau catatan tambahan mengenai kegiatan ini..."
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
                                >Hasil suara dihitung per tahap & disaring
                                berdasarkan peringkat vote.</span
                            >
                        </div>
                    </div>

                    <!-- Jumlah Suara per Pemilih (Multi-Choice) Option -->
                    <div
                        class="space-y-3 rounded-xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-700 dark:bg-slate-800/60"
                    >
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div>
                                <span
                                    class="block text-xs font-bold text-slate-900 dark:text-white"
                                    >Jumlah Pilihan Suara per Pemilih (Multi-Choice)</span
                                >
                                <span
                                    class="block text-[11px] text-slate-500 dark:text-slate-400"
                                    >Tentukan kuota berapa paslon yang dapat dicoblos (misal: 1 paslon atau 5 paslon).</span
                                >
                            </div>
                            <div v-if="!form.is_multi_stage" class="flex items-center gap-2">
                                <input
                                    v-model.number="form.max_votes_per_voter"
                                    type="number"
                                    min="1"
                                    max="20"
                                    class="w-24 rounded-lg border border-slate-300 bg-white p-2 text-xs font-extrabold text-slate-900 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                />
                                <span class="text-xs font-bold text-slate-600 dark:text-slate-400">Suara</span>
                            </div>
                        </div>

                        <!-- Opsi Aturan Pemilihan: Wajib Pas vs Maksimal (Bebas) -->
                        <div
                            v-if="!form.is_multi_stage && form.max_votes_per_voter > 1"
                            class="rounded-lg border border-blue-200/60 bg-blue-50/50 p-3 dark:border-blue-900/40 dark:bg-blue-950/30 space-y-2"
                        >
                            <span class="block text-xs font-bold text-slate-800 dark:text-slate-200">
                                Aturan Pemilihan {{ form.max_votes_per_voter }} Suara:
                            </span>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                <label
                                    :class="[
                                        'flex cursor-pointer items-start gap-2.5 rounded-lg border p-2.5 transition-all',
                                        form.vote_selection_mode === 'max'
                                            ? 'border-blue-500 bg-white shadow-xs dark:border-blue-500 dark:bg-slate-900'
                                            : 'border-slate-200 bg-slate-50/60 dark:border-slate-700 dark:bg-slate-800/40 text-slate-600 dark:text-slate-400',
                                    ]"
                                >
                                    <input
                                        type="radio"
                                        value="max"
                                        v-model="form.vote_selection_mode"
                                        class="mt-0.5 text-blue-600 focus:ring-blue-500"
                                    />
                                    <div>
                                        <strong class="block text-slate-900 dark:text-white">Maksimal {{ form.max_votes_per_voter }} (Fleksibel)</strong>
                                        <span class="text-[11px] text-slate-500 dark:text-slate-400">Pemilih bebas memilih antara 1 sampai {{ form.max_votes_per_voter }} kandidat.</span>
                                    </div>
                                </label>

                                <label
                                    :class="[
                                        'flex cursor-pointer items-start gap-2.5 rounded-lg border p-2.5 transition-all',
                                        form.vote_selection_mode === 'exact'
                                            ? 'border-blue-500 bg-white shadow-xs dark:border-blue-500 dark:bg-slate-900'
                                            : 'border-slate-200 bg-slate-50/60 dark:border-slate-700 dark:bg-slate-800/40 text-slate-600 dark:text-slate-400',
                                    ]"
                                >
                                    <input
                                        type="radio"
                                        value="exact"
                                        v-model="form.vote_selection_mode"
                                        class="mt-0.5 text-blue-600 focus:ring-blue-500"
                                    />
                                    <div>
                                        <strong class="block text-slate-900 dark:text-white">Harus Tepat {{ form.max_votes_per_voter }} (Wajib Pas)</strong>
                                        <span class="text-[11px] text-slate-500 dark:text-slate-400">Pemilih <strong>wajib mencoblos pas {{ form.max_votes_per_voter }}</strong> kandidat, tidak boleh kurang.</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Pengaturan Kuota Suara per Tahap jika Multi-Stage Aktif -->
                        <div
                            v-if="form.is_multi_stage"
                            class="mt-2 space-y-2 rounded-lg border border-blue-100 bg-white p-3 dark:border-blue-900/40 dark:bg-slate-900/80"
                        >
                            <span class="block text-xs font-bold text-blue-600 dark:text-blue-400">
                                Atur Kuota Pilihan Suara per Tahap:
                            </span>
                            <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2 md:grid-cols-3">
                                <div
                                    v-for="stageNum in Math.min(Math.max(form.total_stages || 2, 2), 5)"
                                    :key="stageNum"
                                    class="flex flex-col gap-2 rounded-lg border border-slate-200 bg-slate-50/80 p-2.5 dark:border-slate-700 dark:bg-slate-800/80"
                                >
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex flex-col">
                                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200">
                                                Tahap {{ stageNum }}
                                            </span>
                                            <span class="text-[10px] text-slate-400">
                                                {{ stageNum === 1 ? '(Putaran Awal)' : stageNum === form.total_stages ? '(Putaran Final)' : '(Penyaringan)' }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <input
                                                type="number"
                                                min="1"
                                                max="20"
                                                :value="form.stage_schedules[stageNum]?.max_votes ?? (stageNum === 1 ? form.max_votes_per_voter : 1)"
                                                @input="setStageMaxVotes(stageNum, Number(($event.target as HTMLInputElement).value))"
                                                class="w-14 rounded border border-slate-300 bg-white p-1 text-center text-xs font-black text-slate-900 focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900 dark:text-white"
                                            />
                                            <span class="text-[11px] font-semibold text-slate-500">Suara</span>
                                        </div>
                                    </div>
                                    <div v-if="(form.stage_schedules[stageNum]?.max_votes ?? (stageNum === 1 ? form.max_votes_per_voter : 1)) > 1" class="pt-1 border-t border-slate-200/60 dark:border-slate-700">
                                        <select
                                            :value="form.stage_schedules[stageNum]?.selection_mode ?? (stageNum === 1 ? form.vote_selection_mode : 'max')"
                                            @change="setStageSelectionMode(stageNum, ($event.target as HTMLSelectElement).value)"
                                            class="w-full rounded border border-slate-300 bg-white p-1 text-[11px] font-bold text-slate-800 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200"
                                        >
                                            <option value="max">Maksimal (Fleksibel)</option>
                                            <option value="exact">Harus Tepat (Wajib Pas)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                Contoh: Tahap 1 dapat diset <strong>wajib memilih tepat 5 kandidat</strong>, dan Tahap 2 (final) hanya <strong>1 kandidat</strong>.
                            </p>
                        </div>
                    </div>

                    <!-- Mode Simulasi (Gladi Pemilihan dengan Nama Kandidat Disamarkan) -->
                    <div
                        class="space-y-3 rounded-xl border border-blue-200/70 bg-blue-50/50 p-4 dark:border-blue-900/50 dark:bg-blue-950/30"
                    >
                        <label
                            class="flex cursor-pointer items-start gap-3 select-none"
                        >
                            <input
                                type="checkbox"
                                v-model="form.is_simulation"
                                class="mt-0.5 h-4 w-4 cursor-pointer rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            />
                            <div>
                                <span
                                    class="block text-xs font-bold text-slate-900 dark:text-white"
                                    >Buka Sebagai Sesi Simulasi / Gladi Pemilihan</span
                                >
                                <span
                                    class="block text-[11px] text-slate-600 dark:text-slate-400"
                                    >Saat mode simulasi aktif, <strong>nama kandidat otomatis disamarkan</strong> (Kandidat A, Kandidat B, dst.) bagi pemilih. Suara pemilih tersimpan terpisah di sandbox simulasi tanpa merusak data pemilu asli.</span
                                >
                            </div>
                        </label>

                        <!-- Jadwal Waktu Khusus Simulasi (Opsional) -->
                        <div
                            v-if="form.is_simulation"
                            class="space-y-3 border-t border-blue-200/60 pt-3 dark:border-blue-900/50"
                        >
                            <span class="block text-[11px] font-bold text-slate-800 dark:text-slate-200">
                                Jadwal Waktu Sesi Simulasi (Opsional - default mengikuti waktu pemilihan jika dikosongkan):
                            </span>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div class="space-y-1">
                                    <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400">
                                        Mulai Simulasi:
                                    </label>
                                    <input
                                        v-model="form.simulation_start_at"
                                        type="datetime-local"
                                        class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                    />
                                </div>
                                <div class="space-y-1">
                                    <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400">
                                        Selesai Simulasi:
                                    </label>
                                    <input
                                        v-model="form.simulation_end_at"
                                        type="datetime-local"
                                        class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                    />
                                </div>
                            </div>
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
                                    : "Simpan Pemilihan"
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

defineProps<{
    academicYears: string[];
    classes: any[];
}>();

const form = useForm({
    title: "",
    description: "",
    type: "osis",
    target_voter: "all",
    academic_year: "",
    class_id: "",
    start_at: "",
    end_at: "",
    is_multi_stage: false,
    is_simulation: false,
    simulation_start_at: "",
    simulation_end_at: "",
    max_votes_per_voter: 1,
    vote_selection_mode: "max",
    total_stages: 2,
    stage_schedules: {} as Record<string | number, { max_votes: number; selection_mode?: string }>,
});

const setStageMaxVotes = (stage: number, value: number) => {
    const val = Math.max(1, value || 1);
    if (!form.stage_schedules) {
        form.stage_schedules = {};
    }
    form.stage_schedules[stage] = {
        ...(form.stage_schedules[stage] || {}),
        max_votes: val,
    };
    if (stage === 1) {
        form.max_votes_per_voter = val;
    }
};

const setStageSelectionMode = (stage: number, mode: string) => {
    if (!form.stage_schedules) {
        form.stage_schedules = {};
    }
    form.stage_schedules[stage] = {
        ...(form.stage_schedules[stage] || {}),
        selection_mode: mode,
    };
    if (stage === 1) {
        form.vote_selection_mode = mode;
    }
};

const submit = () => {
    form.post("/admin/pemilihan");
};
</script>

<template>
    <AdminLayout>
        <template #header>Pengaturan Aplikasi</template>

        <div class="mx-auto max-w-4xl space-y-6">
            <!-- Top Info Banner -->
            <div
                class="flex flex-col items-start justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:flex-row sm:items-center dark:border-slate-700 dark:bg-slate-800"
            >
                <div class="space-y-1">
                    <h2
                        class="flex items-center gap-2 text-lg font-extrabold text-slate-900 dark:text-white"
                    >
                        <Sliders
                            class="h-5 w-5 text-blue-600 dark:text-blue-400"
                        />
                        <span>Konfigurasi & Identitas Pemilu</span>
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Atur nama aplikasi, logo instansi, deskripsi umum, dan
                        tahun ajaran aktif yang diterapkan pada sistem.
                    </p>
                </div>
            </div>

            <!-- Settings Form Card -->
            <div
                class="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 dark:border-slate-700 dark:bg-slate-800"
            >
                <form @submit.prevent="submit" class="space-y-6 text-xs">
                    <!-- Section 1: Logo & Favicon Media -->
                    <div
                        class="space-y-6 border-b border-slate-100 pb-6 dark:border-slate-700"
                    >
                        <h3
                            class="text-sm font-extrabold tracking-wider text-blue-600 text-slate-900 uppercase dark:text-blue-400 dark:text-white"
                        >
                            1. Logo & Favicon Instansi
                        </h3>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Item 1: Upload Logo -->
                            <div
                                class="flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-slate-50/70 p-4 dark:border-slate-700/60 dark:bg-slate-900/60"
                            >
                                <div
                                    class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-slate-300 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800"
                                >
                                    <img
                                        v-if="logoPreview || form.current_logo"
                                        :src="logoPreview || form.current_logo"
                                        alt="Logo Aplikasi"
                                        class="h-full w-full object-contain p-2"
                                    />
                                    <div
                                        v-else
                                        class="p-2 text-center text-slate-400"
                                    >
                                        <Image
                                            class="mx-auto mb-1 h-6 w-6 opacity-50"
                                        />
                                        <span
                                            class="block text-[9px] font-semibold"
                                            >Logo</span
                                        >
                                    </div>
                                </div>

                                <div class="min-w-0 flex-1 space-y-1.5">
                                    <label
                                        class="block text-xs font-bold text-slate-700 dark:text-slate-300"
                                    >
                                        Logo Utama (PNG/JPG, max 2MB)
                                    </label>
                                    <input
                                        type="file"
                                        accept="image/*"
                                        @change="handleLogoChange"
                                        class="w-full cursor-pointer rounded-xl border border-slate-300 bg-white p-2 text-[11px] text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    />
                                    <p class="text-[10px] text-slate-400">
                                        Tampil pada header navbar & surat suara.
                                    </p>
                                    <span
                                        v-if="form.errors.app_logo"
                                        class="block text-[10px] font-medium text-rose-500"
                                        >{{ form.errors.app_logo }}</span
                                    >
                                </div>
                            </div>

                            <!-- Item 2: Upload Favicon -->
                            <div
                                class="flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-slate-50/70 p-4 dark:border-slate-700/60 dark:bg-slate-900/60"
                            >
                                <div
                                    class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-slate-300 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800"
                                >
                                    <img
                                        v-if="
                                            faviconPreview ||
                                            form.current_favicon
                                        "
                                        :src="
                                            faviconPreview ||
                                            form.current_favicon
                                        "
                                        alt="Favicon Browser"
                                        class="h-10 w-10 object-contain"
                                    />
                                    <div
                                        v-else
                                        class="p-2 text-center text-slate-400"
                                    >
                                        <Globe
                                            class="mx-auto mb-1 h-6 w-6 opacity-50"
                                        />
                                        <span
                                            class="block text-[9px] font-semibold"
                                            >Favicon</span
                                        >
                                    </div>
                                </div>

                                <div class="min-w-0 flex-1 space-y-1.5">
                                    <label
                                        class="block text-xs font-bold text-slate-700 dark:text-slate-300"
                                    >
                                        Favicon Browser (ICO/PNG/SVG, max 1MB)
                                    </label>
                                    <input
                                        type="file"
                                        accept=".ico,.png,.jpg,.jpeg,.svg"
                                        @change="handleFaviconChange"
                                        class="w-full cursor-pointer rounded-xl border border-slate-300 bg-white p-2 text-[11px] text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    />
                                    <p class="text-[10px] text-slate-400">
                                        Tampil pada tab browser & bookmark.
                                    </p>
                                    <span
                                        v-if="form.errors.app_favicon"
                                        class="block text-[10px] font-medium text-rose-500"
                                        >{{ form.errors.app_favicon }}</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Identitas & Deskripsi -->
                    <div
                        class="space-y-4 border-b border-slate-100 pb-6 dark:border-slate-700"
                    >
                        <h3
                            class="text-sm font-extrabold tracking-wider text-blue-600 text-slate-900 uppercase dark:text-blue-400 dark:text-white"
                        >
                            2. Identitas Aplikasi
                        </h3>

                        <div class="space-y-4">
                            <div class="space-y-1.5">
                                <label
                                    class="block font-bold text-slate-700 dark:text-slate-300"
                                >
                                    Nama Aplikasi / Judul Utama
                                    <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    v-model="form.app_name"
                                    type="text"
                                    required
                                    placeholder="Contoh: E-Voting SMKN 1 Talaga"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 p-3 text-xs text-slate-900 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                />
                                <span
                                    v-if="form.errors.app_name"
                                    class="text-[11px] font-medium text-rose-500"
                                    >{{ form.errors.app_name }}</span
                                >
                            </div>

                            <div class="space-y-1.5">
                                <label
                                    class="block font-bold text-slate-700 dark:text-slate-300"
                                >
                                    Deskripsi Singkat Aplikasi
                                </label>
                                <textarea
                                    v-model="form.app_description"
                                    rows="3"
                                    placeholder="Contoh: Sistem Pemilihan Umum E-Voting Terintegrasi SMKN 1 Talaga"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 p-3 text-xs text-slate-900 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                ></textarea>
                                <span
                                    v-if="form.errors.app_description"
                                    class="text-[11px] font-medium text-rose-500"
                                    >{{ form.errors.app_description }}</span
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Tahun Ajaran Aktif -->
                    <div class="space-y-4">
                        <h3
                            class="text-sm font-extrabold tracking-wider text-blue-600 text-slate-900 uppercase dark:text-blue-400 dark:text-white"
                        >
                            3. Tahun Ajaran Sistem Diterapkan
                        </h3>

                        <div class="max-w-md space-y-1.5">
                            <label
                                class="block font-bold text-slate-700 dark:text-slate-300"
                            >
                                Tahun Ajaran Aktif
                                <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="form.active_academic_year"
                                required
                                class="w-full rounded-xl border border-slate-300 bg-slate-50 p-3 text-xs font-bold text-slate-900 outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                            >
                                <option
                                    v-for="year in academicYears"
                                    :key="year"
                                    :value="year"
                                >
                                    Tahun Ajaran {{ year }}
                                </option>
                            </select>
                            <p class="text-[11px] text-slate-400">
                                Tahun ajaran ini diterapkan sebagai referensi
                                utama data siswa dan hak pilih default dalam
                                sistem.
                            </p>
                            <span
                                v-if="form.errors.active_academic_year"
                                class="text-[11px] font-medium text-rose-500"
                                >{{ form.errors.active_academic_year }}</span
                            >
                        </div>
                    </div>

                    <!-- Submit Action Button -->
                    <div
                        class="flex justify-end border-t border-slate-100 pt-6 dark:border-slate-700"
                    >
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex cursor-pointer items-center gap-2 rounded-xl bg-blue-600 px-6 py-3 text-xs font-bold text-white shadow-sm transition-colors hover:bg-blue-700 disabled:opacity-50"
                        >
                            <Save class="h-4 w-4" />
                            <span>{{
                                form.processing
                                    ? 'Menyimpan Pengaturan...'
                                    : 'Simpan Pengaturan Aplikasi'
                            }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Sliders, Image, Save, Globe } from '@lucide/vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps<{
    settings: {
        app_name: string;
        app_description: string;
        active_academic_year: string;
        app_logo: string | null;
        app_favicon: string | null;
    };
    academicYears: string[];
}>();

const logoPreview = ref<string | null>(null);
const faviconPreview = ref<string | null>(null);

const form = useForm({
    app_name: props.settings.app_name || '',
    app_description: props.settings.app_description || '',
    active_academic_year: props.settings.active_academic_year || '2025/2026',
    app_logo: null as File | null,
    app_favicon: null as File | null,
    current_logo: props.settings.app_logo || null,
    current_favicon: props.settings.app_favicon || null,
});

const handleLogoChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        form.app_logo = file;
        logoPreview.value = URL.createObjectURL(file);
    }
};

const handleFaviconChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        form.app_favicon = file;
        faviconPreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.post('/admin/pengaturan', {
        preserveScroll: true,
    });
};
</script>

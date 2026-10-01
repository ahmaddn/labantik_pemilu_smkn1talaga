<template>
    <Head :title="`${title} - E-Voting SMKN 1 Talaga`" />

    <div
        class="flex min-h-screen flex-col items-center justify-center bg-slate-50 px-4 py-12 text-slate-800 transition-colors sm:px-6 lg:px-8 dark:bg-slate-950 dark:text-slate-100"
    >
        <div class="w-full max-w-md text-center">
            <!-- Icon / Badge -->
            <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-3xl bg-blue-50 text-blue-600 shadow-inner dark:bg-slate-900/80 dark:text-blue-400">
                <component :is="iconComponent" class="h-12 w-12 stroke-[1.75]" />
            </div>

            <!-- Status Code -->
            <div class="mb-2 inline-flex items-center gap-1.5 rounded-full border border-blue-200/60 bg-blue-100/60 px-3.5 py-1 text-xs font-black tracking-widest text-blue-700 uppercase dark:border-blue-900/50 dark:bg-blue-950/50 dark:text-blue-400">
                <span>Error {{ status }}</span>
            </div>

            <!-- Title -->
            <h1 class="text-2xl font-black tracking-tight text-slate-900 sm:text-3xl dark:text-white">
                {{ title }}
            </h1>

            <!-- Description -->
            <p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                {{ description }}
            </p>

            <!-- Action Buttons -->
            <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <button
                    type="button"
                    @click="goBack"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-5 py-3 text-xs font-bold text-slate-700 shadow-sm transition-all hover:bg-slate-50 sm:w-auto dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                >
                    <ArrowLeft class="h-4 w-4" />
                    <span>Kembali</span>
                </button>

                <Link
                    href="/"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-3 text-xs font-bold text-white shadow-md transition-all hover:bg-blue-700 hover:shadow-lg sm:w-auto"
                >
                    <Home class="h-4 w-4" />
                    <span>Halaman Utama</span>
                </Link>
            </div>

            <!-- Footer Note -->
            <div class="mt-12 text-xs font-medium text-slate-400 dark:text-slate-600">
                E-Voting SMKN 1 Talaga &bull; Sistem Pemilihan Digital Terpadu
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    ShieldAlert,
    FileQuestion,
    AlertTriangle,
    ServerCrash,
    Clock,
    Lock,
    ArrowLeft,
    Home,
} from '@lucide/vue';

const props = withDefaults(
    defineProps<{
        status: number;
        message?: string;
    }>(),
    {
        status: 404,
    }
);

const errorData = computed(() => {
    switch (props.status) {
        case 401:
            return {
                title: 'Tidak Terautentikasi',
                description:
                    props.message ||
                    'Sesi Anda belum aktif atau telah berakhir. Silakan masuk terlebih dahulu untuk mengakses halaman ini.',
                icon: Lock,
            };
        case 403:
            return {
                title: 'Akses Ditolak',
                description:
                    props.message ||
                    'Maaf, Anda tidak memiliki izin atau hak akses untuk membuka halaman atau aksi ini.',
                icon: ShieldAlert,
            };
        case 404:
            return {
                title: 'Halaman Tidak Ditemukan',
                description:
                    props.message ||
                    'Halaman atau tautan yang Anda tuju tidak tersedia, telah dipindahkan, atau sudah dihapus.',
                icon: FileQuestion,
            };
        case 419:
            return {
                title: 'Halaman Kedaluwarsa',
                description:
                    props.message ||
                    'Token keamanan sesi halaman telah kedaluwarsa karena tidak ada aktivitas. Silakan muat ulang halaman.',
                icon: Clock,
            };
        case 422:
            return {
                title: 'Entitas Tidak Dapat Diproses',
                description:
                    props.message ||
                    'Data atau formulir yang dikirimkan tidak valid atau tidak memenuhi kriteria verifikasi sistem.',
                icon: AlertTriangle,
            };
        case 429:
            return {
                title: 'Terlalu Banyak Permintaan',
                description:
                    props.message ||
                    'Anda melakukan terlalu banyak permintaan dalam waktu singkat. Mohon tunggu beberapa saat sebelum mencoba lagi.',
                icon: Clock,
            };
        case 500:
            return {
                title: 'Terjadi Kesalahan Server',
                description:
                    props.message ||
                    'Terjadi kendala teknis internal pada sistem. Tim teknis sedang berupaya memperbaikinya.',
                icon: ServerCrash,
            };
        case 503:
            return {
                title: 'Layanan Dalam Pemeliharaan',
                description:
                    props.message ||
                    'Sistem e-voting saat ini sedang dalam proses pemeliharaan berkala. Silakan kembali beberapa saat lagi.',
                icon: ServerCrash,
            };
        default:
            return {
                title: `Kesalahan (${props.status})`,
                description:
                    props.message ||
                    'Terjadi kendala yang tidak terduga saat memproses permintaan Anda.',
                icon: AlertTriangle,
            };
    }
});

const title = computed(() => errorData.value.title);
const description = computed(() => errorData.value.description);
const iconComponent = computed(() => errorData.value.icon);

const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        window.location.href = '/';
    }
};
</script>

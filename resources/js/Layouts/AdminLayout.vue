<template>
    <div
        class="flex min-h-screen flex-col bg-slate-50 text-slate-900 transition-colors duration-200 md:flex-row dark:bg-slate-950 dark:text-slate-100"
    >
        <!-- Mobile Sidebar Backdrop -->
        <Transition name="backdrop-fade">
            <div
                v-if="isSidebarOpen"
                @click="isSidebarOpen = false"
                class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-sm md:hidden"
            ></div>
        </Transition>

        <!-- Sidebar Navigation -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 flex h-screen w-64 flex-col border-r border-slate-200 bg-white text-slate-800 transition-all duration-300 ease-in-out md:sticky md:top-0 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200',
                isSidebarOpen
                    ? 'translate-x-0 shadow-2xl'
                    : '-translate-x-full md:translate-x-0',
            ]"
        >
            <!-- Sidebar Brand Header -->
            <div
                class="flex items-center justify-between border-b border-slate-200 p-5 dark:border-slate-800"
            >
                <Link href="/admin" class="flex min-w-0 items-center gap-3">
                    <div
                        v-if="appSettings.app_logo"
                        class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-white p-1 shadow-sm dark:border-slate-700"
                    >
                        <img
                            :src="appSettings.app_logo"
                            alt="Logo"
                            class="h-full w-full object-contain"
                        />
                    </div>
                    <div
                        v-else
                        class="shrink-0 rounded-xl bg-blue-600 p-2 text-white shadow-sm"
                    >
                        <Shield class="h-5 w-5" />
                    </div>
                    <div class="truncate">
                        <h2
                            class="truncate text-sm leading-tight font-black tracking-tight text-slate-900 dark:text-white"
                        >
                            {{ appSettings.app_name || 'PANITIA PEMILU' }}
                        </h2>
                        <p
                            class="mt-0.5 truncate text-[10px] font-bold tracking-wider text-blue-600 uppercase dark:text-blue-400"
                        >
                            {{
                                appSettings.active_academic_year
                                    ? `TA ${appSettings.active_academic_year}`
                                    : 'SMKN 1 TALAGA'
                            }}
                        </p>
                    </div>
                </Link>
                <button
                    @click="isSidebarOpen = false"
                    class="shrink-0 rounded-xl p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-900 md:hidden dark:hover:bg-slate-800 dark:hover:text-white"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 space-y-6 overflow-y-auto p-4">
                <!-- Menu Group 1: Administrasi -->
                <div class="space-y-1.5">
                    <span
                        class="px-3 text-[10px] font-bold tracking-wider text-slate-400 uppercase dark:text-slate-500"
                    >
                        ADMINISTRASI
                    </span>

                    <Link
                        href="/admin"
                        :class="[
                            'flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-xs font-bold transition-colors',
                            isDashboardActive
                                ? 'bg-blue-600 text-white shadow-sm'
                                : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white',
                        ]"
                    >
                        <BarChart3 class="h-4 w-4 shrink-0" />
                        <span>Dashboard</span>
                    </Link>

                    <Link
                        href="/admin/pemilihan"
                        :class="[
                            'flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-xs font-bold transition-colors',
                            isPemilihanActive
                                ? 'bg-blue-600 text-white shadow-sm'
                                : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white',
                        ]"
                    >
                        <Vote class="h-4 w-4 shrink-0" />
                        <span>Kelola Pemilihan</span>
                    </Link>

                    <Link
                        href="/admin/pengaturan"
                        :class="[
                            'flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-xs font-bold transition-colors',
                            isPengaturanActive
                                ? 'bg-blue-600 text-white shadow-sm'
                                : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white',
                        ]"
                    >
                        <Sliders class="h-4 w-4 shrink-0" />
                        <span>Pengaturan Aplikasi</span>
                    </Link>

                    <Link
                        href="/admin/panduan"
                        :class="[
                            'flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-xs font-bold transition-colors',
                            isPanduanActive
                                ? 'bg-blue-600 text-white shadow-sm'
                                : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white',
                        ]"
                    >
                        <BookOpen class="h-4 w-4 shrink-0" />
                        <span>Panduan Panitia</span>
                    </Link>
                </div>

                <!-- Menu Group 2: Navigasi Publik -->
                <div class="space-y-1.5">
                    <span
                        class="px-3 text-[10px] font-bold tracking-wider text-slate-400 uppercase dark:text-slate-500"
                    >
                        SISTEM
                    </span>

                    <Link
                        href="/dashboard"
                        class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-xs font-bold text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white"
                    >
                        <UserCheck class="h-4 w-4 shrink-0" />
                        <span>Dashboard Pemilih</span>
                    </Link>

                    <Link
                        href="/"
                        class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-xs font-bold text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white"
                    >
                        <Home class="h-4 w-4 shrink-0" />
                        <span>Halaman Depan</span>
                    </Link>
                </div>
            </nav>

            <!-- Sidebar User Profile Footer -->
            <div
                class="border-t border-slate-200 bg-slate-50/50 p-4 dark:border-slate-800 dark:bg-slate-900/50"
            >
                <div
                    class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-700/60 dark:bg-slate-800"
                >
                    <div class="flex min-w-0 items-center gap-3">
                        <div
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-600 text-xs font-black text-white"
                        >
                            {{
                                user?.name
                                    ? user.name.charAt(0).toUpperCase()
                                    : 'A'
                            }}
                        </div>
                        <div class="truncate">
                            <p
                                class="truncate text-xs leading-snug font-bold text-slate-900 dark:text-white"
                            >
                                {{ user?.name || 'Panitia Pemilu' }}
                            </p>
                            <p
                                class="text-[10px] font-medium tracking-wider text-slate-500 uppercase dark:text-slate-400"
                            >
                                {{ user?.role || 'PANITIA' }}
                            </p>
                        </div>
                    </div>

                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="shrink-0 rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-950/40 dark:hover:text-rose-400"
                        title="Keluar"
                    >
                        <LogOut class="h-4 w-4" />
                    </Link>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex min-w-0 flex-1 flex-col">
            <!-- Topbar Header -->
            <header
                class="sticky top-0 z-30 flex items-center justify-between border-b border-slate-200 bg-white px-4 py-3.5 shadow-sm transition-colors sm:px-6 dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="flex items-center gap-3">
                    <button
                        @click="isSidebarOpen = true"
                        class="rounded-xl p-2 text-slate-600 transition-colors hover:bg-slate-100 md:hidden dark:text-slate-300 dark:hover:bg-slate-800"
                    >
                        <Menu class="h-5 w-5" />
                    </button>

                    <h1
                        class="text-sm font-bold text-slate-900 dark:text-white"
                    >
                        <slot name="header">Panel Administrasi E-Voting</slot>
                    </h1>
                </div>

                <div class="flex items-center gap-3">
                    <ThemeToggle />

                    <Link
                        href="/"
                        class="hidden items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700 transition-colors hover:bg-slate-200 sm:inline-flex dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                    >
                        <ExternalLink
                            class="h-3.5 w-3.5 text-blue-600 dark:text-blue-400"
                        />
                        <span>Lihat Website</span>
                    </Link>
                </div>
            </header>

            <!-- Flash Notifications -->
            <div
                v-if="flash.success || flash.error || flash.info"
                class="mx-auto w-full max-w-7xl space-y-2 p-4 pb-0 sm:p-6"
            >
                <div
                    v-if="flash.success"
                    class="flex items-center gap-3 rounded-2xl border border-emerald-300 bg-emerald-50 p-4 text-xs font-bold text-emerald-900 shadow-sm dark:border-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-100"
                >
                    <CheckCircle2 class="h-5 w-5 shrink-0 text-emerald-600" />
                    <span>{{ flash.success }}</span>
                </div>
                <div
                    v-if="flash.error"
                    class="flex items-center gap-3 rounded-2xl border border-rose-300 bg-rose-50 p-4 text-xs font-bold text-rose-900 shadow-sm dark:border-rose-800 dark:bg-rose-950/80 dark:text-rose-100"
                >
                    <AlertCircle class="h-5 w-5 shrink-0 text-rose-600" />
                    <span>{{ flash.error }}</span>
                </div>
            </div>

            <!-- Main Body Container -->
            <main
                class="mx-auto w-full max-w-7xl flex-1 space-y-6 p-4 sm:p-6 md:p-8"
            >
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import {
    Shield,
    BarChart3,
    Vote,
    Home,
    LogOut,
    Menu,
    X,
    CheckCircle2,
    AlertCircle,
    UserCheck,
    ExternalLink,
    Sliders,
    BookOpen,
} from '@lucide/vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';

const isSidebarOpen = ref(false);
const page = usePage();

const user = computed(() => (page.props.auth as any)?.user || null);
const flash = computed(() => (page.props.flash as any) || {});
const appSettings = computed(() => (page.props.appSettings as any) || {});

const isDashboardActive = computed(
    () => page.url === '/admin' || page.url === '/admin/',
);
const isPemilihanActive = computed(() =>
    page.url.startsWith('/admin/pemilihan'),
);
const isPengaturanActive = computed(() =>
    page.url.startsWith('/admin/pengaturan'),
);
const isPanduanActive = computed(() =>
    page.url.startsWith('/admin/panduan'),
);
</script>

<style scoped>
.backdrop-fade-enter-active,
.backdrop-fade-leave-active {
    transition: opacity 0.3s ease;
}

.backdrop-fade-enter-from,
.backdrop-fade-leave-to {
    opacity: 0;
}
</style>

<template>
    <Head :title="computedTitle" />
    <div
        class="flex min-h-screen flex-col bg-slate-50 text-slate-900 transition-colors duration-200 dark:bg-slate-950 dark:text-slate-100"
    >
        <!-- Header (Hidden on Login page) -->
        <header
            v-if="!hideNavbar && !isLoginPage"
            class="sticky top-0 z-30 border-b border-slate-200 bg-white text-slate-900 shadow-sm transition-colors duration-200 dark:border-slate-800 dark:bg-slate-900 dark:text-white"
        >
            <div
                class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3.5 sm:px-6 lg:px-8"
            >
                <!-- Logo Branding -->
                <Link
                    href="/"
                    class="flex min-w-0 items-center gap-2.5 transition-opacity hover:opacity-90"
                >
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
                        <Vote class="h-5 w-5" />
                    </div>
                    <div class="truncate">
                        <h1
                            class="truncate text-base leading-tight font-black tracking-tight text-slate-900 dark:text-white"
                        >
                            {{ appSettings.app_name || 'E-VOTING' }}
                        </h1>
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

                <!-- Center Navigation Links -->
                <nav
                    class="hidden items-center gap-6 text-sm font-semibold text-slate-600 md:flex dark:text-slate-400"
                >
                    <Link
                        href="/"
                        class="transition-colors hover:text-blue-600 dark:hover:text-blue-400"
                        >Beranda</Link
                    >
                    <a
                        href="#pemilihan"
                        @click="scrollToSection($event, 'pemilihan')"
                        class="transition-colors hover:text-blue-600 dark:hover:text-blue-400"
                        >Pemilihan</a
                    >
                    <a
                        href="#cara-memilih"
                        @click="scrollToSection($event, 'cara-memilih')"
                        class="transition-colors hover:text-blue-600 dark:hover:text-blue-400"
                        >Cara Memilih</a
                    >
                    <a
                        href="#faq"
                        @click="scrollToSection($event, 'faq')"
                        class="transition-colors hover:text-blue-600 dark:hover:text-blue-400"
                        >FAQ</a
                    >
                </nav>

                <!-- Navigation Controls -->
                <div class="flex items-center gap-3">
                    <ThemeToggle />

                    <template v-if="user">
                        <div
                            class="hidden items-center gap-2.5 rounded-xl border border-slate-200 bg-slate-100 px-3 py-1.5 sm:flex dark:border-slate-700 dark:bg-slate-800"
                        >
                            <div
                                class="flex h-6 w-6 items-center justify-center rounded-lg bg-blue-600 text-xs font-bold text-white"
                            >
                                {{
                                    user.name
                                        ? user.name.charAt(0).toUpperCase()
                                        : 'U'
                                }}
                            </div>
                            <span
                                class="max-w-[150px] truncate text-xs font-bold text-slate-800 dark:text-slate-200"
                                >{{ user.name }}</span
                            >
                        </div>

                        <Link
                            href="/dashboard"
                            class="hidden items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-100 px-3.5 py-1.5 text-xs font-bold text-slate-900 transition-colors hover:bg-slate-200 sm:inline-flex dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:hover:bg-slate-700"
                        >
                            <Vote
                                class="h-4 w-4 text-blue-600 dark:text-blue-400"
                            />
                            <span>Dashboard</span>
                        </Link>

                        <Link
                            v-if="
                                user.role === 'admin' ||
                                user.role === 'panitia' ||
                                user.role === 'superadmin'
                            "
                            href="/admin"
                            class="hidden items-center gap-1.5 rounded-xl bg-amber-500 px-3.5 py-1.5 text-xs font-bold text-slate-950 shadow-sm transition-colors hover:bg-amber-600 sm:inline-flex"
                        >
                            <Shield class="h-4 w-4" />
                            <span>Panitia</span>
                        </Link>

                        <Link
                            href="/logout"
                            method="post"
                            as="button"
                            class="rounded-xl p-2 text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white"
                            title="Keluar / Logout"
                        >
                            <LogOut class="h-5 w-5" />
                        </Link>
                    </template>

                    <template v-else>
                        <Link
                            href="/login"
                            class="flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white shadow-sm transition-colors hover:bg-blue-700"
                        >
                            <LogIn class="h-4 w-4" />
                            <span>Masuk Aplikasi</span>
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <!-- Flash Notifications -->
        <div
            v-if="flash.success || flash.error || flash.info"
            class="mx-auto w-full max-w-7xl px-4 pt-4 sm:px-6 lg:px-8"
        >
            <div
                v-if="flash.success"
                class="flex items-center gap-3 rounded-2xl border border-emerald-300 bg-emerald-50 p-4 text-xs font-bold text-emerald-900 shadow-sm dark:border-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-100"
            >
                <CheckCircle2
                    class="h-5 w-5 shrink-0 text-emerald-600 dark:text-emerald-400"
                />
                <span>{{ flash.success }}</span>
            </div>
            <div
                v-if="flash.error"
                class="flex items-center gap-3 rounded-2xl border border-rose-300 bg-rose-50 p-4 text-xs font-bold text-rose-900 shadow-sm dark:border-rose-800 dark:bg-rose-950/70 dark:text-rose-100"
            >
                <AlertCircle
                    class="h-5 w-5 shrink-0 text-rose-600 dark:text-rose-400"
                />
                <span>{{ flash.error }}</span>
            </div>
            <div
                v-if="flash.info"
                class="flex items-center gap-3 rounded-2xl border border-blue-300 bg-blue-50 p-4 text-xs font-bold text-blue-900 shadow-sm dark:border-blue-800 dark:bg-blue-950/70 dark:text-blue-100"
            >
                <Info
                    class="h-5 w-5 shrink-0 text-blue-600 dark:text-blue-400"
                />
                <span>{{ flash.info }}</span>
            </div>
        </div>

        <!-- Main Content Container -->
        <main
            :class="[
                'mx-auto w-full flex-1 transition-all',
                isLoginPage
                    ? 'flex items-center justify-center p-4'
                    : 'max-w-7xl px-4 py-6 pb-20 sm:px-6 sm:pb-8 lg:px-8',
            ]"
        >
            <slot />
        </main>

        <!-- Bottom Navigation Bar (Mobile Only, Hidden on Login) -->
        <footer
            v-if="!hideNavbar && !isLoginPage"
            class="fixed inset-x-0 bottom-0 z-20 flex items-center justify-around border-t border-slate-200 bg-white px-6 py-2.5 text-xs font-bold shadow-lg sm:hidden dark:border-slate-800 dark:bg-slate-900"
        >
            <Link
                href="/"
                :class="[
                    'flex flex-col items-center gap-1 transition-colors',
                    isUrl('/') && page.url === '/'
                        ? 'text-blue-600 dark:text-blue-400'
                        : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200',
                ]"
            >
                <Home class="h-5 w-5" />
                <span>Beranda</span>
            </Link>

            <Link
                v-if="user"
                href="/dashboard"
                :class="[
                    'flex flex-col items-center gap-1 transition-colors',
                    isUrl('/dashboard')
                        ? 'text-blue-600 dark:text-blue-400'
                        : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200',
                ]"
            >
                <Vote class="h-5 w-5" />
                <span>Dashboard</span>
            </Link>

            <Link
                v-if="
                    user &&
                    (user.role === 'admin' ||
                        user.role === 'panitia' ||
                        user.role === 'superadmin')
                "
                href="/admin"
                class="flex flex-col items-center gap-1 text-amber-600 dark:text-amber-400"
            >
                <Shield class="h-5 w-5" />
                <span>Panitia</span>
            </Link>
        </footer>

        <!-- Desktop Footer (Hidden on Login) -->
        <footer
            v-if="!hideNavbar && !isLoginPage"
            class="hidden border-t border-slate-200 bg-white py-6 text-center text-xs text-slate-500 transition-colors sm:block dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400"
        >
            <div
                class="mx-auto flex max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8"
            >
                <p>
                    &copy; 2026 E-Voting Pemilu Sekolah SMKN 1 Talaga. Hak Cipta
                    Dilindungi.
                </p>
                <p class="font-medium text-slate-400 dark:text-slate-500">
                    Developed by ICT SMKN 1 Talaga
                </p>
            </div>
        </footer>

        <!-- Floating Toast Notification System -->
        <ToastNotification />
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { usePage, Link, Head } from '@inertiajs/vue3';
import {
    Vote,
    LogOut,
    LogIn,
    Home,
    Shield,
    CheckCircle2,
    AlertCircle,
    Info,
} from '@lucide/vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import ToastNotification from '@/Components/ToastNotification.vue';

const props = defineProps({
    title: {
        type: String,
        default: '',
    },
    hideNavbar: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();

const computedTitle = computed(() => {
    if (props.title) return props.title;
    if (page.url === '/' || page.url === '') return 'Beranda E-Voting';
    if (page.url.startsWith('/login')) return 'Masuk Pemilih';
    if (page.url.startsWith('/dashboard')) return 'Dashboard Pemilih';
    if (page.url.startsWith('/bilik-suara')) return 'Bilik Suara Digital';
    if (page.url.startsWith('/hasil')) return 'Hasil Pemilihan';
    return '';
});
const user = computed(() => (page.props.auth as any)?.user || null);
const flash = computed(() => (page.props.flash as any) || {});
const appSettings = computed(() => (page.props.appSettings as any) || {});

const isLoginPage = computed(() => {
    return page.url.startsWith('/login');
});

const isUrl = (url: string) => {
    return page.url === url || page.url.startsWith(url);
};

const scrollToSection = (e: Event, id: string) => {
    const el = document.getElementById(id);
    if (el) {
        e.preventDefault();
        el.scrollIntoView({ behavior: 'smooth' });
    }
};
</script>

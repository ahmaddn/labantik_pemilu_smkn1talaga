<template>
    <!-- Full-Bleed Viewport 50:50 Split Screen -->
    <div
        class="grid min-h-screen w-full grid-cols-1 overflow-x-hidden bg-slate-50 text-slate-900 transition-colors duration-200 lg:grid-cols-2 dark:bg-slate-950 dark:text-slate-100"
    >
        <!-- LEFT HALF (50%): Full-height Vibrant Solid Blue Branding Area (Desktop Only) -->
        <div
            class="relative hidden min-h-[300px] flex-col items-center justify-between bg-blue-600 p-8 text-center text-white sm:p-12 lg:flex lg:min-h-screen lg:p-16"
        >
            <!-- Top Spacer -->
            <div class="w-full"></div>

            <!-- Centered Branding Text -->
            <div class="mx-auto my-auto max-w-md space-y-3">
                <h1
                    class="text-3xl leading-tight font-extrabold tracking-tight text-white uppercase lg:text-4xl"
                >
                    E-VOTING SMKN 1 TALAGA
                </h1>

                <p
                    class="mx-auto max-w-sm text-sm leading-relaxed font-normal text-blue-100"
                >
                    Sistem Pemilihan Umum & E-Voting Terpadu SMKN 1 Talaga.
                </p>
            </div>

            <!-- Bottom Branding Note -->
            <div
                class="pt-4 text-[10px] font-bold tracking-widest text-blue-200 uppercase"
            >
                SMKN 1 TALAGA &bull; E-VOTING SYSTEM
            </div>
        </div>

        <!-- RIGHT HALF (50%): Full-height Clean Form Area -->
        <div
            class="relative flex min-h-[550px] flex-col justify-between bg-slate-50 p-6 sm:p-12 lg:min-h-screen lg:p-16 dark:bg-slate-900"
        >
            <!-- Top Right Controls (Theme Toggle & Home Link) -->
            <div class="flex w-full items-center justify-end gap-3">
                <ThemeToggle :show-label="true" />
                <Link
                    href="/"
                    class="flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm transition-colors hover:text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:text-white"
                >
                    <Home class="h-4 w-4 text-slate-500" />
                    <span>Beranda</span>
                </Link>
            </div>

            <!-- Centered Form Container -->
            <div class="mx-auto my-auto w-full max-w-md space-y-6 py-6">
                <!-- Welcome Header -->
                <div class="space-y-1 text-left">
                    <h2
                        class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl dark:text-white"
                    >
                        Selamat Datang!
                    </h2>
                    <p
                        class="text-xs font-medium text-slate-500 sm:text-sm dark:text-slate-400"
                    >
                        Silakan masuk untuk mulai menggunakan hak pilih Anda.
                    </p>
                </div>

                <!-- Mode Selector Tabs (Pemilih vs Panitia) -->
                <div
                    class="flex gap-1 rounded-xl bg-slate-200/70 p-1 text-xs font-bold dark:bg-slate-800"
                >
                    <button
                        type="button"
                        @click="activeMode = 'pemilih'"
                        :class="[
                            'flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-lg px-3 py-2 transition-all',
                            activeMode === 'pemilih'
                                ? 'bg-white text-blue-600 shadow-sm dark:bg-slate-950 dark:text-blue-400'
                                : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200',
                        ]"
                    >
                        <UserCheck class="h-4 w-4" />
                        <span>Pemilih (Siswa/Guru)</span>
                    </button>

                    <button
                        type="button"
                        @click="activeMode = 'panitia'"
                        :class="[
                            'flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-lg px-3 py-2 transition-all',
                            activeMode === 'panitia'
                                ? 'bg-white text-amber-600 shadow-sm dark:bg-slate-950 dark:text-amber-400'
                                : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200',
                        ]"
                    >
                        <Shield class="h-4 w-4" />
                        <span>Panitia / Admin</span>
                    </button>
                </div>

                <!-- Login Form -->
                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Error Alert -->
                    <div
                        v-if="form.errors.identifier"
                        class="flex items-center gap-2.5 rounded-xl border border-rose-200 bg-rose-50 p-3.5 text-xs font-semibold text-rose-700 shadow-sm dark:border-rose-800 dark:bg-rose-950/70 dark:text-rose-300"
                    >
                        <AlertCircle class="h-4 w-4 shrink-0 text-rose-600" />
                        <span>{{ form.errors.identifier }}</span>
                    </div>

                    <!-- Identifier Input -->
                    <div class="space-y-1.5 text-left">
                        <label
                            class="block text-[11px] font-bold tracking-wider text-slate-600 uppercase dark:text-slate-400"
                        >
                            {{
                                activeMode === 'pemilih'
                                    ? 'NIS / NIP / EMAIL'
                                    : 'EMAIL ADMIN'
                            }}
                        </label>
                        <div class="relative">
                            <div
                                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400"
                            >
                                <AtSign class="h-4 w-4" />
                            </div>
                            <input
                                v-model="form.identifier"
                                type="text"
                                required
                                :placeholder="
                                    activeMode === 'pemilih'
                                        ? 'Masukkan NIS / NIP / Email'
                                        : 'admin@gmail.com'
                                "
                                class="w-full rounded-xl border border-slate-200/50 bg-slate-100 py-3 pr-3.5 pl-10 text-xs font-semibold text-slate-900 placeholder-slate-400 transition-colors focus:ring-2 focus:ring-blue-600 focus:outline-none dark:border-slate-700/50 dark:bg-slate-800/90 dark:text-white"
                            />
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div class="space-y-1.5 text-left">
                        <label
                            class="block text-[11px] font-bold tracking-wider text-slate-600 uppercase dark:text-slate-400"
                        >
                            PASSWORD
                        </label>
                        <div class="relative">
                            <div
                                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400"
                            >
                                <Lock class="h-4 w-4" />
                            </div>
                            <input
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                placeholder="........"
                                class="w-full rounded-xl border border-slate-200/50 bg-slate-100 py-3 pr-10 pl-10 text-xs font-semibold text-slate-900 placeholder-slate-400 transition-colors focus:ring-2 focus:ring-blue-600 focus:outline-none dark:border-slate-700/50 dark:bg-slate-800/90 dark:text-white"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 flex cursor-pointer items-center pr-3.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                            >
                                <EyeOff v-if="showPassword" class="h-4 w-4" />
                                <Eye v-else class="h-4 w-4" />
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me Checkbox -->
                    <div
                        class="flex items-center justify-between pt-0.5 text-xs"
                    >
                        <label
                            class="flex cursor-pointer items-center gap-2 font-medium text-slate-600 select-none dark:text-slate-400"
                        >
                            <input
                                type="checkbox"
                                v-model="form.remember"
                                class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800"
                            />
                            <span>Ingat Saya</span>
                        </label>
                    </div>

                    <!-- Submit Button (Solid Vibrant Blue Pill matching inspiration) -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="mt-2 flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-3.5 text-xs font-extrabold tracking-wider text-white uppercase shadow-md transition-all hover:bg-blue-700 hover:shadow-lg disabled:opacity-60"
                    >
                        <span v-if="form.processing">MEMPROSES MASUK...</span>
                        <span v-else class="tracking-widest"
                            >MASUK SEKARANG</span
                        >
                    </button>
                </form>
            </div>

            <!-- Bottom Copyright Line -->
            <div
                class="pt-4 text-center text-[10px] font-bold tracking-widest text-slate-400 uppercase dark:text-slate-500"
            >
                DEVELOPED FOR LABANTIK JURUSAN &copy; 2026
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import {
    AtSign,
    UserCheck,
    Shield,
    Lock,
    Eye,
    EyeOff,
    AlertCircle,
    Home,
} from '@lucide/vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';

const activeMode = ref<'pemilih' | 'panitia'>('pemilih');
const showPassword = ref(false);

const form = useForm({
    identifier: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => {
            form.password = '';
        },
    });
};
</script>

<template>
    <Teleport to="body">
        <div
            class="pointer-events-none fixed top-5 right-5 z-50 flex flex-col items-end gap-2.5 max-w-sm sm:max-w-md w-full px-4"
        >
            <TransitionGroup name="toast" tag="div" class="w-full space-y-2.5">
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    :class="[
                        'pointer-events-auto flex items-start gap-3 rounded-2xl border p-4 shadow-xl backdrop-blur-md transition-all duration-300 w-full',
                        toast.type === 'success'
                            ? 'border-emerald-200/80 bg-white/95 text-slate-900 shadow-emerald-500/10 dark:border-emerald-800/80 dark:bg-slate-900/95 dark:text-white'
                            : toast.type === 'error'
                              ? 'border-rose-200/80 bg-white/95 text-slate-900 shadow-rose-500/10 dark:border-rose-800/80 dark:bg-slate-900/95 dark:text-white'
                              : 'border-blue-200/80 bg-white/95 text-slate-900 shadow-blue-500/10 dark:border-blue-800/80 dark:bg-slate-900/95 dark:text-white',
                    ]"
                >
                    <!-- Icon Indicator -->
                    <div
                        :class="[
                            'flex h-9 w-9 shrink-0 items-center justify-center rounded-xl',
                            toast.type === 'success'
                                ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-950/80 dark:text-emerald-400'
                                : toast.type === 'error'
                                  ? 'bg-rose-100 text-rose-600 dark:bg-rose-950/80 dark:text-rose-400'
                                  : 'bg-blue-100 text-blue-600 dark:bg-blue-950/80 dark:text-blue-400',
                        ]"
                    >
                        <CheckCircle2 v-if="toast.type === 'success'" class="h-5 w-5" />
                        <AlertCircle v-else-if="toast.type === 'error'" class="h-5 w-5" />
                        <Info v-else class="h-5 w-5" />
                    </div>

                    <!-- Message Body -->
                    <div class="flex-1 min-w-0 pt-0.5">
                        <h4
                            :class="[
                                'text-xs font-black uppercase tracking-wider',
                                toast.type === 'success'
                                    ? 'text-emerald-700 dark:text-emerald-400'
                                    : toast.type === 'error'
                                      ? 'text-rose-700 dark:text-rose-400'
                                      : 'text-blue-700 dark:text-blue-400',
                            ]"
                        >
                            {{ toast.title }}
                        </h4>
                        <p class="mt-0.5 text-xs font-medium text-slate-600 dark:text-slate-300 break-words leading-relaxed">
                            {{ toast.message }}
                        </p>
                    </div>

                    <!-- Close Button -->
                    <button
                        type="button"
                        @click="removeToast(toast.id)"
                        class="cursor-pointer rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:text-slate-500 dark:hover:bg-slate-800 dark:hover:text-slate-300 transition-colors shrink-0"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { CheckCircle2, AlertCircle, Info, X } from '@lucide/vue';

interface Toast {
    id: number;
    type: 'success' | 'error' | 'info';
    title: string;
    message: string;
}

const toasts = ref<Toast[]>([]);
let toastCounter = 0;
const page = usePage();

const addToast = (type: 'success' | 'error' | 'info', message: string, title?: string) => {
    if (!message) return;
    const id = ++toastCounter;
    const defaultTitle =
        type === 'success'
            ? 'Berhasil!'
            : type === 'error'
              ? 'Terjadi Kesalahan'
              : 'Informasi';

    toasts.value.push({
        id,
        type,
        title: title || defaultTitle,
        message,
    });

    // Auto dismiss after 4 seconds
    setTimeout(() => {
        removeToast(id);
    }, 4500);
};

const removeToast = (id: number) => {
    toasts.value = toasts.value.filter((t) => t.id !== id);
};

// Global event listener for custom toast triggering anywhere in app
const handleTriggerToast = (event: CustomEvent<{ type: 'success' | 'error' | 'info'; message: string; title?: string }>) => {
    if (event.detail) {
        addToast(event.detail.type, event.detail.message, event.detail.title);
    }
};

onMounted(() => {
    window.addEventListener('app:toast' as any, handleTriggerToast);

    // Initial check on mount
    const flash = (page.props.flash as any) || {};
    if (flash.success) addToast('success', flash.success);
    if (flash.error) addToast('error', flash.error);
    if (flash.info) addToast('info', flash.info);
});

onUnmounted(() => {
    window.removeEventListener('app:toast' as any, handleTriggerToast);
});

// Watch Inertia flash props changes
watch(
    () => page.props.flash as any,
    (newFlash) => {
        if (!newFlash) return;
        if (newFlash.success) addToast('success', newFlash.success);
        if (newFlash.error) addToast('error', newFlash.error);
        if (newFlash.info) addToast('info', newFlash.info);
    },
    { deep: true },
);

// Watch for Inertia page errors (form validation error toast)
watch(
    () => page.props.errors as any,
    (errors) => {
        if (errors && Object.keys(errors).length > 0) {
            const firstError = Object.values(errors)[0] as string;
            if (firstError) {
                addToast('error', firstError, 'Periksa Kembali Input');
            }
        }
    },
    { deep: true },
);
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}

.toast-enter-from {
    opacity: 0;
    transform: translateY(-20px) scale(0.95);
}

.toast-leave-to {
    opacity: 0;
    transform: translateX(30px) scale(0.95);
}
</style>

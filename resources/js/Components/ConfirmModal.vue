<template>
    <Teleport v-if="isMounted" to="body">
        <Transition name="modal-fade">
            <div
                v-if="show"
                class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm"
                @click.self="cancel"
            >
                <div
                    class="modal-card w-full max-w-sm space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900"
                >
                    <!-- Icon & Header -->
                    <div class="flex items-start gap-4">
                        <div
                            :class="[
                                'shrink-0 rounded-xl p-3',
                                type === 'danger'
                                    ? 'bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400'
                                    : type === 'warning'
                                      ? 'bg-amber-50 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400'
                                      : 'bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400',
                            ]"
                        >
                            <AlertTriangle
                                v-if="type === 'danger' || type === 'warning'"
                                class="h-6 w-6"
                            />
                            <HelpCircle v-else class="h-6 w-6" />
                        </div>

                        <div class="space-y-1">
                            <h3
                                class="text-base leading-snug font-extrabold text-slate-900 dark:text-white"
                            >
                                {{ title }}
                            </h3>
                            <p
                                class="text-xs leading-relaxed font-medium text-slate-600 dark:text-slate-400"
                            >
                                {{ message }}
                            </p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div
                        class="flex items-center justify-end gap-2 border-t border-slate-100 pt-2 dark:border-slate-800"
                    >
                        <button
                            type="button"
                            @click="cancel"
                            class="cursor-pointer rounded-xl bg-slate-100 px-4 py-2.5 text-xs font-bold text-slate-700 transition-colors hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                        >
                            {{ cancelText }}
                        </button>

                        <button
                            type="button"
                            @click="confirm"
                            :class="[
                                'cursor-pointer rounded-xl px-4 py-2.5 text-xs font-bold text-white shadow-sm transition-colors',
                                type === 'danger'
                                    ? 'bg-rose-600 hover:bg-rose-700'
                                    : type === 'warning'
                                      ? 'bg-amber-600 hover:bg-amber-700'
                                      : 'bg-blue-600 hover:bg-blue-700',
                            ]"
                        >
                            {{ confirmText }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { AlertTriangle, HelpCircle } from '@lucide/vue';

const isMounted = ref(false);
onMounted(() => {
    isMounted.value = true;
});

withDefaults(
    defineProps<{
        show?: boolean;
        title?: string;
        message?: string;
        type?: 'danger' | 'warning' | 'info';
        confirmText?: string;
        cancelText?: string;
    }>(),
    {
        show: false,
        title: 'Konfirmasi Tindakan',
        message: 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
        type: 'danger',
        confirmText: 'Ya, Lanjutkan',
        cancelText: 'Batal',
    },
);

const emit = defineEmits(['confirm', 'cancel']);

const confirm = () => {
    emit('confirm');
};

const cancel = () => {
    emit('cancel');
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

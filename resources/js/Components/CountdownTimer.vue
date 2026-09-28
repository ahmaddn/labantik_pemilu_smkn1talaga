<template>
    <div
        class="rounded-2xl border border-slate-200 bg-white p-5 text-slate-900 shadow-sm transition-colors dark:border-slate-800 dark:bg-slate-900 dark:text-white"
    >
        <div
            class="mb-4 flex items-center gap-2 text-xs font-bold tracking-wider text-slate-700 uppercase dark:text-slate-300"
        >
            <Clock class="h-4 w-4 text-blue-600 dark:text-blue-400" />
            <span>{{ label }}</span>
        </div>

        <div
            v-if="isFinished"
            class="rounded-xl border border-emerald-200 bg-emerald-50 py-4 text-center text-base font-bold text-emerald-600 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-400"
        >
            Waktu Pemilihan Telah Berakhir
        </div>

        <div v-else class="grid grid-cols-4 gap-2 text-center sm:gap-3">
            <div
                class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700/60 dark:bg-slate-900/80"
            >
                <span
                    class="block text-2xl font-black tracking-tight text-blue-600 sm:text-3xl dark:text-blue-400"
                    >{{ formatDigit(days) }}</span
                >
                <span
                    class="text-[10px] font-bold tracking-wider text-slate-500 uppercase sm:text-xs dark:text-slate-400"
                    >Hari</span
                >
            </div>
            <div
                class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700/60 dark:bg-slate-900/80"
            >
                <span
                    class="block text-2xl font-black tracking-tight text-blue-600 sm:text-3xl dark:text-blue-400"
                    >{{ formatDigit(hours) }}</span
                >
                <span
                    class="text-[10px] font-bold tracking-wider text-slate-500 uppercase sm:text-xs dark:text-slate-400"
                    >Jam</span
                >
            </div>
            <div
                class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700/60 dark:bg-slate-900/80"
            >
                <span
                    class="block text-2xl font-black tracking-tight text-blue-600 sm:text-3xl dark:text-blue-400"
                    >{{ formatDigit(minutes) }}</span
                >
                <span
                    class="text-[10px] font-bold tracking-wider text-slate-500 uppercase sm:text-xs dark:text-slate-400"
                    >Menit</span
                >
            </div>
            <div
                class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700/60 dark:bg-slate-900/80"
            >
                <span
                    class="block text-2xl font-black tracking-tight text-amber-600 sm:text-3xl dark:text-amber-400"
                    >{{ formatDigit(seconds) }}</span
                >
                <span
                    class="text-[10px] font-bold tracking-wider text-slate-500 uppercase sm:text-xs dark:text-slate-400"
                    >Detik</span
                >
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Clock } from '@lucide/vue';

const props = defineProps({
    targetDate: {
        type: String,
        required: true,
    },
    label: {
        type: String,
        default: 'Hitung Mundur Waktu Voting',
    },
});

const days = ref(0);
const hours = ref(0);
const minutes = ref(0);
const seconds = ref(0);
const isFinished = ref(false);

let timerInterval: ReturnType<typeof setInterval> | null = null;

const formatDigit = (num: number) => (num < 10 ? `0${num}` : `${num}`);

const calculateTime = () => {
    const target = new Date(props.targetDate).getTime();
    const now = new Date().getTime();
    const diff = target - now;

    if (diff <= 0) {
        days.value = 0;
        hours.value = 0;
        minutes.value = 0;
        seconds.value = 0;
        isFinished.value = true;
        if (timerInterval) clearInterval(timerInterval);
        return;
    }

    days.value = Math.floor(diff / (1000 * 60 * 60 * 24));
    hours.value = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    minutes.value = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    seconds.value = Math.floor((diff % (1000 * 60)) / 1000);
};

onMounted(() => {
    calculateTime();
    timerInterval = setInterval(calculateTime, 1000);
});

onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval);
});
</script>

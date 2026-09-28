<template>
    <button
        type="button"
        @click="toggleTheme"
        class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm transition-colors hover:text-slate-900 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:text-white"
        :title="isDark ? 'Beralih ke Light Mode' : 'Beralih ke Dark Mode'"
    >
        <Sun v-if="isDark" class="h-4 w-4 shrink-0 text-amber-400" />
        <Moon v-else class="h-4 w-4 shrink-0 text-slate-600" />
        <span v-if="showLabel" class="whitespace-nowrap">
            {{ isDark ? 'Mode Terang' : 'Mode Gelap' }}
        </span>
    </button>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Sun, Moon } from '@lucide/vue';

withDefaults(
    defineProps<{
        showLabel?: boolean;
    }>(),
    {
        showLabel: false,
    },
);

const isDark = ref(false);

const updateDOM = (dark: boolean) => {
    if (dark) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
};

const toggleTheme = () => {
    isDark.value = !isDark.value;
    localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
    updateDOM(isDark.value);
};

onMounted(() => {
    const saved = localStorage.getItem('theme');
    if (
        saved === 'dark' ||
        (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches)
    ) {
        isDark.value = true;
    } else {
        isDark.value = false;
    }
    updateDOM(isDark.value);
});
</script>

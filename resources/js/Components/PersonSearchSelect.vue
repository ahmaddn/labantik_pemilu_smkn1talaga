<template>
    <div class="relative w-full" ref="containerRef">
        <!-- Input Container -->
        <div class="relative flex items-center">
            <input
                type="text"
                :value="modelValue"
                @input="handleInput"
                @focus="isOpen = true"
                :placeholder="placeholder"
                :required="required"
                class="w-full rounded-xl border border-slate-300 bg-slate-50 p-3 pr-10 text-xs text-slate-900 transition-all outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
            />

            <!-- Clear Button / Chevron Icon -->
            <div class="absolute right-3 flex items-center gap-1">
                <button
                    v-if="modelValue"
                    type="button"
                    @click="clear"
                    class="p-1 text-slate-400 transition-colors hover:text-slate-600 dark:hover:text-slate-200"
                    title="Hapus"
                >
                    <X class="h-3.5 w-3.5" />
                </button>
                <Search class="pointer-events-none h-4 w-4 text-slate-400" />
            </div>
        </div>

        <!-- Dropdown List -->
        <Transition name="dropdown-fade">
            <div
                v-if="isOpen && filteredOptions.length > 0"
                class="absolute right-0 left-0 z-50 mt-1.5 max-h-60 divide-y divide-slate-100 overflow-y-auto rounded-2xl border border-slate-200 bg-white py-1 shadow-xl dark:divide-slate-800 dark:border-slate-700 dark:bg-slate-900"
            >
                <div
                    class="flex items-center justify-between bg-slate-50 px-3 py-1.5 text-[10px] font-extrabold tracking-wider text-slate-400 uppercase dark:bg-slate-800/60"
                >
                    <span
                        >Pilih dari Database ({{
                            filteredOptions.length
                        }}
                        hasil)</span
                    >
                    <span class="text-[9px] font-normal lowercase"
                        >atau ketik nama langsung</span
                    >
                </div>

                <button
                    v-for="(person, idx) in filteredOptions"
                    :key="idx"
                    type="button"
                    @click="selectPerson(person)"
                    class="group flex w-full items-center justify-between gap-3 px-3.5 py-2.5 text-left transition-colors hover:bg-blue-50 dark:hover:bg-blue-950/60"
                >
                    <div class="truncate">
                        <span
                            class="block truncate text-xs font-bold text-slate-900 group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400"
                        >
                            {{ person.name }}
                        </span>
                        <span
                            v-if="person.info"
                            class="block truncate text-[10px] text-slate-400 dark:text-slate-500"
                        >
                            {{ person.info }}
                        </span>
                    </div>

                    <span
                        :class="[
                            'shrink-0 rounded px-2 py-0.5 text-[10px] font-bold',
                            person.type === 'Siswa'
                                ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300'
                                : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
                        ]"
                    >
                        {{ person.type }}
                    </span>
                </button>
            </div>
        </Transition>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Search, X } from '@lucide/vue';

interface PersonOption {
    name: string;
    type: string;
    info?: string;
}

const props = withDefaults(
    defineProps<{
        modelValue?: string;
        options?: PersonOption[];
        placeholder?: string;
        required?: boolean;
    }>(),
    {
        modelValue: '',
        options: () => [],
        placeholder: 'Cari dari nama siswa/guru atau ketik sendiri...',
        required: false,
    },
);

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const containerRef = ref<HTMLElement | null>(null);

const filteredOptions = computed(() => {
    if (!props.options || props.options.length === 0) return [];
    const query = (props.modelValue || '').toLowerCase().trim();

    if (!query) {
        return props.options.slice(0, 30);
    }

    return props.options
        .filter(
            (p) =>
                p.name.toLowerCase().includes(query) ||
                (p.info && p.info.toLowerCase().includes(query)),
        )
        .slice(0, 40);
});

const handleInput = (e: Event) => {
    const target = e.target as HTMLInputElement;
    emit('update:modelValue', target.value);
    isOpen.value = true;
};

const selectPerson = (person: PersonOption) => {
    emit('update:modelValue', person.name);
    isOpen.value = false;
};

const clear = () => {
    emit('update:modelValue', '');
    isOpen.value = true;
};

const handleClickOutside = (e: MouseEvent) => {
    if (containerRef.value && !containerRef.value.contains(e.target as Node)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
.dropdown-fade-enter-active,
.dropdown-fade-leave-active {
    transition:
        opacity 0.15s ease,
        transform 0.15s ease;
}
.dropdown-fade-enter-from,
.dropdown-fade-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}
</style>

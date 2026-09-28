<template>
    <AdminLayout>
        <template #header>Edit Kandidat Paslon</template>

        <div class="mx-auto max-w-3xl space-y-6">
            <!-- Back Bar -->
            <div class="flex items-center justify-between">
                <Link
                    :href="`/admin/pemilihan/${election.id}/kandidat`"
                    class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 transition-colors hover:text-blue-600 dark:text-slate-300 dark:hover:text-blue-400"
                >
                    <ArrowLeft class="h-4 w-4" />
                    <span>Kembali ke Kandidat {{ election.title }}</span>
                </Link>
            </div>

            <!-- Main Form Card -->
            <div
                class="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800"
            >
                <div>
                    <h2
                        class="text-lg font-extrabold text-slate-900 dark:text-white"
                    >
                        Form Edit Kandidat Paslon
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Perbarui identitas paslon No.
                        {{ candidate.candidate_number }} ({{
                            candidate.chairman_name
                        }}).
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-5 text-xs">
                    <!-- Nomor Urut Paslon -->
                    <div class="space-y-1.5">
                        <label
                            class="block font-bold text-slate-700 dark:text-slate-300"
                        >
                            Nomor Urut Paslon
                            <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="form.candidate_number"
                            type="number"
                            min="1"
                            required
                            class="w-full rounded-xl border border-slate-300 bg-slate-50 p-3 text-xs text-slate-900 outline-none focus:ring-2 focus:ring-blue-500 sm:w-1/3 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                        />
                        <span
                            v-if="form.errors.candidate_number"
                            class="text-[11px] font-medium text-rose-500"
                            >{{ form.errors.candidate_number }}</span
                        >
                    </div>

                    <!-- Nama Ketua & Wakil Grid -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <label
                                class="block font-bold text-slate-700 dark:text-slate-300"
                            >
                                Nama Calon Ketua
                                <span class="text-rose-500">*</span>
                            </label>
                            <PersonSearchSelect
                                v-model="form.chairman_name"
                                :options="people"
                                placeholder="Pilih dari siswa/guru atau ketik nama..."
                                required
                            />
                            <span
                                v-if="form.errors.chairman_name"
                                class="text-[11px] font-medium text-rose-500"
                                >{{ form.errors.chairman_name }}</span
                            >
                        </div>

                        <div class="space-y-1.5">
                            <label
                                class="block font-bold text-slate-700 dark:text-slate-300"
                            >
                                Nama Calon Wakil (Opsional)
                            </label>
                            <PersonSearchSelect
                                v-model="form.vice_chairman_name"
                                :options="people"
                                placeholder="Pilih dari siswa/guru atau ketik nama..."
                            />
                        </div>
                    </div>

                    <!-- Foto Paslon Upload & Preview -->
                    <div class="space-y-2">
                        <label
                            class="block font-bold text-slate-700 dark:text-slate-300"
                            >Foto Paslon (JPG/PNG, max 2MB)</label
                        >
                        <div class="flex items-center gap-4">
                            <div
                                v-if="candidate.photo"
                                class="h-16 w-16 shrink-0 overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700"
                            >
                                <img
                                    :src="candidate.photo"
                                    :alt="candidate.chairman_name"
                                    class="h-full w-full object-cover"
                                />
                            </div>
                            <input
                                type="file"
                                accept="image/*"
                                @change="handlePhotoUpload"
                                class="w-full rounded-xl border border-slate-300 bg-slate-50 p-2.5 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                            />
                        </div>
                        <p class="text-[11px] text-slate-400">
                            Pilih file baru jika ingin mengganti foto kandidat
                            yang ada.
                        </p>
                    </div>

                    <!-- Visi & Misi dengan Quill Editor -->
                    <div class="space-y-1.5">
                        <label
                            class="block font-bold text-slate-700 dark:text-slate-300"
                        >
                            Visi & Misi Kandidat (Rich Text Editor Quill)
                        </label>
                        <div class="quill-wrapper">
                            <QuillEditor
                                v-model:content="form.vision_mission"
                                contentType="html"
                                theme="snow"
                                :toolbar="[
                                    'bold',
                                    'italic',
                                    'underline',
                                    { list: 'ordered' },
                                    { list: 'bullet' },
                                ]"
                                placeholder="Edit Visi dan Misi paslon di sini..."
                            />
                        </div>
                    </div>

                    <!-- Submit Bar -->
                    <div
                        class="flex justify-end gap-3 border-t border-slate-100 pt-4 dark:border-slate-700"
                    >
                        <Link
                            :href="`/admin/pemilihan/${election.id}/kandidat`"
                            class="rounded-xl bg-slate-100 px-5 py-2.5 font-bold text-slate-800 transition-colors hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="cursor-pointer rounded-xl bg-blue-600 px-6 py-2.5 font-bold text-white shadow-sm transition-colors hover:bg-blue-700 disabled:opacity-50"
                        >
                            {{
                                form.processing
                                    ? 'Menyimpan...'
                                    : 'Simpan Perubahan'
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { QuillEditor } from '@vueup/vue-quill';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PersonSearchSelect from '@/Components/PersonSearchSelect.vue';

const props = defineProps<{
    election: {
        id: number;
        title: string;
        type: string;
    };
    candidate: {
        id: number;
        candidate_number: number;
        chairman_name: string;
        vice_chairman_name: string | null;
        photo: string | null;
        vision_mission: string | null;
    };
    people?: Array<{ name: string; type: string; info?: string }>;
}>();

const form = useForm({
    candidate_number: props.candidate.candidate_number,
    chairman_name: props.candidate.chairman_name,
    vice_chairman_name: props.candidate.vice_chairman_name || '',
    photo: null as File | null,
    vision_mission: props.candidate.vision_mission || '',
});

const handlePhotoUpload = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.photo = target.files[0];
    }
};

const submit = () => {
    form.post(
        `/admin/pemilihan/${props.election.id}/kandidat/${props.candidate.id}`,
    );
};
</script>

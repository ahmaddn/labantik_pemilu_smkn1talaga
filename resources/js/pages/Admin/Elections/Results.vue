<template>
    <AdminLayout>
        <template #header
            >Visualisasi Real-Time & Management Tahap Pemilihan</template
        >

        <div class="space-y-6">
            <!-- Back & Action Bar -->
            <div
                class="flex flex-col items-start justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:p-5 dark:border-slate-700 dark:bg-slate-800"
            >
                <div>
                    <Link
                        href="/admin/pemilihan"
                        class="mb-1 inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:underline dark:text-blue-400"
                    >
                        <ArrowLeft class="h-3.5 w-3.5" />
                        <span>Kembali ke Pemilihan</span>
                    </Link>
                    <h2
                        class="flex flex-wrap items-center gap-2 text-base font-extrabold text-slate-900 dark:text-white"
                    >
                        <span>{{ election.title }}</span>
                        <span
                            :class="[
                                'rounded px-2.5 py-0.5 text-[10px] font-bold uppercase',
                                election.is_published
                                    ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300'
                                    : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
                            ]"
                        >
                            {{
                                election.is_published
                                    ? 'Dipublikasi'
                                    : 'Hasil Tersembunyi'
                            }}
                        </span>

                        <span
                            v-if="election.is_multi_stage"
                            class="rounded bg-purple-100 px-2.5 py-0.5 text-[10px] font-extrabold text-purple-800 uppercase dark:bg-purple-950 dark:text-purple-300"
                        >
                            Tahap {{ election.current_stage }} /
                            {{ election.total_stages }}
                        </span>
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Kategori:
                        <strong
                            class="text-slate-700 uppercase dark:text-slate-300"
                            >{{ election.type }}</strong
                        >
                        <span v-if="election.academic_year">
                            | Tahun Ajaran {{ election.academic_year }}</span
                        >
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <!-- Standard Clean Live Sync Status (Dashboard Native) -->
                    <div
                        class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs font-semibold text-slate-600 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-300"
                        title="Data diperbarui otomatis dari server"
                    >
                        <RefreshCw
                            :class="[
                                'h-3.5 w-3.5 text-slate-400 dark:text-slate-500',
                                isRefreshing ? 'animate-spin text-blue-600 dark:text-blue-400' : '',
                            ]"
                        />
                        <span>Pembaruan: <strong class="font-bold text-slate-800 dark:text-slate-200">{{ lastUpdatedTime }}</strong></span>
                    </div>

                    <!-- Toggle Publish Button -->
                    <button
                        type="button"
                        @click="togglePublish"
                        :class="[
                            'flex cursor-pointer items-center gap-1.5 rounded-xl border px-3.5 py-2 text-xs font-bold shadow-sm transition-colors',
                            election.is_published
                                ? 'border-emerald-300 bg-emerald-50 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-300'
                                : 'border-amber-300 bg-amber-50 text-amber-800 dark:border-amber-800 dark:bg-amber-950 dark:text-amber-300',
                        ]"
                    >
                        <Eye v-if="election.is_published" class="h-4 w-4" />
                        <EyeOff v-else class="h-4 w-4" />
                        <span>{{
                            election.is_published
                                ? 'Sembunyikan Hasil'
                                : 'Publikasikan Hasil'
                        }}</span>
                    </button>

                    <!-- Print / Refresh Button -->
                    <button
                        type="button"
                        @click="windowPrint"
                        class="flex cursor-pointer items-center gap-1.5 rounded-xl bg-slate-900 px-3.5 py-2 text-xs font-bold text-white shadow-sm transition-colors hover:bg-slate-800"
                    >
                        <Printer class="h-4 w-4" />
                        <span>Cetak Rekap</span>
                    </button>
                </div>
            </div>

            <!-- Stage Selector Tabs (If Multi-Stage) -->
            <div
                v-if="election.is_multi_stage"
                class="flex flex-wrap items-center justify-between gap-2 rounded-2xl border border-slate-200 bg-white p-2 shadow-sm dark:border-slate-700 dark:bg-slate-800"
            >
                <div class="flex items-center gap-1">
                    <span
                        class="px-3 text-xs font-bold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                        >Lihat Rekap Tahap:</span
                    >
                    <button
                        v-for="stg in election.total_stages"
                        :key="stg"
                        @click="changeStage(stg)"
                        :class="[
                            'cursor-pointer rounded-xl px-3.5 py-1.5 text-xs font-bold transition-all',
                            selectedStage === stg
                                ? 'bg-blue-600 text-white shadow-sm'
                                : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-700/60 dark:text-slate-300',
                        ]"
                    >
                        Tahap {{ stg }}
                        {{ stg === election.current_stage ? '(Aktif)' : '' }}
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    <!-- Advance Stage Button (Only if not final stage) -->
                    <button
                        v-if="election.current_stage < election.total_stages"
                        type="button"
                        @click="showAdvanceModal = true"
                        class="flex cursor-pointer items-center gap-1.5 rounded-xl bg-purple-600 px-3.5 py-2 text-xs font-bold text-white shadow-sm transition-colors hover:bg-purple-700"
                    >
                        <Filter class="h-4 w-4" />
                        <span>Saring Top Paslon & Lanjutkan Tahap</span>
                    </button>
                    <span
                        v-else
                        class="inline-flex items-center gap-1 rounded-xl bg-emerald-100 px-3 py-2 text-xs font-bold text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300"
                    >
                        <CheckCircle2 class="h-4 w-4 text-emerald-600" />
                        <span>Tahap Akhir (Selesai)</span>
                    </span>

                    <!-- Reset Stages Button -->
                    <button
                        type="button"
                        @click="confirmResetStages"
                        class="flex cursor-pointer items-center gap-1 rounded-xl bg-slate-100 px-3 py-2 text-xs font-bold text-slate-600 transition-colors hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300"
                        title="Reset ulang semua tahap ke awal"
                    >
                        <RotateCcw class="h-3.5 w-3.5" />
                        <span>Reset Tahap</span>
                    </button>
                </div>
            </div>

            <!-- 4 Metric Cards Grid -->
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <!-- Total Votes Cast -->
                <div
                    class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800"
                >
                    <div class="space-y-1">
                        <span
                            class="text-xs font-semibold text-slate-500 dark:text-slate-400"
                            >Total Suara (Tahap {{ selectedStage }})</span
                        >
                        <span
                            class="block text-2xl font-black text-blue-600 dark:text-blue-400"
                            >{{ election.total_votes }}</span
                        >
                    </div>
                    <div
                        class="rounded-xl bg-blue-50 p-3 text-blue-600 dark:bg-blue-950 dark:text-blue-400"
                    >
                        <Vote class="h-6 w-6" />
                    </div>
                </div>

                <!-- Total Registered Voters -->
                <div
                    class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800"
                >
                    <div class="space-y-1">
                        <span
                            class="text-xs font-semibold text-slate-500 dark:text-slate-400"
                            >Total Hak Pilih</span
                        >
                        <span
                            class="block text-2xl font-black text-slate-900 dark:text-white"
                            >{{ election.total_voters }}</span
                        >
                    </div>
                    <div
                        class="rounded-xl bg-slate-100 p-3 text-slate-700 dark:bg-slate-700 dark:text-slate-200"
                    >
                        <Users class="h-6 w-6" />
                    </div>
                </div>

                <!-- Turnout Rate -->
                <div
                    class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800"
                >
                    <div class="space-y-1">
                        <span
                            class="text-xs font-semibold text-slate-500 dark:text-slate-400"
                            >Partisipasi Pemilih</span
                        >
                        <span
                            class="block text-2xl font-black text-emerald-600 dark:text-emerald-400"
                            >{{ election.turnout_percentage }}%</span
                        >
                    </div>
                    <div
                        class="rounded-xl bg-emerald-50 p-3 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400"
                    >
                        <TrendingUp class="h-6 w-6" />
                    </div>
                </div>

                <!-- Leading Candidate -->
                <div
                    class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800"
                >
                    <div class="min-w-0 space-y-1">
                        <span
                            class="text-xs font-semibold text-slate-500 dark:text-slate-400"
                            >Paslon Memimpin (Tahap {{ selectedStage }})</span
                        >
                        <span
                            class="block truncate text-base font-black text-slate-900 dark:text-white"
                        >
                            {{
                                leadingCandidate
                                    ? `Paslon ${leadingCandidate.candidate_number}`
                                    : '-'
                            }}
                        </span>
                    </div>
                    <div
                        class="shrink-0 rounded-xl bg-amber-50 p-3 text-amber-600 dark:bg-amber-950 dark:text-amber-400"
                    >
                        <Trophy class="h-6 w-6" />
                    </div>
                </div>
            </div>

            <!-- Vertical Column Chart (Cart Bar Ke Atas) -->
            <div
                class="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800"
            >
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3
                            class="flex items-center gap-2 text-base font-extrabold text-slate-900 dark:text-white"
                        >
                            <BarChart3
                                class="h-5 w-5 text-blue-600 dark:text-blue-400"
                            />
                            <span>Grafik Perolehan Suara Paslon (Tahap {{ selectedStage }})</span>
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Distribusi perolehan suara tegak (vertical bar chart) masing-masing pasangan calon.
                        </p>
                    </div>

                    <span class="rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-700 dark:bg-blue-950/60 dark:text-blue-300">
                        Total: {{ election.total_votes }} Suara Masuk
                    </span>
                </div>

                <!-- Vertical Bars Container with Horizontal Scroll if many candidates -->
                <div class="overflow-x-auto rounded-2xl border border-slate-100 bg-slate-50/60 p-6 dark:border-slate-800/80 dark:bg-slate-900/40">
                    <div
                        v-if="!results || results.length === 0"
                        class="py-12 text-center text-xs font-semibold text-slate-400"
                    >
                        Belum ada kandidat pada tahap ini
                    </div>

                    <div
                        v-else
                        class="flex min-w-full items-end justify-around gap-6 pt-6 pb-2"
                        :style="{ minWidth: results.length > 8 ? `${results.length * 90}px` : '100%' }"
                    >
                        <div
                            v-for="(candidate, index) in results"
                            :key="candidate.id"
                            class="flex flex-1 min-w-[70px] max-w-[100px] flex-col items-center gap-3 text-center"
                        >
                            <!-- Top Value Badge -->
                            <div class="flex flex-col items-center">
                                <span class="text-xs font-black text-slate-900 dark:text-white">
                                    {{ candidate.votes_count }}
                                </span>
                                <span class="text-[10px] font-bold text-blue-600 dark:text-blue-400">
                                    {{ candidate.percentage }}%
                                </span>
                            </div>

                            <!-- Bar Column Track -->
                            <div class="relative flex h-52 w-full max-w-[70px] items-end justify-center rounded-2xl bg-slate-200/70 p-1 dark:bg-slate-800">
                                <div
                                    :class="[
                                        'w-full rounded-xl transition-all duration-700 ease-out shadow-sm',
                                        candidate.is_qualified === false
                                            ? 'bg-slate-400 dark:bg-slate-600'
                                            : index === 0 && candidate.votes_count > 0
                                                ? 'bg-gradient-to-t from-blue-700 to-blue-500'
                                                : index === 1 && candidate.votes_count > 0
                                                    ? 'bg-gradient-to-t from-emerald-700 to-emerald-500'
                                                    : 'bg-gradient-to-t from-indigo-600 to-cyan-500',
                                    ]"
                                    :style="{
                                        height: `${Math.max(Number(candidate.percentage) || 0, candidate.votes_count > 0 ? 8 : 4)}%`,
                                    }"
                                ></div>
                            </div>

                            <!-- Candidate Number & Name Label -->
                            <div class="w-full space-y-1">
                                <span
                                    :class="[
                                        'inline-flex h-6 w-6 items-center justify-center rounded-lg text-xs font-black',
                                        candidate.is_qualified === false
                                            ? 'bg-slate-300 text-slate-700 dark:bg-slate-700 dark:text-slate-300'
                                            : 'bg-slate-900 text-amber-400 dark:bg-white dark:text-slate-900',
                                    ]"
                                >
                                    {{ candidate.candidate_number }}
                                </span>
                                <p
                                    class="truncate text-xs font-bold text-slate-800 dark:text-slate-200"
                                    :title="candidate.chairman_name"
                                >
                                    {{ candidate.chairman_name }}
                                </p>
                                <span
                                    v-if="candidate.is_qualified === false"
                                    class="inline-block rounded bg-rose-100 px-1.5 py-0.5 text-[9px] font-bold text-rose-700 dark:bg-rose-950 dark:text-rose-300"
                                >
                                    Gugur
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table & Progress Rekapitulasi Perolehan Suara (Full Width & Clean) -->
            <div
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800"
            >
                <div
                    class="flex flex-col gap-3 border-b border-slate-200 p-5 sm:flex-row sm:items-center sm:justify-between dark:border-slate-700"
                >
                    <div>
                        <h3
                            class="flex items-center gap-2 text-base font-extrabold text-slate-900 dark:text-white"
                        >
                            <TrendingUp class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                            <span>Tabel Rekapitulasi & Visualisasi Suara (Tahap {{ selectedStage }})</span>
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Rincian lengkap suara sah, perbandingan progres persentase, dan status kualifikasi paslon.
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700 dark:bg-blue-950 dark:text-blue-300">
                            {{ election.total_votes }} Suara Masuk
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead
                            class="border-b border-slate-200 bg-slate-50 font-bold tracking-wider text-slate-600 uppercase dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-400"
                        >
                            <tr>
                                <th class="w-20 p-4 pl-6 text-center">No Paslon</th>
                                <th class="p-4">Pasangan Calon</th>
                                <th class="w-72 p-4">Visualisasi & Distribusi Suara</th>
                                <th class="w-28 p-4 text-center">Jumlah Suara</th>
                                <th class="w-28 p-4 text-center">Persentase</th>
                                <th class="w-48 p-4 pr-6 text-right">Status Kualifikasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            <tr
                                v-for="(candidate, index) in results"
                                :key="candidate.id"
                                :class="[
                                    'transition-colors',
                                    candidate.is_qualified === false
                                        ? 'bg-slate-50/40 text-slate-400 dark:bg-slate-900/30'
                                        : 'hover:bg-slate-50/80 dark:hover:bg-slate-700/40',
                                ]"
                            >
                                <!-- No Paslon -->
                                <td class="p-4 pl-6 text-center">
                                    <span
                                        :class="[
                                            'inline-flex h-9 w-9 items-center justify-center rounded-xl text-sm font-black shadow-xs',
                                            candidate.is_qualified === false
                                                ? 'bg-slate-200 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
                                                : index === 0 && candidate.votes_count > 0
                                                    ? 'bg-amber-400 text-slate-950'
                                                    : 'bg-slate-900 text-white dark:bg-white dark:text-slate-900',
                                        ]"
                                    >
                                        {{ candidate.candidate_number }}
                                    </span>
                                </td>

                                <!-- Pasangan Calon -->
                                <td class="p-4">
                                    <div class="space-y-0.5">
                                        <div class="font-extrabold text-sm text-slate-900 dark:text-white">
                                            {{ candidate.chairman_name }}
                                        </div>
                                        <div
                                            v-if="candidate.vice_chairman_name"
                                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                                        >
                                            Wakil: {{ candidate.vice_chairman_name }}
                                        </div>
                                    </div>
                                </td>

                                <!-- Visualisasi Progress Bar Horizontal -->
                                <td class="p-4">
                                    <div class="space-y-1.5">
                                        <div class="flex items-center justify-between text-[11px] font-bold">
                                            <span class="text-slate-500 dark:text-slate-400">
                                                {{ candidate.votes_count }} dari {{ election.total_votes }} suara
                                            </span>
                                            <span
                                                :class="[
                                                    candidate.is_qualified === false
                                                        ? 'text-slate-400'
                                                        : 'text-blue-600 dark:text-blue-400',
                                                ]"
                                            >
                                                {{ candidate.percentage }}%
                                            </span>
                                        </div>
                                        <div
                                            class="h-3.5 w-full overflow-hidden rounded-full bg-slate-100 p-0.5 dark:bg-slate-700/80 shadow-inner"
                                        >
                                            <div
                                                :class="[
                                                    'h-full rounded-full transition-all duration-700 ease-out shadow-xs',
                                                    candidate.is_qualified === false
                                                        ? 'bg-slate-400 dark:bg-slate-600'
                                                        : index === 0 && candidate.votes_count > 0
                                                            ? 'bg-gradient-to-r from-blue-600 to-indigo-600'
                                                            : index === 1 && candidate.votes_count > 0
                                                                ? 'bg-gradient-to-r from-emerald-500 to-teal-600'
                                                                : 'bg-gradient-to-r from-sky-500 to-blue-500',
                                                ]"
                                                :style="{ width: `${candidate.percentage}%` }"
                                            ></div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Jumlah Suara -->
                                <td class="p-4 text-center">
                                    <span class="text-base font-black text-slate-900 dark:text-white">
                                        {{ candidate.votes_count }}
                                    </span>
                                </td>

                                <!-- Persentase -->
                                <td class="p-4 text-center">
                                    <span
                                        :class="[
                                            'rounded-lg px-2.5 py-1 text-xs font-black',
                                            candidate.is_qualified === false
                                                ? 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'
                                                : 'bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300',
                                        ]"
                                    >
                                        {{ candidate.percentage }}%
                                    </span>
                                </td>

                                <!-- Status Kualifikasi -->
                                <td class="p-4 pr-6 text-right">
                                    <span
                                        v-if="candidate.is_qualified === false"
                                        class="inline-flex items-center gap-1 rounded-lg bg-rose-100 px-2.5 py-1 text-[11px] font-bold text-rose-800 uppercase dark:bg-rose-950/70 dark:text-rose-300"
                                    >
                                        Gugur (Tahap {{ candidate.eliminated_at_stage || 1 }})
                                    </span>
                                    <span
                                        v-else-if="
                                            leadingCandidate &&
                                            leadingCandidate.id === candidate.id &&
                                            candidate.votes_count > 0
                                        "
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-amber-100 px-3 py-1 text-[11px] font-extrabold text-amber-900 uppercase dark:bg-amber-950/80 dark:text-amber-300"
                                    >
                                        <Trophy class="h-3.5 w-3.5 text-amber-600" />
                                        <span>Peringkat 1</span>
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center rounded-lg bg-emerald-100 px-2.5 py-1 text-[11px] font-bold text-emerald-800 uppercase dark:bg-emerald-950/70 dark:text-emerald-300"
                                    >
                                        Lolos (Peringkat {{ index + 1 }})
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Advance Stage (Saring Top Candidate) -->
            <Teleport to="body">
                <Transition name="modal-fade">
                    <div
                        v-if="showAdvanceModal"
                        class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm"
                        @click.self="showAdvanceModal = false"
                    >
                        <div
                            class="modal-card w-full max-w-md space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-xl dark:border-slate-800 dark:bg-slate-900"
                        >
                            <div class="flex items-start gap-3.5">
                                <div
                                    class="shrink-0 rounded-xl bg-purple-50 p-3 text-purple-600 dark:bg-purple-950 dark:text-purple-400"
                                >
                                    <Filter class="h-6 w-6" />
                                </div>
                                <div class="space-y-1">
                                    <h3
                                        class="text-base font-extrabold text-slate-900 dark:text-white"
                                    >
                                        Saring & Lanjutkan ke Tahap
                                        {{ election.current_stage + 1 }}
                                    </h3>
                                    <p
                                        class="text-xs leading-relaxed font-medium text-slate-600 dark:text-slate-400"
                                    >
                                        Sistem akan otomatis mengurutkan
                                        kandidat aktif berdasarkan
                                        <strong
                                            >perolehan suara sah saat ini (Tahap
                                            {{
                                                election.current_stage
                                            }})</strong
                                        >
                                        dan meloloskan Top kuota yang Anda
                                        tentukan.
                                    </p>
                                </div>
                            </div>

                            <!-- Mode Selection Tabs -->
                            <div class="flex rounded-xl bg-slate-100 p-1 dark:bg-slate-800">
                                <button
                                    type="button"
                                    @click="advanceMode = 'auto'"
                                    :class="[
                                        'flex-1 cursor-pointer rounded-lg py-1.5 text-xs font-bold transition-all',
                                        advanceMode === 'auto'
                                            ? 'bg-white text-purple-700 shadow-sm dark:bg-slate-700 dark:text-purple-300'
                                            : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white',
                                    ]"
                                >
                                    Otomatis (Perolehan Suara)
                                </button>
                                <button
                                    type="button"
                                    @click="advanceMode = 'manual'"
                                    :class="[
                                        'flex-1 cursor-pointer rounded-lg py-1.5 text-xs font-bold transition-all',
                                        advanceMode === 'manual'
                                            ? 'bg-white text-purple-700 shadow-sm dark:bg-slate-700 dark:text-purple-300'
                                            : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white',
                                    ]"
                                >
                                    Pilih Manual Paslon
                                </button>
                            </div>

                            <!-- Auto Mode: Kuota Ranking Suara -->
                            <div
                                v-if="advanceMode === 'auto'"
                                class="space-y-2 rounded-xl border border-slate-200 bg-slate-50 p-4 text-xs dark:border-slate-700 dark:bg-slate-800/60"
                            >
                                <label
                                    class="block font-bold text-slate-700 dark:text-slate-300"
                                >
                                    Target Kuota Paslon Lolos Ke Tahap
                                    {{ election.current_stage + 1 }}:
                                </label>
                                <div class="flex items-center gap-3">
                                    <input
                                        v-model.number="qualifiersCount"
                                        type="number"
                                        min="1"
                                        :max="activeQualifiedCount > 1 ? activeQualifiedCount - 1 : 1"
                                        class="w-full rounded-xl border border-slate-300 bg-white p-3 text-sm font-extrabold text-slate-900 outline-none focus:ring-2 focus:ring-purple-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                    />
                                    <span
                                        class="shrink-0 font-bold text-slate-500"
                                        >dari {{ activeQualifiedCount }} Paslon Aktif</span
                                    >
                                </div>
                                <p
                                    class="text-[11px] text-purple-600 dark:text-purple-400"
                                >
                                    Ketik berapa saja kuota paslon yang Anda inginkan (misal <strong>10</strong>, <strong>8</strong>, atau <strong>4</strong>).
                                </p>
                            </div>

                            <!-- Manual Mode: Checkbox Paslon -->
                            <div
                                v-else
                                class="max-h-56 space-y-2 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs dark:border-slate-700 dark:bg-slate-800/60"
                            >
                                <p class="font-bold text-slate-700 dark:text-slate-300 mb-2">
                                    Centang Paslon yang Berhak Lolos Ke Tahap {{ election.current_stage + 1 }}:
                                </p>
                                <div
                                    v-for="cand in results.filter(c => c.is_qualified !== false)"
                                    :key="cand.id"
                                    class="flex items-center gap-3 rounded-lg border border-slate-200 bg-white p-2.5 dark:border-slate-700 dark:bg-slate-900"
                                >
                                    <input
                                        type="checkbox"
                                        :id="`cand-${cand.id}`"
                                        :value="cand.id"
                                        v-model="selectedCandidateIds"
                                        class="h-4 w-4 rounded border-slate-300 text-purple-600 focus:ring-purple-500"
                                    />
                                    <label :for="`cand-${cand.id}`" class="flex flex-1 items-center justify-between cursor-pointer text-xs font-bold text-slate-800 dark:text-slate-200">
                                        <span>No. {{ cand.candidate_number }} - {{ cand.chairman_name }}</span>
                                        <span class="text-purple-600 dark:text-purple-400 font-extrabold">{{ cand.votes_count }} Suara</span>
                                    </label>
                                </div>
                            </div>

                             <!-- Schedule Options Section -->
                             <div class="space-y-3 rounded-xl border border-slate-200 bg-slate-50 p-3.5 text-xs dark:border-slate-700 dark:bg-slate-800/60">
                                 <div class="flex items-center gap-2 font-bold text-slate-800 dark:text-slate-200">
                                     <Calendar class="h-4 w-4 text-purple-600 dark:text-purple-400" />
                                     <span>Jadwal Waktu Tahap {{ election.current_stage + 1 }}</span>
                                 </div>

                                 <div class="grid grid-cols-2 gap-2">
                                     <label
                                         :class="[
                                             'flex cursor-pointer items-center gap-2 rounded-lg border p-2.5 transition-all',
                                             scheduleOption === 'now'
                                                 ? 'border-purple-500 bg-purple-50/70 text-purple-900 dark:border-purple-500 dark:bg-purple-950/60 dark:text-white'
                                                 : 'border-slate-200 bg-white text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300',
                                         ]"
                                     >
                                         <input
                                             type="radio"
                                             value="now"
                                             v-model="scheduleOption"
                                             class="h-3.5 w-3.5 text-purple-600 focus:ring-purple-500"
                                         />
                                         <span class="font-bold">Mulai Sekarang (Detik Ini)</span>
                                     </label>

                                     <label
                                         :class="[
                                             'flex cursor-pointer items-center gap-2 rounded-lg border p-2.5 transition-all',
                                             scheduleOption === 'custom'
                                                 ? 'border-purple-500 bg-purple-50/70 text-purple-900 dark:border-purple-500 dark:bg-purple-950/60 dark:text-white'
                                                 : 'border-slate-200 bg-white text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300',
                                         ]"
                                     >
                                         <input
                                             type="radio"
                                             value="custom"
                                             v-model="scheduleOption"
                                             class="h-3.5 w-3.5 text-purple-600 focus:ring-purple-500"
                                         />
                                         <span class="font-bold">Pilih Tanggal Mulai</span>
                                     </label>
                                 </div>

                                 <!-- Custom Start Time Input -->
                                 <div v-if="scheduleOption === 'custom'" class="space-y-1 pt-1">
                                     <label class="block font-bold text-slate-700 dark:text-slate-300">
                                         Waktu Mulai Tahap {{ election.current_stage + 1 }}:
                                     </label>
                                     <input
                                         type="datetime-local"
                                         v-model="nextStartAt"
                                         class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-bold text-slate-900 outline-none focus:ring-2 focus:ring-purple-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                     />
                                 </div>

                                 <!-- Optional End Time Input -->
                                 <div class="space-y-1 pt-1">
                                     <label class="block font-bold text-slate-700 dark:text-slate-300">
                                         Waktu Selesai Tahap {{ election.current_stage + 1 }} (Opsional / Perbarui):
                                     </label>
                                     <input
                                         type="datetime-local"
                                         v-model="nextEndAt"
                                         class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-bold text-slate-900 outline-none focus:ring-2 focus:ring-purple-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                     />
                                 </div>
                             </div>

                             <div
                                 class="flex items-center justify-end gap-2 border-t border-slate-100 pt-2 dark:border-slate-800"
                             >
                                 <button
                                     type="button"
                                     @click="showAdvanceModal = false"
                                     class="cursor-pointer rounded-xl bg-slate-100 px-4 py-2.5 text-xs font-bold text-slate-700 transition-colors hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                                 >
                                     Batal
                                 </button>
                                 <button
                                     type="button"
                                     @click="submitAdvanceStage"
                                     class="flex cursor-pointer items-center gap-1.5 rounded-xl bg-purple-600 px-5 py-2.5 text-xs font-bold text-white shadow-sm transition-colors hover:bg-purple-700"
                                 >
                                     <Filter class="h-4 w-4" />
                                     <span>Proses Lanjut Tahap {{ election.current_stage + 1 }}</span>
                                 </button>
                             </div>
                         </div>
                     </div>
                 </Transition>
             </Teleport>

             <!-- Reset Stages Modal Confirmation -->
             <ConfirmModal
                 :show="showResetModal"
                 title="Reset Ulang Semua Tahap"
                 message="Apakah Anda yakin ingin memulihkan tahap ke Tahap 1? Semua status eliminasi kandidat akan dikembalikan menjadi aktif."
                 type="danger"
                 confirm-text="Ya, Reset Ke Tahap 1"
                 cancel-text="Batal"
                 @confirm="submitResetStages"
                 @cancel="showResetModal = false"
             />
         </div>
     </AdminLayout>
 </template>

 <script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { router, Link } from '@inertiajs/vue3';
 import {
     ArrowLeft,
     Vote,
     Users,
     TrendingUp,
     Trophy,
     BarChart3,
     Eye,
     EyeOff,
     CheckCircle2,
     Printer,
     Filter,
     RotateCcw,
     Calendar,
     Clock,
     RefreshCw,
 } from '@lucide/vue';
 import AdminLayout from '@/Layouts/AdminLayout.vue';
 import ConfirmModal from '@/Components/ConfirmModal.vue';

 const props = defineProps<{
     election: {
         id: number;
         title: string;
         description: string | null;
         type: string;
         target_voter: string;
         academic_year: string | null;
         start_at: string;
         end_at: string;
         is_published: boolean;
         is_multi_stage: boolean;
         current_stage: number;
         total_stages: number;
         status: string;
         total_votes: number;
         total_voters: number;
         turnout_percentage: number;
     };
     selectedStage: number;
     activeQualifiedCount: number;
     results: any[];
     leadingCandidate: any | null;
 }>();

 const showAdvanceModal = ref(false);
 const showResetModal = ref(false);
 const advanceMode = ref<'auto' | 'manual'>('auto');
 const qualifiersCount = ref(
     props.activeQualifiedCount > 8 ? 8 : props.activeQualifiedCount > 4 ? 4 : 2,
 );
 const selectedCandidateIds = ref<string[]>(
     props.results.filter((c) => c.is_qualified !== false).map((c) => c.id),
 );
 const scheduleOption = ref<'now' | 'custom'>('now');
 const nextStartAt = ref<string>('');
 const nextEndAt = ref<string>('');

 const changeStage = (stageNum: number) => {
     router.get(
         `/admin/pemilihan/${props.election.id}/hasil`,
         { stage: stageNum },
         { preserveState: true },
     );
 };

 const togglePublish = () => {
     router.post(`/admin/pemilihan/${props.election.id}/toggle-publish`);
 };

 const submitAdvanceStage = () => {
     router.post(
         `/admin/pemilihan/${props.election.id}/advance-stage`,
         {
             mode: advanceMode.value,
             qualifiers_count: qualifiersCount.value,
             selected_candidate_ids: selectedCandidateIds.value,
             schedule_option: scheduleOption.value,
             start_at: nextStartAt.value,
             end_at: nextEndAt.value,
         },
         {
             onFinish: () => {
                 showAdvanceModal.value = false;
             },
         },
     );
 };

const confirmResetStages = () => {
    showResetModal.value = true;
};

const submitResetStages = () => {
    router.post(
        `/admin/pemilihan/${props.election.id}/reset-stages`,
        {},
        {
            onFinish: () => {
                showResetModal.value = false;
            },
        },
    );
};

const windowPrint = () => {
    window.print();
};

const isRefreshing = ref(false);
const lastUpdatedTime = ref('Baru saja');

const updateTimestamp = () => {
    const now = new Date();
    lastUpdatedTime.value = now.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });
};

let pollTimer: ReturnType<typeof setInterval> | null = null;

onMounted(() => {
    updateTimestamp();

    // Polling background update setiap 3 detik secara halus tanpa me-reload browser
    pollTimer = setInterval(() => {
        // Jangan auto-reload jika modal sedang aktif dibuka admin
        if (showAdvanceModal.value || showResetModal.value) {
            return;
        }

        isRefreshing.value = true;
        router.reload({
            only: ['election', 'results', 'leadingCandidate', 'activeQualifiedCount'],
            preserveScroll: true,
            preserveState: true,
            onFinish: () => {
                isRefreshing.value = false;
                updateTimestamp();
            },
        });
    }, 4000);
});

onUnmounted(() => {
    if (pollTimer) {
        clearInterval(pollTimer);
        pollTimer = null;
    }
});
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

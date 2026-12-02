<x-sidebar-layout title="IOTM - {{ config('app.name', 'Portal Himalkom') }}">
    <x-slot name="header">
        <x-breadcrumb :links="['Dashboard' => auth()->user()->getDashboardRoute(), 'IOTM' => null]" />
        <h1 class="mt-2 text-xl font-bold text-slate-900">{{ $selectedMonthName }} {{ $selectedYear }}</h1>
    </x-slot>

    <style>
        .ribbon {
            position: absolute;
            top: -2px;
            left: -2px;
            width: 84px;
            height: 84px;
            overflow: hidden;
            z-index: 10;
        }
        .ribbon span {
            position: absolute;
            display: block;
            width: 110px;
            padding: 6px 0;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
            color: #fff;
            font-size: 9px;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            text-align: center;
            transform: rotate(-45deg);
            top: 18px;
            left: -22px;
        }
    </style>

    <div
        x-data="{
            formOpen: false,
            formData: {
                intern_id: '',
                intern_name: '',
                dept_name: '',
                period_month: {{ $selectedMonth }},
                period_year: {{ $selectedYear }},
                score_punctuality: 0,
                score_initiative: 0,
                score_attendance: 0,
                score_cooperation: 0,
                score_professionalism: 0,
                notes: '',
            },
            openForm(intern) {
                const existing = intern.existing_eval || null;
                this.formData = {
                    intern_id: intern.id,
                    intern_name: intern.name,
                    dept_name: intern.dept_name,
                    period_month: {{ $selectedMonth }},
                    period_year: {{ $selectedYear }},
                    score_punctuality: existing ? existing.score_punctuality : 0,
                    score_initiative: existing ? existing.score_initiative : 0,
                    score_attendance: existing ? existing.score_attendance : 0,
                    score_cooperation: existing ? existing.score_cooperation : 0,
                    score_professionalism: existing ? existing.score_professionalism : 0,
                    notes: existing ? (existing.notes || '') : '',
                };
                this.formOpen = true;
            },
            allScored() {
                return this.formData.score_punctuality >= 1
                    && this.formData.score_initiative >= 1
                    && this.formData.score_attendance >= 1
                    && this.formData.score_cooperation >= 1
                    && this.formData.score_professionalism >= 1;
            },
            computedScore() {
                const sum = Number(this.formData.score_punctuality)
                    + Number(this.formData.score_initiative)
                    + Number(this.formData.score_attendance)
                    + Number(this.formData.score_cooperation)
                    + Number(this.formData.score_professionalism);
                return (sum * 0.2).toFixed(2);
            },
        }"
    >
        @if (session('success'))
            <div class="mb-6 rounded-md bg-emerald-50 border border-emerald-200 px-4 py-3 shadow-sm">
                <p class="text-sm font-medium text-emerald-700">{{ session('success') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-md bg-red-50 border border-red-200 px-4 py-3 shadow-sm">
                <p class="text-sm font-medium text-red-700">{{ $errors->first() }}</p>
            </div>
        @endif

        @if ($showWarning)
            <div class="mb-6 rounded-md bg-[#e53e3e] px-4 py-3 shadow-sm">
                <p class="text-sm font-medium text-white">
                    Penilaian Intern of the Month (IOTM) masih ada yang belum Anda isi. Segera isi sebelum batas waktu (tanggal {{ $iotmEndDay }})!
                </p>
            </div>
        @elseif ($periodStatus === 'future')
            <div class="mb-6 rounded-md bg-blue-50 border border-blue-200 px-4 py-3 shadow-sm">
                <p class="text-sm font-medium text-blue-700">
                    Jadwal pengisian penilaian IOTM bulan ini belum dibuka (dibuka tanggal {{ $iotmStartDay }} – {{ $iotmEndDay }}).
                </p>
            </div>
        @elseif ($periodStatus === 'past')
            <div class="mb-6 rounded-md bg-slate-100 border border-slate-200 px-4 py-3 shadow-sm">
                <p class="text-sm font-medium text-slate-600">
                    Periode pengisian penilaian IOTM untuk bulan ini telah ditutup (tanggal {{ $iotmStartDay }} – {{ $iotmEndDay }}).
                </p>
            </div>
        @endif

        @if ($hasResultAccess && $periodStatus !== 'past')
            <div class="mb-6 rounded-md bg-amber-50 border border-amber-200 px-4 py-3 shadow-sm">
                <p class="text-sm font-medium text-amber-800">
                    Hasil rata-rata penilaian, pemenang IOTM, catatan penilai lain, dan Export PDF akan otomatis ditampilkan setelah melewati tanggal {{ $iotmEndDay }}.
                </p>
            </div>
        @endif

        {{-- Toolbar --}}
        <div class="mb-7 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <h2 class="text-2xl font-bold text-slate-900">Intern of the Month (IOTM)</h2>
                    <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">
                        Penilaian oleh Staf & MD Departemen
                    </span>
                </div>
                <p class="mt-1 text-sm text-slate-500">
                    Penilaian bulanan intern oleh seluruh pengurus tetap di departemennya masing-masing (skala 1–5, bobot 20% per poin).
                </p>
            </div>

            @if ($canExport)
                <a href="{{ route('dashboard.iotm.export', ['month' => $selectedMonth, 'year' => $selectedYear]) }}"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-md bg-[#0b5bd3] px-4 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 3v12M8 11l4 4 4-4" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M5 21h14" stroke-linecap="round" />
                    </svg>
                    Export PDF
                </a>
            @endif
        </div>

        {{-- Filter bulan/tahun --}}
        <form method="GET" class="mb-7 flex flex-col gap-4 rounded-xl border border-slate-200 bg-white px-4 py-4 shadow-sm md:flex-row md:items-center md:justify-between">
            <div class="flex flex-wrap items-center gap-3">
                <label for="month" class="text-sm font-medium text-slate-600">Month:</label>
                <select id="month" name="month" onchange="this.form.submit()"
                    class="h-10 rounded-md border-slate-300 bg-slate-50 text-sm font-semibold text-slate-800 focus:border-[#0b5bd3] focus:ring-[#0b5bd3]">
                    @foreach ($months as $mn => $mName)
                        <option value="{{ $mn }}" @selected($selectedMonth === $mn)>{{ $mName }}</option>
                    @endforeach
                </select>
                <select name="year" onchange="this.form.submit()"
                    class="h-10 rounded-md border-slate-300 bg-slate-50 text-sm font-semibold text-slate-800 focus:border-[#0b5bd3] focus:ring-[#0b5bd3]">
                    @foreach ($years as $yr)
                        <option value="{{ $yr }}" @selected($selectedYear === $yr)>{{ $yr }}</option>
                    @endforeach
                </select>
            </div>
            <div class="text-xs font-medium text-slate-500">
                Periode pengisian: Tanggal {{ $iotmStartDay }} – {{ $iotmEndDay }}
            </div>
        </form>

        {{-- List Intern per Departemen --}}
        @if ($departmentGroups->isEmpty() || $departmentGroups->every(fn ($d) => $d['interns_count'] === 0))
            <div class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center">
                <p class="text-base font-semibold text-slate-800">Belum ada data intern untuk ditampilkan.</p>
                <p class="mt-2 text-sm text-slate-500">Data intern dapat ditambahkan oleh Superadmin melalui panel admin.</p>
            </div>
        @else
            <div class="space-y-10">
                @foreach ($departmentGroups as $department)
                    @if ($department['interns_count'] > 0)
                        <section>
                            <div class="mb-5 flex items-end justify-between gap-4">
                                <div class="flex min-w-0 items-center gap-3">
                                    <span class="h-8 w-3 rounded-full bg-amber-400"></span>
                                    <div class="min-w-0">
                                        <h3 class="truncate text-lg font-bold text-slate-800">{{ $department['name'] }}</h3>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                            {{ $department['interns_count'] }} intern
                                        </p>
                                    </div>
                                </div>

                                @if ($canViewResults)
                                    <div class="text-right">
                                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Division Average</p>
                                        <p class="text-xl font-bold text-[#0b5bd3]">
                                            {{ $department['average_score'] !== null ? number_format($department['average_score'], 2) : '–' }}
                                        </p>
                                    </div>
                                @endif
                            </div>

                            @foreach ($department['grouped_interns'] as $subDivisionName => $interns)
                                <div class="mt-6 mb-4">
                                    <h4 class="text-md font-bold text-slate-700 border-b border-slate-200 pb-2">{{ $subDivisionName }}</h4>
                                </div>
                                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
                                    @foreach ($interns as $intern)
                                        @php
                                            $isIotm = $canViewResults
                                                && isset($department['best_interns'][$subDivisionName])
                                                && $department['best_interns'][$subDivisionName] === $intern['id'];
                                            $modalPayload = json_encode([
                                                'id' => $intern['id'],
                                                'name' => $intern['name'],
                                                'dept_name' => $intern['department_name'].($intern['sub_division'] ? ' — '.$intern['sub_division'] : ''),
                                                'existing_eval' => $intern['existing_eval'],
                                            ]);
                                        @endphp

                                        <div class="relative overflow-hidden rounded-xl border {{ $isIotm ? 'border-amber-300 ring-1 ring-amber-200' : 'border-slate-200' }} bg-white p-5 shadow-sm flex flex-col justify-between">
                                            @if ($isIotm)
                                                <div class="ribbon">
                                                    <span>IOTM</span>
                                                </div>
                                            @endif

                                            <div>
                                                <div class="flex items-start justify-between gap-3 {{ $isIotm ? 'pl-8' : '' }}">
                                                    <div class="flex items-center gap-3 min-w-0">
                                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-blue-50 text-sm font-bold text-[#0b5bd3]">
                                                            {{ $intern['initials'] }}
                                                        </div>
                                                        <div class="min-w-0">
                                                            <p class="truncate text-sm font-bold text-slate-900">{{ $intern['name'] }}</p>
                                                            <p class="truncate text-xs text-slate-500">{{ $intern['sub_division'] ?: $intern['department_name'] }}</p>
                                                        </div>
                                                    </div>

                                                    @if ($canViewResults)
                                                        <div class="text-right shrink-0">
                                                            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Rata-rata</p>
                                                            <p class="text-lg font-extrabold text-[#0b5bd3]">
                                                                {{ $intern['avg_final_score'] !== null ? number_format($intern['avg_final_score'], 2) : '–' }}
                                                            </p>
                                                            <p class="text-[11px] text-slate-400">{{ $intern['evaluator_count'] }} penilai</p>
                                                        </div>
                                                    @else
                                                        <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $intern['actor_has_filled'] ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                                            {{ $intern['actor_has_filled'] ? 'Sudah Dinilai' : 'Belum Dinilai' }}
                                                        </span>
                                                    @endif
                                                </div>

                                                {{-- Breakdown skor --}}
                                                <div class="mt-4 space-y-2 border-t border-slate-100 pt-3 text-xs">
                                                    @if ($canViewResults)
                                                        @foreach ($questions as $q)
                                                            <div class="flex items-center justify-between text-slate-600">
                                                                <span class="truncate pr-2">{{ $q['title'] }} (20%)</span>
                                                                <span class="font-semibold text-slate-900">
                                                                    {{ $intern['avg_scores'] ? number_format($intern['avg_scores'][$q['title']], 2) : '–' }}
                                                                </span>
                                                            </div>
                                                        @endforeach

                                                        @if (! empty($intern['notes_list']))
                                                            <div x-data="{ showNotes: false }" class="pt-2 border-t border-slate-100">
                                                                <button type="button" @click="showNotes = !showNotes"
                                                                    class="text-xs font-semibold text-[#0b5bd3] hover:underline">
                                                                    <span x-text="showNotes ? 'Sembunyikan Catatan' : 'Lihat Catatan ({{ count($intern['notes_list']) }})'"></span>
                                                                </button>
                                                                <div x-show="showNotes" x-cloak class="mt-2 space-y-1.5 max-h-32 overflow-y-auto">
                                                                    @foreach ($intern['notes_list'] as $note)
                                                                        <div class="rounded-lg bg-slate-50 p-2 text-[11px] text-slate-700 border border-slate-100">
                                                                            <span class="font-semibold text-slate-900">{{ $note['evaluator_name'] }}:</span>
                                                                            {{ $note['notes'] }}
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @else
                                                        @foreach ($questions as $q)
                                                            <div class="flex items-center justify-between text-slate-600">
                                                                <span class="truncate pr-2">{{ $q['title'] }} (20%)</span>
                                                                <span class="font-semibold text-slate-900">
                                                                    {{ $intern['existing_eval'] ? $intern['existing_eval'][$q['key']].' / 5' : '–' }}
                                                                </span>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="mt-5 pt-3 border-t border-slate-100">
                                                @if ($intern['can_evaluate'])
                                                    @if ($intern['is_open'])
                                                        <button type="button"
                                                            @click='openForm({{ $modalPayload }})'
                                                            class="w-full rounded-lg px-4 py-2.5 text-sm font-semibold transition {{ $intern['actor_has_filled'] ? 'bg-slate-100 text-slate-800 hover:bg-slate-200' : 'bg-[#0b5bd3] text-white hover:bg-blue-700' }}">
                                                            {{ $intern['actor_has_filled'] ? 'Edit Penilaian Saya' : 'Isi Penilaian' }}
                                                        </button>
                                                    @else
                                                        <button type="button" disabled
                                                            class="w-full rounded-lg bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-400 cursor-not-allowed">
                                                            {{ $periodStatus === 'future' ? "Dibuka Tgl {$iotmStartDay}–{$iotmEndDay}" : 'Periode Ditutup' }}
                                                        </button>
                                                    @endif
                                                @else
                                                    <div class="text-center text-xs font-medium text-slate-400 py-1.5">
                                                        Mode Lihat (PJ / Admin)
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </section>
                    @endif
                @endforeach
            </div>
        @endif

        {{-- ==================== MODAL PENILAIAN ==================== --}}
        <div x-show="formOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm"
            @keydown.escape.window="formOpen = false">
            <div class="w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl" @click.outside="formOpen = false">
                <div class="flex items-start justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900" x-text="formData.intern_name"></h3>
                        <p class="text-xs font-medium text-slate-500" x-text="formData.dept_name"></p>
                    </div>
                    <button type="button" @click="formOpen = false" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 6 6 18M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('dashboard.iotm.store') }}" class="mt-5 space-y-5">
                    @csrf
                    <input type="hidden" name="intern_id" :value="formData.intern_id">
                    <input type="hidden" name="period_month" :value="formData.period_month">
                    <input type="hidden" name="period_year" :value="formData.period_year">

                    @foreach ($questions as $index => $q)
                        <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-bold text-slate-900">{{ $index + 1 }}. {{ $q['title'] }}</p>
                                    <p class="mt-1 text-xs leading-relaxed text-slate-600">{{ $q['description'] }}</p>
                                </div>
                                <span class="shrink-0 rounded-full bg-blue-100 px-2.5 py-0.5 text-[11px] font-semibold text-blue-700">20%</span>
                            </div>

                            <input type="hidden" name="{{ $q['key'] }}" :value="formData.{{ $q['key'] }}">

                            <div class="mt-3 grid grid-cols-5 gap-2">
                                @for ($score = 1; $score <= 5; $score++)
                                    <button type="button"
                                        @click="formData.{{ $q['key'] }} = {{ $score }}"
                                        :class="formData.{{ $q['key'] }} == {{ $score }} ? 'bg-[#0b5bd3] text-white border-[#0b5bd3] shadow-sm' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-100'"
                                        class="rounded-lg border py-2 text-sm font-bold transition">
                                        {{ $score }}
                                    </button>
                                @endfor
                            </div>
                        </div>
                    @endforeach

                    <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                        <label for="iotm-notes" class="block text-sm font-bold text-slate-900">
                            Catatan <span class="text-xs font-normal text-slate-400">(Opsional)</span>
                        </label>
                        <textarea id="iotm-notes" name="notes" rows="3" x-model="formData.notes"
                            placeholder="Tambahkan catatan, apresiasi, atau saran untuk intern ini (opsional)..."
                            class="mt-2 w-full rounded-lg border-slate-300 bg-white text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#0b5bd3] focus:ring-[#0b5bd3]"></textarea>
                    </div>

                    <div class="flex items-center justify-between rounded-xl bg-blue-50 px-4 py-3 border border-blue-100">
                        <span class="text-sm font-semibold text-slate-700">Total Skor Anda (Maks. 5.00):</span>
                        <span class="text-xl font-extrabold text-[#0b5bd3]" x-text="computedScore()"></span>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="formOpen = false"
                            class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit" :disabled="!allScored()"
                            :class="allScored() ? 'bg-[#0b5bd3] hover:bg-blue-700 text-white' : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                            class="rounded-lg px-5 py-2 text-sm font-semibold transition">
                            Simpan Penilaian
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-sidebar-layout>

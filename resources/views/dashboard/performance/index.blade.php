<x-sidebar-layout title="Performance Evaluation - {{ config('app.name', 'Portal Himalkom') }}">
    <x-slot name="header">
        <x-breadcrumb :links="['Dashboard' => auth()->user()->getDashboardRoute(), 'Performance' => null]" />
        <h1 class="mt-2 text-xl font-bold text-slate-900">{{ $selectedMonthName }} {{ $selectedYear }}</h1>
    </x-slot>

    <style>
        /* Best Performer ribbon */
        .ribbon {
            position: absolute;
            top: -2px;
            left: -2px;
            width: 84px;
            height: 84px;
            overflow: hidden;
            z-index: 10;
        }
        .ribbon::before, .ribbon::after {
            position: absolute;
            content: '';
            border: 4px solid #b45309;
        }
        .ribbon span {
            position: absolute;
            display: block;
            width: 110px;
            padding: 6px 0;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            box-shadow: 0 2px 8px rgba(0,0,0,0.25);
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

        /* Slider styling */
        input[type=range] {
            -webkit-appearance: none;
            height: 6px;
            border-radius: 9999px;
            background: #cbd5e1;
            outline: none;
        }
        input[type=range]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #0b5bd3;
            cursor: pointer;
            box-shadow: 0 1px 4px rgba(11,91,211,0.4);
        }
        input[type=range]::-moz-range-thumb {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #0b5bd3;
            cursor: pointer;
            border: none;
        }
    </style>

    <div
        x-data="{
            showBestOnly: false,
            showMyDivisionOnly: false,
            myDivisionIds: {{ json_encode($myDivisionIds) }},

            /* ---- Modal Isi Penilaian ---- */
            formOpen: false,
            formData: {
                evaluated_id: '',
                department_id: '',
                period_month: {{ $selectedMonth }},
                period_year: {{ $selectedYear }},
                member_name: '',
                dept_name: '',
                score_attendance: 0,
                score_commitment: 0,
                score_contribution: 0,
                score_initiative: 0,
                notes: '',
            },
            openForm(member) {
                this.formData = {
                    evaluated_id: member.id,
                    department_id: member.department_id,
                    period_month: {{ $selectedMonth }},
                    period_year: {{ $selectedYear }},
                    member_name: member.name,
                    dept_name: member.dept_name,
                    score_attendance: 0,
                    score_commitment: 0,
                    score_contribution: 0,
                    score_initiative: 0,
                    notes: '',
                };
                this.formOpen = true;
            },

            /* ---- Modal Lihat Detail ---- */
            detailOpen: false,
            detailUrl: '',
            openDetail(url) {
                this.detailUrl = url;
                this.detailOpen = true;
            },
        }"
    >

            @if ($showWarning)
                @if ($periodStatus === 'past')
                    <div class="mb-6 rounded-md bg-slate-100 border border-slate-200 px-4 py-3 shadow-sm">
                        <p class="text-sm font-medium text-slate-600">Waktu penilaian untuk bulan ini telah berlalu. Ada form anggota yang belum diselesaikan.</p>
                    </div>
                @elseif ($periodStatus === 'future')
                    <div class="mb-6 rounded-md bg-blue-50 border border-blue-200 px-4 py-3 shadow-sm">
                        <p class="text-sm font-medium text-blue-700">Jadwal pengisian form bulan ini belum dibuka (hanya dapat diakses mulai tanggal 25 hingga tanggal 5 bulan berikutnya).</p>
                    </div>
                @else
                    <div class="mb-6 rounded-md bg-[#e53e3e] px-4 py-3 shadow-sm">
                        <p class="text-sm font-medium text-white">Form penilaian staff bulan ini belum diisi seluruhnya, segera isi sebelum batas waktu (tanggal 5)!</p>
                    </div>
                @endif
            @endif

            {{-- Toolbar --}}
            <div class="mb-7 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <h2 class="text-2xl font-bold text-slate-900">Performance Evaluation</h2>
                        @if ($canEvaluate)
                            <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">
                                MD / PJS / SC Roles Only
                            </span>
                        @endif
                    </div>
                    <p class="mt-1 text-sm text-slate-500">Penilaian bulanan anggota per divisi.</p>
                </div>

                @if ($canExport)
                    <a href="{{ route('dashboard.performance.export', ['month' => $selectedMonth, 'year' => $selectedYear]) }}"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-md bg-[#0b5bd3] px-4 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 3v12M8 11l4 4 4-4" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M5 21h14" stroke-linecap="round" />
                        </svg>
                        Export Report
                    </a>
                @endif
            </div>

            {{-- Filter bulan/tahun + toggle view --}}
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
                <div class="flex items-center gap-2">
                    @if ($viewMode === 'divisions')
                        <label class="flex items-center gap-2 cursor-pointer mr-2">
                            <div class="relative">
                                <input type="checkbox" x-model="showMyDivisionOnly" class="sr-only">
                                <div class="block h-6 w-10 rounded-full transition duration-300" :class="showMyDivisionOnly ? 'bg-blue-500' : 'bg-slate-300'"></div>
                                <div class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition duration-300" :class="showMyDivisionOnly ? 'translate-x-4' : ''"></div>
                            </div>
                            <span class="text-sm font-bold" :class="showMyDivisionOnly ? 'text-blue-600' : 'text-slate-600'">My Division</span>
                        </label>
                        <div class="hidden sm:block h-6 w-px bg-slate-300 mr-1"></div>
                    @endif

                    @if ($viewMode === 'staff')
                        <label class="flex items-center gap-2 cursor-pointer mr-2">
                            <div class="relative">
                                <input type="checkbox" x-model="showBestOnly" class="sr-only">
                                <div class="block h-6 w-10 rounded-full transition duration-300" :class="showBestOnly ? 'bg-amber-400' : 'bg-slate-300'"></div>
                                <div class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition duration-300" :class="showBestOnly ? 'translate-x-4' : ''"></div>
                            </div>
                            <span class="text-sm font-bold" :class="showBestOnly ? 'text-amber-500' : 'text-slate-600'">Best Only</span>
                        </label>
                        <div class="hidden sm:block h-6 w-px bg-slate-300 mr-1"></div>
                    @endif
                    <span class="text-sm font-medium text-slate-600">View:</span>
                    <a href="{{ route('dashboard.performance.index', ['month' => $selectedMonth, 'year' => $selectedYear, 'view' => 'divisions']) }}"
                        class="rounded-md px-4 py-2 text-sm font-semibold transition {{ $viewMode === 'divisions' ? 'bg-[#edf3ff] text-[#0b5bd3] ring-1 ring-blue-200' : 'text-slate-500 hover:bg-slate-100' }}">
                        Divisions
                    </a>
                    <a href="{{ route('dashboard.performance.index', ['month' => $selectedMonth, 'year' => $selectedYear, 'view' => 'staff']) }}"
                        class="rounded-md px-4 py-2 text-sm font-semibold transition {{ $viewMode === 'staff' ? 'bg-[#edf3ff] text-[#0b5bd3] ring-1 ring-blue-200' : 'text-slate-500 hover:bg-slate-100' }}">
                        Staff
                    </a>
                </div>
            </form>

            {{-- ==================== DIVISIONS VIEW ==================== --}}
            @if ($viewMode === 'divisions')
                @if ($departmentGroups->isEmpty())
                    <div class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center">
                        <p class="text-base font-semibold text-slate-800">Belum ada anggota untuk ditampilkan.</p>
                        <p class="mt-2 text-sm text-slate-500">Data akan muncul setelah user memiliki departemen di panel Super Admin.</p>
                    </div>
                @else
                    <div class="space-y-10">
                        @foreach ($departmentGroups as $department)
                            <section x-show="!showMyDivisionOnly || myDivisionIds.includes('{{ $department['id'] }}')">
                                {{-- Dept header --}}
                                <div class="mb-5 flex items-end justify-between gap-4">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <span class="h-8 w-3 rounded-full bg-amber-400"></span>
                                        <div class="min-w-0">
                                            <h3 class="truncate text-lg font-bold text-slate-800">{{ $department['name'] }}</h3>
                                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                                {{ $department['members_count'] }} anggota
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Division Average</p>
                                        <p class="text-xl font-bold text-[#0b5bd3]">
                                            {{ $department['average_score'] !== null ? $department['average_score'] : '–' }}
                                        </p>
                                    </div>
                                </div>

                                @if($department['members_count'] == 0)
                                    <div class="col-span-full rounded-xl border border-dashed border-slate-300 p-8 text-center mt-4">
                                        <p class="text-sm font-medium text-slate-500">Belum ada anggota (staf) di divisi ini yang dapat dinilai.</p>
                                    </div>
                                @else
                                    @foreach ($department['grouped_members'] as $subDivisionName => $members)
                                        <div class="mt-6 mb-4">
                                            <h4 class="text-md font-bold text-slate-700 border-b border-slate-200 pb-2">{{ $subDivisionName }}</h4>
                                        </div>
                                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
                                            @foreach ($members as $member)
                                        @php $isBest = isset($department['best_performers'][$subDivisionName]) && $department['best_performers'][$subDivisionName] === $member['id'] && $member['combined_score'] !== null; @endphp
                                        <article class="relative overflow-hidden rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
                                            {{-- Best Performer ribbon --}}
                                            @if ($isBest)
                                                <div class="ribbon">
                                                    <span>BEST<br>PERFORMER</span>
                                                </div>
                                            @endif

                                            {{-- Accent bar kiri --}}
                                            <div class="absolute inset-y-0 left-0 w-1 {{ $isBest ? 'bg-amber-400' : 'bg-blue-200' }}"></div>

                                            {{-- Header card --}}
                                            <div class="flex items-start justify-between gap-4">
                                                <div class="flex min-w-0 items-center gap-3">
                                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $isBest ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-[#0b5bd3]' }} text-sm font-bold">
                                                        {{ $member['initials'] }}
                                                    </div>
                                                    <div class="min-w-0">
                                                        <h4 class="truncate text-base font-bold text-slate-900">{{ $member['name'] }}</h4>
                                                        <p class="truncate text-xs text-slate-400">{{ $member['role_title'] }}</p>
                                                    </div>
                                                </div>
                                                <div class="shrink-0 text-right">
                                                    <p class="text-[10px] font-medium text-slate-500">Nilai Akhir</p>
                                                    <p class="text-xl font-bold {{ $member['combined_score'] !== null ? 'text-[#0b5bd3]' : 'text-slate-300' }}">
                                                        {{ $member['combined_score'] !== null ? $member['combined_score'] : '–' }}
                                                    </p>
                                                </div>
                                            </div>

                                            {{-- Skor kriteria --}}
                                            <div class="mt-4 space-y-2.5">
                                                @foreach ($member['scores'] as $label => $score)
                                                    <div class="flex items-center justify-between gap-4 text-sm">
                                                        <span class="min-w-0 text-slate-500">{{ $label }}</span>
                                                        <span class="shrink-0 text-base tracking-tight font-medium">
                                                            @if ($score !== null)
                                                                @if ($label === 'Kehadiran (10%)')
                                                                    @php $starC = (int) round($score / 20); @endphp
                                                                    @for ($i = 1; $i <= 5; $i++)
                                                                        <span class="{{ $i <= $starC ? 'text-amber-400' : 'text-slate-300' }}">★</span>
                                                                    @endfor
                                                                @else
                                                                    <span class="text-slate-700">{{ $score }}</span>
                                                                @endif
                                                            @else
                                                                <span class="text-slate-300 font-bold">–</span>
                                                            @endif
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>

                                            {{-- Tombol aksi --}}
                                            <div class="mt-5">
                                                @if ($member['button_status'] === 'evaluate')
                                                    <button type="button"
                                                        @click="openForm({
                                                            id: '{{ $member['id'] }}',
                                                            name: '{{ addslashes($member['name']) }}',
                                                            dept_name: '{{ addslashes($member['department_name']) }}',
                                                            department_id: '{{ $member['department_id'] }}'
                                                        })"
                                                        class="h-9 w-full rounded-md bg-[#0b5bd3] px-4 text-sm font-semibold text-white transition hover:bg-blue-700 active:scale-[0.98] shadow-sm">
                                                        Isi Penilaian
                                                    </button>

                                                @elseif ($member['button_status'] === 'filled')
                                                    <button type="button" disabled
                                                        class="h-9 w-full rounded-md bg-blue-50 px-4 text-sm font-semibold text-blue-400 cursor-not-allowed border border-blue-100 flex items-center justify-center gap-2">
                                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M20 6L9 17l-5-5" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                        Sudah Diisi
                                                    </button>

                                                @elseif ($member['button_status'] === 'detail')
                                                    <a href="{{ route('dashboard.performance.show', [$member['id'], 'month' => $member['period_month'], 'year' => $member['period_year']]) }}"
                                                        class="flex h-9 w-full items-center justify-center rounded-md bg-blue-100 px-4 text-sm font-semibold text-[#0b5bd3] transition hover:bg-blue-200">
                                                        Lihat Detail
                                                    </a>

                                                @elseif ($member['button_status'] === 'self')
                                                    <button type="button" disabled
                                                        class="h-9 w-full rounded-md bg-slate-100 px-4 text-sm font-medium text-slate-400 cursor-not-allowed">
                                                        Diri Sendiri (Tidak Dinilai)
                                                    </button>
                                                @elseif (in_array($member['button_status'], ['closed_past', 'closed_future']))
                                                    <button type="button" disabled
                                                        class="h-9 w-full rounded-md bg-rose-50 px-4 text-sm font-medium text-rose-400 cursor-not-allowed border border-rose-100">
                                                        Di Luar Jadwal (Tgl 25 - 5)
                                                    </button>
                                                @else
                                                    {{-- view_only: belum ada nilai, user tidak bisa menilai --}}
                                                    <button type="button" disabled
                                                        class="h-9 w-full rounded-md bg-slate-100 px-4 text-sm font-medium text-slate-400 cursor-not-allowed">
                                                        Belum Dinilai
                                                    </button>
                                                @endif
                                            </div>
                                        </article>
                                            @endforeach
                                        </div>
                                    @endforeach
                                @endif
                            </section>
                        @endforeach
                    </div>
                @endif

            {{-- ==================== STAFF VIEW ==================== --}}
            @else

                {{-- VIEW: Table (Responsive) --}}
                <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Nama</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Departemen</th>
                                <th class="hidden md:table-cell px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">Kehadiran (10%)</th>
                                <th class="hidden md:table-cell px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">Keaktifan Komunikasi (30%)</th>
                                <th class="hidden md:table-cell px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">Sikap Disiplin (30%)</th>
                                <th class="hidden md:table-cell px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">Inovasi Inisiatif (30%)</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">Nilai Akhir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($departmentGroups as $dept)
                                @foreach ($dept['grouped_members'] as $subDivisionName => $members)
                                @foreach ($members as $member)
                                @php $isBest = isset($dept['best_performers'][$subDivisionName]) && $dept['best_performers'][$subDivisionName] === $member['id'] && $member['combined_score'] !== null; @endphp
                                <tr x-show="(!showMyDivisionOnly || myDivisionIds.includes('{{ $dept['id'] }}')) && (!showBestOnly || {{ $isBest ? 'true' : 'false' }})" class="transition hover:bg-slate-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $isBest ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-[#0b5bd3]' }} text-xs font-bold">
                                                {{ $member['initials'] }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                                                    {{ $member['name'] }}
                                                    @if ($isBest)
                                                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700">★ BEST</span>
                                                    @endif
                                                </p>
                                                <p class="text-xs text-slate-400">{{ $member['role_title'] }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-medium text-slate-700">{{ $member['department_name'] }}</p>
                                        <p class="text-xs text-slate-400 mt-0.5">{{ $member['sub_division'] ?: 'Tanpa Divisi' }}</p>
                                    </td>
                                    <td class="hidden md:table-cell px-4 py-4 text-center text-sm">
                                        @if ($member['scores']['Kehadiran (10%)'] !== null)
                                            @php $starC = (int) round($member['scores']['Kehadiran (10%)'] / 20); @endphp
                                            <span class="text-amber-400">@for ($i = 1; $i <= 5; $i++)<span>{{ $i <= $starC ? '★' : '☆' }}</span>@endfor</span>
                                        @else
                                            <span class="text-slate-300 font-semibold">–</span>
                                        @endif
                                    </td>
                                    <td class="hidden md:table-cell px-4 py-4 text-center text-sm">
                                        @if ($member['scores']['Keaktifan Komunikasi (30%)'] !== null)
                                            <span class="font-bold text-slate-700">{{ $member['scores']['Keaktifan Komunikasi (30%)'] }}</span>
                                        @else
                                            <span class="text-slate-300 font-semibold">–</span>
                                        @endif
                                    </td>
                                    <td class="hidden md:table-cell px-4 py-4 text-center text-sm">
                                        @if ($member['scores']['Sikap Disiplin (30%)'] !== null)
                                            <span class="font-bold text-slate-700">{{ $member['scores']['Sikap Disiplin (30%)'] }}</span>
                                        @else
                                            <span class="text-slate-300 font-semibold">–</span>
                                        @endif
                                    </td>
                                    <td class="hidden md:table-cell px-4 py-4 text-center text-sm">
                                        @if ($member['scores']['Inovasi Inisiatif (30%)'] !== null)
                                            <span class="font-bold text-slate-700">{{ $member['scores']['Inovasi Inisiatif (30%)'] }}</span>
                                        @else
                                            <span class="text-slate-300 font-semibold">–</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <span class="text-lg font-bold {{ $member['combined_score'] !== null ? 'text-[#0b5bd3]' : 'text-slate-300' }}">
                                            {{ $member['combined_score'] ?? '–' }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-sm text-slate-400">
                                        Tidak ada data untuk ditampilkan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        </table>
                    </div>
                </div>
            @endif

        {{-- ============================================================
             MODAL: FORM ISI PENILAIAN
             ============================================================ --}}
        <div x-show="formOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm"
                x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                @click="formOpen = false"></div>

            {{-- Modal panel --}}
            <div class="relative w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl"
                x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">

                {{-- Header --}}
                <div class="flex items-start justify-between border-b border-slate-100 px-6 py-5">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900" x-text="formData.member_name"></h3>
                        <p class="text-sm font-medium text-slate-500" x-text="formData.dept_name"></p>
                    </div>
                    <button @click="formOpen = false" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 6 6 18M6 6l12 12" stroke-linecap="round" />
                        </svg>
                    </button>
                </div>

                {{-- Form body --}}
                <form method="POST" action="{{ route('dashboard.performance.store') }}">
                    @csrf
                    <input type="hidden" name="evaluated_id" :value="formData.evaluated_id">
                    <input type="hidden" name="department_id" :value="formData.department_id">
                    <input type="hidden" name="period_month" :value="formData.period_month">
                    <input type="hidden" name="period_year" :value="formData.period_year">
                    <input type="hidden" name="score_attendance" :value="formData.score_attendance">
                    <input type="hidden" name="score_commitment" :value="formData.score_commitment">
                    <input type="hidden" name="score_contribution" :value="formData.score_contribution">
                    <input type="hidden" name="score_initiative" :value="formData.score_initiative">

                    <div class="max-h-[65vh] overflow-y-auto px-6 py-5 space-y-6">

                        {{-- Kehadiran – Rating Bintang --}}
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">
                                Kehadiran
                                <span class="ml-1 text-xs font-normal text-slate-400">(1 bintang = 20 poin, bobot 10%)</span>
                            </label>
                            <div class="flex items-center gap-4 rounded-xl bg-slate-50 p-4 border border-slate-100">
                                <div class="flex flex-1 justify-around">
                                    <template x-for="star in 5" :key="star">
                                        <button type="button"
                                            @click="formData.score_attendance = star * 20"
                                            class="p-1 transition-all hover:scale-110 focus:outline-none"
                                            :class="formData.score_attendance >= (star * 20) ? 'text-amber-400 drop-shadow-sm' : 'text-slate-300'">
                                            <svg class="h-9 w-9 fill-current" viewBox="0 0 24 24">
                                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                            </svg>
                                        </button>
                                    </template>
                                </div>
                                <div class="w-16 text-center">
                                    <p class="text-xl font-bold text-[#0b5bd3]" x-text="formData.score_attendance"></p>
                                    <p class="text-[10px] text-slate-400">poin</p>
                                </div>
                            </div>
                        </div>

                        {{-- Komitmen – Number Input --}}
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">
                                Keaktifan Komunikasi
                                <span class="ml-1 text-xs font-normal text-slate-400">(0 - 100 poin, bobot 30%)</span>
                            </label>
                            <div class="flex items-center gap-4 rounded-xl bg-slate-50 p-4 border border-slate-100">
                                <div class="flex flex-1 items-center justify-between px-2 sm:px-6">
                                    <button type="button" 
                                        @click="formData.score_commitment = Math.max(0, parseInt(formData.score_commitment || 0) - 5)"
                                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm border border-slate-200 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-[#0b5bd3]">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M5 12h14" stroke-linecap="round"/>
                                        </svg>
                                    </button>
                                    
                                    <input type="number" min="0" max="100" x-model.number="formData.score_commitment" 
                                        class="w-24 rounded-lg border-slate-200 text-center text-2xl font-bold text-[#0b5bd3] focus:border-[#0b5bd3] focus:ring-[#0b5bd3] [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" />

                                    <button type="button" 
                                        @click="formData.score_commitment = Math.min(100, parseInt(formData.score_commitment || 0) + 5)"
                                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm border border-slate-200 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-[#0b5bd3]">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 5v14M5 12h14" stroke-linecap="round"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="w-16 shrink-0 text-center flex flex-col justify-center">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Maks</p>
                                    <p class="text-sm font-bold text-slate-600">100</p>
                                </div>
                            </div>
                        </div>

                        {{-- Kontribusi – Number Input --}}
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">
                                Sikap Disiplin
                                <span class="ml-1 text-xs font-normal text-slate-400">(0 - 100 poin, bobot 30%)</span>
                            </label>
                            <div class="flex items-center gap-4 rounded-xl bg-slate-50 p-4 border border-slate-100">
                                <div class="flex flex-1 items-center justify-between px-2 sm:px-6">
                                    <button type="button" 
                                        @click="formData.score_contribution = Math.max(0, parseInt(formData.score_contribution || 0) - 5)"
                                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm border border-slate-200 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-[#0b5bd3]">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M5 12h14" stroke-linecap="round"/>
                                        </svg>
                                    </button>
                                    
                                    <input type="number" min="0" max="100" x-model.number="formData.score_contribution" 
                                        class="w-24 rounded-lg border-slate-200 text-center text-2xl font-bold text-[#0b5bd3] focus:border-[#0b5bd3] focus:ring-[#0b5bd3] [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" />

                                    <button type="button" 
                                        @click="formData.score_contribution = Math.min(100, parseInt(formData.score_contribution || 0) + 5)"
                                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm border border-slate-200 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-[#0b5bd3]">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 5v14M5 12h14" stroke-linecap="round"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="w-16 shrink-0 text-center flex flex-col justify-center">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Maks</p>
                                    <p class="text-sm font-bold text-slate-600">100</p>
                                </div>
                            </div>
                        </div>

                        {{-- Inisiatif – Number Input --}}
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">
                                Inovasi Inisiatif
                                <span class="ml-1 text-xs font-normal text-slate-400">(0 - 100 poin, bobot 30%)</span>
                            </label>
                            <div class="flex items-center gap-4 rounded-xl bg-slate-50 p-4 border border-slate-100">
                                <div class="flex flex-1 items-center justify-between px-2 sm:px-6">
                                    <button type="button" 
                                        @click="formData.score_initiative = Math.max(0, parseInt(formData.score_initiative || 0) - 5)"
                                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm border border-slate-200 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-[#0b5bd3]">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M5 12h14" stroke-linecap="round"/>
                                        </svg>
                                    </button>
                                    
                                    <input type="number" min="0" max="100" x-model.number="formData.score_initiative" 
                                        class="w-24 rounded-lg border-slate-200 text-center text-2xl font-bold text-[#0b5bd3] focus:border-[#0b5bd3] focus:ring-[#0b5bd3] [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" />

                                    <button type="button" 
                                        @click="formData.score_initiative = Math.min(100, parseInt(formData.score_initiative || 0) + 5)"
                                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm border border-slate-200 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-[#0b5bd3]">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 5v14M5 12h14" stroke-linecap="round"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="w-16 shrink-0 text-center flex flex-col justify-center">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Maks</p>
                                    <p class="text-sm font-bold text-slate-600">100</p>
                                </div>
                            </div>
                        </div>

                        {{-- Catatan --}}
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Catatan Tambahan <span class="font-normal text-slate-400">(opsional)</span></label>
                            <textarea name="notes" x-model="formData.notes" rows="3"
                                placeholder="Masukkan komentar kualitatif di sini..."
                                class="w-full rounded-xl border-slate-200 text-sm shadow-sm placeholder:text-slate-400 focus:border-[#0b5bd3] focus:ring-[#0b5bd3]"></textarea>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="border-t border-slate-100 px-6 py-4">
                        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                            <button type="button" @click="formOpen = false"
                                class="inline-flex items-center justify-center rounded-full border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                Batal
                            </button>
                            <button type="submit"
                                class="inline-flex items-center justify-center rounded-full bg-[#0b5bd3] px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                                Simpan Penilaian
                            </button>
                        </div>
                        <p class="mt-3 text-center text-[11px] font-medium text-slate-400">
                            ⚠️ Formulir ini hanya dapat diisi satu kali. Pastikan semua data sudah benar sebelum menyimpan.
                        </p>
                    </div>
                </form>
            </div>
        </div>

        <script>
            // Remove query parameters from URL so a refresh goes back to current month
            if (window.history.replaceState) {
                const url = new URL(window.location.href);
                // Keep the 'view' parameter so Staff/Divisions mode isn't lost,
                // but remove month and year so they reset on refresh
                const viewParam = url.searchParams.get('view');
                let newUrl = window.location.pathname;
                if (viewParam) {
                    newUrl += '?view=' + viewParam;
                }
                window.history.replaceState(null, null, newUrl);
            }
        </script>

    </div>{{-- end Alpine root --}}
</x-sidebar-layout>

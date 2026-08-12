<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail Penilaian – {{ $evaluatedUser->name }} | {{ config('app.name') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/himalkom_logo.svg') }}">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[#eef3f8]">
<div class="min-h-screen px-4 py-10 sm:px-8">

    {{-- Back button --}}
    <a href="{{ route('dashboard.performance.index', ['month' => $month, 'year' => $year]) }}"
        class="mb-6 inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 12H5M12 5l-7 7 7 7" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Kembali ke Performance
    </a>

    {{-- Page header --}}
    <div class="mb-8">
        <p class="text-sm font-semibold uppercase tracking-widest text-slate-400">Detail Penilaian · {{ $monthName }} {{ $year }}</p>
        <h1 class="mt-1 text-3xl font-bold text-slate-900">{{ $evaluatedUser->name }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ $evaluatedUser->getRoleNameForTitle() }} · {{ optional($evaluatedUser->department)->name }}</p>
    </div>

    @php
        $isBadanPengawas = optional($evaluatedUser->department)->name === 'Badan Pengawas';
        if ($isBadanPengawas) {
            $combinedScore = $mdEval ? $mdEval->final_score : 0;
            $combinedAttendance = $mdEval ? $mdEval->score_attendance : 0;
            $combinedCommitment = $mdEval ? $mdEval->score_commitment : 0;
            $combinedContribution = $mdEval ? $mdEval->score_contribution : 0;
            $combinedInitiative = $mdEval ? $mdEval->score_initiative : 0;
        } else {
            $combinedScore = round(($mdEval->final_score + $scEval->final_score) / 2, 1);
            $combinedAttendance = round(($mdEval->score_attendance + $scEval->score_attendance) / 2);
            $combinedCommitment = round(($mdEval->score_commitment + $scEval->score_commitment) / 2);
            $combinedContribution = round(($mdEval->score_contribution + $scEval->score_contribution) / 2);
            $combinedInitiative = round(($mdEval->score_initiative + $scEval->score_initiative) / 2);
        }
        $starCount = (int) round($combinedAttendance / 20);
    @endphp

    {{-- Combined score banner --}}
    <div class="mb-8 overflow-hidden rounded-2xl bg-gradient-to-br from-[#0b5bd3] to-[#1e3fc2] p-6 text-white shadow-lg">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-sm font-semibold opacity-75">{{ $isBadanPengawas ? 'Nilai Akhir (MD)' : 'Nilai Akhir Gabungan (MD + SC)' }}</p>
                <p class="text-6xl font-extrabold tracking-tight mt-1">{{ $combinedScore }}</p>
                <p class="text-xs opacity-60 mt-1">{{ $isBadanPengawas ? 'Penilaian tunggal oleh MD' : 'Rata-rata dari penilaian MD dan SC' }}</p>
            </div>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach ([
                    ['label' => 'Kehadiran', 'value' => $combinedAttendance],
                    ['label' => 'K. Komunikasi', 'value' => $combinedCommitment],
                    ['label' => 'Sikap Disiplin', 'value' => $combinedContribution],
                    ['label' => 'Inovasi', 'value' => $combinedInitiative],
                ] as $item)
                    <div class="rounded-xl bg-white/15 px-4 py-3 text-center backdrop-blur-sm">
                        <p class="text-xl font-bold">{{ $item['value'] }}</p>
                        <p class="text-[10px] opacity-70 mt-0.5 font-medium">{{ $item['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Two cards: MD vs SC --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        @php
            $cards = [
                ['label' => 'Penilaian dari MD / PJS', 'eval' => $mdEval, 'color' => 'blue', 'badge_bg' => 'bg-blue-100', 'badge_text' => 'text-blue-700', 'accent' => 'bg-[#0b5bd3]'],
            ];
            if (!$isBadanPengawas) {
                $cards[] = ['label' => 'Penilaian dari SC', 'eval' => $scEval, 'color' => 'amber', 'badge_bg' => 'bg-amber-100', 'badge_text' => 'text-amber-700', 'accent' => 'bg-amber-400'];
            }
        @endphp
        @foreach ($cards as $card)
            @php $e = $card['eval']; $starCount = (int) round($e->score_attendance / 20); @endphp
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                {{-- Card accent bar --}}
                <div class="h-1.5 w-full {{ $card['accent'] }}"></div>
                <div class="p-6">
                    <div class="mb-5 flex items-center justify-between">
                        <span class="rounded-full {{ $card['badge_bg'] }} {{ $card['badge_text'] }} px-3 py-1 text-xs font-bold uppercase tracking-wider">
                            {{ $card['label'] }}
                        </span>
                        <div class="text-right">
                            <p class="text-[10px] font-medium text-slate-400">Nilai Akhir</p>
                            <p class="text-2xl font-extrabold text-slate-900">{{ $e->final_score }}</p>
                        </div>
                    </div>

                    <div class="space-y-5">
                        @foreach ([
                            ['label' => 'Kehadiran (10%)', 'score' => $e->score_attendance],
                            ['label' => 'Keaktifan Komunikasi (30%)', 'score' => $e->score_commitment],
                            ['label' => 'Sikap Disiplin (30%)', 'score' => $e->score_contribution],
                            ['label' => 'Inovasi Inisiatif (30%)', 'score' => $e->score_initiative],
                        ] as $item)
                            @php $sCount = (int) round($item['score'] / 20); @endphp
                            <div class="flex items-center justify-between rounded-xl bg-slate-50 border border-slate-100 p-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-semibold text-slate-700">{{ $item['label'] }}</span>
                                    <span class="text-xs font-medium text-slate-400 mt-1">{{ $item['score'] }} poin</span>
                                </div>
                                <span class="text-amber-400 text-xl tracking-tighter drop-shadow-sm">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <span class="{{ $i <= $sCount ? 'text-amber-400' : 'text-slate-300' }}">★</span>
                                    @endfor
                                </span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Catatan --}}
                    @if ($e->notes)
                        <div class="mt-5 rounded-xl bg-slate-50 border border-slate-100 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 mb-1">Catatan</p>
                            <p class="text-sm text-slate-700 leading-relaxed">{{ $e->notes }}</p>
                        </div>
                    @endif

                    {{-- Evaluator info --}}
                    <div class="mt-5 flex items-center gap-2 text-xs text-slate-400">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="8" r="4"/><path d="M6 20v-1a6 6 0 0 1 12 0v1" stroke-linecap="round"/>
                        </svg>
                        Dinilai oleh <strong class="text-slate-600 ml-1">{{ $e->evaluator->name }}</strong>
                        <span class="mx-1">·</span>
                        {{ $e->created_at->format('d M Y, H:i') }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
</body>
</html>

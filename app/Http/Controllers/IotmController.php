<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Intern;
use App\Models\InternEvaluation;
use App\Models\IotmPj;
use App\Models\Setting;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class IotmController extends Controller
{
    public const QUESTIONS = [
        [
            'key' => 'score_punctuality',
            'title' => 'Ketepatan Waktu Kerja',
            'description' => 'Seberapa konsisten intern dalam menyelesaikan tugas dengan teliti dan tepat waktu sesuai deadline?',
        ],
        [
            'key' => 'score_initiative',
            'title' => 'Inisiatif dan Proaktif',
            'description' => 'Seberapa proaktif intern dalam memberikan ide dan mencari solusi?',
        ],
        [
            'key' => 'score_attendance',
            'title' => 'Kehadiran dan Kontribusi Nyata',
            'description' => 'Seberapa konsisten intern dalam menghadiri rapat, kegiatan, maupun agenda organisasi serta menunjukkan keterlibatan selama kegiatan berlangsung?',
        ],
        [
            'key' => 'score_cooperation',
            'title' => 'Komunikasi dan Kerja Sama',
            'description' => 'Seberapa baik komunikasi dan kemampuan kerja sama intern dengan sesama intern maupun staff lainnya?',
        ],
        [
            'key' => 'score_professionalism',
            'title' => 'Profesionalisme',
            'description' => 'Bagaimana profesionalisme intern dalam menerima kritik atau saran serta menjalankan tanggung jawabnya?',
        ],
    ];

    public function index(Request $request): View
    {
        return view('dashboard.iotm.index', $this->buildIotmData($request));
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        $validated = $request->validate([
            'intern_id' => ['required', 'string', 'exists:interns,id'],
            'period_month' => ['required', 'integer', 'min:1', 'max:12'],
            'period_year' => ['required', 'integer', 'min:2020'],
            'score_punctuality' => ['required', 'integer', 'min:1', 'max:5'],
            'score_initiative' => ['required', 'integer', 'min:1', 'max:5'],
            'score_attendance' => ['required', 'integer', 'min:1', 'max:5'],
            'score_cooperation' => ['required', 'integer', 'min:1', 'max:5'],
            'score_professionalism' => ['required', 'integer', 'min:1', 'max:5'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $intern = Intern::where('is_active', true)->findOrFail($validated['intern_id']);

        if (! $actor->isSuperAdmin() && $actor->department_id !== $intern->department_id) {
            abort(403, 'Anda hanya dapat menilai intern di departemen Anda sendiri.');
        }

        $periodStatus = $this->getEvaluationPeriodStatus($validated['period_month'], $validated['period_year']);
        if ($periodStatus !== 'open') {
            $startDay = (int) Setting::getVal('iotm_start_day', 1);
            $endDay = (int) Setting::getVal('iotm_end_day', 5);
            throw ValidationException::withMessages([
                'intern_id' => "Form penilaian IOTM untuk periode ini sedang ditutup. (Dibuka tanggal {$startDay} – {$endDay})",
            ]);
        }

        $finalScore = InternEvaluation::computeFinalScore(
            (int) $validated['score_punctuality'],
            (int) $validated['score_initiative'],
            (int) $validated['score_attendance'],
            (int) $validated['score_cooperation'],
            (int) $validated['score_professionalism'],
        );

        InternEvaluation::updateOrCreate(
            [
                'evaluator_id' => $actor->id,
                'intern_id' => $intern->id,
                'period_month' => $validated['period_month'],
                'period_year' => $validated['period_year'],
            ],
            [
                'department_id' => $intern->department_id,
                'score_punctuality' => $validated['score_punctuality'],
                'score_initiative' => $validated['score_initiative'],
                'score_attendance' => $validated['score_attendance'],
                'score_cooperation' => $validated['score_cooperation'],
                'score_professionalism' => $validated['score_professionalism'],
                'final_score' => $finalScore,
                'notes' => $validated['notes'] ?? null,
            ]
        );

        return redirect()
            ->route('dashboard.iotm.index', [
                'month' => $validated['period_month'],
                'year' => $validated['period_year'],
            ])
            ->with('success', 'Penilaian intern berhasil disimpan.');
    }

    public function export(Request $request)
    {
        /** @var User $actor */
        $actor = $request->user();

        if (! $this->canViewResults($actor)) {
            abort(403, 'Hanya MD dan PJ IOTM yang dapat mengekspor laporan IOTM.');
        }

        $data = $this->buildIotmData($request);

        if ($data['periodStatus'] !== 'past') {
            abort(403, 'Laporan IOTM baru dapat diekspor setelah periode pengisian berakhir.');
        }

        $pdf = Pdf::loadView('exports.iotm_report', $data);
        $pdf->setPaper('a4', 'landscape');

        $filename = "IOTM_{$data['selectedMonthName']}_{$data['selectedYear']}.pdf";

        return $pdf->download($filename);
    }

    private function canViewResults(User $actor): bool
    {
        return $actor->isSuperAdmin()
            || $actor->hasRole(['managing director', 'pjs'])
            || IotmPj::isAnyPj($actor->id);
    }

    private function buildIotmData(Request $request): array
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        /** @var User $actor */
        $actor = $request->user();

        $startDay = (int) Setting::getVal('iotm_start_day', 1);
        $endDay = (int) Setting::getVal('iotm_end_day', 5);

        if ($startDay > $endDay) {
            $defaultDate = (! $request->has('month') && now()->day <= $endDay) ? now()->copy()->subMonth() : now();
        } else {
            $defaultDate = now();
        }

        $selectedMonth = (int) $request->integer('month', $defaultDate->month);
        if (! array_key_exists($selectedMonth, $months)) {
            $selectedMonth = $defaultDate->month;
        }
        $selectedYear = (int) $request->integer('year', $defaultDate->year);
        $years = range(now()->year - 1, now()->year + 1);

        $isPjOrAdmin = $actor->isSuperAdmin() || IotmPj::isAnyPj($actor->id);
        $hasResultAccess = $this->canViewResults($actor);
        $periodStatus = $this->getEvaluationPeriodStatus($selectedMonth, $selectedYear);
        $canViewResults = $hasResultAccess && $periodStatus === 'past';

        $allEvaluations = InternEvaluation::with('evaluator:id,name')
            ->where([
                'period_month' => $selectedMonth,
                'period_year' => $selectedYear,
            ])->get()->groupBy('intern_id');

        $departments = Department::query()
            ->select(['id', 'name', 'abbreviation', 'slug'])
            ->when(! $isPjOrAdmin, function ($query) use ($actor) {
                $query->where('id', $actor->department_id);
            })
            ->when($isPjOrAdmin, function ($query) {
                $query->whereHas('interns', fn ($q) => $q->where('is_active', true));
            })
            ->with(['interns' => function ($query) {
                $query->where('is_active', true)->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        $departmentGroups = $departments->map(function (Department $department) use (
            $actor, $allEvaluations, $canViewResults, $periodStatus
        ) {
            $interns = $department->interns->map(function (Intern $intern) use (
                $actor, $department, $allEvaluations, $canViewResults, $periodStatus
            ) {
                $evals = $allEvaluations->get($intern->id, collect());
                $actorEval = $evals->firstWhere('evaluator_id', $actor->id);
                $actorHasFilled = $actorEval !== null;
                $canEvaluate = $actor->isSuperAdmin() || $actor->department_id === $department->id;

                $avgFinal = $canViewResults && $evals->isNotEmpty()
                    ? round((float) $evals->avg('final_score'), 2)
                    : null;

                $avgScores = $canViewResults && $evals->isNotEmpty()
                    ? [
                        'Ketepatan Waktu Kerja' => round((float) $evals->avg('score_punctuality'), 2),
                        'Inisiatif dan Proaktif' => round((float) $evals->avg('score_initiative'), 2),
                        'Kehadiran dan Kontribusi Nyata' => round((float) $evals->avg('score_attendance'), 2),
                        'Komunikasi dan Kerja Sama' => round((float) $evals->avg('score_cooperation'), 2),
                        'Profesionalisme' => round((float) $evals->avg('score_professionalism'), 2),
                    ]
                    : null;

                $notesList = $canViewResults
                    ? $evals->filter(fn ($e) => filled($e->notes))
                        ->map(fn ($e) => [
                            'evaluator_name' => $e->evaluator->name ?? 'Staf',
                            'notes' => $e->notes,
                        ])
                        ->values()
                        ->toArray()
                    : [];

                return [
                    'id' => $intern->id,
                    'name' => $intern->name,
                    'initials' => $this->initials($intern->name),
                    'department_id' => $department->id,
                    'department_name' => $department->name,
                    'evaluator_count' => $canViewResults ? $evals->count() : null,
                    'avg_scores' => $avgScores,
                    'avg_final_score' => $avgFinal,
                    'notes_list' => $notesList,
                    'actor_has_filled' => $actorHasFilled,
                    'can_evaluate' => $canEvaluate,
                    'is_open' => $periodStatus === 'open',
                    'existing_eval' => $actorEval ? [
                        'score_punctuality' => (int) $actorEval->score_punctuality,
                        'score_initiative' => (int) $actorEval->score_initiative,
                        'score_attendance' => (int) $actorEval->score_attendance,
                        'score_cooperation' => (int) $actorEval->score_cooperation,
                        'score_professionalism' => (int) $actorEval->score_professionalism,
                        'notes' => $actorEval->notes ?? '',
                    ] : null,
                ];
            });

            $sortedInterns = $canViewResults
                ? $interns->sortByDesc('avg_final_score')->values()
                : $interns->values();

            $bestInternId = null;
            if ($canViewResults) {
                $bestScore = $sortedInterns->max('avg_final_score');
                if ($bestScore !== null && $bestScore > 0) {
                    $bestInternId = $sortedInterns->where('avg_final_score', $bestScore)->first()['id'];
                }
            }

            $deptAvg = $canViewResults
                ? $sortedInterns->whereNotNull('avg_final_score')->avg('avg_final_score')
                : null;

            return [
                'id' => $department->id,
                'name' => $department->name,
                'abbreviation' => $department->abbreviation,
                'average_score' => $deptAvg !== null ? round($deptAvg, 2) : null,
                'interns' => $sortedInterns,
                'interns_count' => $sortedInterns->count(),
                'best_intern_id' => $bestInternId,
            ];
        });

        $showWarning = false;
        if ($periodStatus === 'open') {
            foreach ($departmentGroups as $dept) {
                if ($dept['interns']->contains(fn ($intern) => $intern['can_evaluate'] && ! $intern['actor_has_filled'])) {
                    $showWarning = true;
                    break;
                }
            }
        }

        return [
            'departmentGroups' => $departmentGroups,
            'questions' => self::QUESTIONS,
            'months' => $months,
            'years' => $years,
            'selectedMonth' => $selectedMonth,
            'selectedMonthName' => $months[$selectedMonth],
            'selectedYear' => $selectedYear,
            'canViewResults' => $canViewResults,
            'hasResultAccess' => $hasResultAccess,
            'canExport' => $canViewResults,
            'showWarning' => $showWarning,
            'periodStatus' => $periodStatus,
            'iotmStartDay' => $startDay,
            'iotmEndDay' => $endDay,
        ];
    }

    private function getEvaluationPeriodStatus(int $month, int $year): string
    {
        $startDay = (int) Setting::getVal('iotm_start_day', 1);
        $endDay = (int) Setting::getVal('iotm_end_day', 5);

        $startDate = Carbon::create($year, $month, min($startDay, Carbon::create($year, $month, 1)->daysInMonth), 0, 0, 0);

        if ($startDay > $endDay) {
            $endMonth = $startDate->copy()->addMonth()->startOfMonth();
            $endDate = $endMonth->copy()->addDays(min($endDay, $endMonth->daysInMonth) - 1)->endOfDay();
        } else {
            $endDate = Carbon::create($year, $month, min($endDay, Carbon::create($year, $month, 1)->daysInMonth), 23, 59, 59);
        }

        $now = now();

        if ($now->lt($startDate)) {
            return 'future';
        }

        if ($now->gt($endDate)) {
            return 'past';
        }

        return 'open';
    }

    private function initials(string $name): string
    {
        $initials = collect(preg_split('/\s+/', trim($name)))
            ->filter()
            ->take(2)
            ->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)))
            ->implode('');

        return $initials !== '' ? $initials : 'I';
    }
}

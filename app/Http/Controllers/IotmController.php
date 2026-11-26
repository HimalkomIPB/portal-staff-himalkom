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

        $period = $this->getPeriod();
        if ($period['status'] !== 'open') {
            $message = $period['status'] === 'not_set'
                ? 'Jadwal pengisian IOTM belum diatur oleh Superadmin.'
                : "Form penilaian IOTM sedang ditutup. (Dibuka {$period['start_label']} – {$period['end_label']})";

            throw ValidationException::withMessages(['intern_id' => $message]);
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
                'period_month' => $period['month'],
                'period_year' => $period['year'],
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
            ->route('dashboard.iotm.index')
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

        $filename = 'IOTM_'.Str::slug($data['periodLabel'], '_').'.pdf';

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
        /** @var User $actor */
        $actor = $request->user();

        $period = $this->getPeriod();
        $periodStatus = $period['status'];

        $isPjOrAdmin = $actor->isSuperAdmin() || IotmPj::isAnyPj($actor->id);
        $hasResultAccess = $this->canViewResults($actor);
        $canViewResults = $hasResultAccess && $periodStatus === 'past';

        $allEvaluations = $periodStatus === 'not_set'
            ? collect()
            : InternEvaluation::with('evaluator:id,name')
                ->where([
                    'period_month' => $period['month'],
                    'period_year' => $period['year'],
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
            'canViewResults' => $canViewResults,
            'hasResultAccess' => $hasResultAccess,
            'canExport' => $canViewResults,
            'showWarning' => $showWarning,
            'periodStatus' => $periodStatus,
            'periodLabel' => $period['label'],
            'iotmStartLabel' => $period['start_label'],
            'iotmEndLabel' => $period['end_label'],
        ];
    }

    /**
     * Jadwal IOTM bersifat sekali jalan (tanggal mulai & selesai lengkap dengan bulan dan tahun).
     * Penilaian disimpan dengan period_month / period_year dari tanggal mulai.
     *
     * @return array{status: string, month: ?int, year: ?int, start_label: ?string, end_label: ?string, label: string}
     */
    private function getPeriod(): array
    {
        $start = Setting::getVal('iotm_start_date');
        $end = Setting::getVal('iotm_end_date');

        if (! $start || ! $end) {
            return [
                'status' => 'not_set',
                'month' => null,
                'year' => null,
                'start_label' => null,
                'end_label' => null,
                'label' => 'Jadwal belum diatur',
            ];
        }

        $tz = 'Asia/Jakarta';
        $startDate = Carbon::parse($start, $tz)->startOfDay();
        $endDate = Carbon::parse($end, $tz)->endOfDay();
        $now = now($tz);

        $status = match (true) {
            $now->lt($startDate) => 'future',
            $now->gt($endDate) => 'past',
            default => 'open',
        };

        $startLabel = $this->formatDate($startDate);
        $endLabel = $this->formatDate($endDate);

        return [
            'status' => $status,
            'month' => $startDate->month,
            'year' => $startDate->year,
            'start_label' => $startLabel,
            'end_label' => $endLabel,
            'label' => "{$startLabel} – {$endLabel}",
        ];
    }

    private function formatDate(Carbon $date): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return $date->day.' '.$months[$date->month].' '.$date->year;
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

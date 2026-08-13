<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\PerformanceEvaluation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PerformanceController extends Controller
{
    public function index(Request $request): View
    {
        return view('dashboard.performance.index', $this->buildPerformanceData($request));
    }

    // -------------------------------------------------------
    // Store — simpan hasil penilaian
    // -------------------------------------------------------
    public function store(Request $request): RedirectResponse
    {
        $actor = $request->user();

        $validated = $request->validate([
            'evaluated_id'       => ['required', 'string', 'exists:users,id'],
            'department_id'      => ['required', 'string', 'exists:departments,id'],
            'period_month'       => ['required', 'integer', 'min:1', 'max:12'],
            'period_year'        => ['required', 'integer', 'min:2020'],
            'score_attendance'   => ['required', 'integer', 'min:0', 'max:100'],
            'score_commitment'   => ['required', 'integer', 'min:0', 'max:100'],
            'score_contribution' => ['required', 'integer', 'min:0', 'max:100'],
            'score_initiative'   => ['required', 'integer', 'min:0', 'max:100'],
            'notes'              => ['nullable', 'string', 'max:1000'],
        ]);

        // Tentukan role evaluator: md atau sc
        $department = Department::findOrFail($validated['department_id']);
        $evaluatorRole = $this->resolveEvaluatorRole($actor, $department);

        if ($evaluatorRole === null) {
            abort(403, 'Anda tidak memiliki hak untuk menilai anggota departemen ini.');
        }

        // Jangan nilai diri sendiri
        if ($actor->id === $validated['evaluated_id']) {
            abort(403, 'Anda tidak bisa menilai diri sendiri.');
        }

        // Cek apakah sudah pernah menilai
        if (PerformanceEvaluation::alreadyEvaluated(
            $actor->id,
            $validated['evaluated_id'],
            $validated['department_id'],
            $evaluatorRole,
            $validated['period_month'],
            $validated['period_year'],
        )) {
            throw ValidationException::withMessages([
                'evaluated_id' => 'Anda sudah pernah mengisi penilaian untuk anggota ini di periode yang sama.',
            ]);
        }

        $finalScore = PerformanceEvaluation::computeFinalScore(
            $validated['score_attendance'],
            $validated['score_commitment'],
            $validated['score_contribution'],
            $validated['score_initiative'],
        );

        PerformanceEvaluation::create([
            'evaluator_id'       => $actor->id,
            'evaluated_id'       => $validated['evaluated_id'],
            'department_id'      => $validated['department_id'],
            'evaluator_role'     => $evaluatorRole,
            'period_month'       => $validated['period_month'],
            'period_year'        => $validated['period_year'],
            'score_attendance'   => $validated['score_attendance'],
            'score_commitment'   => $validated['score_commitment'],
            'score_contribution' => $validated['score_contribution'],
            'score_initiative'   => $validated['score_initiative'],
            'final_score'        => $finalScore,
            'notes'              => $validated['notes'],
        ]);

        return redirect()
            ->route('dashboard.performance.index', [
                'month' => $validated['period_month'],
                'year'  => $validated['period_year'],
            ])
            ->with('success', 'Penilaian berhasil disimpan.');
    }

    // -------------------------------------------------------
    // Show — detail penilaian (MD card + SC card)
    // Hanya bisa dilihat jika KEDUA sudah mengisi
    // -------------------------------------------------------
    public function show(Request $request, string $evaluated): View
    {
        $actor       = $request->user();
        $evaluatedUser = User::findOrFail($evaluated);

        $month = (int) $request->integer('month', now()->month);
        $year  = (int) $request->integer('year', now()->year);

        $mdEval = PerformanceEvaluation::where([
            'evaluated_id'   => $evaluated,
            'department_id'  => $evaluatedUser->department_id,
            'evaluator_role' => 'md',
            'period_month'   => $month,
            'period_year'    => $year,
        ])->with('evaluator')->first();

        $scEval = PerformanceEvaluation::where([
            'evaluated_id'   => $evaluated,
            'department_id'  => $evaluatedUser->department_id,
            'evaluator_role' => 'sc',
            'period_month'   => $month,
            'period_year'    => $year,
        ])->with('evaluator')->first();

        $isBadanPengawas = $evaluatedUser->department?->name === 'Badan Pengawas';

        if ($isBadanPengawas) {
            // Badan Pengawas hanya butuh mdEval
            if (! $mdEval) {
                abort(403, 'Detail penilaian belum bisa dilihat karena MD belum mengisi formulir.');
            }
        } else {
            // Departemen lain butuh mdEval dan scEval
            if (! $mdEval || ! $scEval) {
                abort(403, 'Detail penilaian belum bisa dilihat karena belum semua pihak mengisi formulir.');
            }
        }

        return view('dashboard.performance.show', [
            'evaluatedUser'  => $evaluatedUser,
            'mdEval'         => $mdEval,
            'scEval'         => $scEval,
            'month'          => $month,
            'year'           => $year,
            'monthName'      => $this->monthName($month),
        ]);
    }

    public function export(Request $request)
    {
        $data = $this->buildPerformanceData($request);
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.performance_report', $data);
        $pdf->setPaper('a4', 'landscape');
        
        $filename = "SOTM_{$data['selectedMonthName']}_{$data['selectedYear']}.pdf";
        return $pdf->download($filename);
    }

    // -------------------------------------------------------
    // Helper: build data untuk halaman index
    // -------------------------------------------------------
    private function buildPerformanceData(Request $request): array
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        /** @var \App\Models\User $actor */
        $actor = $request->user();
        $actor->loadMissing('scDepartments');

        $selectedMonth = (int) $request->integer('month', now()->month);
        if (! array_key_exists($selectedMonth, $months)) {
            $selectedMonth = now()->month;
        }
        $selectedYear = (int) $request->integer('year', now()->year);
        $years        = range(now()->year - 1, now()->year + 1);

        $canViewAll      = $actor->can('performance.view-all');
        $canEvaluateAny  = $actor->can('performance.evaluate');
        $canViewDeptPerf = $actor->can('performance.view');
        // "view-self" hanya berlaku jika tidak punya akses lebih tinggi
        $viewSelfOnly    = $actor->can('performance.view-self') && ! $canViewAll && ! $canEvaluateAny && ! $canViewDeptPerf;

        // Ambil semua evaluasi di periode yang dipilih sekaligus (N+1 prevention)
        $allEvaluations = PerformanceEvaluation::where([
            'period_month' => $selectedMonth,
            'period_year'  => $selectedYear,
        ])->get()->groupBy(fn ($e) => $e->evaluated_id . '_' . $e->department_id);

        $departments = Department::query()
            ->select(['id', 'name', 'abbreviation', 'slug'])
            ->with(['users' => function ($query) use ($actor, $viewSelfOnly) {
                $query->select(['id', 'name', 'email', 'department_id', 'sub_division'])
                    ->with('roles')
                    ->orderBy('name');

                if ($viewSelfOnly) {
                    $query->where('id', $actor->id);
                } else {
                    $query->whereDoesntHave('roles', fn ($rq) => $rq->whereIn('name', ['supervisor', 'managing director', 'pjs']));
                }
            }])
            ->when($viewSelfOnly, function ($query) use ($actor) {
                // Anggota biasa: hanya dept sendiri
                $query->where('id', $actor->department_id);
            })
            // MD, SC, BPH yang bisa evaluate → lihat SEMUA departemen (tapi isi form hanya dept sendiri)
            // supervisor / BPH view-all → sudah covered (tidak di-filter sama sekali)
            ->whereNotIn('name', ['Badan Pengurus Harian'])
            ->orderBy('name')
            ->get();

        $departmentGroups = $departments->map(function (Department $department) use (
            $actor, $allEvaluations, $selectedMonth, $selectedYear
        ) {
            $members = $department->users->map(fn (User $member) =>
                $this->buildMemberCard($actor, $department, $member, $allEvaluations, $selectedMonth, $selectedYear)
            );

            $deptAvg = $members->whereNotNull('combined_score')->avg('combined_score');

            $groupedMembers = $members->groupBy(function ($member) {
                return $member['sub_division'] ?: 'Tanpa Divisi';
            })->map(function ($group) {
                // Urutkan berdasarkan nilai tertinggi ke terendah, nilai kosong (null) di akhir
                return $group->sortByDesc('combined_score')->values();
            });

            $bestPerformers = [];
            foreach ($groupedMembers as $subName => $group) {
                // Pastikan semua anggota di sub-divisi ini sudah memiliki Nilai Akhir
                $allEvaluated = $group->every(fn($member) => $member['combined_score'] !== null);
                
                if ($allEvaluated) {
                    $bestScore = $group->max('combined_score');
                    if ($bestScore !== null && $bestScore > 0) {
                        $bestPerformers[$subName] = $group->where('combined_score', $bestScore)->first()['id'];
                    }
                }
            }

            return [
                'id'               => $department->id,
                'name'             => $department->name,
                'abbreviation'     => $department->abbreviation,
                'slug'             => $department->slug,
                'average_score'    => $deptAvg !== null ? round($deptAvg, 1) : null,
                'grouped_members'  => $groupedMembers,
                'members_count'    => $members->count(),
                'best_performers'  => $bestPerformers,
            ];
        });

        $myDivisionIds = $actor->scDepartments->pluck('id')
            ->push($actor->department_id)
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        return [
            'departmentGroups'  => $departmentGroups,
            'months'            => $months,
            'years'             => $years,
            'selectedMonth'     => $selectedMonth,
            'selectedMonthName' => $months[$selectedMonth],
            'selectedYear'      => $selectedYear,
            'canEvaluate'       => $actor->can('performance.evaluate'),
            'canExport'         => $actor->can('performance.view-all') || $actor->can('performance.evaluate'),
            'viewMode'          => request('view', 'divisions') === 'staff' ? 'staff' : 'divisions',
            'myDivisionIds'     => $myDivisionIds,
        ];
    }

    // -------------------------------------------------------
    // Helper: build data per member card
    // -------------------------------------------------------
    private function buildMemberCard(
        User $actor,
        Department $department,
        User $member,
        $allEvaluations,
        int $month,
        int $year
    ): array {
        $key        = $member->id . '_' . $department->id;
        $evals      = $allEvaluations->get($key, collect());

        $mdEval = $evals->firstWhere('evaluator_role', 'md');
        $scEval = $evals->firstWhere('evaluator_role', 'sc');

        // Nilai gabungan
        $isBadanPengawas = $department->name === 'Badan Pengawas';
        if ($isBadanPengawas) {
            $bothFilled    = (bool) $mdEval; // Badan pengawas hanya butuh MD
            $combinedScore = $bothFilled ? (float) $mdEval->final_score : null;
        } else {
            $bothFilled    = $mdEval && $scEval; // Departemen lain butuh MD dan SC
            $combinedScore = $bothFilled
                ? round(($mdEval->final_score + $scEval->final_score) / 2, 1)
                : null;
        }

        $actorRole      = $this->resolveEvaluatorRole($actor, $department);
        $actorHasFilled = $evals->contains(fn ($e) => $e->evaluator_id === $actor->id);
        $isSelf         = $actor->id === $member->id;

        // Tentukan status tombol
        $canEvaluate   = $this->canEvaluateMember($actor, $department, $member);
        $buttonStatus  = $this->resolveButtonStatus($canEvaluate, $actorHasFilled, $bothFilled, $isSelf);

        return [
            'id'             => $member->id,
            'name'           => $member->name,
            'email'          => $member->email,
            'initials'       => $this->initials($member->name),
            'role_title'     => $member->getRoleNameForTitle(),
            'department_id'  => $department->id,
            'department_name'=> $department->name,
            'sub_division'   => $member->sub_division,
            'scores'         => [
                'Kehadiran (10%)'             => $bothFilled ? ($isBadanPengawas ? $mdEval->score_attendance : round(($mdEval->score_attendance + $scEval->score_attendance) / 2)) : null,
                'Keaktifan Komunikasi (30%)'  => $bothFilled ? ($isBadanPengawas ? $mdEval->score_commitment : round(($mdEval->score_commitment + $scEval->score_commitment) / 2)) : null,
                'Sikap Disiplin (30%)'        => $bothFilled ? ($isBadanPengawas ? $mdEval->score_contribution : round(($mdEval->score_contribution + $scEval->score_contribution) / 2)) : null,
                'Inovasi Inisiatif (30%)'     => $bothFilled ? ($isBadanPengawas ? $mdEval->score_initiative : round(($mdEval->score_initiative + $scEval->score_initiative) / 2)) : null,
            ],
            'combined_score'  => $combinedScore,
            'both_filled'     => $bothFilled,
            'actor_has_filled'=> $actorHasFilled,
            'can_evaluate'    => $canEvaluate,
            'button_status'   => $buttonStatus, // 'evaluate' | 'waiting' | 'detail' | 'view_only'
            'period_month'    => $month,
            'period_year'     => $year,
            // Untuk bintang Kehadiran
            'star_count'      => $bothFilled ? (int) round(($isBadanPengawas ? $mdEval->score_attendance : (($mdEval->score_attendance + $scEval->score_attendance) / 2)) / 20) : 0,
        ];
    }

    // -------------------------------------------------------
    // Helper: tentukan status tombol
    // -------------------------------------------------------
    private function resolveButtonStatus(bool $canEvaluate, bool $actorHasFilled, bool $bothFilled, bool $isSelf): string
    {
        // Keduanya sudah isi -> bisa lihat detail (terlepas dari role, asalkan diizinkan view)
        if ($bothFilled) {
            return 'detail';
        }

        if ($isSelf) {
            return 'self'; // Tidak bisa nilai diri sendiri
        }

        if (! $canEvaluate) {
            // Anggota atau role lain yang hanya bisa lihat, dan belum ada nilai lengkap
            return 'view_only';
        }

        if (! $actorHasFilled) {
            return 'evaluate'; // Belum isi, tampilkan "Isi Penilaian"
        }

        return 'filled'; // Sudah isi, partner belum (sebelumnya 'waiting')
    }

    // -------------------------------------------------------
    // Helper: apakah actor boleh menilai member ini
    // -------------------------------------------------------
    private function canEvaluateMember(User $actor, Department $department, User $member): bool
    {
        if (! $actor->can('performance.evaluate') || $actor->id === $member->id) {
            return false;
        }
        if ($actor->can('user.manage')) {
            return true;
        }
        if ($department->name === 'Badan Pengawas' && $actor->isSCOf($department)) {
            // SC tidak bisa menilai member Badan Pengawas
            return false;
        }
        return $actor->department_id === $department->id || $actor->isSCOf($department);
    }

    // -------------------------------------------------------
    // Helper: resolusi role evaluator (md atau sc)
    // -------------------------------------------------------
    private function resolveEvaluatorRole(User $actor, Department $department): ?string
    {
        if ($actor->can('user.manage')) {
            return 'md'; // supervisor fallback
        }

        if ($actor->department_id === $department->id && $actor->can('performance.evaluate')) {
            return 'md';
        }

        if ($actor->isSCOf($department)) {
            return 'sc';
        }

        return null;
    }

    // -------------------------------------------------------
    // Helper: inisial nama
    // -------------------------------------------------------
    private function initials(string $name): string
    {
        $initials = collect(preg_split('/\s+/', trim($name)))
            ->filter()
            ->take(2)
            ->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)))
            ->implode('');

        return $initials !== '' ? $initials : 'U';
    }

    private function monthName(int $month): string
    {
        return [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ][$month] ?? '';
    }
}

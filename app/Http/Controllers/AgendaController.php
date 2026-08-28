<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AgendaController extends Controller
{
    // -------------------------------------------------------
    // Index — halaman utama Calendar
    // -------------------------------------------------------

    public function index(Request $request): View
    {
        $user = Auth::user();
        $agendas = Agenda::with('department')
            ->visibleTo($user)
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        // Helper: map agenda ke array JS-friendly
        $mapEvent = function ($a) use ($user) {
            return [
                'id' => $a->id,
                'title' => $a->title,
                'date' => $a->date->format('Y-m-d'),
                'start_time' => $a->start_time,
                'end_time' => $a->end_time,
                'jenis' => $a->jenis,
                'lokasi' => $a->lokasi,
                'skala' => $a->skala,
                'deskripsi' => $a->deskripsi,
                'department' => $a->department->abbreviation ?? 'General',
                'can_edit' => $this->canManage($user, $a),
                'can_delete' => $this->canManage($user, $a),
            ];
        };

        // Pre-mapped arrays for JS
        $allEventsJson = $agendas->map($mapEvent)->values();

        // Upcoming: agenda hari ini ke depan, maks 10
        $upcomingJson = $agendas
            ->filter(function ($a) {
                return $a->date->gte(now()->startOfDay());
            })
            ->take(10)
            ->map($mapEvent)
            ->values();

        // Semua departemen (untuk form dropdown jika BPH/supervisor)
        $departments = Department::orderBy('name')->get(['id', 'name', 'abbreviation']);

        return view('dashboard.calendar.index', compact('allEventsJson', 'upcomingJson', 'departments'));
    }

    // -------------------------------------------------------
    // Events — JSON endpoint untuk Alpine.js (filter bulan)
    // -------------------------------------------------------

    public function events(Request $request): JsonResponse
    {
        $user = Auth::user();
        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);

        $agendas = Agenda::with('department')
            ->visibleTo($user)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'date' => $a->date->format('Y-m-d'),
                'start_time' => $a->start_time,
                'end_time' => $a->end_time,
                'jenis' => $a->jenis,
                'lokasi' => $a->lokasi,
                'skala' => $a->skala,
                'deskripsi' => $a->deskripsi,
                'department' => $a->department?->abbreviation ?? 'General',
                'can_edit' => $this->canManage($user, $a),
                'can_delete' => $this->canManage($user, $a),
            ]);

        return response()->json($agendas);
    }

    // -------------------------------------------------------
    // Store — simpan agenda baru
    // -------------------------------------------------------

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'start_time' => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'end_time' => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'jenis' => 'required|in:offline,online',
            'lokasi' => 'nullable|string|max:255',
            'skala' => 'required|in:departemen,general',
            'deskripsi' => 'nullable|string|max:2000',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        // Normalize waktu ke HH:MM
        $validated['start_time'] = substr($validated['start_time'], 0, 5);
        $validated['end_time'] = substr($validated['end_time'], 0, 5);

        // Tentukan department_id
        // - BPH / Supervisor bisa pilih department_id (bisa null = general)
        // - MD/PJS/Sekretaris hanya bisa buat agenda departemen sendiri
        if ($user->can('agenda.create-org')) {
            // BPH/supervisor: pakai department_id dari form (bisa null)
            $deptId = $validated['department_id'] ?? null;
        } else {
            // MD/PJS/Sekretaris: selalu pakai department_id sendiri
            $deptId = $user->department_id;
        }

        // Skala general → hapus lokasi jika online
        $lokasi = ($validated['jenis'] === 'online') ? $validated['lokasi'] : $validated['lokasi'];

        Agenda::create([
            'title' => $validated['title'],
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'jenis' => $validated['jenis'],
            'lokasi' => $validated['lokasi'] ?? null,
            'skala' => $validated['skala'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'department_id' => $deptId,
            'created_by' => $user->id,
        ]);

        return redirect()->route('dashboard.calendar.index')
            ->with('success', ['id' => uniqid(), 'message' => 'Agenda berhasil ditambahkan!']);
    }

    // -------------------------------------------------------
    // Update — edit agenda
    // -------------------------------------------------------

    public function update(Request $request, Agenda $agenda): RedirectResponse
    {
        $user = Auth::user();

        if (! $this->canManage($user, $agenda)) {
            abort(403, 'Anda tidak berwenang mengubah agenda ini.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'start_time' => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'end_time' => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'jenis' => 'required|in:offline,online',
            'lokasi' => 'nullable|string|max:255',
            'skala' => 'required|in:departemen,general',
            'deskripsi' => 'nullable|string|max:2000',
        ]);

        // Normalize waktu ke HH:MM
        $validated['start_time'] = substr($validated['start_time'], 0, 5);
        $validated['end_time'] = substr($validated['end_time'], 0, 5);

        $agenda->update($validated);

        return redirect()->route('dashboard.calendar.index')
            ->with('success', ['id' => uniqid(), 'message' => 'Agenda berhasil diperbarui!']);
    }

    // -------------------------------------------------------
    // Destroy — hapus agenda
    // -------------------------------------------------------

    public function destroy(Agenda $agenda): RedirectResponse
    {
        $user = Auth::user();

        if (! $this->canManage($user, $agenda)) {
            abort(403, 'Anda tidak berwenang menghapus agenda ini.');
        }

        $agenda->delete();

        return redirect()->route('dashboard.calendar.index')
            ->with('success', ['id' => uniqid(), 'message' => 'Agenda berhasil dihapus.']);
    }

    // -------------------------------------------------------
    // Helper — apakah user boleh manage (edit/delete) agenda ini
    // -------------------------------------------------------

    private function canManage($user, Agenda $agenda): bool
    {
        // Supervisor boleh semua
        if ($user->hasRole('supervisor')) {
            return true;
        }

        // BPH dengan create-org: bisa manage semua agenda (creator-nya BPH)
        if ($user->can('agenda.create-org') && $agenda->created_by == $user->id) {
            return true;
        }

        // MD/PJS/Sekretaris: hanya bisa manage agenda departemennya sendiri
        if ($user->can('agenda.edit-dept') && $agenda->department_id === $user->department_id) {
            return true;
        }

        return false;
    }
}

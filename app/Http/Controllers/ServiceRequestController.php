<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\Department;
use App\Notifications\ServiceRequestNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ServiceRequestController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $tab = $request->query('tab', 'my_requests'); // 'my_requests' or 'incoming'
        $statusFilter = $request->query('status', 'all');
        
        $query = ServiceRequest::with(['requester', 'assignees', 'department']);
        
        if ($tab === 'incoming') {
            // For incoming requests, user must be from Kreatif or RnT
            $departmentSlug = $user->department?->slug;
            
            if ($departmentSlug === 'creative') {
                $query->whereIn('type', ['copm', 'codm']);
            } elseif ($departmentSlug === 'research-and-technology') {
                $query->whereIn('type', ['komnews', 'riset']);
            } else {
                abort(403, 'Anda tidak memiliki akses ke tab ini.');
            }

            // Anggota biasa hanya bisa melihat request yang di-assign ke dia
            if ($user->hasRole('anggota')) {
                $query->whereHas('assignees', function($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
            }
        } else {
            // My Requests
            $query->where('department_id', $user->department_id);
        }
        
        // Status filter
        if ($statusFilter === 'not_completed') {
            $query->where('status', '!=', 'completed');
        } elseif ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }
        
        $serviceRequests = $query->latest()->get();
        
        // Calculate incoming count for badge (Active requests that need manager's attention)
        $incomingCount = 0;
        $departmentSlug = $user->department?->slug;
        if (in_array($departmentSlug, ['creative', 'research-and-technology'])) {
            $types = $departmentSlug === 'creative' ? ['copm', 'codm'] : ['komnews', 'riset'];
            $incomingCount = ServiceRequest::whereIn('type', $types)
                ->where('status', 'pending')
                ->count();
        }

        return view('dashboard.services.index', compact('serviceRequests', 'tab', 'statusFilter', 'incomingCount'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $user = $request->user();
        
        if (!$user->department_id) {
            abort(403, 'Anda harus tergabung dalam sebuah departemen untuk membuat request.');
        }

        return view('dashboard.services.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:copm,codm,komnews,riset',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'due_date' => 'nullable|date',
        ]);

        $user = $request->user();
        
        if (!$user->department_id) {
            abort(403, 'Anda harus tergabung dalam sebuah departemen untuk membuat request.');
        }

        $userDept = $user->department->slug ?? '';
        
        if (in_array($request->type, ['copm', 'codm']) && $userDept === 'creative') {
            return back()->withErrors(['type' => 'Divisi Kreatif tidak dapat mengajukan layanan ke divisinya sendiri.'])->withInput();
        }

        if (in_array($request->type, ['komnews', 'riset']) && $userDept === 'research-and-technology') {
            return back()->withErrors(['type' => 'Divisi RnT tidak dapat mengajukan layanan ke divisinya sendiri.'])->withInput();
        }

        $serviceRequest = ServiceRequest::create([
            'requester_id' => $user->id,
            'department_id' => $user->department_id,
            'type' => $request->type,
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'status' => 'pending',
        ]);
        
        $targetDepartmentSlug = in_array($request->type, ['copm', 'codm']) ? 'creative' : 'research-and-technology';
        $targetDepartment = Department::where('slug', $targetDepartmentSlug)->first();
        if ($targetDepartment) {
            Notification::send($targetDepartment->users, new ServiceRequestNotification(
                $serviceRequest,
                "Ada pengajuan layanan baru: {$serviceRequest->title}"
            ));
        }

        return redirect()->route('dashboard.services.show', $serviceRequest)
            ->with('success', [
                'id' => uniqid(),
                'message' => 'Pengajuan layanan berhasil dibuat.'
            ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ServiceRequest $service)
    {
        // Only allow edit if rejected and user is requester
        if ($service->status !== 'rejected' || auth()->id() !== $service->requester_id) {
            abort(403);
        }
        
        return view('dashboard.services.edit', compact('service'));
    }

    /**
     * Display the specified resource.
     */
    public function show(ServiceRequest $service)
    {
        $this->authorize('view', $service);
        
        $service->load(['requester', 'assignees', 'department', 'comments.user']);
        
        return view('dashboard.services.show', compact('service'));
    }

    /**
     * Update status of the service request.
     */
    public function updateStatus(Request $request, ServiceRequest $service)
    {
        $this->authorize('update', $service);
        
        $request->validate([
            'status' => 'required|in:pending,accepted,in_progress'
        ]);

        $service->update(['status' => $request->status]);

        return back()->with('success', [
            'id' => uniqid(),
            'message' => 'Status berhasil diperbarui.'
        ]);
    }
    
    /**
     * Assign the request to a division member.
     */
    public function assign(Request $request, ServiceRequest $service)
    {
        $this->authorize('update', $service);
        
        $request->validate([
            'assigned_to' => 'nullable|array',
            'assigned_to.*' => 'exists:users,id'
        ]);
        
        // Validation to ensure assignee is from the same department as the manager
        if ($request->assigned_to) {
            $assignees = User::whereIn('id', $request->assigned_to)->get();
            foreach ($assignees as $assignee) {
                if ($assignee->department_id !== $request->user()->department_id) {
                    return back()->with('error', [
                        'id' => uniqid(),
                        'message' => 'Hanya bisa menugaskan ke anggota divisi yang sama.'
                    ]);
                }
            }
        }

        $service->assignees()->sync($request->assigned_to ?? []);

        return back()->with('success', [
            'id' => uniqid(),
            'message' => 'Penugasan berhasil diperbarui.'
        ]);
    }
    
    /**
     * Upload the final result.
     */
    public function uploadFinal(Request $request, ServiceRequest $service)
    {
        $this->authorize('update', $service);
        
        $request->validate([
            'final_file' => 'nullable|file|mimes:png,jpg,jpeg,pdf,zip|max:20480', // Max 20MB
            'final_link' => 'nullable|string|max:1000',
        ]);
        
        if (!$request->hasFile('final_file') && empty($request->final_link)) {
            return back()->withErrors(['final_file' => 'Harap unggah file atau masukkan link hasil akhir.']);
        }
        
        $path = $service->final_file_path;
        if ($request->hasFile('final_file')) {
            $path = $request->file('final_file')->store('services/final', 'public');
        }
        
        $service->update([
            'final_file_path' => $path,
            'final_link' => $request->final_link,
            'status' => 'uploaded', // Status berubah jadi diunggah, menunggu persetujuan pengaju
        ]);
        
        $service->requester->notify(new ServiceRequestNotification(
            $service,
            "Hasil akhir untuk layanan {$service->title} telah diunggah."
        ));

        return back()->with('success', [
            'id' => uniqid(),
            'message' => 'Hasil akhir berhasil diunggah. Status pengajuan menjadi Diunggah.'
        ]);
    }
    
    /**
     * Approve the final result (Requester only).
     */
    public function approveFinal(Request $request, ServiceRequest $service)
    {
        $this->authorize('approve', $service);
        
        $service->update([
            'status' => 'completed',
            'is_approved_by_requester' => true,
        ]);
        
        $targetDepartmentSlug = in_array($service->type, ['copm', 'codm']) ? 'creative' : 'research-and-technology';
        $targetDepartment = Department::where('slug', $targetDepartmentSlug)->first();
        if ($targetDepartment) {
            Notification::send($targetDepartment->users, new ServiceRequestNotification(
                $service,
                "Hasil akhir layanan {$service->title} telah disetujui pengaju."
            ));
        }

        return back()->with('success', [
            'id' => uniqid(),
            'message' => 'Hasil akhir telah diterima.'
        ]);
    }

    /**
     * Reject the final result (Requester only).
     */
    public function rejectFinal(Request $request, ServiceRequest $service)
    {
        $this->authorize('approve', $service);
        
        $service->update([
            'status' => 'revision', // Kembalikan ke revisi jika ditolak
        ]);
        
        $targetDepartmentSlug = in_array($service->type, ['copm', 'codm']) ? 'creative' : 'research-and-technology';
        $targetDepartment = Department::where('slug', $targetDepartmentSlug)->first();
        if ($targetDepartment) {
            Notification::send($targetDepartment->users, new ServiceRequestNotification(
                $service,
                "Hasil akhir layanan {$service->title} ditolak (Butuh Revisi)."
            ));
        }

        return back()->with('success', [
            'id' => uniqid(),
            'message' => 'Hasil akhir ditolak dan dikembalikan untuk direvisi.'
        ]);
    }

    /**
     * Manager accepts the service request.
     */
    public function acceptByManager(Request $request, ServiceRequest $service)
    {
        $this->authorize('update', $service);
        
        $service->update([
            'status' => 'accepted',
        ]);
        
        $service->requester->notify(new ServiceRequestNotification(
            $service,
            "Pengajuan layanan {$service->title} telah diterima oleh pengelola dan akan segera dikerjakan."
        ));

        return back()->with('success', [
            'id' => uniqid(),
            'message' => 'Pengajuan berhasil diterima.'
        ]);
    }

    /**
     * Manager rejects the service request.
     */
    public function rejectByManager(Request $request, ServiceRequest $service)
    {
        $this->authorize('update', $service);
        
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);
        
        $service->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);
        
        $service->requester->notify(new ServiceRequestNotification(
            $service,
            "Pengajuan layanan {$service->title} ditolak oleh pengelola."
        ));

        return back()->with('success', [
            'id' => uniqid(),
            'message' => 'Pengajuan berhasil ditolak.'
        ]);
    }

    /**
     * Update the specified resource in storage. (Used for resubmitting a rejected request)
     */
    public function update(Request $request, ServiceRequest $service)
    {
        // Only requester can update/resubmit
        if ($request->user()->id !== $service->requester_id || $service->status !== 'rejected') {
            abort(403);
        }
        
        $request->validate([
            'type' => 'required|in:copm,codm,komnews,riset',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'due_date' => 'nullable|date',
        ]);
        
        $userDept = $request->user()->department->slug ?? '';
        
        if (in_array($request->type, ['copm', 'codm']) && $userDept === 'creative') {
            return back()->withErrors(['type' => 'Divisi Kreatif tidak dapat mengajukan layanan ke divisinya sendiri.'])->withInput();
        }

        if (in_array($request->type, ['komnews', 'riset']) && $userDept === 'research-and-technology') {
            return back()->withErrors(['type' => 'Divisi RnT tidak dapat mengajukan layanan ke divisinya sendiri.'])->withInput();
        }
        
        $service->update([
            'type' => $request->type,
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'status' => 'pending',
            'rejection_reason' => null,
        ]);
        
        $targetDepartmentSlug = in_array($service->type, ['copm', 'codm']) ? 'creative' : 'research-and-technology';
        $targetDepartment = Department::where('slug', $targetDepartmentSlug)->first();
        if ($targetDepartment) {
            Notification::send($targetDepartment->users, new ServiceRequestNotification(
                $service,
                "Pengajuan layanan {$service->title} telah diajukan ulang oleh pengaju."
            ));
        }

        return redirect()->route('dashboard.services.show', $service)->with('success', [
            'id' => uniqid(),
            'message' => 'Pengajuan berhasil diajukan ulang.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ServiceRequest $service)
    {
        $this->authorize('delete', $service);
        
        $service->delete();

        return redirect()->route('dashboard.services.index')
            ->with('success', [
                'id' => uniqid(),
                'message' => 'Pengajuan layanan berhasil dihapus.'
            ]);
    }
}

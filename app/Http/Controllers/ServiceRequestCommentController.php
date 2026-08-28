<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class ServiceRequestCommentController extends Controller
{
    use AuthorizesRequests;

    /**
     * Store a newly created comment in storage.
     */
    public function store(Request $request, ServiceRequest $service)
    {
        $this->authorize('comment', $service);

        $request->validate([
            'content' => 'required|string',
            'attachment' => 'nullable|file|max:10240', // 10MB max
        ]);

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('services/attachments', 'public');
        }

        $service->comments()->create([
            'user_id' => $request->user()->id,
            'content' => $request->content,
            'attachment_path' => $path,
        ]);

        // If the requester commented, maybe change status to 'pending' if it was rejected or revision?
        // Let's leave status changing to the manager or explicit actions.

        return back()->with('success', [
            'id' => uniqid(),
            'message' => 'Komentar berhasil ditambahkan.',
        ]);
    }
}

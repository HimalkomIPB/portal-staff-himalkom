<?php

namespace App\Http\Controllers;

use App\Models\WorkProgram;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\WorkProgramComment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Notifications\WorkProgramCommentNotification;


class WorkProgramCommentController extends Controller
{
    public function store(Request $request, WorkProgram $workProgram)
    {
        $request->validate([
            'content' => 'required|string|max:500',
        ]);

        DB::beginTransaction();

        try {
            $newComment = WorkProgramComment::create([
                'content' => $request->content,
                'work_program_id' => $workProgram->id,
                'user_id' => Auth::id(),
            ]);

            $managingDirector = $workProgram->department->managing_director;
            $pjs = $workProgram->department->pjs;

            if ($managingDirector && $managingDirector->id !== Auth::user()->id) {
                $routeToMD = route('dashboard.workProgram.detail', [
                    'department' => $workProgram->department,
                    'workProgram' => $workProgram,
                ]);

                $managingDirector->notify(new WorkProgramCommentNotification(
                    'Komentar Baru pada Program Kerja ' . $workProgram->name,
                    '"' . strip_tags(html_entity_decode($newComment->content)) .  '" oleh ' . Auth::user()->name,
                    $routeToMD
                ));
            }

            // Notify all PJs (except the commenter)
            foreach ($pjs as $pj) {
                if ($pj->id !== Auth::user()->id) {
                    $routeToPjs = route('dashboard.workProgram.detail', [
                        'department' => $workProgram->department,
                        'workProgram' => $workProgram,
                    ]);
                    $pj->notify(new WorkProgramCommentNotification(
                        'Komentar Baru pada Program Kerja ' . $workProgram->name,
                         '"' . strip_tags(html_entity_decode($newComment->content)) .  '" oleh ' . Auth::user()->name,
                        $routeToPjs
                    ));
                }
            }

            DB::commit();
            return redirect()->back()->with('success', ['message' => 'Komentar Berhasil Ditambahkan!', 'id' => Str::ulid()->toBase32()]);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', ['message' => 'Terjadi kesalahan saat menambahkan komentar: ' . $e->getMessage(), 'id' => Str::ulid()->toBase32()]);
        }
    }

    public function destroy(WorkProgram $workProgram, WorkProgramComment $comment)
    {
        if (Auth::user()->id !== $comment->user_id) {
            abort(403, 'Unauthorized Access of Comment WorkProgram Action.');
        }

        $comment->delete();
        return redirect()->back()->with('success', ['message' => 'Komentar Berhasil Dihapus!', 'id' => Str::ulid()->toBase32()]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PDFController extends Controller
{
    public function showPrivatePdf(string $filename)
    {
        if (! Auth::user()) {
            abort(403, 'Unauthorized access to file');
        }

        $pathPrivate = Storage::disk('private')->path('private/'.$filename);
        $pathProposals = Storage::disk('private')->path('proposals/'.$filename);

        if (file_exists($pathPrivate)) {
            $pathToFile = $pathPrivate;
        } elseif (file_exists($pathProposals)) {
            $pathToFile = $pathProposals;
        } else {
            abort(404, 'File not found');
        }

        return response()->file($pathToFile);
    }
}

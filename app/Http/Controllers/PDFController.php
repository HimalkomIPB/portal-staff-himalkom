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

        $pathToFile = Storage::disk('private')->path('private/'.$filename);

        if (! file_exists($pathToFile)) {
            abort(404, 'File not found');
        }

        return response()->file($pathToFile);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function downloadFile($fileName)
    {
        // Generate a unique filename to avoid overwriting
        $storageFileName = uniqid() . '_' . $fileName;

        // Upload the file to AWS S3
        Storage::disk('s3')->put($storageFileName, file_get_contents(public_path($fileName)));

        // Generate a temporary URL for download
        $url = Storage::disk('s3')->url($storageFileName);
        return $this->success(['url' => $url]);
        // return response()->json(['url' => $url]);
    }
}


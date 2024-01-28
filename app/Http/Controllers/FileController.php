<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Illuminate\Support\Facades\Log;

class FileController extends Controller
{
    public function downloadFile($fileName)
    {
        Storage::disk('s3')->put($fileName, file_get_contents(public_path($fileName)));
        $url = Storage::disk('s3')->url($fileName);
        return $this->success(['url' => $url,'localpath'=>public_path($fileName)]);
    }

    public function downloadFile2($fileName)
    {
        try {
            // Get the S3 client
            $s3 = new S3Client([
                'version' => 'latest',
                'region' => env('AWS_DEFAULT_REGION'),
                'credentials' => [
                    'key' => env('AWS_ACCESS_KEY_ID'),
                    'secret' => env('AWS_SECRET_ACCESS_KEY'),
                ],
            ]);

            // Generate a temporary URL for download
            $cmd = $s3->getCommand('GetObject', [
                'Bucket' => env('AWS_BUCKET'),
                'Key' => $fileName,
            ]);

            $url = $s3->getObjectUrl(env('AWS_BUCKET'), $fileName);
            dd($url,$fileName);
            // Return the temporary URL for download
            return $this->success(['url' => $url]);
        } catch (AwsException $e) {
            // Log the error
            Log::error($e->getMessage());

            // Return an error response
            return $this->error('Failed to generate download URL.');
        }
    }
}


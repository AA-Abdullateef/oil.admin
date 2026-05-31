<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProofFileService
{
    public function download(string $path): BinaryFileResponse
    {
        $path = ltrim($path, '/');

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            abort(404, 'Stored proof URL cannot be served by this application.');
        }

        foreach (['public', 'private'] as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                return response()->file(Storage::disk($disk)->path($path));
            }
        }

        $legacySeedPath = base_path($path);
        if (str_starts_with($path, 'database/seeders/files/') && is_file($legacySeedPath)) {
            return response()->file($legacySeedPath);
        }

        abort(404, 'Proof file was not found.');
    }
}

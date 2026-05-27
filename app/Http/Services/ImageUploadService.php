<?php

namespace App\Http\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageUploadService
{
    /*
     */

    public function store(UploadedFile $file, string $folder = 'id_cards'): string
{
    $originalName = str_replace(' ', '_', $file->getClientOriginalName());
    
    $fileName = time() . "_" . $originalName;
    $path = $file->storeAs("uploads/{$folder}", $fileName, 'public');

    return "storage/" . $path;
}

    public function delete(?string $path): bool
    {
        if (!$path) return false;
        $relativePath = str_replace('storage/', '', $path);
        
        if (Storage::disk('public')->exists($relativePath)) {
            return Storage::disk('public')->delete($relativePath);
        }

        return false;
    }
}
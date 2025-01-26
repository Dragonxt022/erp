<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HasFile
{
    public function uploadFile(UploadedFile $file, string $folder = 'arquivos')
    {
        $originalName = $file->getClientOriginalName();
        $filename = date('Y-m-d_His') . '_' . $originalName;

        // Armazenar o arquivo usando o Storage facade
        Storage::putFileAs($folder, $file, $filename, 'public');

        return $filename;
    }
}

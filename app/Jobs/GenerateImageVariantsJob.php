<?php

namespace App\Jobs;

use App\Models\FileStorage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\Storage;

class GenerateImageVariantsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private FileStorage $file;

    public function __construct(FileStorage $file)
    {
        $this->file = $file;
    }

    public function handle(): void
    {
        if (!Str::startsWith($this->file->mime_type, 'image/')) {
            return;
        }
        // Download original file to temp storage
        $tempPath = tempnam(sys_get_temp_dir(), 'img_');
        $imageContent = Storage::disk($this->file->disk)->get($this->file->file_path);
        file_put_contents($tempPath, $imageContent);
        // Generate variants
        $this->generateThumbnail($tempPath);
        $this->generateMediumSize($tempPath);
        // Cleanup
        unlink($tempPath);
    }

    private function generateThumbnail(string $tempPath): void
    {
        $image = Image::make($tempPath);
        $image->fit(150, 150, function ($constraint) {
            $constraint->aspectRatio();
        });
        // 
        $thumbnailPath = $this->getVariantPath('thumb');
        Storage::disk($this->file->disk)->put(
            $thumbnailPath,
            $image->encode(null, 80)->stream(),
            ['visibility' => 'public']
        );

        $this->file->update([
            'thumbnail_path' => $thumbnailPath
        ]);
    }

    private function generateMediumSize(string $tempPath): void
    {
        $image = Image::make($tempPath);
        $image->fit(450, 450, function ($constraint) {
            $constraint->aspectRatio();
        });

        $mediumPath = $this->getVariantPath('medium');
        Storage::disk($this->file->disk)->put(
            $mediumPath,
            $image->encode(null, 85)->stream(),
            ['visibility' => 'public']
        );

        $this->file->update([
            'medium_path' => $mediumPath
        ]);
    }

    private function getVariantPath(string $variant): string
    {
        $directory = dirname($this->file->file_path);
        $filename = pathinfo($this->file->filename, PATHINFO_FILENAME);
        $extension = pathinfo($this->file->filename, PATHINFO_EXTENSION);

        return "{$directory}/{$filename}_{$variant}.{$extension}";
    }
}

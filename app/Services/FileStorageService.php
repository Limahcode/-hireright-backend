<?php


namespace App\Services;

use App\Jobs\GenerateImageVariantsJob;
use App\Models\FileStorage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Constants\EntityTypes;
use Illuminate\Support\Facades\Log;

class FileStorageService
{
    private $disk;

    public function __construct(string $disk = 's3')
    {
        $this->disk = $disk;
    }

    public function initializeUpload(
        array $fileInfo,
        string $entityType,
        int $entityId,
        ?string $entityGroup = null,
        bool $needsThumbnail = false,
        array $metadata = []
    ) {
        // Check if entity type exists (case-insensitive)
        if (!in_array($entityType, EntityTypes::VALID_TYPES, true)) {
            // Generate a comma-separated list of supported types
            $supportedTypes = implode(', ', EntityTypes::VALID_TYPES);
            // Return a JSON response with the error message and supported types
            return response()->json([
                'errors' => "Unsupported entity type. Supported types are: $supportedTypes."
            ], 422);
        }
        //
        $filename = Str::uuid() . '-' . Str::slug($fileInfo['name']) . '.' . pathinfo($fileInfo['name'], PATHINFO_EXTENSION);
        //
        $basePath = $entityGroup ? Str::slug($entityGroup) . '/' . strtolower($entityType) : strtolower($entityType);
        $filePath =  'media/' . $basePath . '/' . $filename;
        // Create pending file record
        $file = FileStorage::create([
            'original_name' => $fileInfo['name'],
            'filename' => $filename,
            'file_path' => $filePath,
            'mime_type' => $fileInfo['type'],
            'size' => $fileInfo['size'],
            'disk' => $this->disk,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'entity_group' => $entityGroup,
            'needs_thumbnail' => $needsThumbnail,
            'metadata' => $metadata,
            'visibility' => 'private',
            'status' => FileStorage::STATUS_PENDING,
            'upload_expires_at' => now()->addHours(1)
        ]);
        // Generate signed PUT URL for upload
        $signedUrl = Storage::temporaryUploadUrl(
            $filePath,
            $file->upload_expires_at,
            [
                'ContentType' => $fileInfo['type'],
                'ContentLength' => $fileInfo['size']
            ]
        );
        //
        return [
            'file_id' => $file->id,
            'upload_url' => $signedUrl['url'],
            'expires_at' => $file->upload_expires_at
        ];
    }

    public function confirmUpload(FileStorage $file): bool
    {
        //
        if (!Storage::disk($file->disk)->exists($file->file_path)) {
            $file->update(['status' => FileStorage::STATUS_FAILED]);
            return false;
        }
        //
        $file->update(['status' => FileStorage::STATUS_UPLOADED]);
        // Generate thumbnail if needed (implement async job for this)
        if ($file->needs_thumbnail && Str::startsWith($file->mime_type, 'image/')) {
            dispatch(new GenerateImageVariantsJob($file));
        }
        return true;
    }

    public function getEntityImages($groupId, $entityType, $entityId): array
    {
        // Check if entity type exists (case-insensitive)
        if (!in_array($entityType, EntityTypes::VALID_TYPES, true)) {
            return response()->json(['errors' => "Invalid entity type"], 422);
        }
        //
        $files = $this->getEntityFiles(
            $groupId,
            $entityType,
            $entityId,
            [
                'entity_group' => $groupId,
                'mime_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
                'status' => FileStorage::STATUS_UPLOADED
            ]
        )->whereNull('deleted_at');

        if (!$files || empty($files)) {
            return [];
        }
        //
        return $files->map(function ($file) {
            return [
                'id' => $file->id,
                'original_name' => $file->original_name,
                'mime_type' => $file->mime_type,
                'size' => $file->size,
                'url' => Storage::temporaryUrl(
                    $file->file_path,
                    now()->addMinutes(30)
                ),
                'created_at' => $file->created_at
            ];
        })->toArray();
    }

    public function getEntityVideos($groupId, $entityType, $entityId): array
    {
        // Check if entity type exists (case-insensitive)
        if (!in_array($entityType, EntityTypes::VALID_TYPES, true)) {
            return response()->json(['errors' => "Invalid entity type"], 422);
        }
        //
        $files = $this->getEntityFiles(
            $groupId,
            $entityType,
            $entityId,
            [
                'entity_group' => $groupId,
                'mime_types' => ['video/mp4', 'video/webm', 'video/quicktime'],
                'status' => FileStorage::STATUS_UPLOADED
            ]
        )->whereNull('deleted_at');

        if (!$files || empty($files)) {
            return [];
        }

        return $files->map(function ($file) {
            return [
                'id' => $file->id,
                'original_name' => $file->original_name,
                'mime_type' => $file->mime_type,
                'size' => $file->size,
                'url' => Storage::temporaryUrl(
                    $file->file_path,
                    now()->addMinutes(30)
                ),
                'created_at' => $file->created_at
            ];
        })->toArray();
    }

    public function getFile($groupId, int $fileId, $size = 'thumbnail'): array|bool
    {
        $query = FileStorage::where('id', $fileId)
            ->where('status', FileStorage::STATUS_UPLOADED)
            ->whereNull('deleted_at');

        if ($groupId !== null) {
            $query->where('entity_group', $groupId);
        }

        $file = $query->first();

        if (!$file) {
            return null;
        }

        $url = $this->getImageUrl($file, $size);

        return [
            'id' => $file->id,
            'original_name' => $file->original_name,
            'mime_type' => $file->mime_type,
            'size' => $file->size,
            'url' => $url,
            'size' => $size
        ];
    }

    public function deleteFile($groupId, int $fileId): bool
    {
        $query = FileStorage::query()->where('id', $fileId)
            ->whereNull('deleted_at');
        //
        if ($groupId !== null) {
            $query->where('entity_group', $groupId);
        }
        //
        $file = $query->first();
        //
        if (!$file) {
            return false;
        }
        // Delete from storage
        Storage::delete($file->file_path);
        // Soft delete the record
        return (bool) $file->delete();
    }

    public function getEntityPrimaryPublicImage($entityType, $entityId, $size = 'thumbnail'): ?array
    {
        if (!in_array($entityType, EntityTypes::VALID_TYPES, true)) {
            return response()->json(['errors' => "Invalid entity type"], 422);
        }
        //
        $file = FileStorage::where([
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'status' => FileStorage::STATUS_UPLOADED
        ])->whereNull('deleted_at')
            ->ofMimeType(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
            ->orderBy('id', 'asc')  // Get the first uploaded image
            ->first();

        if (!$file) {
            return null;
        }

        $url = $this->getImageUrl($file, $size);

        return [
            'id' => $file->id,
            'url' => $url,
            'size' => $size
        ];
    }

    public function getEntityPublicImages($entityType, $entityId, $size = 'medium'): array
    {
        if (!in_array($entityType, EntityTypes::VALID_TYPES, true)) {
            return response()->json(['errors' => "Invalid entity type"], 422);
        }
        //
        $files = FileStorage::query()
            ->where([
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'status' => FileStorage::STATUS_UPLOADED
            ])
            ->whereNull('deleted_at')
            ->ofMimeType(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
            ->get();

        if (!$files || empty($files)) {
            return [];
        }
        // 
        return $files->map(function ($file) use ($size) {
            $url = $this->getImageUrl($file, $size);
            // 
            return [
                'id' => $file->id,
                'url' => $url,
                'size' => $size
            ];
        })->toArray();
        // 
    }

    private function getImageUrl(FileStorage $file, $size = ''): string
    {
        // Check for requested size
        $path = match ($size) {
            'thumbnail' => $file->thumbnail_path,
            'medium' => $file->medium_path,
            'original' => $file->file_path,
            default => $file->medium_path
        };
        // If we have the requested size and it's public
        if ($path && ($size === 'thumbnail' || $size === 'medium')) {
            return Storage::url($path);
        }
        // Fall back to signed URL for original
        return Storage::temporaryUrl(
            $file->file_path,
            now()->addHours(2)
        );
    }

    public function getEntityFiles($groupId, $entityType, $entityId, array $options = []): EloquentCollection
    {
        try {
            // Start the query
            $query = FileStorage::query()
                ->where([
                    'entity_type' => $entityType,
                    'entity_id' => $entityId
                ]);
            // Add group filter if provided
            if ($groupId !== null) {
                $query->where('entity_group', $groupId);
            }
            // Apply additional query options
            $this->applyQueryOptions($query, $options);
            // Execute the query and map the results
            $files = $query->get();
            // If no files are found, return an empty Eloquent Collection
            if ($files->isEmpty()) {
                return new EloquentCollection();
            }
            // Map the files to the desired format
            $mappedFiles = $files->map(function ($file) {
                return [
                    'id'            => $file->id,
                    'original_name' => $file->original_name,
                    'mime_type'     => $file->mime_type,
                    'size'          => $file->size,
                    'url'           => $this->getImageUrl($file),
                ];
            });
            // Convert the mapped Support Collection into an Eloquent Collection
            return new EloquentCollection($mappedFiles->all());
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Failed to retrieve entity files', [
                'groupId' => $groupId,
                'entityType' => $entityType,
                'entityId' => $entityId,
                'error' => $e->getMessage(),
            ]);
            // Return an empty Eloquent Collection in case of failure
            return new EloquentCollection();
        }
    }

    public function getGroupFiles($entityGroup, array $options = []): EloquentCollection
    {
        try {
            $query = FileStorage::query()->where('entity_group', $entityGroup);

            $this->applyQueryOptions($query, $options);
            //
            $files = $query->get();
            // If no files are found, return an empty Eloquent Collection
            if ($files->isEmpty()) {
                return new EloquentCollection();
            }
            //
            return $files->map(function ($file) {
                return [
                    'id' => $file->id,
                    'original_name' => $file->original_name,
                    'mime_type' => $file->mime_type,
                    'size' => $file->size,
                    'url' => $this->getImageUrl($file),
                ];
            });
            //
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Failed to retrieve entity files', [
                'error' => $e->getMessage(),
            ]);
            // Return an empty Eloquent Collection in case of failure
            return new EloquentCollection();
        }
    }


    protected function applyQueryOptions(Builder $query, array $options): void
    {
        if (isset($options['visibility'])) {
            $query->where('visibility', $options['visibility']);
        }

        if (isset($options['mime_types'])) {
            $query->ofMimeType($options['mime_types']);
        }

        if (isset($options['status'])) {
            $query->where('status', $options['status']);
        }

        if (!empty($options['metadata'])) {
            foreach ($options['metadata'] as $key => $value) {
                $query->where("metadata->$key", $value);
            }
        }
        // Apply sorting
        $sortField = $options['sort_by'] ?? 'created_at';
        $sortDirection = $options['sort_direction'] ?? 'desc';
        $query->orderBy($sortField, $sortDirection);
    }
}

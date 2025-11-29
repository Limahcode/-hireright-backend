<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FileStorage extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'original_name',
        'filename',
        'file_path',
        'thumbnail_path',
        'mime_type',
        'size',
        'disk',
        'entity_type',
        'entity_id',
        'entity_group',
        'needs_thumbnail',
        'metadata',
        'visibility',
        'status',
        'upload_expires_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'needs_thumbnail' => 'boolean',
        'size' => 'integer',
        'upload_expires_at' => 'datetime'
    ];
    //
    protected $hidden = ['disk', 'visibility', 'status'];
    //
    const STATUS_PENDING = 'pending';
    const STATUS_UPLOADED = 'uploaded';
    const STATUS_FAILED = 'failed';
    //
    const VISIBILITY_PRIVATE = 'private';
    const VISIBILITY_PUBLIC = 'public';

    public function storable()
    {
        return $this->morphTo('entity');
    }

    protected function getUrlOptions(array $customOptions = []): array
    {
        $options = [];
        // Set content type if available
        if ($this->mime_type) {
            $options['ResponseContentType'] = $this->mime_type;
        }
        return array_merge($options, $customOptions);
    }

    // Query Scopes
    public function scopeOfEntity(Builder $query, Model $entity): Builder
    {
        return $query->where([
            'entity_type' => get_class($entity),
            'entity_id' => $entity->id
        ]);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('entity_type', $type);
    }

    public function scopeInGroup(Builder $query, string $group): Builder
    {
        return $query->where('entity_group', $group);
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('visibility', self::VISIBILITY_PUBLIC);
    }

    public function scopePrivate(Builder $query): Builder
    {
        return $query->where('visibility', self::VISIBILITY_PRIVATE);
    }

    public function scopeUploaded(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_UPLOADED);
    }

    public function scopeOfMimeType(Builder $query, string|array $mimeTypes): Builder
    {
        if (empty($mimeTypes)) {
            return $query;
        }
        return $query->whereIn('mime_type', (array)$mimeTypes);
    }

    public function scopeImages(Builder $query): Builder
    {
        return $query->where('mime_type', 'like', 'image/%');
    }

    public function scopeDocuments(Builder $query): Builder
    {
        return $query->whereIn('mime_type', [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            // Add more document types as needed
        ]);
    }
}

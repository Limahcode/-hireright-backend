<?php

namespace App\Constants;

class EntityTypes
{
    const VALID_TYPES = [
        'Test',
        'QuestionFile',
        'JobPostingFile',
        'HomeBanner',
        'BlogPost',
        'CandidateResume',
        'UserProfile',
        'CompanyLogo',
        'CoverLetter',
        'Certification'
    ];

    public const FILE_TYPE_CONFIGS = [
        'image' => [
            'mime_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            'max_size' => 5242880, // 5MB
            'variants' => ['thumbnail', 'medium', 'original'],
            'process_job' => 'App\Jobs\GenerateImageVariantsJob'
        ],
        'video' => [
            'mime_types' => ['video/mp4', 'video/webm', 'video/quicktime'],
            'max_size' => 104857600, // 100MB
            'variants' => ['preview', 'original'],
            'process_job' => 'App\Jobs\ProcessVideoJob'
        ],
        'document' => [
            'mime_types' => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ],
            'max_size' => 10485760, // 10MB
            'variants' => ['preview', 'original'],
            'process_job' => null
        ],
        'data' => [
            'mime_types' => [
                'text/csv',
                'application/json',
                'application/sql',
                'text/plain'
            ],
            'max_size' => 52428800, // 50MB
            'variants' => ['original'],
            'process_job' => null
        ]
    ];

    // Define which entities can have which file types
    public const ENTITY_FILE_TYPES = [
        'Product' => ['image', 'video', 'document'],
        'User' => ['image', 'document'],
        'JobApplication' => ['document', 'image'],
        'Company' => ['image', 'video', 'document'],
        'Job' => ['document']
    ];

    // Define visibility rules
    public const VISIBILITY_RULES = [
        'Product' => [
            'image' => 'public',
            'video' => 'public',
            'document' => 'private'
        ],
        'JobApplication' => [
            'document' => 'private',
            'image' => 'private'
        ],
        'User' => [
            'image' => 'public',
            'document' => 'private'
        ]
    ];
}

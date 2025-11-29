<?php

namespace App\Http\Controllers;

use App\Models\FileStorage;
use App\Models\User;
use App\Services\FileStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class FileStorageController extends Controller
{
    public function initializeUpload(Request $request, FileStorageService $service)
    {
        // Validate the request data
        try {
            $validated = $request->validate([
                'files' => 'required|array',
                'files.*.name' => 'required|string',
                'files.*.type' => 'required|string',
                'files.*.size' => 'required|integer',
                'entity_type' => 'required|string',
                'entity_id' => 'required|integer',
                'entity_group' => 'nullable|string',
                'needs_thumbnail' => 'boolean',
                'metadata' => 'array'
            ]);
        } catch (ValidationException $e) {
            // Return a JSON response with validation errors and a 422 status code
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors(),
            ], 422);
        }

        $userId = Auth::id();
        // Re-fetch the user using the User model
        $user = User::findOrFail($userId);

        //
        $uploads = [];
        foreach ($validated['files'] as $fileInfo) {
            $uploads[] = $service->initializeUpload(
                $fileInfo,
                $validated['entity_type'],
                $validated['entity_id'],
                $validated['entity_group'] ?? null,
                $validated['needs_thumbnail'] ?? false,
                $validated['metadata'] ?? []
            );
        }
        return response()->json(['uploads' => $uploads]);
    }

    public function confirmUpload(Request $request, FileStorageService $service)
    {
        $groupId = $request->query('groupId');
        // Validate the incoming request
        $validated = $request->validate([
            'file_id' => 'required|integer'
        ]);
        // Start the query
        $query = FileStorage::where('id', $validated['file_id']);
        // Include entity_group only if $groupId is not null
        if ($groupId !== null) {
            $query->where('entity_group', $groupId);
        }
        // Find the file
        $file = $query->first();
        // Return error if file is not found
        if (!$file) {
            return response()->json(['status' => 'failed', 'message' => 'File not found'], 404);
        }
        // Confirm upload using the service
        if ($service->confirmUpload($file)) {
            return response()->json(['status' => 'success']);
        }
        return response()->json(['status' => 'failed'], 400);
    }


    public function getFile(Request $request, FileStorageService $service, $fileId)
    {
        $groupId = $request->query('groupId');
        //
        return $service->getFile($groupId, $fileId);
    }

    public function getFiles(Request $request, FileStorageService $service)
    {
        // Retrieve query parameters
        $entityType = $request->query('entityType');
        $entityId = $request->query('entityId');
        $groupId = $request->query('groupId');

        return $service->getEntityFiles($groupId, $entityType, $entityId);
    }

    public function deleteFile(Request $request, int $fileId, FileStorageService $service)
    {
        $groupId = $request->query('groupId');

        if ($service->deleteFile($groupId, $fileId)) {
            return response()->json(['status' => 'success']);
        }
        //
        return response()->json(['status' => 'failed', 'message' => 'File not found or already deleted'], 404);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    /**
     * Check system health
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function check(): JsonResponse
    {
        $status = [
            'status' => 'up',
            'timestamp' => now()->toISOString(),
            'services' => [
                'app' => [
                    'status' => 'up',
                ],
                'database' => [
                    'status' => 'up',
                ],
            ],
        ];

        try {
            // Check database connection
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $status['status'] = 'error';
            $status['services']['database']['status'] = 'down';
            $status['services']['database']['error'] = config('app.debug') ? $e->getMessage() : 'Database connection failed';
        }

        $httpStatus = $status['status'] === 'up' ? 200 : 503;

        return response()->json($status, $httpStatus)
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
    }
}
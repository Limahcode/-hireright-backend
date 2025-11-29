<?php

// Create service directory and class
// app/Services/JobService.php
namespace App\Services;

use App\Models\Job;
use App\Models\Test;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class JobService
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    public function createJob(array $data): Job
    {
        // Start transaction
        return DB::transaction(function () use ($data) {
            // Create job
            $job = Job::create($data);
            // Attach skills
            $job->skills()->attach($data['skills']);
            //
            if (isset($data['test_id'])) {
                $this->assignTest($job, $data['test_id']);
            }
            // 
            $this->notifyMatchingCandidates($job);
            return $job;
        });
    }

    private function assignTest(Job $job, int $testId): void
    {
        $test = Test::findOrFail($testId);
        $job->test()->associate($test);
        $job->save();
    }

    private function notifyMatchingCandidates(Job $job): void
    {
        // Find matching job alerts
        // $matchingAlerts = $this->findMatchingJobAlerts($job);

        // foreach ($matchingAlerts as $alert) {
        //     $this->notificationService->notifyUser(
        //         $alert->user_id,
        //         'New matching job found',
        //         "A new job matching your alert '{$alert->title}' has been posted"
        //     );
        // }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Arr;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // HR platform specific notification messages
        $notifications = [
            // Job Application Related
            ['title' => 'New Job Application', 'message' => 'A new candidate has applied for the Senior Developer position.'],
            ['title' => 'Application Status Update', 'message' => 'Your application for Product Manager at Tech Corp has been reviewed.'],
            ['title' => 'Interview Scheduled', 'message' => 'Your interview has been scheduled for next Tuesday at 2 PM.'],
            ['title' => 'Application Shortlisted', 'message' => 'Congratulations! Your profile has been shortlisted for the next round.'],
            
            // Profile Related
            ['title' => 'Profile Completion Reminder', 'message' => 'Complete your profile to improve visibility to potential employers.'],
            ['title' => 'Profile Views Update', 'message' => 'Your profile was viewed by 5 companies this week.'],
            ['title' => 'Skills Endorsement', 'message' => 'John Smith has endorsed you for Project Management skills.'],
            ['title' => 'Profile Verification', 'message' => 'Your profile has been verified. You can now apply to verified jobs.'],
            
            // Job Posting Related
            ['title' => 'New Job Match', 'message' => 'We found 3 new jobs matching your preferences.'],
            ['title' => 'Job Post Expiring', 'message' => 'Your job posting for Marketing Manager will expire in 3 days.'],
            ['title' => 'Featured Job Available', 'message' => 'A new featured position is available in your industry.'],
            ['title' => 'Job Post Performance', 'message' => 'Your job post has received 50 applications this week.'],
            
            // Company Related
            ['title' => 'Company Profile Update', 'message' => 'Please update your company information to maintain verified status.'],
            ['title' => 'New Company Review', 'message' => 'A new review has been posted about your company.'],
            ['title' => 'Company Verification', 'message' => 'Your company has been successfully verified.'],
            ['title' => 'Team Member Invited', 'message' => 'You have been invited to join Tech Innovators Ltd as a team member.'],
            
            // Assessment Related
            ['title' => 'Assessment Invitation', 'message' => 'Complete the technical assessment for your application.'],
            ['title' => 'Assessment Completed', 'message' => 'Candidate John Doe has completed the required assessment.'],
            ['title' => 'Assessment Results', 'message' => 'Your assessment results are now available.'],
            ['title' => 'New Assessment Required', 'message' => 'A new skill assessment is required for your application.'],
            
            // Platform Updates
            ['title' => 'Account Security Alert', 'message' => 'We noticed a login from a new device. Please verify if this was you.'],
            ['title' => 'Subscription Status', 'message' => 'Your premium recruitment plan will renew in 7 days.'],
            ['title' => 'New Feature Available', 'message' => 'Try our new AI-powered candidate matching system.'],
            ['title' => 'Platform Maintenance', 'message' => 'Scheduled maintenance: System will be updated this weekend.'],
            
            // Event Related
            ['title' => 'Upcoming Job Fair', 'message' => 'Virtual job fair starting next week. Register now!'],
            ['title' => 'Workshop Invitation', 'message' => 'Join our resume writing workshop this Friday.'],
            ['title' => 'Networking Event', 'message' => 'Tech Industry Networking Event - RSVP now.'],
            ['title' => 'Webinar Reminder', 'message' => 'Your registered webinar starts in 24 hours.'],
            
            // Recruitment Process
            ['title' => 'Candidate Feedback', 'message' => 'Please provide feedback for yesterday\'s interview candidates.'],
            ['title' => 'Offer Letter Status', 'message' => 'Candidate has accepted the job offer.'],
            ['title' => 'Interview Panel Update', 'message' => 'You\'ve been added to the interview panel for tomorrow.'],
            ['title' => 'Background Check', 'message' => 'Background verification process has been initiated.'],
            
            // Analytics and Reports
            ['title' => 'Weekly Recruitment Report', 'message' => 'Your weekly recruitment analytics report is ready.'],
            ['title' => 'Application Pipeline Update', 'message' => 'Review your updated recruitment pipeline stats.'],
            ['title' => 'Hiring Goals Progress', 'message' => 'You\'ve achieved 75% of your quarterly hiring goals.'],
            ['title' => 'Industry Insights', 'message' => 'New salary insights report available for your industry.']
        ];

        // Define possible types for HR notifications
        $notificationTypes = [
            'application',
            'profile',
            'job_posting',
            'company',
            'assessment',
            'system',
            'event',
            'recruitment'
        ];

        // Loop through each user
        User::all()->each(function ($user) use ($notifications, $notificationTypes) {
            // Check if the user already has at least 5 notifications
            if (Notification::where('user_id', $user->id)->count() >= 5) {
                return; // Skip this user
            }

            // Generate a random number of notifications between 5 and 10
            $notificationCount = rand(5, 10);

            // Create notifications for the user based on their role
            for ($i = 0; $i < $notificationCount; $i++) {
                // Randomly select a notification from the pool
                $notificationData = Arr::random($notifications);
                
                Notification::create([
                    'user_id' => $user->id,
                    'title' => $notificationData['title'],
                    'message' => $notificationData['message'],
                    'type' => Arr::random($notificationTypes),
                    'is_read' => (bool) rand(0, 1), // Randomly mark as read/unread
                    'status' => 'active', // Default to 'active' status
                ]);
            }
        });
    }
}
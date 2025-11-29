<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Post;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $posts = [
            [
                'title' => 'How to Write a Resume That Stands Out in 2025',
                'content' => '<p>A well-crafted resume is your ticket to landing your dream job. Learn the essential elements of a modern resume, including <strong>AI-friendly formatting</strong>, <strong>skills-based highlighting</strong>, and how to showcase your remote work experience effectively.</p>',
                'status' => 'published',
                'published_at' => Carbon::now(),
                'category_id' => 1,
                'author_id' => 1,
            ],
            [
                'title' => 'Top Interview Questions for Software Engineers',
                'content' => '<p>Prepare for your next tech interview with our comprehensive guide covering both <strong>technical skills assessment</strong> and <strong>behavioral questions</strong>. Include real-world examples and best practices for remote interviews.</p>',
                'status' => 'published',
                'published_at' => Carbon::now(),
                'category_id' => 1,
                'author_id' => 1,
            ],
            [
                'title' => 'Building an Inclusive Workplace Culture',
                'content' => '<p>Learn how to create and maintain an inclusive workplace environment that values <strong>diversity</strong>, promotes <strong>equity</strong>, and ensures everyone feels welcomed and respected.</p>',
                'status' => 'published',
                'published_at' => Carbon::now(),
                'category_id' => 2,
                'author_id' => 1,
            ],
            [
                'title' => 'The Future of Remote Work: Trends and Best Practices',
                'content' => '<p>Explore the evolving landscape of remote work, including <strong>hybrid work models</strong>, <strong>digital collaboration tools</strong>, and strategies for maintaining team productivity and engagement.</p>',
                'status' => 'published',
                'published_at' => Carbon::now(),
                'category_id' => 2,
                'author_id' => 1,
            ],
            [
                'title' => 'Effective Employee Onboarding Strategies',
                'content' => '<p>Discover how to create a seamless onboarding experience that helps new hires integrate quickly and effectively, whether in-person or remote, focusing on <strong>cultural integration</strong> and <strong>role clarity</strong>.</p>',
                'status' => 'published',
                'published_at' => Carbon::now(),
                'category_id' => 3,
                'author_id' => 1,
            ],
            [
                'title' => 'Navigating Career Transitions Successfully',
                'content' => '<p>Tips and strategies for successfully changing careers, including <strong>skill assessment</strong>, <strong>networking strategies</strong>, and how to position your transferable skills effectively.</p>',
                'status' => 'published',
                'published_at' => Carbon::now(),
                'category_id' => 1,
                'author_id' => 1,
            ],
            [
                'title' => 'Understanding Employment Laws and Rights',
                'content' => '<p>A comprehensive guide to essential employment laws, covering <strong>worker rights</strong>, <strong>workplace safety regulations</strong>, and recent legislative changes affecting both employers and employees.</p>',
                'status' => 'published',
                'published_at' => Carbon::now(),
                'category_id' => 4,
                'author_id' => 1,
            ],
            [
                'title' => 'Mastering Salary Negotiations',
                'content' => '<p>Learn effective strategies for negotiating your salary, including <strong>market research</strong>, <strong>timing considerations</strong>, and how to discuss compensation in both initial offers and raises.</p>',
                'status' => 'published',
                'published_at' => Carbon::now(),
                'category_id' => 1,
                'author_id' => 1,
            ],
            [
                'title' => 'The Impact of AI on Recruitment',
                'content' => '<p>Explore how <strong>artificial intelligence</strong> is transforming recruitment processes, from resume screening to interview scheduling, and what it means for both recruiters and job seekers.</p>',
                'status' => 'published',
                'published_at' => Carbon::now(),
                'category_id' => 5,
                'author_id' => 1,
            ],
            [
                'title' => 'Building a Strong Employee Benefits Package',
                'content' => '<p>Guide to creating competitive benefits packages that attract and retain top talent, including <strong>healthcare options</strong>, <strong>flexible work arrangements</strong>, and innovative perks.</p>',
                'status' => 'published',
                'published_at' => Carbon::now(),
                'category_id' => 3,
                'author_id' => 1,
            ],
            [
                'title' => 'Effective Performance Review Techniques',
                'content' => '<p>Best practices for conducting meaningful performance reviews that promote growth and engagement, including <strong>goal setting</strong>, <strong>feedback methods</strong>, and documentation strategies.</p>',
                'status' => 'published',
                'published_at' => Carbon::now(),
                'category_id' => 3,
                'author_id' => 1,
            ],
            [
                'title' => 'Creating an Effective LinkedIn Profile',
                'content' => '<p>Maximize your professional online presence with tips for optimizing your LinkedIn profile, including <strong>keyword optimization</strong>, <strong>content strategy</strong>, and networking techniques.</p>',
                'status' => 'published',
                'published_at' => Carbon::now(),
                'category_id' => 1,
                'author_id' => 1,
            ],
            [
                'title' => 'Managing Remote Teams Successfully',
                'content' => '<p>Learn effective strategies for leading remote teams, including <strong>communication best practices</strong>, <strong>productivity tracking</strong>, and maintaining team cohesion across time zones.</p>',
                'status' => 'published',
                'published_at' => Carbon::now(),
                'category_id' => 2,
                'author_id' => 1,
            ],
            [
                'title' => 'Workplace Mental Health and Wellness',
                'content' => '<p>Strategies for promoting mental health in the workplace, including <strong>stress management</strong>, <strong>work-life balance</strong>, and creating supportive environments.</p>',
                'status' => 'published',
                'published_at' => Carbon::now(),
                'category_id' => 2,
                'author_id' => 1,
            ],
            [
                'title' => 'Essential Skills for Modern HR Professionals',
                'content' => '<p>Explore the key competencies needed in modern HR, from <strong>data analytics</strong> to <strong>digital transformation management</strong>, and strategies for developing these skills.</p>',
                'status' => 'published',
                'published_at' => Carbon::now(),
                'category_id' => 3,
                'author_id' => 1,
            ]
        ];

        foreach ($posts as $post) {
            // Generate slug based on title
            $slug = Str::slug($post['title']);
            // Check if a post with this slug already exists
            if (Post::where('slug', $slug)->exists()) {
                continue; // Skip if slug already exists
            }
            // Insert post with slug
            DB::table('posts')->insert([
                'title' => $post['title'],
                'slug' => $slug,
                'content' => $post['content'],
                'status' => $post['status'],
                'published_at' => $post['published_at'],
                'category_id' => $post['category_id'],
                'author_id' => $post['author_id'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
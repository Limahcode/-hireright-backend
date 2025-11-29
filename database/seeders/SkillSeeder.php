<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SkillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $skills = [
            // Programming Languages
            'PHP',
            'JavaScript',
            'Python',
            'Java',
            'C++',
            'Ruby',
            'Swift',
            // Frameworks
            'Laravel',
            'React',
            'Vue.js',
            'Angular',
            'Django',
            'Spring Boot',
            // Databases
            'MySQL',
            'PostgreSQL',
            'MongoDB',
            'Redis',
            // Cloud Platforms
            'AWS',
            'Azure',
            'Google Cloud',
            // DevOps
            'Docker',
            'Kubernetes',
            'Jenkins',
            'Git',
            // Soft Skills
            'Leadership',
            'Communication',
            'Problem Solving',
            'Team Management',
            'Project Management',
            'Agile Methodology',
            'Scrum',
            // Business Skills
            'Business Analysis',
            'Product Management',
            'Strategic Planning',
            // HR Skills
            'Recruitment',
            'Employee Relations',
            'Performance Management',
            'Training & Development',
            'Compensation & Benefits',
            // Marketing Skills
            'Digital Marketing',
            'Content Marketing',
            'SEO',
            'Social Media Marketing',
            // Design Skills
            'UI Design',
            'UX Design',
            'Graphic Design',
            'Adobe Creative Suite'
        ];

        foreach ($skills as $skillName) {
            // Check if skill exists by slug
            Skill::firstOrCreate(
                ['name' => $skillName],
                [
                    'name' => $skillName,
                    'description' => "Knowledge and expertise in $skillName"
                ]
            );
        }
    }
}

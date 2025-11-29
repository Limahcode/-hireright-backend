# HireRight Backend Complete Documentation

**Submitted By:** Abimbola (LimahCode)  
**Date:** November 26, 2024  
**Project:** HireRight - Recruitment Platform Backend  
**Branch:** hireright_backend  
**Repository:** https://github.com/GaniyuMubarak/hirerightProject

---

## Table of Contents

1. [Executive Summary](#executive-summary)
2. [Project Overview](#project-overview)
3. [Technical Architecture](#technical-architecture)
4. [Database Schema](#database-schema)
5. [API Documentation](#api-documentation)
6. [Models Documentation](#models-documentation)
7. [Controllers Documentation](#controllers-documentation)
8. [Validation Rules](#validation-rules)
9. [Authentication & Security](#authentication--security)
10. [Issues Found & Fixed](#issues-found--fixed)
11. [Recommendations](#recommendations)
12. [Next Steps](#next-steps)

---

## Executive Summary

HireRight is a comprehensive three-sided recruitment platform connecting job seekers (candidates), employers, and platform administrators. The backend is built with Laravel and provides a robust API for job postings, applications, candidate assessments, and company management.

### Key Metrics:
- **Total API Endpoints:** 100+
- **Database Tables:** 40+
- **Models:** 40+
- **Controllers:** 35+
- **Authentication:** JWT-based
- **Key Features:** Job matching, Testing system, AI test generation (pending)

### Project Status:
- ✅ Core functionality implemented
- ✅ Authentication system functional
- ✅ Database migrations complete
- ⚠️ AI test generation feature (assigned for implementation)
- ⚠️ Some placeholder features (applicant counts, test counts)

---

## Project Overview

### Purpose
HireRight streamlines the recruitment process by providing:
- **For Candidates:** Job search, applications, profile management, skill assessments
- **For Employers:** Job postings, applicant tracking, custom tests, team management
- **For Admins:** Platform oversight, user management, reporting

### User Types

#### 1. Candidates (Job Seekers)
**Capabilities:**
- Create comprehensive profiles (experience, education, certifications)
- Search and apply for jobs
- Save jobs and set alerts
- Take recruitment tests
- Track application status

#### 2. Employers (Companies)
**Capabilities:**
- Create company profiles
- Post and manage job listings
- Review applications
- Create custom assessment tests
- Manage team members with permissions
- Access hiring analytics

#### 3. Admins (Platform Managers)
**Capabilities:**
- Oversee all users and companies
- Verify companies
- Manage job categories
- Generate platform reports
- Control system settings

---

## Technical Architecture

### Tech Stack

**Backend Framework:**
- Laravel 10.x (PHP 8.2+)
- Composer for dependency management

**Database:**
- MySQL 8.0+
- Eloquent ORM

**Authentication:**
- JWT (tymon/jwt-auth)
- Role-based access control

**Key Packages:**
- JWT Authentication
- Laravel Sanctum (prepared)
- File Storage System
- Job Queue System

### Project Structure
```
hireright_backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/          # Admin controllers
│   │   │   ├── Candidate/      # Candidate controllers
│   │   │   ├── Company/        # Employer controllers
│   │   │   └── AuthController.php
│   │   ├── Middleware/         # Custom middleware
│   │   └── Requests/           # Form requests
│   ├── Models/                 # Eloquent models
│   ├── Services/               # Business logic
│   └── Providers/              # Service providers
├── database/
│   ├── migrations/             # Database migrations
│   └── seeders/                # Database seeders
├── routes/
│   └── api.php                 # API routes
├── config/
│   ├── auth.php                # Auth configuration
│   ├── jwt.php                 # JWT configuration
│   └── database.php            # DB configuration
└── storage/                    # File storage
```

---

## Database Schema

### Total Tables: 40+

### Core Tables by Category:

**Authentication & Users:**
- `users` - User accounts (all types)
- `roles` - User roles
- `permissions` - Granular permissions
- `password_reset_tokens` - Password reset tracking

**Job Management:**
- `job_listings` - Job postings
- `job_applications` - Application submissions
- `job_categories` - Job categorization
- `job_titles` - Standardized job titles
- `saved_jobs` - Bookmarked jobs
- `job_alerts` - Email alerts for matching jobs
- `job_listing_skills` - Required skills (pivot)

**Candidate Profile:**
- `experiences` - Work history
- `education` - Educational background
- `certifications` - Professional certifications
- `skills` - Candidate skills
- `user_skills` - User-skill mapping (pivot)

**Company Management:**
- `companies` - Company profiles
- `company_staff` - Team members
- `industries` - Industry categories

**Testing System:**
- `tests` - Assessment tests
- `test_questions` - Individual questions
- `question_options` - Multiple choice options (stored in test_questions.settings as JSON)
- `question_attachments` - File attachments
- `test_assignments` - Tests assigned to candidates
- `test_submissions` - Completed tests
- `candidate_responses` - Individual answers
- `response_attachments` - Answer attachments
- `test_categories` - Test categorization
- `test_tags` - Test tagging
- `test_sections` - Test organization
- `test_category_mappings` - Category associations (pivot)
- `test_tag_mappings` - Tag associations (pivot)
- `test_job_title_mappings` - Job title associations (pivot)
- `recruitment_stages` - Hiring pipeline stages
- `stage_test_mappings` - Stage-test associations
- `a_i_generated_tests` - AI-generated tests (feature pending)

**File Management:**
- `file_storages` - Uploaded files (resumes, attachments, logos)

**System:**
- `notifications` - In-app notifications
- `migrations` - Migration tracking
- `cache` - Application cache
- `jobs` - Queue jobs
- `failed_jobs` - Failed queue jobs

### Key Relationships:
```
users
├── belongsTo: Company
├── hasMany: Experiences
├── hasMany: Education
├── hasMany: Certifications
├── hasMany: JobApplications
├── hasMany: SavedJobs
└── hasMany: TestAssignments

companies
├── hasMany: Users (employees)
├── hasMany: JobListings
├── hasMany: CompanyStaff
└── hasMany: Tests

job_listings
├── belongsTo: Company
├── hasMany: JobApplications
├── hasMany: SavedJobs
└── belongsToMany: Skills

job_applications
├── belongsTo: User (candidate)
├── belongsTo: JobListing
└── hasMany: TestAssignments

tests
├── belongsTo: Company
├── hasMany: TestQuestions
├── hasMany: TestAssignments
└── belongsToMany: JobListings

test_assignments
├── belongsTo: Test
├── belongsTo: User (candidate)
├── belongsTo: JobApplication
├── hasOne: TestSubmission
└── hasMany: CandidateResponses
```

### Database Schema Diagram:
[Include a visual diagram if time permits - tools: dbdiagram.io, draw.io]

**Detailed table structures available in:** `DATABASE_SCHEMA.md`

---

## API Documentation

### Base URL
```
http://127.0.0.1:8000/api
```

### Authentication
All protected endpoints require JWT token in header:
```
Authorization: Bearer {token}
```

### Response Format

**Success Response:**
```json
{
  "status": "success",
  "message": "Operation completed",
  "data": { ... }
}
```

**Error Response:**
```json
{
  "status": "error",
  "message": "Error description",
  "errors": { ... }
}
```

### Endpoint Summary

**Total Endpoints:** 100+

**By Category:**
- Authentication: 8 endpoints
- Candidate Operations: 40+ endpoints
- Employer Operations: 30+ endpoints
- Admin Operations: 20+ endpoints
- File Storage: 4 endpoints
- Common: 2 endpoints

**Detailed endpoint documentation available in:** `API_ROUTES_DOCUMENTATION.md`

### Key Endpoints:

**Authentication:**
- POST /auth/register
- POST /auth/login
- POST /auth/logout
- POST /auth/forgot/password
- POST /auth/reset/password

**Candidates:**
- GET /candidates/dashboard
- GET /candidates/profile
- POST /candidates/profile
- GET /candidates/jobs
- POST /candidates/jobs/{id}/apply
- GET /candidates/tests/assigned
- POST /candidates/tests/{id}/submit

**Employers:**
- GET /employers/dashboard
- POST /employers/company
- POST /employers/jobs
- GET /employers/jobs/{id}/applications
- POST /employers/tests
- POST /employers/tests/{id}/questions

**Admin:**
- GET /admin/users
- PUT /admin/users/{id}/status
- GET /admin/companies
- PUT /admin/companies/{id}/verify
- GET /admin/reports/users

---

## Models Documentation

### Total Models: 40+

### Key Models:

**User Model**
- Implements: JWTSubject (for JWT authentication)
- Key relationships: Company, Experiences, Education, Certifications
- Features: OTP verification, role-based access, social media profiles
- Security: Hidden OTP fields, hashed passwords

**Company Model**
- Relationships: Users, JobListings, CompanyStaff, Tests
- Features: Slug generation, verification status, employee size ranges
- Methods: getFullAddress(), getSizeRange(), getActiveSocialLinks()

**JobListing Model**
- Relationships: Company, JobApplications, SavedJobs, Skills
- Features: Status management, visibility control

**JobApplication Model**
- Relationships: User, JobListing, TestAssignments
- Status tracking: pending, under_review, shortlisted, rejected, accepted

**Test Model**
- Relationships: Company, TestQuestions, TestAssignments, JobListings
- Features: Test categories, tags, sections, time limits

**TestAssignment Model**
- Relationships: Test, User, JobApplication, TestSubmission, CandidateResponses
- Status: assigned, in_progress, completed, expired, graded
- Scoring: Automatic and manual grading support

**Detailed model documentation available in:** `MODELS_DOCUMENTATION.md`

---

## Controllers Documentation

### Total Controllers: 35+

### By Category:

**Admin Controllers (7):**
- AdminController
- AdminTestController
- CompanyController (Admin)
- JobCategoryController
- ReportController
- SettingController
- UserController

**Candidate Controllers (9):**
- CandidateController
- CandidateJobApplicationController
- CertificationController
- EducationController
- ExperienceController
- JobAlertController
- SavedJobController
- TestController
- TestSubmissionController

**Company Controllers (5):**
- CompanyController
- CompanyJobController
- CompanyStaffController
- CompanyTestController
- JobApplicationController

**Common Controllers:**
- AuthController
- UserProfileController
- FileStorageController
- NotificationController
- HealthController

**Detailed controller documentation available in:** `CONTROLLERS_DOCUMENTATION.md`

---

## Validation Rules

### Authentication Validation

**Registration:**
- first_name, last_name: required, string
- email: required, email, unique
- password: required, confirmed, min:8
- phone: optional, string
- app_role: optional (defaults to 'candidate')

**Login:**
- email: required, email
- password: required

### Candidate Profile Validation

**Education:**
- institution, degree, field_of_study: required
- dates: must be before_or_equal:today
- description: max:2000 chars

**Experience:**
- company_name, job_title: required
- employment_type: enum [full_time, part_time, freelance, contract, internship]
- description: max:5000 chars

**Certifications:**
- name, organization, issue_date: required
- expiration_date: must be after issue_date

### Company Validation

**Company Profile:**
- name: required, max:255
- email: nullable, email
- website: nullable, url
- size_max: must be greater than size_min
- All social URLs: valid URL format

**Detailed validation rules available in:** `VALIDATION_DOCUMENTATION.md`

---

## Authentication & Security

### Authentication Method: JWT (JSON Web Tokens)

**Implementation:**
- Package: tymon/jwt-auth
- Token location: Authorization header
- Token format: Bearer {token}

### Security Features:

**1. Role-Based Access Control (RBAC)**
- Middleware: `auth:api`, `can:candidate`, `can:employer`, `role:admin`
- Prevents cross-role access
- Gates and policies for fine-grained control

**2. Password Security**
- Automatic hashing (bcrypt)
- Password confirmation required
- OTP-based password reset
- OTP expiration (30 minutes)

**3. Multi-Factor Authentication**
- Email OTP verification
- Phone OTP verification
- OTP expiration tracking

**4. API Security**
- All sensitive endpoints protected
- Token validation on every request
- Token invalidation on logout
- Request validation

**5. Data Protection**
- Hidden sensitive fields (passwords, OTPs)
- Input sanitization
- SQL injection protection (via Eloquent)
- XSS protection

### Security Testing Results:

**Detailed security testing available in:** `AUTHENTICATION_TESTING_REPORT.md`

---

## Issues Found & Fixed

### Issue 1: Migration Error - test_assignments Table

**Problem:**
```php
$table->timestamp('expires_at')->default(now());
```
Using `Symfony\Component\Clock\now()` caused conversion error:
```
Object of class Symfony\Component\Clock\DatePoint could not be converted to string
```

**Root Cause:**
- `now()` returns a DatePoint object
- Migrations need SQL-compatible default values
- Database can't convert PHP objects to SQL

**Fix:**
```php
use Illuminate\Support\Facades\DB;

$table->timestamp('expires_at')->default(DB::raw('CURRENT_TIMESTAMP'));
```

**Status:** ✅ Fixed

---

### Issue 2: Migration Error - candidate_responses Table

**Problem:**
```php
$table->foreignId('option_id')->nullable()->constrained('question_options')->onDelete('set null');
```
Error: Foreign key to non-existent `question_options` table

**Root Cause:**
- `question_options` table doesn't exist
- Question options stored as JSON in `test_questions.settings` column
- Migration referenced wrong table structure

**Investigation:**
```bash
# Checked all tables
DB::select('SHOW TABLES');
# Result: No question_options table found

# Checked test_questions structure
DB::select('DESCRIBE test_questions');
# Found: settings column (longtext) stores options as JSON
```

**Fix:**
```php
// Removed foreign key constraint
$table->string('option_id')->nullable()->comment('Reference to option within test_questions.settings JSON');
```

**Status:** ✅ Fixed

---

### Issue 3: Missing sodium Extension

**Problem:**
```
lcobucci/jwt requires ext-sodium * -> it is missing from your system
```

**Root Cause:**
- PHP sodium extension disabled in php.ini
- Required for JWT token encryption

**Fix:**
1. Opened `C:\xampp\php\php.ini`
2. Changed `;extension=sodium` to `extension=sodium`
3. Restarted Apache
4. Ran `composer install` successfully

**Status:** ✅ Fixed

---

### Issue 4: Frontend Missing package.json

**Problem:**
- `hireright_frontend` branch has files but no `package.json`
- Cannot run `npm install`

**Status:** ⚠️ Reported to frontend developer (Nurudeen)

**Note:** Not backend responsibility, but documented for team awareness

---

## Recommendations

### High Priority

#### 1. Complete AI Test Generation Feature
**Status:** Assigned to me  
**Timeline:** [Set deadline]

**Requirements:**
- Integrate with AI API (OpenAI/Claude)
- Generate questions based on job title/category
- Store in `a_i_generated_tests` table
- Allow customization before use

**Endpoint Design:**
```
POST /api/employers/tests/generate-ai
{
  "job_title": "Software Developer",
  "difficulty": "intermediate",
  "question_count": 20,
  "categories": ["coding", "problem_solving"]
}
```

---

#### 2. Implement Missing Dashboard Metrics

**Current Placeholders:**
```php
'total_applicants' => 0,  // Placeholder
'total_tests' => 0,       // Placeholder
```

**Fix:**
```php
$totalApplicants = JobApplication::whereHas('jobListing', function($q) use ($companyId) {
    $q->where('company_id', $companyId);
})->count();

$totalTests = Test::where('company_id', $companyId)->count();
```

---

#### 3. Add API Rate Limiting

**Current:** No rate limiting on auth endpoints

**Recommendation:**
```php
// In routes/api.php
Route::middleware(['throttle:10,1'])->group(function () {
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/register', [AuthController::class, 'register']);
});
```

Prevents brute force attacks.

---

#### 4. Enable JWT Token Blacklist

**Current:** Tokens work after logout

**Fix in config/jwt.php:**
```php
'blacklist_enabled' => true,
'blacklist_grace_period' => 0,
```

Ensures tokens are invalidated on logout.

---

### Medium Priority

#### 5. Add Comprehensive API Tests

**Missing:** Automated testing suite

**Recommendation:**
- PHPUnit tests for all endpoints
- Feature tests for user flows
- Integration tests for complex workflows

**Example:**
```php
public function test_candidate_can_apply_to_job()
{
    $candidate = User::factory()->create(['app_role' => 'candidate']);
    $job = JobListing::factory()->create();
    
    $response = $this->actingAs($candidate, 'api')
        ->postJson("/api/candidates/jobs/{$job->id}/apply");
    
    $response->assertStatus(201);
}
```

---

#### 6. Add API Documentation (Swagger/OpenAPI)

**Tool:** L5-Swagger package

**Benefits:**
- Interactive API documentation
- Automatic endpoint discovery
- Frontend developers can test easily

---

#### 7. Implement Caching

**Strategy:**
- Cache job listings (frequently accessed)
- Cache company profiles
- Cache test questions
- Use Redis for session storage

**Example:**
```php
public function index()
{
    return Cache::remember('jobs.all', 3600, function () {
        return JobListing::where('status', 'active')->get();
    });
}
```

---

#### 8. Add Logging & Monitoring

**Current:** Basic error logging

**Recommendation:**
- Log all authentication attempts
- Log failed login attempts (security)
- Log API errors with context
- Integrate monitoring (Sentry, New Relic)

---

### Low Priority

#### 9. Database Optimization

**Add Indexes:**
```sql
ALTER TABLE job_applications ADD INDEX idx_user_status (user_id, status);
ALTER TABLE job_listings ADD INDEX idx_company_status (company_id, status);
ALTER TABLE test_assignments ADD INDEX idx_candidate_status (candidate_id, status);
```

**Benefits:**
- Faster queries
- Better performance at scale

---

#### 10. Clean Up Legacy E-commerce Tables

**Found:** Product, Order, Store tables (not used in recruitment)

**Action:**
- Remove unused migrations
- Clean up database
- Remove related models/controllers

**Tables to Remove:**
- products, product_categories, product_variants
- orders, order_items
- stores, store_users
- online_payments, payment_gateways

---

#### 11. Implement Soft Deletes

**Current:** Hard deletes

**Recommendation:**
Add soft deletes to key models:
```php
use Illuminate\Database\Eloquent\SoftDeletes;

class JobListing extends Model
{
    use SoftDeletes;
}
```

**Benefits:**
- Data recovery
- Audit trail
- Compliance with data retention policies

---

#### 12. Add Notification System

**Current:** Basic notification table exists

**Expand:**
- Email notifications (job matches, application status)
- Push notifications (mobile app ready)
- In-app notifications (real-time)
- SMS notifications (optional)

---

## Next Steps

### Immediate Actions (This Week)

1. **✅ Submit this documentation package**
   - All markdown files
   - Test results
   - Recommendations

2. **⏳ Implement AI Test Generation Feature**
   - Research AI API options
   - Design database schema extensions
   - Implement generation logic
   - Test and document

3. **⏳ Fix Dashboard Placeholders**
   - Implement actual counts
   - Test with real data

4. **⏳ Add Rate Limiting**
   - Configure throttle middleware
   - Test limits

### Short Term (Next 2 Weeks)

5. **Write API Tests**
   - Set up PHPUnit
   - Write authentication tests
   - Write feature tests for key flows

6. **Add API Documentation**
   - Install L5-Swagger
   -
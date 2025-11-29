# Models Documentation

## User Model

**File:** `app/Models/User.php`

**Table:** `users`

**Implements:** JWTSubject (for JWT authentication)

### Fillable Attributes:
- `first_name` - User's first name
- `last_name` - User's last name
- `company_id` - Foreign key to companies table (for employers)
- `email` - User email address
- `phone` - Primary phone number
- `phone_2` - Secondary phone number
- `signup_strategy` - How user registered (email/phone/social)
- `reg_channel` - Registration channel (web/mobile/api)
- `referral_code` - Referral code used during signup
- `status` - Account status (active/suspended/pending)
- `firebase_device_token` - For push notifications
- `dob` - Date of birth
- `email_otp` - Email OTP code
- `phone_otp` - Phone OTP code
- `password_otp` - Password reset OTP
- `login_otp` - Login verification OTP
- `password` - Hashed password
- `email_verified` - Email verification status (boolean)
- `phone_verified` - Phone verification status (boolean)
- `app_role` - User role (candidate/employer/admin)
- `last_seen` - Last activity timestamp
- `login_count` - Number of times user logged in
- `address` - Physical address
- `website` - Personal/company website
- `bio` - User biography
- `title` - Job title/professional title
- `cover_letter` - Default cover letter
- `linkedin_url` - LinkedIn profile URL
- `twitter_url` - Twitter profile URL
- `facebook_url` - Facebook profile URL
- `instagram_url` - Instagram profile URL
- `youtube_url` - YouTube channel URL
- `tiktok_url` - TikTok profile URL

### Hidden Attributes:
- `password` - Never exposed in JSON responses
- `email_otp` - Security: OTP codes hidden
- `phone_otp` - Security: OTP codes hidden
- `password_otp` - Security: OTP codes hidden
- `login_otp` - Security: OTP codes hidden

### Casts:
- `email_verified` → boolean
- `phone_verified` → boolean
- `dob` → date
- `email_verified_at` → datetime
- `phone_otp_expiry` → datetime
- `email_otp_expiry` → datetime
- `password_otp_expiry` → datetime
- `login_otp_expiry` → datetime
- `created_at` → datetime
- `last_seen` → datetime
- `password` → hashed (automatically hashed on save)

### Relationships:

#### belongsTo
- **company()** → `Company` model
  - Foreign Key: `company_id`
  - Purpose: Links employers to their company

#### hasMany
- **experiences()** → `Experience` model
  - Purpose: User's work experience records
  
- **education()** → `Education` model
  - Purpose: User's education records
  
- **certifications()** → `Certification` model
  - Purpose: User's certifications/licenses

### Custom Methods:

#### getJWTIdentifier()
- **Purpose:** Returns user's primary key for JWT token generation
- **Returns:** User ID

#### getJWTCustomClaims()
- **Purpose:** Add custom claims to JWT token
- **Returns:** Empty array (can be extended)

#### hasRole($role)
- **Purpose:** Check if user has specific role
- **Parameters:** `$role` (string) - Role to check
- **Returns:** Boolean
- **Usage:** `$user->hasRole('candidate')`

### Notes:
- Uses JWT for authentication (Tymon/JWTAuth)
- Supports multi-factor authentication (email OTP, phone OTP)
- Has social media profile integration
- Tracks user activity (last_seen, login_count)
- Role-based access control via `app_role` field

---

## JobListing Model

**File:** `app/Models/JobListing.php`

**Table:** `job_listings`

### Key Relationships (Expected):
- belongsTo: Company
- hasMany: JobApplications, SavedJobs
- belongsToMany: Skills

### Purpose:
Represents job postings created by employers

---

## JobApplication Model

**File:** `app/Models/JobApplication.php`

**Table:** `job_applications`

### Key Relationships (Expected):
- belongsTo: User (candidate)
- belongsTo: JobListing
- hasMany: TestAssignments

### Purpose:
Tracks candidate applications to job postings

---

## Company Model

**File:** `app/Models/Company.php`

**Table:** `companies`

### Key Relationships (Expected):
- hasMany: Users (employees)
- hasMany: JobListings
- hasMany: CompanyStaff
- hasMany: Tests

### Purpose:
Represents employer/company profiles

---

## Experience Model

**File:** `app/Models/Experience.php`

**Table:** `experiences`

### Key Relationships:
- belongsTo: User

### Purpose:
Stores candidate work experience history

---

## Education Model

**File:** `app/Models/Education.php`

**Table:** `education`

### Key Relationships:
- belongsTo: User

### Purpose:
Stores candidate education history

---

## Certification Model

**File:** `app/Models/Certification.php`

**Table:** `certifications`

### Key Relationships:
- belongsTo: User

### Purpose:
Stores candidate certifications and licenses

---

## Test Model

**File:** `app/Models/Test.php`

**Table:** `tests`

### Key Relationships (Expected):
- belongsTo: Company
- hasMany: TestQuestions
- hasMany: TestAssignments
- belongsToMany: JobListings

### Purpose:
Represents assessment tests for job applicants

---

## TestQuestion Model

**File:** `app/Models/TestQuestion.php`

**Table:** `test_questions`

### Key Relationships (Expected):
- belongsTo: Test
- hasMany: QuestionOptions
- hasMany: QuestionAttachments
- hasMany: CandidateResponses

### Purpose:
Stores individual questions within tests

---

## TestAssignment Model

**File:** `app/Models/TestAssignment.php`

**Table:** `test_assignments`

### Key Relationships (Expected):
- belongsTo: Test
- belongsTo: User (candidate)
- belongsTo: JobApplication
- hasOne: TestSubmission

### Purpose:
Assigns tests to candidates for specific job applications

---

## CandidateResponse Model

**File:** `app/Models/CandidateResponse.php`

**Table:** `candidate_responses`

### Key Relationships (Expected):
- belongsTo: TestAssignment
- belongsTo: TestQuestion
- belongsTo: QuestionOption (nullable)

### Purpose:
Stores candidate's answers to test questions

---

## FileStorage Model

**File:** `app/Models/FileStorage.php`

**Table:** `file_storages`

### Purpose:
Manages file uploads (resumes, attachments, company logos)

---

## SavedJob Model

**File:** `app/Models/SavedJob.php`

**Table:** `saved_jobs`

### Key Relationships (Expected):
- belongsTo: User
- belongsTo: JobListing

### Purpose:
Tracks jobs bookmarked by candidates

---

## JobAlert Model

**File:** `app/Models/JobAlert.php`

**Table:** `job_alerts`

### Key Relationships (Expected):
- belongsTo: User

### Purpose:
Email/notification alerts for matching jobs

---

## CompanyStaff Model

**File:** `app/Models/CompanyStaff.php`

**Table:** `company_staff`

### Key Relationships (Expected):
- belongsTo: Company
- belongsTo: User
- hasMany: Permissions

### Purpose:
Manages company staff members and their permissions

---

## AIGeneratedTest Model

**File:** `app/Models/AIGeneratedTest.php`

**Table:** `a_i_generated_tests`

### Purpose:
Tracks tests created using AI generation feature

**Note:** This feature is assigned to you for implementation!

---

## Summary

### Total Models: 40+

### Core Models by Category:

**Authentication & Users:**
- User (with JWT support)
- Role
- Permission

**Job Management:**
- JobListing
- JobApplication
- JobCategory
- JobTitle
- SavedJob
- JobAlert

**Candidate Profile:**
- Experience
- Education
- Certification
- Skill

**Company Management:**
- Company
- CompanyStaff

**Testing System:**
- Test
- TestQuestion
- QuestionOption
- QuestionAttachment
- TestAssignment
- TestSubmission
- CandidateResponse
- TestCategory
- TestTag
- TestSection
- RecruitmentStage
- StageTestMapping
- AIGeneratedTest (to be implemented)

**File Management:**
- FileStorage

**E-commerce (Legacy - Not used in recruitment):**
- Product, Order, Store, etc. (These appear to be from a previous project)

### Key Design Patterns:
1. **Polymorphic relationships** for file storage
2. **Pivot tables** for many-to-many relationships
3. **Soft deletes** (likely implemented)
4. **UUID or auto-increment** IDs
5. **Timestamp tracking** (created_at, updated_at)
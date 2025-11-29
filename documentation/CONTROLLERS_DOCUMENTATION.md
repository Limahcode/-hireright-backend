## CandidateController

**File:** `app/Http/Controllers/Candidate/CandidateController.php`

**Namespace:** `App\Http\Controllers\Candidate`

**Dependencies:** 
- CandidateService (injected)

**Purpose:** Manages candidate dashboard and profile operations

---

### Methods:

#### 1. dashboard()
- **Route:** GET /api/candidates/dashboard
- **Auth Required:** Yes (Candidate role)
- **Purpose:** Get candidate dashboard statistics
- **Parameters:** None
- **Returns:**
```json
  {
    "status": "success",
    "data": {
      "applied_jobs": 15,
      "saved_jobs": 8,
      "job_alerts": 3
    }
  }
```
- **Status Codes:** 
  - 200 (success)
  - 500 (server error)
- **Logic:**
  - Counts total job applications by candidate
  - Counts total saved jobs
  - Counts active job alerts
- **Notes:** Uses Auth::id() to get current user

---

#### 2. getProfile()
- **Route:** GET /api/candidates/profile
- **Auth Required:** Yes (Candidate role)
- **Purpose:** Retrieve complete candidate profile data
- **Parameters:** None
- **Returns:**
```json
  {
    "status": "success",
    "data": {
      "user": {...},
      "education": [...],
      "experiences": [...],
      "certifications": [...]
    }
  }
```
- **Status Codes:** 
  - 200 (success)
  - 500 (server error)
- **Logic:** Delegates to CandidateService for data retrieval
- **Notes:** Returns aggregated profile data from multiple tables

---

#### 3. storeProfile()
- **Route:** POST /api/candidates/profile
- **Auth Required:** Yes (Candidate role)
- **Purpose:** Create or update candidate profile (education, experience, certifications)
- **Request Body:**
```json
  {
    "education": [
      {
        "institution": "University of Lagos",
        "degree": "Bachelor's",
        "field_of_study": "Computer Science",
        "location": "Lagos, Nigeria",
        "start_date": "2018-09-01",
        "end_date": "2022-06-30",
        "is_current": false,
        "activities": "Programming club, hackathons",
        "description": "Focused on software engineering"
      }
    ],
    "experiences": [
      {
        "company_name": "Tech Corp",
        "job_title": "Software Developer",
        "description": "Developed web applications",
        "location": "Lagos",
        "employment_type": "full_time",
        "start_date": "2022-08-01",
        "end_date": null,
        "is_current": true
      }
    ],
    "certifications": [
      {
        "name": "AWS Certified Developer",
        "organization": "Amazon Web Services",
        "issue_date": "2023-05-15",
        "expiration_date": "2026-05-15",
        "has_expiry": true
      }
    ]
  }
```

**Validation Rules:**

**Education:**
- `education` - array (optional)
- `education.*.institution` - required, string, max:255
- `education.*.degree` - required, string, max:255
- `education.*.field_of_study` - required, string, max:255
- `education.*.location` - nullable, string, max:255
- `education.*.start_date` - required, date, before_or_equal:today
- `education.*.end_date` - nullable, date, before_or_equal:today
- `education.*.is_current` - boolean
- `education.*.activities` - nullable, string, max:2000
- `education.*.description` - nullable, string, max:2000

**Experiences:**
- `experiences` - array (optional)
- `experiences.*.company_name` - required, string, max:255
- `experiences.*.job_title` - required, string, max:255
- `experiences.*.description` - nullable, string, max:5000
- `experiences.*.location` - nullable, string, max:255
- `experiences.*.employment_type` - required, enum: [full_time, part_time, self_employed, freelance, contract, internship]
- `experiences.*.start_date` - required, date, before_or_equal:today
- `experiences.*.end_date` - nullable, date, before_or_equal:today
- `experiences.*.is_current` - boolean

**Certifications:**
- `certifications` - array (optional)
- `certifications.*.name` - required, string, max:255
- `certifications.*.organization` - required, string, max:255
- `certifications.*.issue_date` - required, date, before_or_equal:today
- `certifications.*.expiration_date` - nullable, date, after:issue_date
- `certifications.*.has_expiry` - boolean

**Returns:**
```json
{
  "status": "success",
  "message": "Profile data stored successfully",
  "data": {
    "education": [...],
    "experiences": [...],
    "certifications": [...]
  }
}
```

**Status Codes:**
- 201 (created/updated successfully)
- 422 (validation error)
- 500 (server error)

**Logic:**
- Validates all input data
- Delegates to CandidateService for database operations
- Returns stored profile data

**Notes:**
- Batch operation - can create/update multiple records at once
- Supports current position/education (is_current flag)
- Date validation ensures no future dates

---

### Error Handling:
All methods include try-catch blocks with:
- Development mode: Returns detailed error messages
- Production mode: Returns generic "Internal server error"
- Proper HTTP status codes

---

### Service Layer:
Uses **CandidateService** for business logic:
- `getProfileData()` - Retrieves profile
- `storeProfileData()` - Stores/updates profile

This follows the Service-Repository pattern for clean code architecture.


**Authorization:**
- Must be company owner OR active staff member
- Returns 403 if not associated with company

**Status Codes:**
- 200 (success)
- 403 (unauthorized)
- 500 (server error)

**Helper Methods Used:**
- `getFullAddress()` - Concatenates address fields
- `getSizeRange()` - Formats employee range
- `getActiveSocialLinks()` - Returns non-null social media links

---

#### 5. generateUniqueSlug() (Private Helper)
- **Purpose:** Generate unique URL-friendly slug from company name
- **Parameters:** `$name` (string)
- **Returns:** Unique slug string
- **Logic:**
  - Converts name to slug format
  - Checks if slug exists in database
  - Appends counter if duplicate (e.g., "company-name-2")
  - Loops until unique slug found

---

### Security Features:
- Database transactions for data integrity
- Authorization checks (owner/admin verification)
- Prevents duplicate company creation
- Validates social media URLs
- Logs errors for debugging

---

### Error Handling:
- Try-catch blocks on all methods
- Database rollback on failures
- Detailed logs with user/company context
- Environment-aware error messages (debug vs production)

---
---
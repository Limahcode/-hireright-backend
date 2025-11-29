# Validation Rules Documentation

## Authentication Endpoints

### Login
**Endpoint:** POST /api/auth/login

**Rules:**
- `email` - required, email format
- `password` - required, string

---

### Register
**Endpoint:** POST /api/auth/register

**Rules:**
- `first_name` - required, string
- `last_name` - required, string
- `email` - required, string, email format, unique in users table
- `password` - required, string, must be confirmed (password_confirmation field)
- `phone` - optional, string
- `app_role` - optional, string (defaults to 'candidate')

**Password Requirements:**
- Must be confirmed with password_confirmation field
- Automatically hashed on save

---

### Validate Email OTP
**Endpoint:** POST /api/auth/validate-email-otp

**Rules:**
- `email` - required, email format
- `otp` - required, string (6-digit code)

**Business Logic:**
- OTP must match hashed value in database
- OTP must not be expired (checked against email_otp_expiry)
- OTP is cleared after successful validation

---

### Validate Phone OTP
**Endpoint:** POST /api/auth/validate-phone-otp

**Rules:**
- `phone` - required, string
- `otp` - required, string (6-digit code)

**Business Logic:**
- OTP must match hashed value in database
- OTP must not be expired (checked against phone_otp_expiry)
- OTP is cleared after successful validation

---

### Request Password Reset
**Endpoint:** POST /api/auth/forgot/password

**Rules:**
- `email` - required, email format

**Business Logic:**
- Generates 6-digit OTP
- OTP expires after 30 minutes
- Sends OTP via email job queue

---

### Reset Password
**Endpoint:** POST /api/auth/reset/password

**Rules:**
- `email` - required, email format
- `otp` - required, string
- `password` - required, string, must be confirmed
- `password_confirmation` - required, must match password

**Business Logic:**
- Validates OTP against hashed value
- Checks OTP expiration
- Clears OTP after successful reset
- Hashes new password

---

### Resend OTP
**Endpoint:** POST /api/auth/resend-otp

**Rules:**
- `type` - required, enum: [email, phone]
- `email` - required if type=email, email format
- `phone` - required if type=phone, string

**Business Logic:**
- Generates new 6-digit OTP
- Email OTP expires in 10 minutes
- Phone OTP expires in 10 minutes (assumed)
- Sends via appropriate channel

---

## Candidate Endpoints

### Store Profile
**Endpoint:** POST /api/candidates/profile

**Education Validation:**
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

**Experience Validation:**
- `experiences` - array (optional)
- `experiences.*.company_name` - required, string, max:255
- `experiences.*.job_title` - required, string, max:255
- `experiences.*.description` - nullable, string, max:5000
- `experiences.*.location` - nullable, string, max:255
- `experiences.*.employment_type` - required, enum: [full_time, part_time, self_employed, freelance, contract, internship]
- `experiences.*.start_date` - required, date, before_or_equal:today
- `experiences.*.end_date` - nullable, date, before_or_equal:today
- `experiences.*.is_current` - boolean

**Certification Validation:**
- `certifications` - array (optional)
- `certifications.*.name` - required, string, max:255
- `certifications.*.organization` - required, string, max:255
- `certifications.*.issue_date` - required, date, before_or_equal:today
- `certifications.*.expiration_date` - nullable, date, must be after issue_date
- `certifications.*.has_expiry` - boolean

**Date Logic:**
- No future dates allowed for start/end dates
- End date must be after start date
- Expiration date must be after issue date

---

## Company Endpoints

### Create Company
**Endpoint:** POST /api/employers/company

**Rules:**
- `name` - required, string, max:255
- `email` - nullable, email
- `phone` - nullable, string, max:50
- `about` - nullable, string (no length limit)
- `website` - nullable, url, max:255
- `address` - nullable, string, max:255
- `city` - nullable, string, max:255
- `state` - nullable, string, max:255
- `country` - nullable, string, max:255
- `postal_code` - nullable, string, max:50
- `size_min` - nullable, integer, min:1
- `size_max` - nullable, integer, must be greater than size_min
- `industry_code` - nullable, string, max:50
- `linkedin_url` - nullable, url, max:255
- `twitter_url` - nullable, url, max:255
- `facebook_url` - nullable, url, max:255
- `instagram_url` - nullable, url, max:255
- `youtube_url` - nullable, url, max:255
- `tiktok_url` - nullable, url, max:255

**Business Logic:**
- size_max must be greater than size_min
- All social URLs must be valid URLs
- Slug is auto-generated from name
- User can only create one company

---

### Update Company
**Endpoint:** PUT /api/employers/company

**Rules:** Same as Create Company, but:
- `name` - sometimes|required (only validated if present)
- All other fields remain optional

**Authorization:**
- Must be company owner OR admin staff

---

## Common Validation Patterns

### Date Validation:
- `before_or_equal:today` - No future dates
- `after:field_name` - Must be after another date field

### String Validation:
- `max:255` - Standard field length
- `max:2000` - Medium text (activities, short descriptions)
- `max:5000` - Long text (job descriptions, about)

### URL Validation:
- Must be valid URL format
- Includes protocol (http/https)

### Email Validation:
- Standard RFC email format
- Unique check where applicable

### Enum Validation:
- `in:value1,value2` - Must match one of specified values
- Used for: employment_type, user roles, OTP types

---

## Error Response Format

**Validation Errors (422):**
```json
{
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password confirmation does not match."]
  }
}
```

**Success Responses:**
- 200 - OK (retrieve/update)
- 201 - Created (new resource)
- 204 - No Content (delete)

**Error Responses:**
- 401 - Unauthorized (invalid credentials)
- 403 - Forbidden (insufficient permissions)
- 404 - Not Found (resource doesn't exist)
- 412 - Precondition Failed (business rule violation)
- 422 - Unprocessable Entity (validation failed)
- 500 - Internal Server Error (unexpected error)
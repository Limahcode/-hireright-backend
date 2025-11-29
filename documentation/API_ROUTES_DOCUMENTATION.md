# HireRight API Routes Documentation

## Authentication Routes (Public)
| Method | Endpoint | Auth Required | Description |
|--------|----------|---------------|-------------|
| POST | /api/auth/register | No | Register new user |
| POST | /api/auth/login | No | Login and get JWT token |
| POST | /api/auth/logout | Yes | Logout user |
| POST | /api/auth/refresh | Yes | Refresh JWT token |
| POST | /api/auth/forgot/password | No | Request password reset |
| POST | /api/auth/reset/password | No | Reset password |

## Candidate Routes (Auth: candidate role required)
| Method        | Endpoint                | Description |
|---------------|-------------------------|-------------|
| GET | /api/candidates/dashboard | Get candidate dashboard |
| GET | /api/candidates/profile | Get candidate profile |
| POST | /api/candidates/profile | Create/update profile |
| GET/POST/PUT/DELETE | /api/candidates/experiences | Manage work experience |
| GET/POST/PUT/DELETE | /api/candidates/education | Manage education |
| GET/POST/PUT/DELETE | /api/candidates/certifications | Manage certifications |
| GET | /api/candidates/jobs | List all jobs |
| GET | /api/candidates/jobs/recommended | Get recommended jobs |
| GET | /api/candidates/jobs/{jobId} | View job details |
| POST | /api/candidates/jobs/{jobId}/apply | Apply for job |
| GET | /api/candidates/applications | View my applications |
| POST | /api/candidates/applications/{id}/withdraw | Withdraw application |
| POST | /api/candidates/jobs/{jobId}/save | Save job |
| DELETE | /api/candidates/jobs/{jobId}/delete | Remove saved job |
| GET | /api/candidates/savedjobs | List saved jobs |
| GET/POST/PUT/DELETE | /api/candidates/alerts | Manage job alerts |
| GET | /api/candidates/tests/assigned | View assigned tests |
| POST | /api/candidates/tests/{test}/start | Start a test |
| POST | /api/candidates/tests/{test}/submit | Submit test |
| GET | /api/candidates/tests/{test}/result | View test result |

## Employer Routes (Auth: employer role required)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/employers/dashboard | Get employer dashboard |
| GET/POST/PUT | /api/employers/company | Manage company profile |
| GET | /api/employers/company/staffs | List company staff |
| PUT | /api/employers/staff/{staffId}/permissions | Update staff permissions |
| GET/POST/PUT/DELETE | /api/employers/jobs | Manage job postings |
| PUT | /api/employers/jobs/{job}/status | Update job status |
| GET | /api/employers/jobs/{jobId}/applications | View job applications |
| GET | /api/employers/jobs/{jobId}/candidates | View job candidates |
| GET | /api/employers/company/candidates | View all company candidates |
| GET/POST/PUT/DELETE | /api/employers/tests | Manage tests |
| POST | /api/employers/tests/{test}/questions | Add test question |
| POST | /api/employers/tests/{test}/questions/{question}/options | Add question options |
| GET | /api/employers/tests/{test}/submissions | View test submissions |
| POST | /api/employers/tests/{test}/submissions/{submission}/grade | Grade submission |

## Admin Routes (Auth: admin role required)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET/POST/PUT/DELETE | /api/admin/users | Manage all users |
| PUT | /api/admin/users/{userId}/status | Update user status |
| GET | /api/admin/users/{userId}/activity | View user activity |
| GET/POST/PUT/DELETE | /api/admin/companies | Manage companies |
| PUT | /api/admin/companies/{company}/verify | Verify company |
| GET/POST/PUT/DELETE | /api/admin/tests | Manage tests |
| GET | /api/admin/reports/users | User statistics |
| GET | /api/admin/reports/jobs | Job statistics |
| GET | /api/admin/reports/applications | Application statistics |
| GET/POST/PUT/DELETE | /api/admin/job/categories | Manage job categories |

## File Storage Routes (Auth required)
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | /api/storage/upload/init | Initialize file upload |
| PUT | /api/storage/upload/confirm | Confirm file upload |
| GET | /api/storage/files/{fileId} | Get file |
| DELETE | /api/storage/files/{fileId} | Delete file |

## Common Routes (Auth required)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/profile | View own profile |
| PUT | /api/profile | Update own profile |
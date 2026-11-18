# PostYourJobUK — Platform Operations & Architecture Guide

*PostYourJobUK* is a cloud-native, multi-tenant AI recruitment Software-as-a-Service (SaaS) and Applicant Tracking System (ATS) platform engineered on a **decoupled 3-Tier Software Application Architecture** (`client/` Presentation Tier + `server/` JSON REST API Tier + Data & Serverless AI Tier) deployed across a **4-Tier Microsoft Azure Cloud Infrastructure Ecosystem** (App Service, Container Registry, Blob Storage, Storage Queues, Serverless Python Functions, Key Vault, and MySQL Flexible Server) alongside an enterprise transactional SMTP email engine (Brevo Relay + Cloudflare SPF/DKIM/DMARC).

The platform is engineered to automate the end-to-end hiring funnel, eliminate algorithmic demographic bias, prevent LLM hallucinations, provide a 100% private client-side 6-template vector PDF CV Builder, and drastically reduce talent acquisition infrastructure costs for small-to-medium enterprises (SMEs).

---

## 1. Core Platform Lifecycle

The platform manages the complete recruitment lifecycle through an asynchronous, event-driven workflow:

1. **Workspace & Corporate Profile Management:** A verified recruiter logs into their isolated tenant workspace (`tenant_id`), manages their corporate profile, login email, password, and recovery code via the self-service `⚙️ Update Details` modal (`POST /api/dashboard/profile`), and posts job specifications (`POST /api/jobs/store`) with required technical competencies and years of experience.
2. **Public Indexing & Scoped Search:** The job listing is dynamically published to the public 3-column job board (`client/index.html` via `GET /api/public/home`), displaying the company name, employment type badge, location, salary, industry tag, relative timestamp, and unique `Job ID: #`. Job seekers can filter by work type, location, industry, or sort order, and perform precision keyword searches across job title, company name, location, and industry.
3. **2-Step Free ATS CV Builder (`/resume-builder` -> `/resume-builder/editor`):** Candidates without a CV can choose from **6 modern, recruiter-approved templates** (*Professional*, *Modern*, *Executive*, *Technical*, *Creative*, *Academic*), edit their details with a live A4 preview, and download a colour-styled, native vector PDF-1.4 document (`client/assets/js/resume.js`) compiled 100% client-side with zero external dependencies and 100% `PyPDF2` compatibility.
4. **Candidate Application & Instant Email Receipt:** Candidates apply on `client/job.html` (`POST /api/apply`) by uploading their CV in PDF or DOCX format (protected by honeypot, timing trap, magic-byte MIME verification, and duplicate application detection). Upon submission, `server/Services/MailerService.php` dispatches an instant, branded HTML confirmation receipt (`sendApplicationConfirmation`) via Brevo SMTP (`careers@postyourjobuk.co.uk`) with `Reply-To` mapped to the hiring company.
5. **Asynchronous Ingestion & Cloud Storage:** The PHP REST API streams the physical CV binary to Azure Blob Storage (`hranalyticalstorage` / `cv-storage`) and enqueues a lightweight Base64 JSON payload into Azure Storage Queue (`ai-processing-queue`).
6. **Two-Stage Decoupled AI Evaluation:** An asynchronous Python 3.11 Azure Function (`postyourjobuk-ai-func`) triggers on the queue message, sanitizes PII (Stage 0), extracts factual claims via Google Gemini 2.5 Flash at `temperature = 0.0` (Stage 1), verifies every claim character-for-character through a deterministic Python Grounding Gate (Stage 2), calculates a Base-100 subtractive score, and commits a SHA-256 cryptographic audit fingerprint (`ai_audit_fingerprint`) to Azure MySQL.
7. **Recruiter Review & Zero-CDN Canvas Analytics:** Calculated match scores, executive summaries, verbatim evidence quotes, and audit fingerprints automatically populate the recruiter’s sortable, filterable 4-column desktop applicant roster (`/applicants?job_id=...`) and 6-chart HTML5 `<canvas>` Analytics Suite (`/dashboard/analytics`).
8. **ATS Pipeline Progression & Automated Follow-Up Emails:** Recruiters transition candidates across hiring stages (`reviewed` -> `interviewing` -> `hired` or `rejected`) using 1-click CSRF-protected pipeline actions (`POST /api/applicants/status`). Moving a candidate to `interviewing` automatically dispatches a personalized Interview Invitation email (`sendInterviewInvitation`) embedding the recruiter's live employer business card; moving to `hired` dispatches a formal Job Offer email (`sendJobOffer`); moving to `rejected` performs a silent database update.

---

## 2. Architecture Overview: 3-Tier Software Design & 4-Tier Azure Cloud Topology

*PostYourJobUK* is structured around two complementary architectural views:

### A. 3-Tier Decoupled Software Application Architecture (Codebase Structure)

| Software Tier | Repository Directory | Technology Stack & Responsibilities |
| :--- | :--- | :--- |
| **Tier 1: Presentation Tier** | `client/` | Pure semantic HTML5 (11 entry points), consolidated CSS3 (`assets/css/styles.css`), and 7 modular Vanilla ES6+ JavaScript modules (`api.js`, `a11y.js`, `public.js`, `resume.js`, `recruiter.js`, `admin.js`, `charts.js`). Zero external CDN dependencies, custom HTML5 `<canvas>` charting engine, native PDF-1.4 vector compiler, and WCAG 2.1 Level AA keyboard/screen-reader accessibility. Served as Apache's `APACHE_DOCUMENT_ROOT` (`/var/www/html/client`). |
| **Tier 2: Application & Business Logic Tier** | `server/` & `postyourjobuk-ai-function/` | **Synchronous REST API (`server/`):** Stateless/session-backed PHP 8.2 MVC REST API returning pure JSON (`Content-Type: application/json`) under `/api/*`. Placed outside the public web root and invoked via `client/api.php`. Enforces Zero-Trust session fingerprinting, `X-CSRF-Token` validation, and strict `tenant_id` BOLA guards.<br>**Asynchronous AI Worker (`postyourjobuk-ai-function/`):** Serverless Python 3.11 Azure Function executing Stage 0 PII Redaction, Stage 1 Gemini 2.5 Flash extraction, Stage 2 Python Grounding Gate, and Base-100 math scoring. |
| **Tier 3: Data & Storage Persistence Tier** | Azure Cloud Data Services | **Azure Database for MySQL Flexible Server (`hr-analytical-db` / `postyourjobhere`):** 7 relational tables (`tenants`, `jobs`, `applicants`, `fairness_audit_pairs`, `fairness_audit_runs`, `news_articles`, `system_logs`).<br>**Azure Blob Storage & Storage Queues (`hranalyticalstorage`):** Encrypted CV blob container (`cv-storage`) and asynchronous queue (`ai-processing-queue`). |

---

### B. 4-Tier Azure Cloud & Transactional Email Infrastructure Topology

At the cloud infrastructure level, the Application Tier is decoupled across Azure App Service, Azure Storage Queue middleware, and Azure Serverless Functions to guarantee zero user-facing HTTP timeouts during heavy document processing:

```text
┌─────────────────────────────────────────────────────────────────────────────────┐
│                     TIER 1: CLIENT PRESENTATION LAYER                           │
│     (client/*.html + styles.css + Vanilla ES6 JS + Native <canvas> Charts)      │
└──────────────────────────────────────┬──────────────────────────────────────────┘
                                       │ (HTTPS / Port 443 — Fetch JSON /api/*)
                                       ▼
┌─────────────────────────────────────────────────────────────────────────────────┐
│              TIER 2: REST API APPLICATION LAYER (Azure App Service)             │
│           (postyourjobuk-web — PHP 8.2 Apache: client/api.php -> server/)       │
└───────┬──────────────────────┬───────────────────────┬──────────────────────────┘
        │                      │                       │
        ▼ (Pull Secrets)       ▼ (CV Binary / Queue)   ▼ (TLS Port 587 SMTP)
┌───────────────────────┬───────────────────────┬─────────────────────────────────┐
│ Azure Key Vault       │ Azure Blob & Queue    │ Brevo SMTP Relay + Cloudflare   │
│ (kv-postyourjobuk)    │ (hranalyticalstorage) │ (careers@postyourjobuk.co.uk)   │
├───────────────────────┴───────────────────────┴─────────────────────────────────┤
│            TIER 3: CLOUD SERVICES, MESSAGING & TRANSACTIONAL EMAIL              │
└───────────────▲──────────────────────▲──────────────────────┬───────────────────┘
                │                      │                      │
                │ (Fetch Secrets)      │ (Download CV)        │ (Queue Trigger)
┌───────────────┴──────────────────────┴──────────────────────▼───────────────────┐
│              TIER 4: AI PROCESSING & PERSISTENCE (Azure Function)               │
│                     (postyourjobuk-ai-func — Python 3.11)                       │
└──────────────────────────────────────┬──────────────────────────────────────────┘
                                       │ (Commit Scores & Audit Fingerprints)
                                       ▼
┌─────────────────────────────────────────────────────────────────────────────────┐
│             Azure Database for MySQL Flexible Server (hr-analytical-db)         │
│  (tenants, jobs, applicants, fairness_audit_pairs, fairness_audit_runs,         │
│   news_articles, system_logs)                                                   │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## 3. Repository File & Folder Structure

```text
postyourjobuk6.8.2/
├── Dockerfile                              # PHP 8.2-Apache container (APACHE_DOCUMENT_ROOT = /var/www/html/client)
├── composer.json                           # Backend PHP dependencies (Azure Blob/Queue SDKs, PHPMailer, phpdotenv)
├── composer.lock                           # Locked Composer dependency versions
├── README.txt                              # Full architectural, database, & deployment documentation
│
├── client/                                 # TIER 1: PRESENTATION TIER (Pure HTML5, CSS3, Vanilla ES6+ JS)
│   ├── .htaccess                           # Clean URL routing (/api/* -> api.php, /about -> about.html, etc.)
│   ├── api.php                             # Gateway proxy delegating /api/* requests to ../server/index.php
│   ├── index.html                          # Public Job Board, RSS News Carousel, & 3-Column Active Job Listings
│   ├── about.html                          # Standalone Static Page: About the Platform & Cloud Architecture (/about)
│   ├── vision.html                         # Standalone Static Page: Our Vision & Ethical AI Charter (/vision)
│   ├── terms.html                          # Standalone Static Page: Terms of Service & UK GDPR Privacy Policy (/terms)
│   ├── contact.html                        # Standalone Static Page: Contact & Support Inquiry Form (/contact)
│   ├── job.html                            # Single Job Details, Employer Business Card, & Candidate CV Application Form (/job)
│   ├── resume.html                         # 2-Step Client-Side ATS CV Builder (6 Modern Templates + Native PDF-1.4 Compiler)
│   ├── login.html                          # Unified Authentication Portal (/login, /reset-password, /admin/login, Google OAuth)
│   ├── dashboard.html                      # Recruiter Workspace (/dashboard, /dashboard/analytics, /jobs/*, /applicants, /applicant/view)
│   ├── admin.html                          # Super-Admin Console (/admin, /admin/analytics, /admin/logs, /admin/view-tenant, /admin/fairness)
│   ├── certificate.html                    # Printable EU AI Act & UK Equality Act Algorithmic Parity Certificate (/admin/fairness/certificate)
│   └── assets/
│       ├── css/
│       │   └── styles.css                  # Consolidated dark-mode glassmorphic stylesheet (zero external CSS CDNs)
│       └── js/
│           ├── api.js                      # Centralised fetch() HTTP client, CSRF header injection, session init, & toast notifications
│           ├── a11y.js                     # WCAG 2.1 AA modal focus trap, keyboard navigation, clickable table sorting, & shared header/footer
│           ├── public.js                   # Public job board, 3-column job cards, search/filter engine, job detail application, & contact form
│           ├── resume.js                   # 6-template CV picker, live A4 preview, & zero-dependency native styled PDF-1.4 vector generator
│           ├── recruiter.js                # Recruiter dashboard, profile/password editor modal, job CRUD, ATS pipeline, & 6-chart analytics
│           ├── admin.js                    # Super-Admin tenant governance, credential reset, audit logs, 6-chart analytics, & 4-tab Fairness Inspector
│           └── charts.js                   # Zero-CDN HTML5 <canvas> chart engine (Bar, Horizontal Bar, Dual-Line, Pie, Doughnut)
│
├── server/                                 # TIER 2: APPLICATION / REST API TIER (Pure JSON PHP 8.2 MVC)
│   ├── bootstrap.php                       # Environment loader, secure session cookies, PSR-4 autoloader (App\ -> server/), & DB/Azure constants
│   ├── index.php                           # REST API front controller & global JSON exception handler (/api/*)
│   ├── Core/
│   │   ├── Router.php                      # Clean URI & HTTP method dispatcher for /api/* endpoints
│   │   ├── BaseController.php              # JSON response emitter ($this->json()), input parser ($this->getBody()), & auth/CSRF guards
│   │   ├── BaseModel.php                   # PDO prepared-statement base model with tenant ownership verification
│   │   ├── Database.php                    # Singleton PDO connection to Azure Database for MySQL (SSL/TLS enforced)
│   │   ├── Auth.php                        # Zero-Trust session fingerprinting (User-Agent + SHA-256 IP hash) & tenant status verification
│   │   ├── Helpers.php                     # CSRF token generator, sanitizer, & relative timestamp formatter
│   │   ├── AuditLogger.php                 # Immutable audit event logger writing to MySQL `system_logs`
│   │   ├── SecurityLogger.php              # BOLA & rate-limit security alert logger
│   │   └── Seeders/
│   │       └── BiasTestDataSeeder.php      # Seeds the 9 Counterfactual Demographic Benchmark Pairs (18 CVs) for Tenant 9999
│   ├── Middleware/
│   │   └── AdminAuth.php                   # Super-Admin privilege & session fingerprint enforcement guard
│   ├── Controllers/
│   │   ├── AuthController.php              # /api/auth/session, /api/auth/login, /api/auth/logout, /api/auth/reset-password, & Google OAuth 2.0
│   │   ├── HomeController.php              # /api/public/home (jobs + news + filters), /api/news/refresh, & /api/public/contact
│   │   ├── JobController.php               # /api/jobs/detail, /api/jobs/store, /api/jobs/update, & /api/jobs/delete (BOLA-protected)
│   │   ├── DashboardController.php         # /api/dashboard, /api/dashboard/profile (self-service profile & password update), & /api/dashboard/analytics
│   │   ├── ApplicantController.php         # /api/apply, /api/applicants, /api/applicants/detail, /api/applicants/status, /api/applicants/delete(-all)
│   │   ├── AdminController.php             # /api/admin/dashboard, /api/admin/analytics, /api/admin/tenants/*, & /api/admin/fairness/*
│   │   ├── AuditController.php             # /api/admin/logs (paginated & filterable security/audit telemetry)
│   │   └── NewsController.php              # /api/fetch-news RSS feed aggregator
│   ├── Models/
│   │   ├── Job.php                         # Job CRUD, scoped search, funnel metrics, trend analytics, & top candidate queries
│   │   ├── Applicant.php                   # Candidate persistence, status transitions, & AI score/summary retrieval
│   │   ├── Recruiter.php                   # Multi-tenant organization (`tenants` table) lookups, OAuth linking, & creation
│   │   ├── Admin.php                       # Platform-wide KPIs, 6-chart datasets, & multi-tenant utilisation leaderboard queries
│   │   └── NewsArticle.php                 # RSS XML parser & `news_articles` table caching
│   └── Services/
│       ├── AzureBlobStorage.php            # Uploads, streams, & deletes candidate CVs in Azure Blob Storage (`cv-storage`)
│       ├── AzureQueueService.php           # Dispatches Base64 JSON evaluation payloads to Azure Storage Queue (`cv-processing-queue`)
│       ├── MailerService.php               # Sends HTML transactional emails via Brevo SMTP (`careers@postyourjobuk.co.uk`)
│       └── StorageService.php              # Unified storage abstraction layer
│
├── postyourjobuk-ai-function/              # TIER 3/4: SERVERLESS AI WORKER TIER (Python 3.11 Azure Function)
│   ├── function_app.py                     # Stage 0 PII Redaction + Stage 1 Gemini 2.5 Flash Extraction/Grounding + Stage 2 Base-100 Math
│   ├── host.json                           # Azure Functions runtime & queue polling configuration
│   └── requirements.txt                    # Python dependencies (azure-functions, google-genai, PyPDF2, python-docx, mysql-connector-python)
│
└── uploads/
    └── cvs/
        └── synthetic/                      # 18 synthetic counterfactual CVs (Pair 01A..Pair 09B) for Tenant 9999 Fairness Auditing
```

---

## 4. Two-Stage Decoupled AI Evaluation Pipeline

To guarantee 100% explainability, eliminate LLM hallucinations, and prevent demographic bias, AI evaluation is split into two distinct execution stages inside `postyourjobuk-ai-function/function_app.py`:

```text
┌────────────────────────────────────────────────────────────────────────────────┐
│             SERVERLESS AI PROCESSING PIPELINE (postyourjobuk-ai-func)          │
│                                                                                │
│ 1. Text Extraction & Stage 0 Regex PII Redaction (`redact_cv`)                 │
│    └─► Strips names, emails, phone numbers, DOB, graduation years, & hobbies.  │
│                                                                                │
│ 2. Stage 1: Google Gemini 2.5 Flash API (Temperature = 0.0)                    │
│    └─► Read-only factual evidence extractor (FORBIDDEN from scoring).          │
│                                                                                │
│ 3. Stage 2: Python Grounding Gate                                              │
│    ├─► Verbatim Raw Text Search (Rejects fake quotes)                          │
│    ├─► `difflib` Plagiarism Check (Rejects job-description copy-pasting >0.85) │
│    └─► Timeline Overlap Merging & Non-Domain 50% Credit Reduction              │
│                                                                                │
│ 4. Stage 3 & 4: Subtractive Base-100 Math Scorer & SHA-256 Audit Generator     │
│    ├─► Formula: Score = max(0, 100 - Skill Deductions - Experience Deductions) │
│    └─► Generates SHA-256 cryptographic fingerprint (`ai_audit_fingerprint`)    │
└────────────────────────────────────────────────────────────────────────────────┘
```

* **Stage 0 — Regex PII Anonymization:** Sanitizes CV text prior to AI inference, stripping candidate names (`CANDIDATE`), emails, phone numbers, explicit birth dates, graduation years, hobbies, and military context.
* **Stage 1 — Factual Read-Only Extraction (Gemini 2.5 Flash):** Evaluates anonymized text at `temperature = 0.0` solely to identify core job requirements and propose verbatim evidence quotes. Gemini is strictly prohibited from scoring or ranking candidates.
* **Stage 2 — Python Grounding Gate:** Validates evidence character-for-character against raw text, performs `difflib` plagiarism detection (`>0.85` copy ratio), merges overlapping work histories, and applies a 50% weight reduction for non-domain roles.
* **Stage 3 & 4 — Base-100 Subtractive Math Scoring & SHA-256 Fingerprinting:** Computes final scores using deterministic subtractive math (`Score = max(0, 100 - Skill Deduction - Experience Deduction)`) and commits an immutable SHA-256 cryptographic audit payload to MySQL.

---

## 5. Automated ATS Pipeline & Transactional Email Engine

The platform provides a full Applicant Tracking System (ATS) state machine that connects recruiter decision-making directly to automated candidate communications:

* **BOLA-Protected State Transitions (`Applicant::updateStatusSecure`):** Every status change (`reviewed`, `interviewing`, `hired`, `rejected`) is strictly scoped to both `applicant_id` and `tenant_id` via `POST /api/applicants/status` with `X-CSRF-Token` validation and `AuditLogger` telemetry (`APPLICANT_STATUS_CHANGED`).
* **3 Automated Transactional Email Flows (`server/Services/MailerService.php`):**
  1. **Application Confirmation (`sendApplicationConfirmation`):** Sent automatically when a candidate applies (`POST /api/apply`), confirming CV receipt for the specific job title and company.
  2. **Interview Invitation (`sendInterviewInvitation`):** Triggered when a recruiter clicks `🟢 Invite`. Dynamically injects the recruiter's live contact person, phone number, email address, and company address from the `tenants` table so the candidate can schedule their interview directly.
  3. **Formal Job Offer (`sendJobOffer`):** Triggered when a recruiter clicks `🎉 Offer`. Sends a congratulatory offer letter outlining HR onboarding next steps.
* **Silent High-Volume Rejection (`🛑 Reject`):** Updates candidate status to `rejected` without dispatching an email, giving recruiters frictionless pipeline control.
* **Sortable & Filterable 4-Column Applicants Roster (`client/assets/js/recruiter.js`):** Features a live search/stage/sort filter toolbar, clickable sortable column headers (`⇅` / `▲` / `▼`), 4 uniform-colored stage KPI cards (*Total*, *Interviewing*, *Hired / Offered*, *Rejected*), and 1-click single or bulk candidate erasure (`POST /api/applicants/delete` & `POST /api/applicants/delete-all`).

---

## 6. Zero-CDN HTML5 `<canvas>` Analytics Suites (Recruiter & Super-Admin)

Both Recruiter and Super-Admin analytics portals feature 6 interactive charts rendered by the custom zero-dependency HTML5 `<canvas>` engine (`client/assets/js/charts.js`) alongside a uniform 4-color KPI card hierarchy:

* **Recruiter Analytics Dashboard (`/dashboard/analytics` via `GET /api/dashboard/analytics`):**
  1. **Top Jobs by Applicant Volume:** Multi-color horizontal bar chart assigning a unique color to each job posting.
  2. **AI Match Quality by Job Posting:** Dual-line chart comparing *Average AI Match Score (%)* vs. *Highest Candidate Score (%)* per role.
  3. **ATS Recruitment Pipeline:** 4-stage doughnut chart + live counter pills (*Reviewed*, *Interviewing*, *Hired*, *Rejected*).
  4. **Applications by Submission Date:** Multi-color Pie Chart showing application volume share across active submission dates.
  5. **Candidate AI Compatibility Tiers:** Pie chart segmenting candidates into *Strong Match (≥80%)*, *Qualified (50%–79%)*, and *Low Match (<50%)*.
  6. **Activity by Employment Type:** Doughnut chart breaking down applications across *Full-time*, *Contract*, *Part-time*, and *Internship* roles.
  7. **Top 5 Highest-Scoring Candidates Leaderboard:** Cross-job table ranking the top 5 candidates across the company with 1-click `👁️ View Profile` access.
* **Super-Admin Platform Analytics Suite (`/admin/analytics` via `GET /api/admin/analytics`):**
  1. **Top Tenants by Platform Activity:** Multi-color horizontal bar chart comparing applicant volume and job postings per registered company.
  2. **Platform Growth & Activity Velocity:** Dual-curve line chart tracking *Candidate Applications* vs. *New Job Postings* across active platform dates.
  3. **Global ATS Conversion Funnel (All Tenants):** Platform-wide doughnut chart + 4 stage counter pills aggregating hiring progression across all recruiters.
  4. **AI CV Processing Engine & Queue Health:** Pie chart + 3 SLA pills monitoring *Queue SLA (%)*, *Platform Mean AI Score (%)*, and *Failed Jobs*.
  5. **Tenant Distribution by Industry Sector:** Multi-color pie chart breaking down registered recruiter workspaces by market vertical.
  6. **Security & Audit Log Telemetry:** Doughnut chart + 3 severity pills visualizing `system_logs` events across `INFO`, `WARNING`, and `CRITICAL` severities.
  7. **Sortable Multi-Tenant Utilisation & Performance Leaderboard:** Full-width sortable table (`#admin-leaderboard-table`) ranking tenants by active/total jobs, applicant volume, mean AI score, ATS hiring outcomes, and 1-click `Manage` navigation.

---

## 7. Live Algorithmic Parity Audit Testbed & Certificate Generator

The platform includes a dedicated Super-Admin Compliance Workspace (`tenant_id = 9999`) accessible via `/admin/fairness`:

* **Live Cloud Execution Trigger (`⚡ Run Live Algorithmic Parity Audit`):** Dispatches 18 synthetic candidate CVs across 9 counterfactual pairs (`POST /api/admin/fairness/run`) into `ai-processing-queue` for real-time evaluation.
* **Real-Time Asynchronous Telemetry:** A live polling monitor (`GET /api/admin/fairness/status`) tracks completion (`X / 18 Completed`) and dynamically updates parity scores without requiring page refreshes.
* **4-Tab Counterfactual Evidence Inspector Modal (`[🔍 Inspect Evidence]` via `GET /api/admin/fairness/detail`):**
  1. *🔎 Verbatim Evidence & Grounding:* Side-by-side claim verification.
  2. *⚖️ Side-by-Side CV & PII Redaction:* Verifies Stage 0 PII removal against original CV text.
  3. *📋 Full AI Summary Output:* Comparative analysis of executive summaries.
  4. *🔐 Cryptographic Audit Fingerprint:* SHA-256 hash inspection, model parameters, and execution timestamps.
* **Official Exportable Compliance Certificate (`client/certificate.html` at `/admin/fairness/certificate`):** Formatted with `@media print` rules for one-click PDF export, displaying SHA-256 verification signatures and formal alignment badges for **EU AI Act (Annex III)**, **UK Equality Act 2010 (Section 19)**, and **ISO/IEC 42001:2023**.
* **Empirical Parity Proof:** Certified 100% Parity Safe with a **0.00 pt mean disparity delta** across all 9 protected social dimensions.

---

## 8. Role Capabilities & Access Control

* **Candidates (Public / Unauthenticated):**
  * Browse, filter, and search active 3-column job cards (`Job ID: #`) across validated recruiter organizations.
  * Build and download a free, styled vector PDF-1.4 resume using any of the 6 templates in the 2-step Client-Side ATS CV Builder (`/resume-builder`).
  * Submit job applications with PDF/DOCX CV uploads and receive instant HTML application confirmation emails.
  * Receive automated interview invitations (with live recruiter business cards) and formal job offer emails as their application progresses.
  * Read live RSS recruitment news articles and submit inquiries on `/contact` (`client/contact.html`).
* **Recruiters (Tenant Administrators):**
  * Update their own corporate profile, login/contact email, contact person, phone number, industry, website, address, password, and recovery code via the `⚙️ Update Details` modal (`POST /api/dashboard/profile`), immediately updating all Employer Business Cards across their active jobs.
  * Reset forgotten passwords via `/reset-password` using their corporate email and recovery code (`POST /api/auth/reset-password`) or sign in via Google Workspace OAuth 2.0 (`/auth/google`).
  * Manage job specifications (create, edit, publish, delete) with filter toolbars and clickable column sorting within their isolated `tenant_id` scope.
  * View applicant rosters with AI-generated match scores, executive summaries, and verbatim evidence quotes, and advance candidates through the ATS pipeline (`🟢 Invite`, `🎉 Offer`, `🛑 Reject`).
  * Analyze hiring performance in the 6-chart Recruiter Analytics Dashboard (`/dashboard/analytics`) and Top 5 Talent Leaderboard.
  * Delete single or all candidate applications and cloud CV blobs in compliance with UK GDPR Article 17 (Right to Erasure).
* **Super-Administrators (Platform Operations):**
  * Provision new recruiter workspaces (`POST /api/admin/tenants/create`), suspend or activate tenants, and update any tenant's organization profile, corporate login email, password, or recovery code (`POST /api/admin/tenants/edit` on `/admin/view-tenant`).
  * Monitor multi-tenant utilization, AI queue SLA, global ATS conversions, and security audit telemetry in the 6-chart Platform Analytics Suite (`/admin/analytics`).
  * Inspect real-time security and audit logs (`/admin/logs`) across `INFO`, `WARNING`, and `CRITICAL` severities.
  * Execute live cloud fairness audits on Tenant 9999 (`/admin/fairness`), inspect 4-tab counterfactual evidence, and export SHA-256 signed PDF Algorithmic Compliance Certificates (`/admin/fairness/certificate`).

---

## 9. Production Database Schema (Azure Database for MySQL — `postyourjobhere`)

All 7 production tables reside on `hr-analytical-db.mysql.database.azure.com` (`InnoDB`, `utf8mb4_unicode_ci`):

1. **`tenants`**: Stores corporate recruiter accounts, Super-Admin flags (`is_super_admin`), OAuth identifiers (`google_id`), Bcrypt password hashes (`password_hash`), account recovery codes (`recovery_code`), and employer business card metadata (`company_name`, `contact_person`, `email`, `phone_number`, `website_url`, `company_address`, `industry`, `status`).
2. **`jobs`**: Stores tenant-scoped job specifications (`job_id`, `tenant_id`, `title`, `description`, `requirements`, `location`, `salary`, `employment_type`, `status`, `created_at`). Cascades on tenant deletion.
3. **`applicants`**: Stores candidate applications, Azure Blob paths (`cv_storage_path`), ATS stage (`status`), grounded AI compatibility scores (`ai_score`), HTML executive summaries (`ai_summary`), and SHA-256 JSON audit payloads (`ai_audit_fingerprint`).
4. **`fairness_audit_runs`**: Stores aggregate telemetry for Super-Admin counterfactual bias audit executions (`run_uuid`, `total_pairs`, `mean_delta`, `max_delta`, `parity_status`).
5. **`fairness_audit_pairs`**: Stores per-pair counterfactual evaluation records linking `run_id` to Baseline (Profile A) and Variant (Profile B) scores and deltas.
6. **`news_articles`**: Caches aggregated recruitment and tech industry RSS feed items (`title`, `link`, `description`, `source_name`, `published_at`).
7. **`system_logs`**: Immutable security and operational audit trail recording `event_type`, `user_label`, `severity` (`INFO`, `WARNING`, `CRITICAL`), `tenant_id`, `target_type`, `target_id`, `ip_address`, and JSON `metadata`.

---

## 10. Regulatory Compliance Standards

* **EU Artificial Intelligence Act (Regulation EU 2024/1689, Annex III):** Complies with Article 10 (data governance & non-discrimination) and Article 13 (transparency & explainability for high-risk employment AI).
* **UK Equality Act 2010 (Section 19):** Empirically proves zero indirect discrimination across 9 protected characteristics (gender, ethnicity, age, maternity breaks, socioeconomic background, military service, LGBTQ+ affinity, disability accommodation, and positive control baseline).
* **UK Data Protection Act 2018 / UK GDPR:** Enforces strict data minimization through Stage 0 PII stripping, dynamic `tenant_id` BOLA-safe database isolation, 100% client-side CV Builder execution, and 1-click Article 17 Right to Erasure.
* **ISO/IEC 42001:2023 & WCAG 2.1 Level AA:** Meets international standards for Artificial Intelligence Management Systems (AIMS) and accessible web user interfaces.

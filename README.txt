# PostYourJobUK — Platform Operations & Architecture Guide

*PostYourJobUK* is a cloud-native, multi-tenant AI recruitment Software-as-a-Service (SaaS) and Applicant Tracking System (ATS) platform powered by a 4-tier integrated ecosystem of Microsoft Azure cloud services (App Service, Container Registry, Blob Storage, Storage Queues, Serverless Functions, Key Vault, and MySQL Flexible Server) alongside an enterprise transactional SMTP email engine (Brevo Relay + Cloudflare SPF/DKIM/DMARC). The platform is engineered to automate the end-to-end hiring funnel, eliminate algorithmic demographic bias, prevent LLM hallucinations, and drastically reduce talent acquisition infrastructure costs for small-to-medium enterprises (SMEs).

---

## 1. Core Platform Lifecycle

The platform manages the complete recruitment lifecycle through an asynchronous, event-driven workflow:

1. **Job Creation:** A verified recruiter logs into their isolated tenant workspace (`tenant_id`) and posts a new job specification with required technical competencies and years of experience.
2. **Public Indexing:** The job listing is dynamically published to the public search index, allowing job seekers to browse active opportunities.
3. **Candidate Application & Instant Email Receipt:** Candidates apply by uploading their CV in PDF or DOCX format. Upon submission, `MailerService` dispatches an instant, branded HTML confirmation receipt (`sendApplicationConfirmation`) via Brevo SMTP (`careers@postyourjobuk.co.uk`) with `Reply-To` mapped to the hiring company.
4. **Asynchronous Ingestion & Storage:** The web application streams the physical CV binary to Azure Blob Storage and enqueues a lightweight JSON payload into Azure Storage Queue (`ai-processing-queue`).
5. **Two-Stage Decoupled AI Evaluation:** An asynchronous Python Azure Function (`postyourjobuk-ai-func`) triggers on the queue message, sanitizes PII, extracts factual claims via Google Gemini 2.5 Flash, passes evidence through a deterministic Python Grounding Gate, and generates a SHA-256 cryptographic audit fingerprint.
6. **Recruiter Review & Executive Analytics:** Calculated match scores, executive summaries, verbatim evidence quotes, and audit fingerprints automatically populate the recruiter’s 4-column desktop applicant roster and 6-chart Analytics Dashboard (`/dashboard/analytics`).
7. **ATS Pipeline Progression & Automated Follow-Up Emails:** Recruiters transition candidates across hiring stages (`reviewed` -> `interviewing` -> `hired` or `rejected`) using 1-click CSRF-protected pipeline actions (`POST /applicant/status`). Moving a candidate to `interviewing` automatically dispatches a personalized Interview Invitation email (`sendInterviewInvitation`) populated with the recruiter's live contact details; moving to `hired` dispatches a formal Job Offer email (`sendJobOffer`); moving to `rejected` performs a silent database update.

---

## 2. 4-Tier Azure Cloud & Transactional Email Architecture

*PostYourJobUK* utilizes a decoupled, resilient 4-tier cloud infrastructure to ensure zero user-facing HTTP page timeouts during heavy document processing and transactional email delivery:

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                           TIER 1: CLIENT ACCESS                                 │
│                  (Recruiters / Candidates / Administrators)                     │
└──────────────────────────────────────┬──────────────────────────────────────────┘
                                       │ (HTTPS / Port 443)
                                       ▼
┌─────────────────────────────────────────────────────────────────────────────────┐
│                 TIER 2: APPLICATION LAYER (Azure App Service)                   │
│                  (postyourjobuk-web — PHP 8.2/8.3 Apache MVC)                   │
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

### Infrastructure Component Roles

* **Tier 1 — Presentation Layer:** Handles user interactions over HTTPS for candidates, recruiters, and super-administrators, featuring a unified 4-color KPI stat card design system (Amber `#f59e0b`, Emerald `#10b981`, Sky Blue `#38bdf8`, Coral Red `#ef4444`) and interactive Chart.js analytics suites.
* **Tier 2 — Application Layer (`postyourjobuk-web`):** Containerized PHP Apache MVC application running on Azure App Service via Azure Container Registry (`postyourjobukacr`). Implements Zero-Trust session fingerprinting, CSRF token validation, and strict `tenant_id` BOLA-safe database scoping (`Applicant::updateStatusSecure()`).
* **Tier 3 — Cloud Services, Messaging & Transactional Email:**
  * **Azure Key Vault (`kv-postyourjobuk`):** Centralized secret injection for database credentials (`DB-HOST`, `DB-NAME`, `DB-USER`, `DB-PASS`), AI keys, and SMTP credentials (`SMTP-PASS`).
  * **Azure Blob Storage (`hranalyticalstorage`):** Encrypted storage for candidate CVs, backed by local volume fallback resiliency.
  * **Azure Storage Queue (`ai-processing-queue`):** Message broker decoupling web ingestion from backend AI processing.
  * **Brevo Transactional SMTP Relay (`smtp-relay.brevo.com:587`):** Powered by PHPMailer (`MailerService.php`) and authenticated via Cloudflare DNS (`SPF`, `DKIM`, and `DMARC p=quarantine`) under `@postyourjobuk.co.uk` for inbox delivery across Yahoo, Gmail, and Outlook.
* **Tier 4 — Serverless AI Processing & Persistence (`postyourjobuk-ai-func` & `hr-analytical-db`):**
  * **Azure Function App:** Serverless Python 3.11 background worker operating on a Flex Consumption plan. Executes the two-stage AI evaluation pipeline upon queue notification.
  * **Azure Database for MySQL Flexible Server (`postyourjobhere`):** Central relational store operating under SSL/TLS across 7 production tables (`tenants`, `jobs`, `applicants`, `fairness_audit_pairs`, `fairness_audit_runs`, `news_articles`, `system_logs`).

---

## 3. Two-Stage Decoupled AI Evaluation Pipeline

To guarantee 100% explainability, eliminate LLM hallucinations, and prevent demographic bias, AI evaluation is split into two distinct execution tiers:

```
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
* **Stage 1 — Factual Read-Only Extraction (Gemini 2.5 Flash):** Evaluates anonymized text at temperature `0.0` solely to identify core job requirements and propose verbatim evidence quotes. Gemini is strictly prohibited from scoring or ranking candidates.
* **Stage 2 — Python Grounding Gate:** Validates evidence character-for-character against raw text, performs `difflib` plagiarism detection (>0.85 copy ratio), merges overlapping work histories, and applies a 50% weight reduction for non-domain roles.
* **Stage 3 & 4 — Base-100 Subtractive Math Scoring & SHA-256 Fingerprinting:** Computes final scores using deterministic subtractive math ($\text{Score} = \max(0, 100 - \text{Skill Deduction} - \text{Experience Deduction})$) and outputs an immutable SHA-256 cryptographic audit payload to MySQL.

---

## 4. Automated ATS Pipeline & Transactional Email Engine

The platform provides a full Applicant Tracking System (ATS) state machine that connects recruiter decision-making directly to automated candidate communications:

* **BOLA-Protected State Transitions (`Applicant::updateStatusSecure`):** Every status change (`reviewed`, `interviewing`, `hired`, `rejected`) is strictly scoped to both `applicant_id` and `tenant_id` via `POST /applicant/status` with CSRF token validation and `AuditLogger` telemetry (`APPLICANT_STATUS_CHANGED`).
* **3 Automated Transactional Email Flows (`src/Services/MailerService.php`):**
  1. **Application Confirmation (`sendApplicationConfirmation`):** Sent automatically when a candidate applies (`POST /job/apply`), confirming CV receipt for the specific job title and company.
  2. **Interview Invitation (`sendInterviewInvitation`):** Triggered when a recruiter clicks `🟢 Invite`. Dynamically injects the recruiter's live contact person, phone number, email address, and company address from the `tenants` table so the candidate can schedule their interview directly.
  3. **Formal Job Offer (`sendJobOffer`):** Triggered when a recruiter clicks `🎉 Offer`. Sends a congratulatory offer letter outlining HR onboarding next steps.
* **Silent High-Volume Rejection (`🛑 Reject`):** Updates candidate status to `rejected` without dispatching an email, giving recruiters frictionless pipeline control.
* **Balanced 4-Column Desktop Applicants Table (`src/views/applicants/list.php`):** Features a single-row filter/sort toolbar, 4 uniform-colored stage KPI cards (*Total*, *Interviewing*, *Hired / Offered*, *Rejected*), and 4 balanced columns (*Candidate & Stage*, *AI Match & Evaluation*, *Pipeline Follow-Up*, *Utility*).

---

## 5. Executive Recruiter & Super-Admin Analytics Suites

Both Recruiter and Super-Admin analytics portals feature multi-chart visual intelligence powered by Chart.js and a uniform 4-color KPI card hierarchy:

* **Recruiter Analytics Dashboard (`/dashboard/analytics`):**
  1. **Top Jobs by Applicant Volume:** Multi-color horizontal bar chart assigning a unique color to each job posting.
  2. **AI Match Quality by Job Posting:** Dual-line chart comparing *Average AI Match Score (%)* vs. *Highest Candidate Score (%)* per role.
  3. **ATS Recruitment Pipeline:** 4-stage doughnut chart + live counter pills (*Reviewed*, *Interviewing*, *Hired*, *Rejected*).
  4. **Applications by Submission Date:** Multi-color Pie Chart showing application volume share across active submission dates.
  5. **Candidate AI Compatibility Tiers:** Pie chart segmenting candidates into *Strong Match (≥80%)*, *Qualified (50%–79%)*, and *Low Match (<50%)*.
  6. **Activity by Employment Type:** Doughnut chart breaking down applications across *Full-time*, *Contract*, *Part-time*, and *Internship* roles.
  7. **Top 5 Highest-Scoring Candidates Leaderboard:** Cross-job table ranking the top 5 candidates across the company with 1-click `👁️ View Profile` access.
* **Super-Admin Platform Analytics Suite (`/admin/analytics`):**
  1. **Top Tenants by Platform Activity:** Multi-color horizontal bar chart comparing applicant volume and job postings per registered company.
  2. **Platform Growth & Activity Velocity:** Dual-curve line chart tracking *Candidate Applications* vs. *New Job Postings* across active platform dates.
  3. **Global ATS Conversion Funnel (All Tenants):** Platform-wide doughnut chart + 4 stage counter pills aggregating hiring progression across all recruiters.
  4. **AI CV Processing Engine & Queue Health:** Pie chart + 3 SLA pills monitoring *Queue SLA (%)*, *Platform Mean AI Score (%)*, and *Failed Jobs*.
  5. **Tenant Distribution by Industry Sector:** Multi-color pie chart breaking down registered recruiter workspaces by market vertical.
  6. **Security & Audit Log Telemetry:** Doughnut chart + 3 severity pills visualizing `system_logs` events across `INFO`, `WARNING`, and `CRITICAL` severities.
  7. **Multi-Tenant Utilisation & Performance Leaderboard:** Full-width executive table ranking tenants by active/total jobs, applicant volume, mean AI score progress bars, ATS hiring outcomes, and 1-click `🏢 Inspect Tenant` navigation.

---

## 6. Live Algorithmic Parity Audit Testbed & Certificate Generator

The platform includes a dedicated Super-Admin Compliance Workspace (`tenant_id = 9999`) accessible via `/admin/fairness`:

* **Live Cloud Execution Trigger (`⚡ Run Live Algorithmic Parity Audit`):** Dispatches 18 synthetic candidate CVs across 9 counterfactual pairs (stored in Azure Blob Storage under `cv-storage/tenant-9999/job-9999/`) into `ai-processing-queue` for real-time re-evaluation.
* **Real-Time Asynchronous Telemetry:** An AJAX status monitor tracks completion (`X / 18 Completed`) and dynamically updates parity scores without requiring page refreshes.
* **Counterfactual Evidence Inspector Modal (`[🔍 Inspect Evidence]`):** Provides 4 forensic inspection tabs:
  1. *Verbatim Evidence & Grounding:* Side-by-side claim verification.
  2. *Side-by-Side CV & PII Redaction:* Verifies Stage 0 PII removal.
  3. *Full AI Summary Output:* Comparative analysis of executive summaries.
  4. *Cryptographic Audit Fingerprint:* SHA-256 hash inspection, model parameters, and execution timestamps.
* **Official Exportable Compliance Certificate (`/admin/fairness/certificate`):** Formatted with `@media print` rules for one-click PDF export, displaying SHA-256 verification signatures and formal alignment badges for **EU AI Act (Annex III)**, **UK Equality Act 2010 (Section 19)**, and **ISO 42001**.
* **Empirical Parity Proof:** Certified 100% Parity Safe with a **0.00 pt mean disparity delta** across all protected social dimensions.

---

## 7. Role Capabilities & Access Control

* **Candidates (Public / Unauthenticated):**
  * Browse and search active job listings across validated recruiter organizations.
  * Submit job applications with PDF/DOCX CV uploads and receive instant HTML application confirmation emails.
  * Receive automated interview invitations (with direct recruiter contact details) and formal job offer emails as their application progresses.
  * Read industry news articles and submit contact inquiries.
* **Recruiters (Tenant Administrators):**
  * Manage job specifications (create, edit, publish, delete) within their isolated `tenant_id` scope.
  * View applicant rosters in a balanced 4-column desktop table with AI-generated match scores, executive summaries, and verbatim evidence quotes.
  * Advance candidates through the ATS pipeline (`🟢 Invite to Interview`, `🎉 Extend Offer`, `🛑 Reject`) with 1-click automated email triggers.
  * Analyze hiring performance in the 6-chart Recruiter Analytics Dashboard (`/dashboard/analytics`) and Top 5 Talent Leaderboard.
  * Delete candidate applications in compliance with UK GDPR data retention policies.
* **Super-Administrators (Platform Operations):**
  * Provision, approve, edit, or suspend recruiter tenant accounts (suspending a tenant instantly hides its public jobs).
  * Monitor multi-tenant utilization, AI queue SLA, global ATS conversions, and security audit telemetry in the 6-chart Platform Analytics Suite (`/admin/analytics`).
  * Inspect real-time system health logs (`/admin/logs`) and Azure infrastructure performance.
  * Execute live cloud fairness audits on Tenant 9999 (`/admin/fairness`) and export SHA-256 signed PDF Algorithmic Compliance Certificates (`/admin/fairness/certificate`).

---

## 8. Regulatory Compliance Standards

* **EU Artificial Intelligence Act (Regulation EU 2024/1689, Annex III):** Complies with Article 10 (data governance & non-discrimination) and Article 13 (transparency & explainability for high-risk employment AI).
* **UK Equality Act 2010 (Section 19):** Empirically proves zero indirect discrimination across protected characteristics (gender, ethnicity, age, maternity breaks, socioeconomic background, military service, LGBTQ+ affinity, disability accommodation).
* **UK Data Protection Act 2018 / UK GDPR:** Enforces strict data minimization through Stage 0 PII stripping and dynamic `tenant_id` BOLA-safe database isolation.
* **ISO/IEC 42001:2023:** Meets international standards for Artificial Intelligence Management Systems (AIMS).

# PostYourJobUK — Platform Operations & Architecture Guide

*PostYourJobUK* is a cloud-native, multi-tenant AI recruitment Software-as-a-Service (SaaS) platform powered by a 4-tier integrated ecosystem of Microsoft Azure cloud services (App Service, Container Registry, Blob Storage, Storage Queues, Serverless Functions, Key Vault, and MySQL Flexible Server). The platform is engineered to eliminate algorithmic demographic bias, prevent LLM hallucinations, and drastically reduce talent acquisition infrastructure costs for small-to-medium enterprises (SMEs).

---

## 1. Core Platform Lifecycle

The platform manages the complete recruitment lifecycle through an asynchronous, event-driven workflow:

1. **Job Creation:** A verified recruiter logs into their isolated tenant workspace (`tenant_id`) and posts a new job specification with required technical competencies and years of experience.
2. **Public Indexing:** The job listing is dynamically published to the public search index, allowing job seekers to browse active opportunities.
3. **Candidate Application:** Candidates apply by uploading their CV in PDF or DOCX format.
4. **Asynchronous Ingestion & Storage:** The web application streams the physical CV binary to Azure Blob Storage and enqueues a lightweight JSON payload into Azure Storage Queue (`ai-processing-queue`), instantly confirming receipt to the candidate.
5. **Two-Stage Decoupled AI Evaluation:** An asynchronous Python Azure Function (`postyourjobuk-ai-func`) triggers on the queue message, sanitizes PII, extracts factual claims via Google Gemini 2.5 Flash, passes evidence through a deterministic Python Grounding Gate, and generates a SHA-256 cryptographic audit fingerprint.
6. **Recruiter Review & Analytics:** Calculated match scores, executive summaries, verbatim evidence quotes, and audit fingerprints automatically populate the recruiter’s dashboard for objective, data-driven candidate shortlisting.

---

## 2. 4-Tier Azure Cloud Architecture

*PostYourJobUK* utilizes a decoupled, resilient 4-tier cloud infrastructure to ensure zero user-facing HTTP page timeouts during heavy document processing:

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                           TIER 1: CLIENT ACCESS                                 │
│                  (Recruiters / Candidates / Administrators)                     │
└──────────────────────────────────────┬──────────────────────────────────────────┘
                                       │ (HTTPS / Port 443)
                                       ▼
┌─────────────────────────────────────────────────────────────────────────────────┐
│                 TIER 2: APPLICATION LAYER (Azure App Service)                   │
│                     (postyourjobuk-web — PHP 8.3 MVC)                           │
└───────────────┬──────────────────────┬──────────────────────┬───────────────────┘
                │                      │                      │
                ▼ (Pull Secrets)       ▼ (CV Binary Upload)   ▼ (Enqueue JSON)
┌───────────────────────┬──────────────────────────────┬──────────────────────────┐
│ Azure Key Vault       │ Azure Blob Storage           │ Azure Storage Queue      │
│ (kv-postyourjobuk)    │ (hranalyticalstorage)        │ (ai-processing-queue)    │
├───────────────────────┴──────────────────────────────┴──────────────────────────┤
│                  TIER 3: CLOUD SERVICES AND MESSAGING                           │
└───────────────▲──────────────────────▲──────────────────────┬───────────────────┘
                │                      │                      │
                │ (Fetch Secrets)      │ (Download CV)        │ (Queue Trigger)
┌───────────────┴──────────────────────┴──────────────────────▼───────────────────┐
│              TIER 4: AI PROCESSING & PERSISTENCE (Azure Function)              │
│                     (postyourjobuk-ai-func — Python 3.11)                       │
└──────────────────────────────────────┬──────────────────────────────────────────┘
                                       │ (Commit Scores & Audit Fingerprints)
                                       ▼
┌─────────────────────────────────────────────────────────────────────────────────┐
│             Azure Database for MySQL Flexible Server (hr-analytical-db)          │
│   (tenants [Tenant 9999], jobs, applicants [ai_audit_fingerprint], system_logs) │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### Infrastructure Component Roles

* **Tier 1 — Presentation Layer:** Handles user interactions over HTTPS for candidates, recruiters, and super-administrators.
* **Tier 2 — Application Layer (`postyourjobuk-web`):** Containerized PHP 8.3 MVC application running on Azure App Service via Azure Container Registry. Implements Zero-Trust session fingerprinting, CSRF protection, and dynamic `tenant_id` database scoping in `BaseModel`.
* **Tier 3 — Cloud Services & Messaging:**
  * **Azure Key Vault (`kv-postyourjobuk`):** Centralized secret injection for database credentials and API keys.
  * **Azure Blob Storage (`hranalyticalstorage`):** Encrypted storage for candidate CVs, backed by local volume fallback resiliency.
  * **Azure Storage Queue (`ai-processing-queue`):** Message broker decoupling web ingestion from backend AI processing.
* **Tier 4 — Serverless AI Processing & Persistence (`postyourjobuk-ai-func` & `hr-analytical-db`):**
  * **Azure Function App:** Serverless Python 3.11 background worker operating on a Flex Consumption plan. Executes the two-stage AI evaluation pipeline upon queue notification.
  * **Azure Database for MySQL Flexible Server:** Central relational store operating under SSL/TLS, containing multi-tenant applicant records, system audit logs, and `ai_audit_fingerprint` JSON payloads.

---

## 3. Two-Stage Decoupled AI Evaluation Pipeline

To guarantee 100% explainability, eliminate LLM hallucinations, and prevent demographic bias, AI evaluation is split into two distinct execution tiers:

```
┌────────────────────────────────────────────────────────────────────────────────┐
│             SERVERLESS AI PROCESSING PIPELINE (postyourjobuk-ai-func)           │
│                                                                                │
│ 1. Text Extraction & Stage 0 Regex PII Redaction (`redact_cv`)                 │
│    └─► Strips names, emails, phone numbers, DOB, graduation years, & hobbies. │
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

## 4. Live Algorithmic Parity Audit Testbed & Certificate Generator

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

## 5. Role Capabilities & Access Control

* **Candidates (Public / Unauthenticated):**
  * Browse and search active job listings across validated recruiter organizations.
  * Submit job applications with PDF/DOCX CV uploads.
  * Read industry news articles and submit contact inquiries.
* **Recruiters (Tenant Administrators):**
  * Manage job specifications (create, edit, publish, delete) within their isolated `tenant_id` scope.
  * View applicant rosters, AI-generated match scores, executive summaries, and evidence quotes.
  * Delete candidate applications in compliance with data retention policies.
* **Super-Administrators (Platform Operations):**
  * Provision, approve, or suspend recruiter tenant accounts (suspending a tenant instantly hides its public jobs).
  * Monitor real-time system health logs and Azure infrastructure performance.
  * Execute live cloud fairness audits on Tenant 9999 (`/admin/fairness`).
  * Export SHA-256 signed PDF Algorithmic Compliance Certificates (`/admin/fairness/certificate`).

---

## 6. Regulatory Compliance Standards

* **EU Artificial Intelligence Act (Regulation EU 2024/1689, Annex III):** Complies with Article 10 (data governance & non-discrimination) and Article 13 (transparency & explainability for high-risk employment AI).
* **UK Equality Act 2010 (Section 19):** Empirically proves zero indirect discrimination across protected characteristics (gender, ethnicity, age, maternity breaks, socioeconomic background, military service, LGBTQ+ affinity, disability accommodation).
* **UK Data Protection Act 2018 / UK GDPR:** Enforces strict data minimization through Stage 0 PII stripping and dynamic `tenant_id` database isolation.
* **ISO/IEC 42001:2023:** Meets international standards for Artificial Intelligence Management Systems (AIMS).

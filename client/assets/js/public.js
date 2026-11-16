/**
 * PostYourJobUK — Public Job Board, Static Views, Job Details & Candidate Application (public.js)
 * Zero External Dependencies | WCAG 2.1 Level AA Compliant
 */
(function (window) {
    'use strict';

    const { escapeHtml, showToast } = window.AppApi;
    let allJobs = [];

    const STATIC_VIEWS = {
        '/about': `
            <section class="container section">
                <div style="margin-bottom: 2.25rem;">
                    <span class="badge" style="background: rgba(139, 92, 246, 0.16); color: #c4b5fd; border: 1px solid rgba(139, 92, 246, 0.35); text-transform: uppercase; letter-spacing: 0.08em; font-size: 0.72rem; margin-bottom: 0.75rem; display: inline-block;">
                        About the Platform &amp; Cloud Architecture
                    </span>
                    <h1 style="margin: 0 0 0.85rem 0;">The Engineering Behind PostYourJobUK</h1>
                    <p class="text-muted" style="max-width: 820px; font-size: 1rem; line-height: 1.75; margin: 0 0 1.75rem 0;">
                        <strong>PostYourJobUK</strong> is a cloud-native, multi-tenant AI recruitment Software-as-a-Service (SaaS) and Applicant Tracking System (ATS) engineered to solve two critical industry challenges: prohibitive enterprise ATS licensing costs for UK SMEs and black-box algorithmic bias in automated CV screening.
                    </p>
                    <hr style="border: none; border-top: 1px solid var(--glass-border); margin: 0;">
                </div>

                <!-- 4 Uniform KPI Stat Cards -->
                <div class="stats-grid" style="margin-bottom: 2.25rem;">
                    <div class="card"><h3>AI Grounding Gate</h3><p class="stat-value">100%</p></div>
                    <div class="card"><h3>Demographic Disparity</h3><p class="stat-value">0.0 pt</p></div>
                    <div class="card"><h3>Cloud Architecture</h3><p class="stat-value">4-Tier Azure</p></div>
                    <div class="card"><h3>Compliance Standards</h3><p class="stat-value">EU AI Act</p></div>
                </div>

                <!-- 6 Core Architectural Pillars (2x3 Grid) -->
                <div class="grid-2" style="gap: 1.5rem; margin-bottom: 2.25rem;">
                    <article class="card" style="border-top: 3px solid #8b5cf6;">
                        <h3 style="font-size: 1.12rem; color: #ffffff; margin-bottom: 0.5rem;">🏢 Multi-Tenant Isolation &amp; Zero-Trust Security</h3>
                        <p class="text-muted" style="font-size: 0.9rem; line-height: 1.65;">
                            Built on a decoupled Three-Tier architecture (<code>client/</code> Presentation Tier + <code>server/</code> REST API Tier) enforcing strict <code>tenant_id</code> BOLA database scoping, Zero-Trust session fingerprinting (<code>User-Agent</code> + SHA-256 IP hash), and timing-safe CSRF token verification.
                        </p>
                    </article>

                    <article class="card" style="border-top: 3px solid #10b981;">
                        <h3 style="font-size: 1.12rem; color: #ffffff; margin-bottom: 0.5rem;">🧠 Two-Stage Decoupled AI Evaluation</h3>
                        <p class="text-muted" style="font-size: 0.9rem; line-height: 1.65;">
                            Candidate CVs undergo Stage 0 regex PII redaction before factual claim extraction via Google Gemini 2.5 Flash at <code>temperature = 0.0</code>. Every claim is verified character-for-character by a deterministic Python Grounding Gate (<code>difflib</code> anti-plagiarism + timeline overlap merging) and scored via Base-100 subtractive math.
                        </p>
                    </article>

                    <article class="card" style="border-top: 3px solid #38bdf8;">
                        <h3 style="font-size: 1.12rem; color: #ffffff; margin-bottom: 0.5rem;">⚡ Event-Driven Serverless Azure Ecosystem</h3>
                        <p class="text-muted" style="font-size: 0.9rem; line-height: 1.65;">
                            Web ingestion (<code>postyourjobuk-web</code> on Azure App Service) is decoupled from heavy document processing via Azure Blob Storage (<code>hranalyticalstorage</code>), Azure Storage Queues (<code>ai-processing-queue</code>), Python 3.11 Azure Functions (<code>postyourjobuk-ai-func</code>), Azure Key Vault (<code>kv-postyourjobuk</code>), and Azure MySQL Flexible Server.
                        </p>
                    </article>

                    <article class="card" style="border-top: 3px solid #f59e0b;">
                        <h3 style="font-size: 1.12rem; color: #ffffff; margin-bottom: 0.5rem;">✉️ Automated ATS Pipeline &amp; Brevo Transactional SMTP</h3>
                        <p class="text-muted" style="font-size: 0.9rem; line-height: 1.65;">
                            Recruiters transition candidates across <code>Reviewed</code>, <code>Interviewing</code>, <code>Hired</code>, and <code>Rejected</code> stages with 1-click automated HTML emails dispatched via PHPMailer and Brevo SMTP Relay (authenticated with Cloudflare <code>SPF</code>, <code>DKIM</code>, and <code>DMARC</code>) embedding live employer business cards.
                        </p>
                    </article>

                    <article class="card" style="border-top: 3px solid #ec4899;">
                        <h3 style="font-size: 1.12rem; color: #ffffff; margin-bottom: 0.5rem;">⚖️ Live Counterfactual Fairness Testbed (Tenant 9999)</h3>
                        <p class="text-muted" style="font-size: 0.9rem; line-height: 1.65;">
                            Features a dedicated Super-Admin compliance workspace that dispatches 18 synthetic CVs across 9 protected demographic pairs into the live Azure Queue, providing a 4-tab Counterfactual Evidence Inspector and SHA-256 signed PDF Compliance Certificates.
                        </p>
                    </article>

                    <article class="card" style="border-top: 3px solid #06b6d4;">
                        <h3 style="font-size: 1.12rem; color: #ffffff; margin-bottom: 0.5rem;">📊 Executive Analytics &amp; Zero-CDN Vector CV Builder</h3>
                        <p class="text-muted" style="font-size: 0.9rem; line-height: 1.65;">
                            Provides candidates with a 100% private, 6-template client-side Resume Builder that compiles native PDF-1.4 vector text streams for <code>PyPDF2</code>, alongside 12 interactive HTML5 <code>&lt;canvas&gt;</code> charts across the Recruiter and Super-Admin analytics suites.
                        </p>
                    </article>
                </div>

                <div class="card" style="border-left: 4px solid #8b5cf6; background: rgba(30, 41, 59, 0.75); padding: 1.35rem 1.75rem;">
                    <p style="margin: 0; font-size: 0.95rem; color: #e2e8f0; font-style: italic; line-height: 1.7;">
                        "PostYourJobUK was engineered to prove that automated AI talent acquisition can be simultaneously low-cost for SMEs, cloud-scalable on Microsoft Azure, and 100% mathematically transparent."
                    </p>
                </div>
            </section>
        `,
        '/vision': `
            <section class="container section">
                <div style="margin-bottom: 2.25rem;">
                    <span class="badge" style="background: rgba(16, 185, 129, 0.16); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35); text-transform: uppercase; letter-spacing: 0.08em; font-size: 0.72rem; margin-bottom: 0.75rem; display: inline-block;">
                        Our Vision &amp; Ethical AI Charter
                    </span>
                    <h1 style="margin: 0 0 0.85rem 0;">Hiring Should Be Simple, Fast, Fair, and Verifiable</h1>
                    <p class="text-muted" style="max-width: 820px; font-size: 1rem; line-height: 1.75; margin: 0 0 1.75rem 0;">
                        PostYourJobUK was built for recruiters and candidates who value transparency and merit. We believe every applicant deserves to be evaluated strictly on verified competencies and domain experience—never on demographic proxies, name origin, or ungrounded black-box AI guesses.
                    </p>
                    <hr style="border: none; border-top: 1px solid var(--glass-border); margin: 0;">
                </div>

                <!-- 4 Uniform KPI Vision Pillars -->
                <div class="stats-grid" style="margin-bottom: 2.25rem;">
                    <div class="card"><h3>Zero Hallucinations</h3><p class="stat-value">Verbatim</p></div>
                    <div class="card"><h3>Protected Dimensions</h3><p class="stat-value">9 Pairs</p></div>
                    <div class="card"><h3>PII Anonymization</h3><p class="stat-value">Stage 0</p></div>
                    <div class="card"><h3>Audit Traceability</h3><p class="stat-value">SHA-256</p></div>
                </div>

                <!-- 6 Core Vision Cards (2x3 Grid) -->
                <div class="grid-2" style="gap: 1.5rem; margin-bottom: 2.25rem;">
                    <article class="card" style="border-top: 3px solid #f59e0b;">
                        <h3 style="font-size: 1.12rem; color: #ffffff; margin-bottom: 0.5rem;">🚀 Speed Without Compromise</h3>
                        <p class="text-muted" style="font-size: 0.9rem; line-height: 1.65;">
                            Post a live job specification in minutes and receive instant AI-scored candidate evaluations asynchronously via Azure Storage Queues—removing manual screening bottlenecks from sign-up to shortlisting.
                        </p>
                    </article>

                    <article class="card" style="border-top: 3px solid #10b981;">
                        <h3 style="font-size: 1.12rem; color: #ffffff; margin-bottom: 0.5rem;">🔎 Verbatim Evidence Over Formatting</h3>
                        <p class="text-muted" style="font-size: 0.9rem; line-height: 1.65;">
                            Our Python Grounding Gate rejects unverified AI claims and job-description copy-pasting (<code>difflib &gt; 0.85</code>), ensuring recruiters only see candidate scores backed by exact character-for-character quotes from the CV.
                        </p>
                    </article>

                    <article class="card" style="border-top: 3px solid #38bdf8;">
                        <h3 style="font-size: 1.12rem; color: #ffffff; margin-bottom: 0.5rem;">⚖️ Mathematical Parity by Design</h3>
                        <p class="text-muted" style="font-size: 0.9rem; line-height: 1.65;">
                            By stripping names, contact handles, birth dates, graduation eras, and affinity markers before LLM extraction and applying deterministic Base-100 subtractive math, identical qualifications always yield an identical score (<code>0.0 pt</code> delta).
                        </p>
                    </article>

                    <article class="card" style="border-top: 3px solid #8b5cf6;">
                        <h3 style="font-size: 1.12rem; color: #ffffff; margin-bottom: 0.5rem;">🤝 Respectful Candidate Communication</h3>
                        <p class="text-muted" style="font-size: 0.9rem; line-height: 1.65;">
                            Candidates are never left in the dark. Every application triggers an instant HTML confirmation receipt, and 1-click recruiter stage transitions dispatch official Interview Invitations and Job Offers with direct hiring contact details.
                        </p>
                    </article>

                    <article class="card" style="border-top: 3px solid #ec4899;">
                        <h3 style="font-size: 1.12rem; color: #ffffff; margin-bottom: 0.5rem;">🇬🇧 UK-Rooted, Regulatory-First Engineering</h3>
                        <p class="text-muted" style="font-size: 0.9rem; line-height: 1.65;">
                            Purpose-built for UK organisations with native alignment to the <strong>UK Equality Act 2010 (Section 19)</strong>, <strong>UK GDPR / Data Protection Act 2018</strong>, <strong>EU AI Act (Annex III)</strong>, and <strong>ISO/IEC 42001:2023</strong>.
                        </p>
                    </article>

                    <article class="card" style="border-top: 3px solid #06b6d4;">
                        <h3 style="font-size: 1.12rem; color: #ffffff; margin-bottom: 0.5rem;">♿ Universal Accessibility (WCAG 2.1 Level AA)</h3>
                        <p class="text-muted" style="font-size: 0.9rem; line-height: 1.65;">
                            Engineered with high-contrast dark-mode typography, keyboard-navigable tables and focus-trapped modals, screen-reader live regions, and a free client-side ATS CV builder so every job seeker can participate equally.
                        </p>
                    </article>
                </div>

                <div class="card" style="border-left: 4px solid #10b981; background: rgba(30, 41, 59, 0.75); padding: 1.35rem 1.75rem;">
                    <p style="margin: 0; font-size: 0.95rem; color: #e2e8f0; font-style: italic; line-height: 1.7;">
                        "Our goal is straightforward: to give recruiters the simplest, most effective, and most verifiable AI hiring platform available—where technology amplifies human judgment without ever introducing bias."
                    </p>
                </div>
            </section>
        `,
        '/terms': `
            <section class="container section">
                <div style="margin-bottom: 2.25rem;">
                    <span class="badge" style="background: rgba(56, 189, 248, 0.16); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.35); text-transform: uppercase; letter-spacing: 0.08em; font-size: 0.72rem; margin-bottom: 0.75rem; display: inline-block;">
                        Legal, Governance &amp; Data Protection
                    </span>
                    <h1 style="margin: 0 0 0.5rem 0;">Terms of Service &amp; UK GDPR Privacy Policy</h1>
                    <p style="font-size: 0.82rem; color: var(--text-subtle); margin: 0 0 0.85rem 0;">
                        Effective &amp; Last Updated: October 2026 &bull; Aligned with UK Data Protection Act 2018, UK GDPR, EU AI Act (Regulation EU 2024/1689 Annex III), UK Equality Act 2010, &amp; ISO/IEC 42001:2023
                    </p>
                    <p class="text-muted" style="max-width: 820px; font-size: 0.98rem; line-height: 1.75; margin: 0 0 1.75rem 0;">
                        Please read these Terms of Service and Data Governance provisions carefully before using <strong>PostYourJobUK</strong>. By registering a recruiter workspace, posting a job specification, or submitting a candidate application, you agree to be bound by the following terms.
                    </p>
                    <hr style="border: none; border-top: 1px solid var(--glass-border); margin: 0;">
                </div>

                <!-- 9 Comprehensive Legal & Compliance Cards -->
                <div class="grid-2" style="gap: 1.35rem; margin-bottom: 2rem;">
                    <article class="card">
                        <h3 style="font-size: 1.05rem; color: #f59e0b; margin-bottom: 0.45rem;">1. Acceptance of Terms &amp; Eligibility</h3>
                        <p class="text-muted" style="font-size: 0.88rem; line-height: 1.65;">
                            By accessing or using any part of the PostYourJobUK platform, you confirm that you have read, understood, and agreed to these Terms of Service. Recruiter workspaces are restricted to authorised hiring managers, HR professionals, and registered organisations (18+).
                        </p>
                    </article>

                    <article class="card">
                        <h3 style="font-size: 1.05rem; color: #10b981; margin-bottom: 0.45rem;">2. Multi-Tenant Isolation &amp; BOLA Security</h3>
                        <p class="text-muted" style="font-size: 0.88rem; line-height: 1.65;">
                            Each recruiter organisation operates within a strictly isolated tenant boundary (<code>tenant_id</code>). Attempting to access, modify, or exfiltrate job postings or candidate CVs belonging to another tenant is blocked by Broken Object Level Authorization (BOLA) guards and logged in <code>system_logs</code>.
                        </p>
                    </article>

                    <article class="card">
                        <h3 style="font-size: 1.05rem; color: #38bdf8; margin-bottom: 0.45rem;">3. Stage 0 PII Redaction &amp; Data Minimization</h3>
                        <p class="text-muted" style="font-size: 0.88rem; line-height: 1.65;">
                            In compliance with UK GDPR Article 5(1)(c) (Data Minimization), candidate CVs undergo automated Stage 0 regular-expression anonymization prior to AI inference—redacting names, email addresses, phone numbers, dates of birth, graduation years, and demographic markers before text reaches Google Gemini 2.5 Flash.
                        </p>
                    </article>

                    <article class="card">
                        <h3 style="font-size: 1.05rem; color: #a855f7; margin-bottom: 0.45rem;">4. Encrypted Cloud Storage &amp; Right to Erasure</h3>
                        <p class="text-muted" style="font-size: 0.88rem; line-height: 1.65;">
                            Uploaded PDF and DOCX resumes are stored in encrypted Azure Blob Storage (<code>hranalyticalstorage</code>) scoped strictly to the hiring job and tenant. Recruiters can permanently delete individual or bulk candidate records and cloud CV blobs at any time in accordance with UK GDPR Article 17 (Right to Erasure).
                        </p>
                    </article>

                    <article class="card">
                        <h3 style="font-size: 1.05rem; color: #ec4899; margin-bottom: 0.45rem;">5. Algorithmic Transparency (EU AI Act Annex III)</h3>
                        <p class="text-muted" style="font-size: 0.88rem; line-height: 1.65;">
                            AI compatibility scores are advisory decision-support metrics computed deterministically from verbatim CV evidence and Base-100 subtractive math. Every evaluation generates an immutable SHA-256 cryptographic audit fingerprint (<code>ai_audit_fingerprint</code>), and final hiring decisions remain under human recruiter oversight.
                        </p>
                    </article>

                    <article class="card">
                        <h3 style="font-size: 1.05rem; color: #06b6d4; margin-bottom: 0.45rem;">6. Non-Discrimination &amp; UK Equality Act 2010</h3>
                        <p class="text-muted" style="font-size: 0.88rem; line-height: 1.65;">
                            Recruiters must ensure all job specifications comply with the UK Equality Act 2010 and do not discriminate across protected characteristics. PostYourJobUK reserves the right to suspend any tenant account (<code>status = 'suspended'</code>) that posts unlawful, misleading, or discriminatory listings.
                        </p>
                    </article>

                    <article class="card">
                        <h3 style="font-size: 1.05rem; color: #eab308; margin-bottom: 0.45rem;">7. Transactional Email Communications</h3>
                        <p class="text-muted" style="font-size: 0.88rem; line-height: 1.65;">
                            By submitting an application or advancing a candidate in the ATS pipeline, users consent to receive transactional status notifications (Application Confirmation, Interview Invitation, and Formal Job Offer) dispatched via our authenticated Brevo SMTP relay (<code>careers@postyourjobuk.co.uk</code>).
                        </p>
                    </article>

                    <article class="card">
                        <h3 style="font-size: 1.05rem; color: #f43f5e; margin-bottom: 0.45rem;">8. Client-Side Resume Builder Privacy</h3>
                        <p class="text-muted" style="font-size: 0.88rem; line-height: 1.65;">
                            The Free ATS CV &amp; Resume Builder (<code>/resume-builder</code>) executes 100% client-side inside the user's browser. Draft resume inputs are never transmitted to or stored on PostYourJobUK servers unless the candidate subsequently chooses to upload the generated PDF to an active job listing.
                        </p>
                    </article>
                </div>

                <!-- 9th Full-Width Governing Law & Contact Card -->
                <article class="card" style="border-left: 4px solid #38bdf8; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                    <div style="flex: 1; min-width: 260px;">
                        <h3 style="font-size: 1.05rem; color: #ffffff; margin-bottom: 0.35rem;">9. Limitation of Liability &amp; Governing Law (England &amp; Wales)</h3>
                        <p class="text-muted" style="font-size: 0.88rem; line-height: 1.6; margin: 0;">
                            These Terms of Service are governed by and construed in accordance with the laws of England and Wales. Any disputes arising in connection with the platform shall be subject to the exclusive jurisdiction of the courts of England and Wales.
                        </p>
                    </div>
                    <a href="/contact" class="btn btn-primary btn-sm">✉️ Contact Data Protection &amp; Support Team →</a>
                </article>
            </section>
        `
    };

    function renderContactView(host) {
        host.innerHTML = `
            <section class="container section">
                <p class="contact-eyebrow">Support &amp; Enterprise Inquiries</p>
                <h1 class="contact-title">Get in Touch</h1>
                <p class="contact-sub">Have questions about our AI recruitment platform, tenant onboarding, or compliance certification? Send our team a message below.</p>
                <div class="grid-2">
                    <div class="card">
                        <h2 style="font-size: 1.2rem; margin-bottom: 1rem;">Send an Inquiry</h2>
                        <form id="public-contact-form">
                            <div class="form-group">
                                <label for="contact-name">Full Name *</label>
                                <input type="text" id="contact-name" name="name" required autocomplete="name">
                            </div>
                            <div class="form-group">
                                <label for="contact-email">Email Address *</label>
                                <input type="email" id="contact-email" name="email" required autocomplete="email">
                            </div>
                            <div class="form-group">
                                <label for="contact-subject">Subject</label>
                                <input type="text" id="contact-subject" name="subject" placeholder="Tenant Onboarding / General Question">
                            </div>
                            <div class="form-group">
                                <label for="contact-message">Message *</label>
                                <textarea id="contact-message" name="message" rows="5" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Send Message</button>
                        </form>
                    </div>
                    <div class="card">
                        <h2 style="font-size: 1.2rem; margin-bottom: 1rem;">Platform Operations</h2>
                        <p class="text-muted" style="margin-bottom: 1rem;"><strong>Email:</strong> careers@postyourjobuk.co.uk</p>
                        <p class="text-muted" style="margin-bottom: 1rem;"><strong>Headquarters:</strong> London, United Kingdom</p>
                        <p class="text-muted"><strong>Operating Hours:</strong> Monday – Friday, 09:00 – 18:00 GMT</p>
                    </div>
                </div>
            </section>
        `;

        const form = document.getElementById('public-contact-form');
        if (form) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const payload = {
                    name: form.name.value.trim(),
                    email: form.email.value.trim(),
                    subject: form.subject.value.trim(),
                    message: form.message.value.trim()
                };
                const res = await window.AppApi.post('/api/public/contact', payload);
                if (res.ok && res.data.success) {
                    showToast(res.data.message, 'success');
                    form.reset();
                } else {
                    showToast((res.data && res.data.error) || 'Could not send message.', 'error');
                }
            });
        }
    }

    function renderJobsList(jobs, totalCount = null) {
        const container = document.getElementById('jobs-list-container');
        const counterEl = document.getElementById('jobs-count-label');
        if (!container) return;

        const total = totalCount !== null ? totalCount : allJobs.length;
        if (counterEl) {
            counterEl.textContent = jobs.length === total
                ? `Showing ${jobs.length} opportunities`
                : `Showing ${jobs.length} of ${total} opportunities`;
        }

        if (!jobs.length) {
            container.style.display = 'block';
            container.innerHTML = `
                <div class="card text-center" style="padding: 2.5rem; background: #1f2937; border: 1px solid #374151;">
                    <p style="font-size: 1.1rem; color: #94a3b8; margin-bottom: 1rem;">🔍 No job opportunities match your selected filter criteria.</p>
                    <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('filter-reset-btn')?.click()">Clear Filters</button>
                </div>
            `;
            return;
        }

        container.style.display = 'grid';
        container.innerHTML = jobs.map(job => {
            const loc = job.location ? escapeHtml(job.location) : 'Remote';
            const salary = job.salary ? escapeHtml(job.salary) : 'Competitive';
            const rawEmp = (job.employment_type || 'full-time').replace('-', ' ');
            const empType = escapeHtml(rawEmp.charAt(0).toUpperCase() + rawEmp.slice(1));
            const industry = job.industry ? escapeHtml(job.industry) : '';

            return `
                <div class="job-card-wrapper">
                    <a href="/job?id=${Number(job.job_id)}" class="card job-card-link">
                        <div>
                            <!-- Header: FULL Company Name + Employment Badge -->
                            <div class="flex-between" style="margin-bottom: 1rem; align-items: center; gap: 0.75rem; display: flex; justify-content: space-between;">
                                <div style="font-weight: 700; color: #f3f4f6; font-size: 1.05rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; min-width: 0; flex: 1;" title="${escapeHtml(job.company_name || 'Company')}">
                                    ${escapeHtml(job.company_name || 'Hiring Company')}
                                </div>
                                <span class="badge" style="background: rgba(139, 92, 246, 0.15); color: #a78bfa; border: 1px solid rgba(139, 92, 246, 0.3); font-size: 0.75rem; white-space: nowrap; flex-shrink: 0;">
                                    ${empType}
                                </span>
                            </div>

                            <!-- Title -->
                            <h3 style="margin-bottom: 0.5rem; font-size: 1.15rem; color: #ffffff;">${escapeHtml(job.title)}</h3>

                            <!-- Meta details (Location, Salary, Industry) -->
                            <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 1.25rem; display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center;">
                                <span>📍 ${loc}</span>
                                <span style="color: var(--border);">•</span>
                                <span>💷 ${salary}</span>
                                ${industry ? `<span style="color: var(--border);">•</span><span style="color: #38bdf8;">🏷️ ${industry}</span>` : ''}
                            </p>
                        </div>

                        <!-- Card Footer: Time Ago (Left) + Unique Job ID (Right) -->
                        <div class="text-muted" style="font-size: 0.8rem; border-top: 1px solid var(--border); padding-top: 0.85rem; display: flex; justify-content: space-between; align-items: center;">
                            <span>🕒 Posted ${escapeHtml(job.time_ago || 'recently')}</span>
                            <span style="font-family: monospace; font-size: 0.78rem; font-weight: 600; color: #94a3b8; background: rgba(255, 255, 255, 0.04); border: 1px solid var(--border); padding: 0.2rem 0.5rem; border-radius: 4px;">
                                Job ID: ${Number(job.job_id)}
                            </span>
                        </div>
                    </a>
                </div>
            `;
        }).join('');
    }

    function applyClientJobFilters() {
        const typeVal = (document.getElementById('filter-type')?.value || 'all').toLowerCase();
        const locVal = (document.getElementById('filter-location')?.value || 'all').toLowerCase();
        const indVal = (document.getElementById('filter-industry')?.value || 'all').toLowerCase();
        const sortVal = document.getElementById('filter-sort')?.value || 'newest';
        const resetBtn = document.getElementById('filter-reset-btn');

        const isFiltered = (typeVal !== 'all' || locVal !== 'all' || indVal !== 'all' || sortVal !== 'newest');
        if (resetBtn) {
            resetBtn.style.display = isFiltered ? 'inline-block' : 'none';
        }

        let filtered = allJobs.slice();

        if (typeVal !== 'all') {
            filtered = filtered.filter(j => {
                const t = String(j.employment_type || '').toLowerCase();
                const l = String(j.location || 'remote').toLowerCase();
                if (typeVal === 'remote') return t.includes('remote') || l.includes('remote');
                return t === typeVal;
            });
        }
        if (locVal !== 'all') {
            filtered = filtered.filter(j => String(j.location || 'Remote').toLowerCase() === locVal);
        }
        if (indVal !== 'all') {
            filtered = filtered.filter(j => String(j.industry || 'General').toLowerCase() === indVal);
        }

        filtered.sort((a, b) => {
            if (sortVal === 'title_asc') return String(a.title || '').localeCompare(String(b.title || ''));
            if (sortVal === 'company_asc') return String(a.company_name || '').localeCompare(String(b.company_name || ''));
            const da = new Date(a.created_at || 0).getTime();
            const db = new Date(b.created_at || 0).getTime();
            return sortVal === 'oldest' ? da - db : db - da;
        });

        renderJobsList(filtered, allJobs.length);
    }

    async function initHomeView() {
        const path = window.location.pathname.replace(/\/+$/, '') || '/';
        const mainEl = document.getElementById('main-content');
        if (!mainEl) return;

        if (STATIC_VIEWS[path]) {
            mainEl.innerHTML = STATIC_VIEWS[path];
            return;
        }
        if (path === '/contact') {
            renderContactView(mainEl);
            return;
        }

        const params = new URLSearchParams(window.location.search);
        const searchQ = params.get('search') || '';
        const res = await window.AppApi.get('/api/public/home' + (searchQ ? `?search=${encodeURIComponent(searchQ)}` : ''));
        if (!res.ok || !res.data.success) {
            showToast('Failed to load job listings.', 'error');
            return;
        }

        allJobs = res.data.jobs || [];
        const news = res.data.news || [];
        const locations = (res.data.filters && res.data.filters.locations) || [];
        const industries = (res.data.filters && res.data.filters.industries) || [];

        const locSelect = document.getElementById('filter-location');
        if (locSelect) {
            locSelect.innerHTML = '<option value="all">📍 All Locations</option>' +
                locations.map(l => `<option value="${escapeHtml(l.toLowerCase())}">${escapeHtml(l)}</option>`).join('');
        }

        const indSelect = document.getElementById('filter-industry');
        if (indSelect) {
            indSelect.innerHTML = '<option value="all">🏢 All Industries</option>' +
                industries.map(i => `<option value="${escapeHtml(i.toLowerCase())}">${escapeHtml(i)}</option>`).join('');
        }

        const newsContainer = document.getElementById('news-carousel-container');
        if (newsContainer) {
            newsContainer.innerHTML = news.length
                ? news.map(n => `
                    <article class="card news-card-item">
                        <div>
                            <div style="display: flex; justify-content: space-between; font-size: 0.78rem; color: #94a3b8; margin-bottom: 0.45rem;">
                                <span style="color: #a78bfa; font-weight: 700;">${escapeHtml(n.source_name)}</span>
                                <span>${escapeHtml(n.time_ago || '')}</span>
                            </div>
                            <h3 style="font-size: 1rem; margin-bottom: 0.5rem;">
                                <a href="${escapeHtml(n.link)}" target="_blank" rel="noopener noreferrer" style="color: #ffffff;">${escapeHtml(n.title)}</a>
                            </h3>
                            <p class="text-muted" style="font-size: 0.85rem;">${escapeHtml(n.description || '')}</p>
                        </div>
                        <div style="margin-top: 0.85rem;">
                            <a href="${escapeHtml(n.link)}" target="_blank" rel="noopener noreferrer" style="font-size: 0.82rem; font-weight: 700;">Read Full Article →</a>
                        </div>
                    </article>
                `).join('')
                : '<p class="text-muted">No news articles available right now.</p>';
        }

        ['filter-type', 'filter-location', 'filter-industry', 'filter-sort'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('change', applyClientJobFilters);
        });

        const resetBtn = document.getElementById('filter-reset-btn');
        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
                ['filter-type', 'filter-location', 'filter-industry'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.value = 'all';
                });
                const sEl = document.getElementById('filter-sort');
                if (sEl) sEl.value = 'newest';
                applyClientJobFilters();
            });
        }

        const refreshNewsBtn = document.getElementById('refresh-news-btn');
        if (refreshNewsBtn) {
            refreshNewsBtn.addEventListener('click', async () => {
                refreshNewsBtn.disabled = true;
                refreshNewsBtn.textContent = 'Refreshing…';
                const r = await window.AppApi.post('/api/news/refresh');
                refreshNewsBtn.disabled = false;
                refreshNewsBtn.textContent = '↻ Refresh News';
                if (r.ok && r.data.success) {
                    showToast(r.data.message, 'success');
                    initHomeView();
                }
            });
        }

        renderJobsList(allJobs);
    }

    /**
     * Initialize Single Job Details & Candidate Application View (job.html)
     */
    async function initJobDetailView() {
        const params = new URLSearchParams(window.location.search);
        const jobId = Number(params.get('id') || 0);
        if (!jobId) {
            window.location.href = '/';
            return;
        }

        const res = await window.AppApi.get(`/api/jobs/detail?id=${jobId}`);
        if (!res.ok || !res.data.success) {
            showToast((res.data && res.data.error) || 'Job not found.', 'error');
            setTimeout(() => { window.location.href = '/'; }, 1200);
            return;
        }

        const job = res.data.job;
        document.title = `${job.title} — PostYourJobUK`;

        const host = document.getElementById('job-detail-host');
        if (!host) return;

        host.innerHTML = `
            <div style="margin-bottom: 1.5rem;">
                <a href="/" class="btn btn-ghost btn-sm">← Back to Job Listings</a>
            </div>
            <div class="grid-2" style="grid-template-columns: 1.25fr 0.95fr; align-items: start;">
                <div>
                    <article class="card" style="margin-bottom: 1.5rem;">
                        <span class="status-badge active" style="margin-bottom: 0.75rem;">${escapeHtml(job.employment_type || 'Full-time')}</span>
                        <h1 style="font-size: 1.85rem; margin-bottom: 0.5rem;">${escapeHtml(job.title)}</h1>
                        <p style="color: #a78bfa; font-weight: 700; margin-bottom: 1rem;">
                            🏢 ${escapeHtml(job.company_name || 'Verified Employer')} &bull; 📍 ${escapeHtml(job.location || 'Remote')} &bull; 💷 ${escapeHtml(job.salary || 'Competitive')}
                        </p>
                        <hr style="border: none; border-top: 1px solid rgba(255,255,255,0.1); margin: 1.25rem 0;">
                        <h2 style="font-size: 1.15rem; margin-bottom: 0.65rem;">Role Description</h2>
                        <div class="text-muted" style="white-space: pre-line; margin-bottom: 1.5rem;">${escapeHtml(job.description)}</div>
                        <h2 style="font-size: 1.15rem; margin-bottom: 0.65rem;">Key Requirements &amp; Qualifications</h2>
                        <div class="text-muted" style="white-space: pre-line;">${escapeHtml(job.requirements)}</div>
                    </article>

                    <article class="card">
                        <h2 style="font-size: 1.1rem; margin-bottom: 0.75rem;">🏢 Employer &amp; Recruiter Business Card</h2>
                        <p><strong>Organisation:</strong> ${escapeHtml(job.company_name || 'N/A')}</p>
                        <p><strong>Hiring Contact:</strong> ${escapeHtml(job.contact_person || 'Recruitment Team')}</p>
                        <p><strong>Industry:</strong> ${escapeHtml(job.industry || 'General')}</p>
                        ${job.website_url ? `<p><strong>Website:</strong> <a href="${escapeHtml(job.website_url)}" target="_blank" rel="noopener">${escapeHtml(job.website_url)}</a></p>` : ''}
                    </article>
                </div>

                <aside class="card" aria-labelledby="apply-heading">
                    <h2 id="apply-heading" style="font-size: 1.3rem; margin-bottom: 0.35rem;">Apply for This Position</h2>
                    <p class="text-muted" style="font-size: 0.88rem; margin-bottom: 1.25rem;">Upload your CV in PDF or DOCX format (max 5 MB). You will receive an instant email confirmation.</p>

                    <form id="candidate-apply-form" enctype="multipart/form-data">
                        <input type="hidden" name="job_id" value="${Number(job.job_id)}">
                        <input type="hidden" name="form_rendered_at" value="${Number(res.data.form_rendered_at || Math.floor(Date.now() / 1000))}">
                        <div class="sr-only" aria-hidden="true">
                            <label for="candidate_website_url">Leave this field blank</label>
                            <input type="text" id="candidate_website_url" name="candidate_website_url" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="form-group">
                            <label for="app-name">Full Name *</label>
                            <input type="text" id="app-name" name="name" required minlength="2" maxlength="100" autocomplete="name">
                        </div>
                        <div class="form-group">
                            <label for="app-email">Email Address *</label>
                            <input type="email" id="app-email" name="email" required maxlength="150" autocomplete="email">
                        </div>
                        <div class="form-group">
                            <label for="app-phone">Phone Number (Optional)</label>
                            <input type="tel" id="app-phone" name="phone" maxlength="30" autocomplete="tel">
                        </div>
                        <div class="form-group">
                            <label for="app-cv">CV Document (PDF or DOCX, max 5 MB) *</label>
                            <input type="file" id="app-cv" name="cv_document" accept=".pdf,.docx" required>
                        </div>

                        <button type="submit" id="apply-submit-btn" class="btn btn-primary w-100" style="width: 100%;">Submit Application</button>
                    </form>
                </aside>
            </div>
        `;

        const form = document.getElementById('candidate-apply-form');
        if (form) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const btn = document.getElementById('apply-submit-btn');
                btn.disabled = true;
                btn.textContent = 'Uploading & Verifying CV…';

                const fd = new FormData(form);
                const applyRes = await window.AppApi.post('/api/apply', fd);

                btn.disabled = false;
                btn.textContent = 'Submit Application';

                if (applyRes.status === 409 && applyRes.data && applyRes.data.duplicate) {
                    const d = applyRes.data.duplicate_details || {};
                    const dupBody = document.getElementById('duplicate-modal-body');
                    if (dupBody) {
                        dupBody.innerHTML = `
                            We already have an active application on file for <strong>${escapeHtml(d.email || '')}</strong>
                            for the <strong>${escapeHtml(d.job_title || job.title)}</strong> role (submitted on ${escapeHtml(d.applied_at || 'recently')}).
                        `;
                    }
                    window.AppA11y.openModal('duplicate-app-modal', btn);
                    return;
                }

                if (applyRes.ok && applyRes.data.success) {
                    showToast(applyRes.data.message, 'success', 8000);
                    form.reset();
                } else {
                    showToast((applyRes.data && applyRes.data.error) || 'Application submission failed.', 'error');
                }
            });
        }
    }

    window.AppPublic = {
        initHomeView,
        initJobDetailView
    };
})(window);

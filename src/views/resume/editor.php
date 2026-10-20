<!-- Load jsPDF for True Vector/Text-Layer PDF Generation (100% PyPDF2 & ATS Compatible) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<style>
.builder-layout {
    display: grid;
    grid-template-columns: 1.05fr 0.95fr;
    gap: 2rem;
    align-items: start;
}
@media (max-width: 1080px) {
    .builder-layout {
        grid-template-columns: 1fr;
    }
}
.step-tabs {
    display: flex;
    gap: 0.4rem;
    flex-wrap: wrap;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border);
}
.step-tab-btn {
    flex: 1 1 auto;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid var(--border);
    color: var(--text-muted);
    padding: 0.55rem 0.75rem;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: center;
}
.step-tab-btn.active {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: #ffffff;
    border-color: var(--primary);
    box-shadow: 0 4px 14px rgba(139, 92, 246, 0.35);
}
.step-tab-btn.completed {
    border-color: rgba(16, 185, 129, 0.5);
    color: #34d399;
}
.wizard-step {
    display: none;
}
.wizard-step.active {
    display: block;
    animation: fadeInStep 0.22s ease;
}
@keyframes fadeInStep {
    from { opacity: 0; transform: translateY(4px); }
    to { opacity: 1; transform: translateY(0); }
}
.repeater-box {
    background: rgba(17, 24, 39, 0.75);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 10px;
    padding: 1.25rem;
    margin-bottom: 1rem;
    position: relative;
}
.ai-tip-banner {
    background: rgba(16, 185, 129, 0.08);
    border: 1px solid rgba(16, 185, 129, 0.3);
    border-radius: 8px;
    padding: 0.85rem 1rem;
    margin-bottom: 1.25rem;
    font-size: 0.83rem;
    color: #d1fae5;
    line-height: 1.5;
}
/* Live A4 Preview Paper */
.preview-stage {
    background: #1e293b;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 14px;
    padding: 1.5rem;
    position: sticky;
    top: 90px;
}
#cv-document {
    background: #ffffff;
    color: #0f172a;
    width: 100%;
    min-height: 680px;
    padding: 28px 32px;
    border-radius: 6px;
    box-shadow: 0 12px 35px rgba(0, 0, 0, 0.45);
    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
    font-size: 12.5px;
    line-height: 1.5;
    overflow-wrap: break-word;
}
/* Template Variations for Live Preview */
#cv-document.tpl-professional {
    border-top: 6px solid #1e293b;
}
#cv-document.tpl-professional .cv-doc-header {
    border-bottom: 2px solid #0f172a;
    padding-bottom: 12px;
    margin-bottom: 16px;
}
#cv-document.tpl-professional .cv-sec-title {
    color: #0f172a;
    border-bottom: 1.5px solid #cbd5e1;
    padding-bottom: 3px;
    margin: 14px 0 8px 0;
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

#cv-document.tpl-modern {
    padding: 0;
    overflow: hidden;
}
#cv-document.tpl-modern .cv-doc-header {
    background: linear-gradient(135deg, #6d28d9, #4f46e5);
    color: #ffffff !important;
    padding: 22px 28px;
    margin-bottom: 16px;
}
#cv-document.tpl-modern .cv-doc-header h1,
#cv-document.tpl-modern .cv-doc-header div,
#cv-document.tpl-modern .cv-doc-header span {
    color: #ffffff !important;
}
#cv-document.tpl-modern .cv-doc-body {
    padding: 0 28px 28px 28px;
}
#cv-document.tpl-modern .cv-sec-title {
    color: #6d28d9;
    border-left: 4px solid #7c3aed;
    padding-left: 8px;
    margin: 14px 0 8px 0;
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

#cv-document.tpl-executive {
    border-top: 10px solid #0f172a;
}
#cv-document.tpl-executive .cv-doc-header {
    text-align: center;
    border-bottom: 1px solid #94a3b8;
    padding-bottom: 14px;
    margin-bottom: 16px;
}
#cv-document.tpl-executive .cv-sec-title {
    text-align: center;
    color: #0f172a;
    border-top: 1px solid #e2e8f0;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
    padding: 4px 0;
    margin: 14px 0 8px 0;
    font-size: 11.5px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.12em;
}

#cv-document.tpl-technical {
    border-left: 8px solid #059669;
}
#cv-document.tpl-technical .cv-doc-header {
    border-bottom: 2px solid #059669;
    padding-bottom: 12px;
    margin-bottom: 16px;
}
#cv-document.tpl-technical .cv-sec-title {
    color: #065f46;
    background: #ecfdf5;
    border-left: 3px solid #059669;
    padding: 3px 8px;
    margin: 14px 0 8px 0;
    font-size: 11.5px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}
</style>

<div class="container" style="max-width: 1360px; margin-top: 2rem; margin-bottom: 4rem;">

    <!-- Top Workspace Bar -->
    <div class="card" style="padding: 1.1rem 1.5rem; margin-bottom: 1.75rem; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;">
        <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
            <a href="/resume-builder" class="btn btn-ghost btn-sm">← Change Template</a>
            <div>
                <h2 style="font-size: 1.25rem; margin: 0; color: #ffffff;">🛠️ Interactive Resume Builder</h2>
                <span style="font-size: 0.78rem; color: var(--text-muted);">Complete the 5 steps below • Live A4 preview updates as you type</span>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            <label for="live-template-switcher" style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">Active Style:</label>
            <select id="live-template-switcher" onchange="switchTemplateStyle(this.value)" style="width: auto; padding: 0.45rem 2rem 0.45rem 0.75rem; font-size: 0.85rem;">
                <option value="professional" <?= ($template === 'professional') ? 'selected' : '' ?>>The Professional (ATS Classic)</option>
                <option value="modern" <?= ($template === 'modern') ? 'selected' : '' ?>>The Modern Accent</option>
                <option value="executive" <?= ($template === 'executive') ? 'selected' : '' ?>>The Executive Minimalist</option>
                <option value="technical" <?= ($template === 'technical') ? 'selected' : '' ?>>The Technical Specialist</option>
            </select>

            <button type="button" onclick="saveAndDownloadPDF()" class="btn btn-success btn-sm" style="padding: 0.55rem 1.25rem; font-size: 0.85rem;">
                💾 Complete & Save PDF
            </button>
        </div>
    </div>

    <div class="builder-layout">

        <!-- LEFT COLUMN: 5-STEP GUIDED FORM -->
        <div class="card" style="padding: 1.75rem;">

            <!-- Step Navigation Pills -->
            <div class="step-tabs">
                <button type="button" class="step-tab-btn active" data-step="1" onclick="goToStep(1)">1. Personal</button>
                <button type="button" class="step-tab-btn" data-step="2" onclick="goToStep(2)">2. Summary</button>
                <button type="button" class="step-tab-btn" data-step="3" onclick="goToStep(3)">3. Experience</button>
                <button type="button" class="step-tab-btn" data-step="4" onclick="goToStep(4)">4. Education & Certs</button>
                <button type="button" class="step-tab-btn" data-step="5" onclick="goToStep(5)">5. Skills & Save</button>
            </div>

            <!-- STEP 1: PERSONAL DETAILS -->
            <div class="wizard-step active" id="step-1">
                <h3 style="margin-bottom: 0.35rem; color: #ffffff;">Step 1: Personal & Contact Details</h3>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.25rem;">
                    Enter your contact information so recruiters can reach you. (Note: Our AI strips personal identifiers during scoring to guarantee 100% fairness).
                </p>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Full Name *</label>
                        <input type="text" id="cv-name" class="form-control" placeholder="e.g. Alex Taylor" oninput="renderLiveCV()">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Target Role / Professional Headline *</label>
                        <input type="text" id="cv-headline" class="form-control" placeholder="e.g. Full-Stack Software Engineer" oninput="renderLiveCV()">
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Email Address *</label>
                        <input type="email" id="cv-email" class="form-control" placeholder="alex.taylor@email.co.uk" oninput="renderLiveCV()">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number *</label>
                        <input type="text" id="cv-phone" class="form-control" placeholder="+44 7700 900123" oninput="renderLiveCV()">
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">City / Location (UK)</label>
                        <input type="text" id="cv-location" class="form-control" placeholder="e.g. London, UK" oninput="renderLiveCV()">
                    </div>
                    <div class="form-group">
                        <label class="form-label">LinkedIn / Portfolio URL (Optional)</label>
                        <input type="text" id="cv-website" class="form-control" placeholder="linkedin.com/in/alextaylor" oninput="renderLiveCV()">
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; margin-top: 1rem;">
                    <button type="button" class="btn btn-primary" onclick="goToStep(2)">Next: Professional Summary →</button>
                </div>
            </div>

            <!-- STEP 2: PROFESSIONAL SUMMARY -->
            <div class="wizard-step" id="step-2">
                <h3 style="margin-bottom: 0.35rem; color: #ffffff;">Step 2: Professional Summary</h3>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;">
                    Write 2–4 concise sentences summarizing your domain expertise, years of practice, and core strengths.
                </p>

                <div class="form-group">
                    <label class="form-label">Career Summary Statement</label>
                    <textarea id="cv-summary" class="form-control" rows="5" placeholder="Results-driven professional with extensive experience designing, building, and maintaining production systems..." oninput="renderLiveCV()"></textarea>
                </div>

                <div style="display: flex; justify-content: space-between; margin-top: 1.25rem;">
                    <button type="button" class="btn btn-ghost" onclick="goToStep(1)">← Previous</button>
                    <button type="button" class="btn btn-primary" onclick="goToStep(3)">Next: Work Experience →</button>
                </div>
            </div>

            <!-- STEP 3: WORK EXPERIENCE (DYNAMIC REPEATER) -->
            <div class="wizard-step" id="step-3">
                <h3 style="margin-bottom: 0.35rem; color: #ffffff;">Step 3: Work Experience & Evidence</h3>
                <div class="ai-tip-banner">
                    💡 <strong>AI Scoring Tip:</strong> Our evaluation engine calculates your years of experience from your <strong>Start Year – End Year</strong> (e.g. <code>2021</code> to <code>Present</code>) and verifies required skills only when they are demonstrated in action inside your role bullet points!
                </div>

                <div id="experience-list"></div>

                <button type="button" class="btn btn-ghost btn-sm w-100" onclick="addExperienceItem()" style="margin-bottom: 1.5rem; border-style: dashed; border-color: rgba(139, 92, 246, 0.5); color: #c4b5fd;">
                    + Add Another Work Experience Role
                </button>

                <div style="display: flex; justify-content: space-between;">
                    <button type="button" class="btn btn-ghost" onclick="goToStep(2)">← Previous</button>
                    <button type="button" class="btn btn-primary" onclick="goToStep(4)">Next: Education & Certifications →</button>
                </div>
            </div>

            <!-- STEP 4: EDUCATION & ACTIVE CERTIFICATIONS -->
            <div class="wizard-step" id="step-4">
                <h3 style="margin-bottom: 0.35rem; color: #ffffff;">Step 4: Education, Licenses & Certifications</h3>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;">
                    Include your academic qualifications and any active professional licenses or statutory certifications (e.g. Active NMC PIN, CSCS Card, ACCA, AWS Certified).
                </p>

                <div id="education-list"></div>

                <button type="button" class="btn btn-ghost btn-sm w-100" onclick="addEducationItem()" style="margin-bottom: 1.25rem; border-style: dashed; border-color: rgba(139, 92, 246, 0.5); color: #c4b5fd;">
                    + Add Another Qualification / Degree
                </button>

                <div class="form-group" style="margin-top: 0.5rem;">
                    <label class="form-label">Active Certifications, Licenses & Projects (One per line)</label>
                    <textarea id="cv-certs" class="form-control" rows="4" placeholder="• Active AWS Certified Solutions Architect — Amazon Web Services (Current)&#10;• Full UK Driving Licence (Active)&#10;• Key Project: Built automated microservice pipeline using Docker and REST APIs" oninput="renderLiveCV()"></textarea>
                </div>

                <div style="display: flex; justify-content: space-between; margin-top: 1.25rem;">
                    <button type="button" class="btn btn-ghost" onclick="goToStep(3)">← Previous</button>
                    <button type="button" class="btn btn-primary" onclick="goToStep(5)">Next: Core Skills & Finish →</button>
                </div>
            </div>

            <!-- STEP 5: SKILLS & FINAL DOWNLOAD -->
            <div class="wizard-step" id="step-5">
                <h3 style="margin-bottom: 0.35rem; color: #ffffff;">Step 5: Core Skills & Save Your CV</h3>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.25rem;">
                    List your technical, clinical, or operational skills separated by commas. Remember to make sure your top skills are also mentioned in your Step 3 Experience bullet points!
                </p>

                <div class="form-group">
                    <label class="form-label">Key Competencies & Technical Skills (Comma Separated)</label>
                    <textarea id="cv-skills" class="form-control" rows="3" placeholder="PHP MVC, MySQL, JavaScript, Docker, REST API Integration, Git, Agile Delivery" oninput="renderLiveCV()"></textarea>
                </div>

                <div style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.14), rgba(139, 92, 246, 0.12)); border: 1px solid rgba(16, 185, 129, 0.4); border-radius: 12px; padding: 1.5rem; text-align: center; margin: 1.5rem 0;">
                    <div style="font-size: 2rem; margin-bottom: 0.4rem;">🎯</div>
                    <h4 style="color: #ffffff; margin-bottom: 0.4rem;">Ready to Generate Your Official PDF Resume?</h4>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.25rem;">
                        Click below to compile a high-resolution, ATS-searchable PDF document and choose where to save it on your device.
                    </p>
                    <button type="button" onclick="saveAndDownloadPDF()" class="btn btn-success" style="padding: 0.9rem 2.2rem; font-size: 1rem; font-weight: 700;">
                        💾 Complete & Save CV (PDF)
                    </button>
                </div>

                <div style="display: flex; justify-content: flex-start;">
                    <button type="button" class="btn btn-ghost" onclick="goToStep(4)">← Back to Education & Certs</button>
                </div>
            </div>

        </div>

        <!-- RIGHT COLUMN: LIVE A4 PREVIEW -->
        <div class="preview-stage">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem;">
                <span style="font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #94a3b8;">
                    📄 Live A4 Document Preview
                </span>
                <span id="preview-template-badge" style="font-size: 0.75rem; background: rgba(139, 92, 246, 0.2); color: #c4b5fd; padding: 0.2rem 0.65rem; border-radius: 999px; font-weight: 700;">
                    Template: <?= ucfirst(htmlspecialchars($template)) ?>
                </span>
            </div>

            <div id="cv-document" class="tpl-<?= htmlspecialchars($template) ?>">
                <div class="cv-doc-header">
                    <h1 id="pv-name" style="margin: 0; font-size: 22px; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 0.02em;">YOUR FULL NAME</h1>
                    <div id="pv-headline" style="font-size: 13px; font-weight: 700; color: #475569; margin-top: 2px;">Target Role / Professional Headline</div>
                    <div id="pv-contact" style="font-size: 11px; color: #64748b; margin-top: 5px;">
                        email@example.co.uk • +44 7700 900000 • London, UK
                    </div>
                </div>

                <div class="cv-doc-body">
                    <div class="cv-sec-title">Professional Summary</div>
                    <div id="pv-summary" style="font-size: 11.5px; color: #1e293b; margin-bottom: 12px; white-space: pre-wrap;">Results-driven professional with proven experience delivering high-impact solutions.</div>

                    <div class="cv-sec-title">Work Experience</div>
                    <div id="pv-experience"></div>

                    <div class="cv-sec-title">Education & Qualifications</div>
                    <div id="pv-education"></div>

                    <div id="pv-certs-block">
                        <div class="cv-sec-title">Certifications, Licenses & Key Projects</div>
                        <div id="pv-certs" style="font-size: 11.5px; color: #1e293b; margin-bottom: 12px; white-space: pre-wrap;"></div>
                    </div>

                    <div class="cv-sec-title">Core Competencies & Technical Skills</div>
                    <div id="pv-skills" style="font-size: 11.5px; color: #0f172a; font-weight: 600;">PHP MVC, MySQL, JavaScript, Docker, REST API Integration</div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- COMPLETION & SAVE MODAL -->
<div id="cv-completed-modal" style="display: none; position: fixed; inset: 0; background: rgba(7, 10, 18, 0.85); backdrop-filter: blur(8px); z-index: 9999; align-items: center; justify-content: center; padding: 1.5rem;">
    <div class="card" style="max-width: 520px; width: 100%; padding: 2.5rem 2rem; text-align: center; border: 1px solid rgba(16, 185, 129, 0.5); box-shadow: 0 25px 60px rgba(0,0,0,0.65); background: #111827;">
        <div style="width: 64px; height: 64px; border-radius: 50%; background: rgba(16, 185, 129, 0.15); border: 2px solid #10b981; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 1.25rem auto;">
            ✅
        </div>
        <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35); margin-bottom: 0.75rem; display: inline-block;">
            Resume Completed & Saved
        </span>
        <h3 style="color: #ffffff; font-size: 1.5rem; margin-bottom: 0.6rem;">Your ATS-Ready CV is Ready!</h3>
        <p style="color: var(--text-muted); font-size: 0.92rem; line-height: 1.6; margin-bottom: 1.25rem;">
            Your PDF resume (<strong id="saved-filename-display" style="color: #f3f4f6;">resume.pdf</strong>) has been generated with a native searchable text layer for 100% compatibility with our AI evaluation engine.
        </p>
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            <a href="/" class="btn btn-primary w-100" style="padding: 0.85rem; font-weight: 700;">
                🧭 Browse Open Jobs & Apply Now →
            </a>
            <button type="button" class="btn btn-ghost w-100" onclick="closeCompletionModal()">
                ✏️ Continue Editing Resume
            </button>
        </div>
    </div>
</div>

<script>
let currentTemplate = <?= json_encode($template) ?>;

const defaultExperiences = [
    {
        role: 'Senior Software Engineer',
        company: 'CoreTech Systems UK',
        start: '2021',
        end: 'Present',
        duties: '• Architected and maintained core backend platforms leveraging PHP MVC architecture and MySQL databases.\n• Developed secure, high-throughput REST API integration endpoints connecting external services.\n• Containerized development and production deployment microservices using Docker and modern JavaScript.'
    }
];

const defaultEducations = [
    {
        degree: 'BSc (Hons) Computer Science',
        school: 'University of Manchester',
        year: '2018 – 2021'
    }
];

function goToStep(stepNum) {
    document.querySelectorAll('.wizard-step').forEach(el => el.classList.remove('active'));
    const target = document.getElementById('step-' + stepNum);
    if (target) target.classList.add('active');

    document.querySelectorAll('.step-tab-btn').forEach(btn => {
        const s = parseInt(btn.getAttribute('data-step'), 10);
        btn.classList.remove('active');
        if (s < stepNum) {
            btn.classList.add('completed');
        } else if (s === stepNum) {
            btn.classList.add('active');
        }
    });
}

function switchTemplateStyle(tpl) {
    currentTemplate = tpl;
    const docEl = document.getElementById('cv-document');
    docEl.className = 'tpl-' + tpl;
    const badge = document.getElementById('preview-template-badge');
    if (badge) {
        badge.textContent = 'Template: ' + tpl.charAt(0).toUpperCase() + tpl.slice(1);
    }
}

function addExperienceItem(data = null) {
    const container = document.getElementById('experience-list');
    const idx = container.children.length;
    const box = document.createElement('div');
    box.className = 'repeater-box exp-item';
    box.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
            <strong style="font-size: 0.85rem; color: #c4b5fd;">Role #${idx + 1}</strong>
            ${idx > 0 ? `<button type="button" onclick="this.closest('.exp-item').remove(); renderLiveCV();" style="background: none; border: none; color: #ef4444; font-size: 0.8rem; cursor: pointer; font-weight: 600;">✕ Remove</button>` : ''}
        </div>
        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">Job Title *</label>
                <input type="text" class="form-control exp-role" placeholder="e.g. Software Engineer" value="${escapeAttr(data ? data.role : '')}" oninput="renderLiveCV()">
            </div>
            <div class="form-group">
                <label class="form-label">Company / Organisation *</label>
                <input type="text" class="form-control exp-company" placeholder="e.g. FinTech Solutions Ltd" value="${escapeAttr(data ? data.company : '')}" oninput="renderLiveCV()">
            </div>
        </div>
        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">Start Year (4-digit) *</label>
                <input type="text" class="form-control exp-start" placeholder="e.g. 2021" value="${escapeAttr(data ? data.start : '')}" oninput="renderLiveCV()">
            </div>
            <div class="form-group">
                <label class="form-label">End Year (or Present) *</label>
                <input type="text" class="form-control exp-end" placeholder="e.g. Present or 2025" value="${escapeAttr(data ? data.end : '')}" oninput="renderLiveCV()">
            </div>
        </div>
        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label">Key Duties & Verbatim Skill Evidence (Bullet Points) *</label>
            <textarea class="form-control exp-duties" rows="4" placeholder="• Describe how you used specific job-required skills in practice..." oninput="renderLiveCV()">${escapeHtml(data ? data.duties : '')}</textarea>
        </div>
    `;
    container.appendChild(box);
    renderLiveCV();
}

function addEducationItem(data = null) {
    const container = document.getElementById('education-list');
    const idx = container.children.length;
    const box = document.createElement('div');
    box.className = 'repeater-box edu-item';
    box.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
            <strong style="font-size: 0.85rem; color: #c4b5fd;">Qualification #${idx + 1}</strong>
            ${idx > 0 ? `<button type="button" onclick="this.closest('.edu-item').remove(); renderLiveCV();" style="background: none; border: none; color: #ef4444; font-size: 0.8rem; cursor: pointer; font-weight: 600;">✕ Remove</button>` : ''}
        </div>
        <div class="grid-2">
            <div class="form-group" style="margin-bottom: 0.5rem;">
                <label class="form-label">Degree / Qualification</label>
                <input type="text" class="form-control edu-degree" placeholder="e.g. BSc Computer Science" value="${escapeAttr(data ? data.degree : '')}" oninput="renderLiveCV()">
            </div>
            <div class="form-group" style="margin-bottom: 0.5rem;">
                <label class="form-label">Institution / University</label>
                <input type="text" class="form-control edu-school" placeholder="e.g. University of Leeds" value="${escapeAttr(data ? data.school : '')}" oninput="renderLiveCV()">
            </div>
        </div>
        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label">Years Attended / Graduated</label>
            <input type="text" class="form-control edu-year" placeholder="e.g. 2018 – 2021" value="${escapeAttr(data ? data.year : '')}" oninput="renderLiveCV()">
        </div>
    `;
    container.appendChild(box);
    renderLiveCV();
}

function escapeHtml(str) {
    return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}
function escapeAttr(str) {
    return escapeHtml(str).replace(/"/g, '&quot;');
}

function collectCVData() {
    const name = document.getElementById('cv-name').value.trim() || 'YOUR FULL NAME';
    const headline = document.getElementById('cv-headline').value.trim() || 'Professional Title / Headline';
    const email = document.getElementById('cv-email').value.trim() || 'email@example.co.uk';
    const phone = document.getElementById('cv-phone').value.trim() || '+44 7700 900000';
    const location = document.getElementById('cv-location').value.trim() || 'United Kingdom';
    const website = document.getElementById('cv-website').value.trim();
    const summary = document.getElementById('cv-summary').value.trim() || 'Dedicated professional with verified domain experience and strong technical execution.';
    const certs = document.getElementById('cv-certs').value.trim();
    const skills = document.getElementById('cv-skills').value.trim() || 'PHP MVC, MySQL, JavaScript, Docker, REST API Integration';

    const experiences = [];
    document.querySelectorAll('.exp-item').forEach(item => {
        const role = item.querySelector('.exp-role').value.trim();
        const company = item.querySelector('.exp-company').value.trim();
        const start = item.querySelector('.exp-start').value.trim();
        const end = item.querySelector('.exp-end').value.trim();
        const duties = item.querySelector('.exp-duties').value.trim();
        if (role || company || duties) {
            experiences.push({
                role: role || 'Professional Role',
                company: company || 'Organisation',
                start: start || '2021',
                end: end || 'Present',
                duties: duties
            });
        }
    });

    const educations = [];
    document.querySelectorAll('.edu-item').forEach(item => {
        const degree = item.querySelector('.edu-degree').value.trim();
        const school = item.querySelector('.edu-school').value.trim();
        const year = item.querySelector('.edu-year').value.trim();
        if (degree || school) {
            educations.push({
                degree: degree || 'Qualification',
                school: school || 'Institution',
                year: year || ''
            });
        }
    });

    return { name, headline, email, phone, location, website, summary, experiences, educations, certs, skills };
}

function renderLiveCV() {
    const data = collectCVData();

    document.getElementById('pv-name').textContent = data.name;
    document.getElementById('pv-headline').textContent = data.headline;

    const contactParts = [data.email, data.phone, data.location];
    if (data.website) contactParts.push(data.website);
    document.getElementById('pv-contact').textContent = contactParts.filter(Boolean).join(' • ');

    document.getElementById('pv-summary').textContent = data.summary;

    // Render Experience
    const expContainer = document.getElementById('pv-experience');
    expContainer.innerHTML = '';
    data.experiences.forEach(exp => {
        const div = document.createElement('div');
        div.style.marginBottom = '10px';
        div.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: baseline; font-weight: 700; color: #0f172a; font-size: 12px;">
                <span>${escapeHtml(exp.role)} | ${escapeHtml(exp.company)}</span>
                <span style="font-size: 11px; color: #475569;">${escapeHtml(exp.start)} - ${escapeHtml(exp.end)}</span>
            </div>
            <div style="font-size: 11.5px; color: #1e293b; margin-top: 3px; white-space: pre-wrap;">${escapeHtml(exp.duties)}</div>
        `;
        expContainer.appendChild(div);
    });

    // Render Education
    const eduContainer = document.getElementById('pv-education');
    eduContainer.innerHTML = '';
    data.educations.forEach(edu => {
        const div = document.createElement('div');
        div.style.marginBottom = '6px';
        div.innerHTML = `
            <div style="display: flex; justify-content: space-between; font-size: 11.5px;">
                <span><strong>${escapeHtml(edu.degree)}</strong> — ${escapeHtml(edu.school)}</span>
                <span style="color: #475569;">${escapeHtml(edu.year)}</span>
            </div>
        `;
        eduContainer.appendChild(div);
    });

    // Render Certifications
    const certsBlock = document.getElementById('pv-certs-block');
    const certsEl = document.getElementById('pv-certs');
    if (data.certs) {
        certsBlock.style.display = 'block';
        certsEl.textContent = data.certs;
    } else {
        certsBlock.style.display = 'none';
    }

    // Render Skills
    document.getElementById('pv-skills').textContent = data.skills;
}

/**
 * Generates a True Vector Text-Layer PDF using jsPDF so PyPDF2 & ATS parsers
 * extract 100% of the candidate text accurately, then lets the user save it locally.
 */
async function saveAndDownloadPDF() {
    const data = collectCVData();
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ unit: 'mm', format: 'a4', orientation: 'portrait' });

    const pageW = 210;
    const pageH = 297;
    const margin = 16;
    const contentW = pageW - margin * 2;
    let y = 18;

    // Template Color Palette
    const palettes = {
        professional: { primary: [15, 23, 42], accent: [51, 65, 85], banner: false },
        modern:       { primary: [109, 40, 217], accent: [79, 70, 229], banner: true },
        executive:    { primary: [15, 23, 42], accent: [71, 85, 105], banner: false, centered: true },
        technical:    { primary: [6, 95, 70], accent: [5, 150, 105], banner: false }
    };
    const pal = palettes[currentTemplate] || palettes.professional;

    function checkPageBreak(neededHeight = 14) {
        if (y + neededHeight > pageH - 15) {
            doc.addPage();
            y = 18;
        }
    }

    function addSectionHeading(title) {
        checkPageBreak(14);
        y += 3;
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(10.5);
        doc.setTextColor(...pal.primary);
        if (pal.centered) {
            doc.text(title.toUpperCase(), pageW / 2, y, { align: 'center' });
        } else {
            doc.text(title.toUpperCase(), margin, y);
        }
        y += 2;
        doc.setDrawColor(...pal.accent);
        doc.setLineWidth(0.35);
        doc.line(margin, y, pageW - margin, y);
        y += 5;
    }

    // Header Rendering
    if (pal.banner) {
        doc.setFillColor(...pal.primary);
        doc.rect(0, 0, pageW, 36, 'F');
        doc.setTextColor(255, 255, 255);
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(18);
        doc.text(data.name.toUpperCase(), margin, 15);

        doc.setFont('helvetica', 'normal');
        doc.setFontSize(11);
        doc.text(data.headline, margin, 22);

        const contactLine = [data.email, data.phone, data.location, data.website].filter(Boolean).join('  |  ');
        doc.setFontSize(9);
        doc.text(contactLine, margin, 29);
        y = 43;
    } else {
        doc.setTextColor(...pal.primary);
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(18);
        if (pal.centered) {
            doc.text(data.name.toUpperCase(), pageW / 2, y, { align: 'center' });
            y += 6;
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(11);
            doc.setTextColor(...pal.accent);
            doc.text(data.headline, pageW / 2, y, { align: 'center' });
            y += 5;
            const contactLine = [data.email, data.phone, data.location, data.website].filter(Boolean).join('  •  ');
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(9);
            doc.text(contactLine, pageW / 2, y, { align: 'center' });
            y += 4;
        } else {
            doc.text(data.name.toUpperCase(), margin, y);
            y += 6;
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(11);
            doc.setTextColor(...pal.accent);
            doc.text(data.headline, margin, y);
            y += 5;
            const contactLine = [data.email, data.phone, data.location, data.website].filter(Boolean).join('  •  ');
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(9);
            doc.setTextColor(80, 90, 105);
            doc.text(contactLine, margin, y);
            y += 4;
        }
        doc.setDrawColor(...pal.primary);
        doc.setLineWidth(0.6);
        doc.line(margin, y, pageW - margin, y);
        y += 6;
    }

    // 1. Professional Summary
    addSectionHeading('Professional Summary');
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(9.8);
    doc.setTextColor(30, 41, 59);
    const summaryLines = doc.splitTextToSize(data.summary, contentW);
    checkPageBreak(summaryLines.length * 4.8);
    doc.text(summaryLines, margin, y);
    y += summaryLines.length * 4.6 + 2;

    // 2. Work Experience
    if (data.experiences.length > 0) {
        addSectionHeading('Work Experience');
        data.experiences.forEach(exp => {
            checkPageBreak(18);
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(10.2);
            doc.setTextColor(15, 23, 42);
            const roleHeader = `${exp.role} | ${exp.company}`;
            doc.text(roleHeader, margin, y);

            const dateStr = `${exp.start} - ${exp.end}`;
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(9.5);
            doc.setTextColor(...pal.accent);
            doc.text(dateStr, pageW - margin, y, { align: 'right' });
            y += 5;

            if (exp.duties) {
                doc.setFont('helvetica', 'normal');
                doc.setFontSize(9.5);
                doc.setTextColor(30, 41, 59);
                const dutyLines = doc.splitTextToSize(exp.duties, contentW);
                dutyLines.forEach(line => {
                    checkPageBreak(6);
                    doc.text(line, margin, y);
                    y += 4.5;
                });
            }
            y += 3;
        });
    }

    // 3. Education & Qualifications
    if (data.educations.length > 0) {
        addSectionHeading('Education & Qualifications');
        data.educations.forEach(edu => {
            checkPageBreak(10);
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(9.8);
            doc.setTextColor(15, 23, 42);
            doc.text(`${edu.degree} — ${edu.school}`, margin, y);

            if (edu.year) {
                doc.setFont('helvetica', 'normal');
                doc.setFontSize(9.2);
                doc.setTextColor(71, 85, 105);
                doc.text(edu.year, pageW - margin, y, { align: 'right' });
            }
            y += 5.5;
        });
    }

    // 4. Certifications, Licenses & Projects
    if (data.certs) {
        addSectionHeading('Certifications, Licenses & Key Projects');
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(9.5);
        doc.setTextColor(30, 41, 59);
        const certLines = doc.splitTextToSize(data.certs, contentW);
        certLines.forEach(line => {
            checkPageBreak(6);
            doc.text(line, margin, y);
            y += 4.5;
        });
        y += 2;
    }

    // 5. Core Skills
    addSectionHeading('Core Competencies & Technical Skills');
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(9.6);
    doc.setTextColor(15, 23, 42);
    const skillLines = doc.splitTextToSize(data.skills, contentW);
    checkPageBreak(skillLines.length * 4.8);
    doc.text(skillLines, margin, y);

    // Prepare Filename
    const rawName = document.getElementById('cv-name').value.trim() || 'Candidate';
    const safeName = rawName.replace(/[^a-z0-9]+/gi, '_').replace(/^_|_$/g, '').toLowerCase();
    const defaultFilename = `cv_${safeName}_${currentTemplate}.pdf`;

    const pdfBlob = doc.output('blob');

    // Use native Save As File Picker when supported so user can choose exact destination folder
    if (window.showSaveFilePicker) {
        try {
            const handle = await window.showSaveFilePicker({
                suggestedName: defaultFilename,
                types: [{
                    description: 'PDF Document',
                    accept: { 'application/pdf': ['.pdf'] }
                }]
            });
            const writable = await handle.createWritable();
            await writable.write(pdfBlob);
            await writable.close();
            showCompletionModal(handle.name || defaultFilename);
            return;
        } catch (err) {
            if (err.name === 'AbortError') return; // User cancelled save dialog
        }
    }

    // Standard browser download fallback
    doc.save(defaultFilename);
    showCompletionModal(defaultFilename);
}

function showCompletionModal(filename) {
    document.getElementById('saved-filename-display').textContent = filename;
    document.getElementById('cv-completed-modal').style.display = 'flex';
}

function closeCompletionModal() {
    document.getElementById('cv-completed-modal').style.display = 'none';
}

// Initialize builder with 1 starter experience and 1 starter education row
window.addEventListener('DOMContentLoaded', () => {
    addExperienceItem(defaultExperiences[0]);
    addEducationItem(defaultEducations[0]);
    renderLiveCV();
});
</script>

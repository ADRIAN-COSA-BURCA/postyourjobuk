/**
 * PostYourJobUK — 2-Stage ATS Resume Builder (6 Modern Templates + 5-Step Wizard + True Styled Vector PDF-1.4 Writer)
 * Zero External CDNs | 100% PyPDF2 Grounding Gate Compatible | Matches Live Preview Colors & Formatting
 */
(function (window) {
    'use strict';

    const { escapeHtml, showToast } = window.AppApi;
    let chosenTemplate = null;
    let currentTemplate = 'professional';

    const TEMPLATES = {
        professional: {
            title: 'The Professional (ATS Classic)',
            badge: 'ATS Classic',
            badgeStyle: 'background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3);',
            desc: 'Clean, traditional chronological structure favored by UK corporate, finance, healthcare, and enterprise hiring managers.',
            primaryRgb: [0.06, 0.09, 0.16],   // #0f172a
            accentRgb:  [0.20, 0.25, 0.33],   // #334155
            banner: false,
            centered: false,
            topBarRgb: [0.12, 0.16, 0.23]
        },
        modern: {
            title: 'The Modern Accent',
            badge: 'Popular',
            badgeStyle: 'background: rgba(139, 92, 246, 0.18); color: #c4b5fd; border: 1px solid rgba(139, 92, 246, 0.35);',
            desc: 'Contemporary royal-violet header banner with crisp timeline accents and vibrant section dividers that stand out visually.',
            primaryRgb: [0.43, 0.16, 0.85],   // #6d28d9
            accentRgb:  [0.31, 0.27, 0.90],   // #4f46e5
            banner: true,
            centered: false
        },
        executive: {
            title: 'The Executive Minimalist',
            badge: 'Leadership',
            badgeStyle: 'background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.35);',
            desc: 'Refined centered typography with authoritative dark-slate framing. Designed for senior management, directors, and consultancy.',
            primaryRgb: [0.06, 0.09, 0.16],   // #0f172a
            accentRgb:  [0.28, 0.33, 0.41],   // #475569
            banner: false,
            centered: true,
            topBarRgb: [0.06, 0.09, 0.16]
        },
        technical: {
            title: 'The Technical Specialist',
            badge: 'AI-Score Max',
            badgeStyle: 'background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35);',
            desc: 'Engineered specifically for technical, software, IT, and engineering roles with high-density evidence & license blocks.',
            primaryRgb: [0.02, 0.37, 0.27],   // #065f46
            accentRgb:  [0.02, 0.59, 0.41],   // #059669
            banner: false,
            centered: false,
            leftBarRgb: [0.02, 0.59, 0.41]
        },
        creative: {
            title: 'The Creative Studio',
            badge: 'Modern Rose',
            badgeStyle: 'background: rgba(244, 63, 94, 0.16); color: #fb7185; border: 1px solid rgba(244, 63, 94, 0.35);',
            desc: 'Bold crimson-rose header banner and expressive section hierarchy tailored for product design, marketing, and digital media.',
            primaryRgb: [0.75, 0.07, 0.24],   // #be123c
            accentRgb:  [0.88, 0.11, 0.28],   // #e11d48
            banner: true,
            centered: false
        },
        academic: {
            title: 'The Academic & Clinical',
            badge: 'Sapphire Pro',
            badgeStyle: 'background: rgba(56, 189, 248, 0.16); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.35);',
            desc: 'Structured sapphire-blue banner and crisp double-ruled sections ideal for NHS clinical, research, nursing, and education roles.',
            primaryRgb: [0.01, 0.41, 0.63],   // #0369a1
            accentRgb:  [0.01, 0.52, 0.78],   // #0284c7
            banner: true,
            centered: false
        }
    };

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

    function escapeAttr(str) {
        return escapeHtml(str).replace(/"/g, '&quot;');
    }

    /**
     * STEP 1 VIEW: 6-Template Visual Selector Gallery (/resume-builder)
     */
    function renderGalleryView(host) {
        document.title = 'Select CV Template (Step 1 of 2) — PostYourJobUK';
        chosenTemplate = null;

        host.innerHTML = `
            <div class="container" style="margin-top: 2.5rem; margin-bottom: 4rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
                    <a href="/" class="btn btn-ghost btn-sm">← Back to Job Opportunities</a>
                    <span style="font-size: 0.82rem; color: var(--text-muted); background: rgba(255,255,255,0.04); padding: 0.35rem 0.85rem; border-radius: 999px; border: 1px solid var(--border);">
                        🔒 100% Private Client-Side Generation • Zero Server Storage
                    </span>
                </div>

                <div class="card" style="text-align: center; padding: 2.5rem 2rem; margin-bottom: 2.25rem; background: linear-gradient(135deg, rgba(30, 27, 75, 0.75), rgba(17, 24, 39, 0.9)); border: 1px solid rgba(139, 92, 246, 0.35);">
                    <span style="display: inline-block; background: rgba(139, 92, 246, 0.18); color: #c4b5fd; border: 1px solid rgba(139, 92, 246, 0.35); padding: 0.3rem 0.9rem; border-radius: 999px; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 0.85rem;">
                        Step 1 of 2 • Choose Your Layout
                    </span>
                    <h1 style="font-size: clamp(1.75rem, 3.5vw, 2.4rem); margin-bottom: 0.75rem; color: #ffffff;">
                        Select One of 6 Modern ATS-Ready CV Templates
                    </h1>
                    <p style="color: var(--text-muted); max-width: 680px; margin: 0 auto; font-size: 0.96rem; line-height: 1.6;">
                        Click on any of the 6 templates below to select your preferred style. Once selected, click <strong>"Start Building My CV"</strong> to complete your step-by-step profile and download a styled, searchable vector PDF.
                    </p>
                </div>

                <div class="resume-gallery-grid">
                    <!-- 1. Professional -->
                    <div class="template-card" data-template="professional" data-title="The Professional (ATS Classic)">
                        <span class="selected-pill">✓ Selected</span>
                        <div>
                            <div class="mini-a4-preview" style="border-top: 4px solid #1e293b;">
                                <div style="border-bottom: 1.5px solid #0f172a; padding-bottom: 6px; margin-bottom: 8px;">
                                    <div style="font-size: 11px; font-weight: 800; color: #0f172a;">ALEXANDER WRIGHT</div>
                                    <div style="font-size: 6.5px; color: #475569; margin-top: 2px;">London, UK • alex@email.co.uk • +44 7700 900123</div>
                                </div>
                                <div class="mini-sec-head" style="border-bottom: 1px solid #cbd5e1; padding-bottom: 2px;">Professional Summary</div>
                                <div class="mini-line full"></div><div class="mini-line med"></div>
                                <div class="mini-sec-head" style="border-bottom: 1px solid #cbd5e1; padding-bottom: 2px;">Work Experience</div>
                                <div style="display: flex; justify-content: space-between; font-size: 6.5px; font-weight: 700; color: #1e293b; margin-bottom: 3px;"><span>Senior Software Engineer — FinTech UK</span><span>2021 – Present</span></div>
                                <div class="mini-line full"></div><div class="mini-line full"></div><div class="mini-line short"></div>
                                <div class="mini-sec-head" style="border-bottom: 1px solid #cbd5e1; padding-bottom: 2px;">Education &amp; Certifications</div>
                                <div class="mini-line med"></div><div class="mini-line short"></div>
                            </div>
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.4rem;">
                                <h3 style="font-size: 1.15rem; color: #ffffff; margin: 0;">The Professional</h3>
                                <span class="badge" style="${TEMPLATES.professional.badgeStyle} font-size: 0.7rem;">${TEMPLATES.professional.badge}</span>
                            </div>
                            <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5; margin-bottom: 1.25rem;">${TEMPLATES.professional.desc}</p>
                        </div>
                        <button type="button" class="btn btn-ghost w-100 template-select-btn" style="width: 100%; border-color: rgba(255,255,255,0.2); font-size: 0.85rem;">Select "The Professional"</button>
                    </div>

                    <!-- 2. Modern -->
                    <div class="template-card" data-template="modern" data-title="The Modern Accent">
                        <span class="selected-pill">✓ Selected</span>
                        <div>
                            <div class="mini-a4-preview" style="padding: 0;">
                                <div style="background: linear-gradient(135deg, #7c3aed, #4f46e5); color: #ffffff; padding: 12px 14px;">
                                    <div style="font-size: 11px; font-weight: 800;">SOPHIA CHEN</div>
                                    <div style="font-size: 6.5px; opacity: 0.9; margin-top: 2px;">Product &amp; Cloud Architect • Manchester • sophia@domain.uk</div>
                                </div>
                                <div style="padding: 10px 14px;">
                                    <div class="mini-sec-head" style="color: #7c3aed; margin-top: 2px;">Career Profile</div>
                                    <div class="mini-line full"></div><div class="mini-line med"></div>
                                    <div class="mini-sec-head" style="color: #7c3aed;">Experience &amp; Impact</div>
                                    <div style="border-left: 2px solid #8b5cf6; padding-left: 6px; margin-bottom: 6px;">
                                        <div style="font-size: 6.5px; font-weight: 700; color: #1e293b; margin-bottom: 2px;">Lead Platform Engineer (2022 – Present)</div>
                                        <div class="mini-line full"></div><div class="mini-line med"></div>
                                    </div>
                                    <div class="mini-sec-head" style="color: #7c3aed;">Verified Skills</div>
                                    <div style="display: flex; gap: 3px; flex-wrap: wrap;">
                                        <span style="background: #ede9fe; color: #6d28d9; font-size: 5.5px; font-weight: 700; padding: 2px 5px; border-radius: 3px;">Docker</span>
                                        <span style="background: #ede9fe; color: #6d28d9; font-size: 5.5px; font-weight: 700; padding: 2px 5px; border-radius: 3px;">PHP MVC</span>
                                        <span style="background: #ede9fe; color: #6d28d9; font-size: 5.5px; font-weight: 700; padding: 2px 5px; border-radius: 3px;">MySQL</span>
                                    </div>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.4rem;">
                                <h3 style="font-size: 1.15rem; color: #ffffff; margin: 0;">The Modern</h3>
                                <span class="badge" style="${TEMPLATES.modern.badgeStyle} font-size: 0.7rem;">${TEMPLATES.modern.badge}</span>
                            </div>
                            <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5; margin-bottom: 1.25rem;">${TEMPLATES.modern.desc}</p>
                        </div>
                        <button type="button" class="btn btn-ghost w-100 template-select-btn" style="width: 100%; border-color: rgba(255,255,255,0.2); font-size: 0.85rem;">Select "The Modern"</button>
                    </div>

                    <!-- 3. Executive -->
                    <div class="template-card" data-template="executive" data-title="The Executive Minimalist">
                        <span class="selected-pill">✓ Selected</span>
                        <div>
                            <div class="mini-a4-preview" style="border-top: 8px solid #0f172a;">
                                <div style="text-align: center; border-bottom: 1px solid #cbd5e1; padding-bottom: 7px; margin-bottom: 8px;">
                                    <div style="font-size: 11px; font-weight: 800; letter-spacing: 0.12em; color: #0f172a;">ELEANOR VANCE</div>
                                    <div style="font-size: 6px; color: #64748b; margin-top: 2px; text-transform: uppercase;">Operations Director • Edinburgh • +44 7911 123456</div>
                                </div>
                                <div class="mini-sec-head" style="text-align: center; color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 2px;">Executive Summary</div>
                                <div class="mini-line full" style="margin: 0 auto 4px auto;"></div><div class="mini-line med" style="margin: 0 auto 6px auto;"></div>
                                <div class="mini-sec-head" style="text-align: center; color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 2px;">Leadership Experience</div>
                                <div style="display: flex; justify-content: space-between; font-size: 6.5px; font-weight: 700; color: #0f172a; margin-bottom: 3px;"><span>Head of Operations — Sterling Group</span><span>2020 – Present</span></div>
                                <div class="mini-line full"></div><div class="mini-line med"></div>
                            </div>
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.4rem;">
                                <h3 style="font-size: 1.15rem; color: #ffffff; margin: 0;">The Executive</h3>
                                <span class="badge" style="${TEMPLATES.executive.badgeStyle} font-size: 0.7rem;">${TEMPLATES.executive.badge}</span>
                            </div>
                            <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5; margin-bottom: 1.25rem;">${TEMPLATES.executive.desc}</p>
                        </div>
                        <button type="button" class="btn btn-ghost w-100 template-select-btn" style="width: 100%; border-color: rgba(255,255,255,0.2); font-size: 0.85rem;">Select "The Executive"</button>
                    </div>

                    <!-- 4. Technical -->
                    <div class="template-card" data-template="technical" data-title="The Technical Specialist">
                        <span class="selected-pill">✓ Selected</span>
                        <div>
                            <div class="mini-a4-preview" style="border-left: 6px solid #059669;">
                                <div style="display: flex; justify-content: space-between; border-bottom: 1.5px solid #059669; padding-bottom: 6px; margin-bottom: 6px;">
                                    <div>
                                        <div style="font-size: 10.5px; font-weight: 800; color: #064e3b;">MARCUS REID</div>
                                        <div style="font-size: 6px; color: #059669; font-weight: 700;">DevOps &amp; Backend Specialist</div>
                                    </div>
                                    <div style="font-size: 5.5px; color: #475569; text-align: right;">github.com/mreid<br>Bristol, UK</div>
                                </div>
                                <div class="mini-sec-head" style="color: #065f46; margin-top: 4px;">Core Technical Stack</div>
                                <div style="background: #ecfdf5; border: 1px solid #a7f3d0; padding: 4px; border-radius: 4px; font-size: 5.5px; color: #065f46; font-family: monospace; margin-bottom: 6px;">PHP 8.2 • MySQL • Docker • Azure • CI/CD</div>
                                <div class="mini-sec-head" style="color: #065f46;">Engineering Experience</div>
                                <div style="font-size: 6.5px; font-weight: 700; color: #0f172a; margin-bottom: 2px;">Platform Engineer | CloudScale (2021 – 2026)</div>
                                <div class="mini-line full"></div><div class="mini-line med"></div>
                            </div>
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.4rem;">
                                <h3 style="font-size: 1.15rem; color: #ffffff; margin: 0;">The Technical</h3>
                                <span class="badge" style="${TEMPLATES.technical.badgeStyle} font-size: 0.7rem;">${TEMPLATES.technical.badge}</span>
                            </div>
                            <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5; margin-bottom: 1.25rem;">${TEMPLATES.technical.desc}</p>
                        </div>
                        <button type="button" class="btn btn-ghost w-100 template-select-btn" style="width: 100%; border-color: rgba(255,255,255,0.2); font-size: 0.85rem;">Select "The Technical"</button>
                    </div>

                    <!-- 5. Creative Studio (NEW) -->
                    <div class="template-card" data-template="creative" data-title="The Creative Studio">
                        <span class="selected-pill">✓ Selected</span>
                        <div>
                            <div class="mini-a4-preview" style="padding: 0;">
                                <div style="background: linear-gradient(135deg, #e11d48, #be123c); color: #ffffff; padding: 12px 14px;">
                                    <div style="font-size: 11px; font-weight: 800; letter-spacing: 0.03em;">OLIVIA BENNETT</div>
                                    <div style="font-size: 6.5px; opacity: 0.92; margin-top: 2px;">Lead UX &amp; Product Strategist • London • olivia@design.uk</div>
                                </div>
                                <div style="padding: 10px 14px;">
                                    <div class="mini-sec-head" style="color: #be123c; border-bottom: 1px solid #fecdd3; padding-bottom: 2px;">Creative &amp; Product Profile</div>
                                    <div class="mini-line full"></div><div class="mini-line med"></div>
                                    <div class="mini-sec-head" style="color: #be123c; border-bottom: 1px solid #fecdd3; padding-bottom: 2px;">Selected Career Highlights</div>
                                    <div style="font-size: 6.5px; font-weight: 700; color: #0f172a; margin-bottom: 2px;">Principal Product Designer — StudioNova (2022 – Present)</div>
                                    <div class="mini-line full"></div><div class="mini-line short"></div>
                                    <div class="mini-sec-head" style="color: #be123c;">Core Capabilities</div>
                                    <div style="display: flex; gap: 3px; flex-wrap: wrap;">
                                        <span style="background: #ffe4e6; color: #be123c; font-size: 5.5px; font-weight: 700; padding: 2px 5px; border-radius: 3px;">Design Systems</span>
                                        <span style="background: #ffe4e6; color: #be123c; font-size: 5.5px; font-weight: 700; padding: 2px 5px; border-radius: 3px;">WCAG 2.1 AA</span>
                                        <span style="background: #ffe4e6; color: #be123c; font-size: 5.5px; font-weight: 700; padding: 2px 5px; border-radius: 3px;">Frontend UI</span>
                                    </div>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.4rem;">
                                <h3 style="font-size: 1.15rem; color: #ffffff; margin: 0;">The Creative</h3>
                                <span class="badge" style="${TEMPLATES.creative.badgeStyle} font-size: 0.7rem;">${TEMPLATES.creative.badge}</span>
                            </div>
                            <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5; margin-bottom: 1.25rem;">${TEMPLATES.creative.desc}</p>
                        </div>
                        <button type="button" class="btn btn-ghost w-100 template-select-btn" style="width: 100%; border-color: rgba(255,255,255,0.2); font-size: 0.85rem;">Select "The Creative"</button>
                    </div>

                    <!-- 6. Academic & Clinical (NEW) -->
                    <div class="template-card" data-template="academic" data-title="The Academic & Clinical">
                        <span class="selected-pill">✓ Selected</span>
                        <div>
                            <div class="mini-a4-preview" style="padding: 0;">
                                <div style="background: linear-gradient(135deg, #0284c7, #0369a1); color: #ffffff; padding: 12px 14px;">
                                    <div style="font-size: 11px; font-weight: 800; letter-spacing: 0.03em;">DR. HANNAH PATEL</div>
                                    <div style="font-size: 6.5px; opacity: 0.92; margin-top: 2px;">Advanced Clinical Practitioner • Leeds • h.patel@nhs.uk</div>
                                </div>
                                <div style="padding: 10px 14px;">
                                    <div class="mini-sec-head" style="color: #0369a1; border-bottom: 1px solid #bae6fd; padding-bottom: 2px;">Clinical &amp; Academic Summary</div>
                                    <div class="mini-line full"></div><div class="mini-line med"></div>
                                    <div class="mini-sec-head" style="color: #0369a1; border-bottom: 1px solid #bae6fd; padding-bottom: 2px;">Appointments &amp; Practice</div>
                                    <div style="font-size: 6.5px; font-weight: 700; color: #0f172a; margin-bottom: 2px;">Lead Nurse Practitioner — NHS Trust (2021 – Present)</div>
                                    <div class="mini-line full"></div><div class="mini-line med"></div>
                                    <div class="mini-sec-head" style="color: #0369a1; border-bottom: 1px solid #bae6fd; padding-bottom: 2px;">Registrations &amp; Qualifications</div>
                                    <div class="mini-line med"></div><div class="mini-line short"></div>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.4rem;">
                                <h3 style="font-size: 1.15rem; color: #ffffff; margin: 0;">The Academic &amp; Clinical</h3>
                                <span class="badge" style="${TEMPLATES.academic.badgeStyle} font-size: 0.7rem;">${TEMPLATES.academic.badge}</span>
                            </div>
                            <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5; margin-bottom: 1.25rem;">${TEMPLATES.academic.desc}</p>
                        </div>
                        <button type="button" class="btn btn-ghost w-100 template-select-btn" style="width: 100%; border-color: rgba(255,255,255,0.2); font-size: 0.85rem;">Select "The Academic &amp; Clinical"</button>
                    </div>
                </div>

                <div class="start-build-bar" id="start-build-bar">
                    <div>
                        <div style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); font-weight: 700;">
                            Current Selection
                        </div>
                        <div id="selected-template-label" style="font-size: 1.2rem; font-weight: 800; color: #ffffff; margin-top: 0.2rem;">
                            👆 Please click one of the 6 templates above to activate the builder
                        </div>
                    </div>
                    <div>
                        <a href="#" id="start-build-btn" class="btn-start-build">
                            🚀 Start Building My CV →
                        </a>
                    </div>
                </div>
            </div>
        `;

        host.querySelectorAll('.template-card').forEach(cardEl => {
            cardEl.addEventListener('click', () => {
                host.querySelectorAll('.template-card').forEach(c => {
                    c.classList.remove('selected');
                    const btn = c.querySelector('.template-select-btn');
                    const title = c.getAttribute('data-title').split(' (')[0];
                    if (btn) {
                        btn.textContent = 'Select "' + title + '"';
                        btn.className = 'btn btn-ghost w-100 template-select-btn';
                    }
                });

                cardEl.classList.add('selected');
                chosenTemplate = cardEl.getAttribute('data-template');
                const chosenTitle = cardEl.getAttribute('data-title');

                const activeBtn = cardEl.querySelector('.template-select-btn');
                if (activeBtn) {
                    activeBtn.textContent = '✓ Selected — Ready to Build';
                    activeBtn.className = 'btn btn-success w-100 template-select-btn';
                }

                const labelEl = document.getElementById('selected-template-label');
                labelEl.innerHTML = '✅ Selected: <span style="color: #34d399;">' + escapeHtml(chosenTitle) + '</span>';

                const startBtn = document.getElementById('start-build-btn');
                startBtn.classList.add('active');
                startBtn.href = '/resume-builder/editor?template=' + encodeURIComponent(chosenTemplate);
            });
        });

        const startBtn = document.getElementById('start-build-btn');
        startBtn.addEventListener('click', (e) => {
            if (!chosenTemplate) {
                e.preventDefault();
                const bar = document.getElementById('start-build-bar');
                bar.style.borderColor = '#ef4444';
                showToast('Please select one of the 6 CV templates first.', 'warning');
                setTimeout(() => { bar.style.borderColor = 'rgba(139, 92, 246, 0.4)'; }, 800);
            }
        });
    }

    /**
     * STEP 2 VIEW: 5-Step Guided CV Editor + Live A4 Preview (/resume-builder/editor?template=...)
     */
    function renderEditorView(host, initialTemplate) {
        currentTemplate = TEMPLATES[initialTemplate] ? initialTemplate : 'professional';
        document.title = `Editing CV (${TEMPLATES[currentTemplate].title}) — PostYourJobUK`;

        host.innerHTML = `
            <div class="container" style="max-width: 1360px; margin-top: 2rem; margin-bottom: 4rem;">
                <!-- Top Workspace Bar -->
                <div class="card" style="padding: 1.1rem 1.5rem; margin-bottom: 1.75rem; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;">
                    <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                        <a href="/resume-builder" class="btn btn-ghost btn-sm">← Change Template (6 Styles)</a>
                        <div>
                            <h2 style="font-size: 1.25rem; margin: 0; color: #ffffff;">🛠️ Interactive Resume Builder</h2>
                            <span style="font-size: 0.78rem; color: var(--text-muted);">Complete the 5 steps below • Live A4 preview &amp; styled PDF update as you type</span>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                        <label for="live-template-switcher" style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; margin: 0;">Active Style:</label>
                        <select id="live-template-switcher" style="width: auto; padding: 0.45rem 2rem 0.45rem 0.75rem; font-size: 0.85rem;">
                            ${Object.keys(TEMPLATES).map(k => `
                                <option value="${k}" ${currentTemplate === k ? 'selected' : ''}>${escapeHtml(TEMPLATES[k].title)}</option>
                            `).join('')}
                        </select>

                        <button type="button" id="top-save-pdf-btn" class="btn btn-success btn-sm" style="padding: 0.55rem 1.25rem; font-size: 0.85rem;">
                            💾 Complete &amp; Save PDF
                        </button>
                    </div>
                </div>

                <div class="builder-layout">
                    <!-- LEFT COLUMN: 5-STEP GUIDED FORM -->
                    <div class="card" style="padding: 1.75rem;">
                        <div class="step-tabs">
                            <button type="button" class="step-tab-btn active" data-step="1">1. Personal</button>
                            <button type="button" class="step-tab-btn" data-step="2">2. Summary</button>
                            <button type="button" class="step-tab-btn" data-step="3">3. Experience</button>
                            <button type="button" class="step-tab-btn" data-step="4">4. Education &amp; Certs</button>
                            <button type="button" class="step-tab-btn" data-step="5">5. Skills &amp; Save</button>
                        </div>

                        <!-- STEP 1 -->
                        <div class="wizard-step active" id="step-1">
                            <h3 style="margin-bottom: 0.35rem; color: #ffffff;">Step 1: Personal &amp; Contact Details</h3>
                            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.25rem;">
                                Enter your contact information so recruiters can reach you. (Note: Our AI strips personal identifiers during scoring to guarantee 100% fairness).
                            </p>
                            <div class="grid-2">
                                <div class="form-group"><label class="form-label">Full Name *</label><input type="text" id="cv-name" placeholder="e.g. Alex Taylor" value="Alex Sterling"></div>
                                <div class="form-group"><label class="form-label">Target Role / Professional Headline *</label><input type="text" id="cv-headline" placeholder="e.g. Full-Stack Software Engineer" value="Full-Stack Software Engineer"></div>
                            </div>
                            <div class="grid-2">
                                <div class="form-group"><label class="form-label">Email Address *</label><input type="email" id="cv-email" placeholder="alex.taylor@email.co.uk" value="alex.sterling@example.co.uk"></div>
                                <div class="form-group"><label class="form-label">Phone Number *</label><input type="text" id="cv-phone" placeholder="+44 7700 900123" value="+44 7700 900123"></div>
                            </div>
                            <div class="grid-2">
                                <div class="form-group"><label class="form-label">City / Location (UK)</label><input type="text" id="cv-location" placeholder="e.g. London, UK" value="London, UK"></div>
                                <div class="form-group"><label class="form-label">LinkedIn / Portfolio URL (Optional)</label><input type="text" id="cv-website" placeholder="linkedin.com/in/alextaylor"></div>
                            </div>
                            <div style="display: flex; justify-content: flex-end; margin-top: 1rem;">
                                <button type="button" class="btn btn-primary js-goto-step" data-target="2">Next: Professional Summary →</button>
                            </div>
                        </div>

                        <!-- STEP 2 -->
                        <div class="wizard-step" id="step-2">
                            <h3 style="margin-bottom: 0.35rem; color: #ffffff;">Step 2: Professional Summary</h3>
                            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;">
                                Write 2–4 concise sentences summarizing your domain expertise, years of practice, and core strengths.
                            </p>
                            <div class="form-group">
                                <label class="form-label">Career Summary Statement</label>
                                <textarea id="cv-summary" rows="5">Full-Stack Engineer with 5+ years of production experience building cloud-native PHP MVC, Python serverless microservices, MySQL databases, and accessible web applications.</textarea>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-top: 1.25rem;">
                                <button type="button" class="btn btn-ghost js-goto-step" data-target="1">← Previous</button>
                                <button type="button" class="btn btn-primary js-goto-step" data-target="3">Next: Work Experience →</button>
                            </div>
                        </div>

                        <!-- STEP 3 -->
                        <div class="wizard-step" id="step-3">
                            <h3 style="margin-bottom: 0.35rem; color: #ffffff;">Step 3: Work Experience &amp; Evidence</h3>
                            <div class="ai-tip-banner">
                                💡 <strong>AI Scoring Tip:</strong> Our evaluation engine calculates your years of experience from your <strong>Start Year – End Year</strong> (e.g. <code>2021</code> to <code>Present</code>) and verifies required skills only when they are demonstrated in action inside your role bullet points!
                            </div>
                            <div id="experience-list"></div>
                            <button type="button" id="add-exp-btn" class="btn btn-ghost btn-sm w-100" style="width: 100%; margin-bottom: 1.5rem; border-style: dashed; border-color: rgba(139, 92, 246, 0.5); color: #c4b5fd;">
                                + Add Another Work Experience Role
                            </button>
                            <div style="display: flex; justify-content: space-between;">
                                <button type="button" class="btn btn-ghost js-goto-step" data-target="2">← Previous</button>
                                <button type="button" class="btn btn-primary js-goto-step" data-target="4">Next: Education &amp; Certifications →</button>
                            </div>
                        </div>

                        <!-- STEP 4 -->
                        <div class="wizard-step" id="step-4">
                            <h3 style="margin-bottom: 0.35rem; color: #ffffff;">Step 4: Education, Licenses &amp; Certifications</h3>
                            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;">
                                Include your academic qualifications and any active professional licenses or statutory certifications.
                            </p>
                            <div id="education-list"></div>
                            <button type="button" id="add-edu-btn" class="btn btn-ghost btn-sm w-100" style="width: 100%; margin-bottom: 1.25rem; border-style: dashed; border-color: rgba(139, 92, 246, 0.5); color: #c4b5fd;">
                                + Add Another Qualification / Degree
                            </button>
                            <div class="form-group" style="margin-top: 0.5rem;">
                                <label class="form-label">Active Certifications, Licenses &amp; Projects (One per line)</label>
                                <textarea id="cv-certs" rows="4" placeholder="• Active AWS Certified Solutions Architect (Current)&#10;• Full UK Driving Licence (Active)"></textarea>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-top: 1.25rem;">
                                <button type="button" class="btn btn-ghost js-goto-step" data-target="3">← Previous</button>
                                <button type="button" class="btn btn-primary js-goto-step" data-target="5">Next: Core Skills &amp; Finish →</button>
                            </div>
                        </div>

                        <!-- STEP 5 -->
                        <div class="wizard-step" id="step-5">
                            <h3 style="margin-bottom: 0.35rem; color: #ffffff;">Step 5: Core Skills &amp; Save Your CV</h3>
                            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.25rem;">
                                List your technical, clinical, or operational skills separated by commas.
                            </p>
                            <div class="form-group">
                                <label class="form-label">Key Competencies &amp; Technical Skills (Comma Separated)</label>
                                <textarea id="cv-skills" rows="3">PHP MVC, MySQL, JavaScript, Docker, REST API Integration, Git, Agile Delivery</textarea>
                            </div>

                            <div style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.14), rgba(139, 92, 246, 0.12)); border: 1px solid rgba(16, 185, 129, 0.4); border-radius: 12px; padding: 1.5rem; text-align: center; margin: 1.5rem 0;">
                                <div style="font-size: 2rem; margin-bottom: 0.4rem;">🎯</div>
                                <h4 style="color: #ffffff; margin-bottom: 0.4rem;">Ready to Generate Your Styled PDF Resume?</h4>
                                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.25rem;">
                                    Compiles a full-color, ATS-searchable vector PDF matching your chosen template style.
                                </p>
                                <button type="button" id="bottom-save-pdf-btn" class="btn btn-success" style="padding: 0.9rem 2.2rem; font-size: 1rem; font-weight: 700;">
                                    💾 Complete &amp; Save CV (PDF)
                                </button>
                            </div>

                            <div style="display: flex; justify-content: flex-start;">
                                <button type="button" class="btn btn-ghost js-goto-step" data-target="4">← Back to Education &amp; Certs</button>
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
                                Template: ${escapeHtml(TEMPLATES[currentTemplate].title)}
                            </span>
                        </div>

                        <div id="cv-document" class="tpl-${currentTemplate}">
                            <div class="cv-doc-header">
                                <h1 id="pv-name" style="margin: 0; font-size: 22px; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 0.02em;">ALEX STERLING</h1>
                                <div id="pv-headline" style="font-size: 13px; font-weight: 700; color: #475569; margin-top: 2px;">Full-Stack Software Engineer</div>
                                <div id="pv-contact" style="font-size: 11px; color: #64748b; margin-top: 5px;">alex.sterling@example.co.uk • +44 7700 900123 • London, UK</div>
                            </div>

                            <div class="cv-doc-body">
                                <div class="cv-sec-title">Professional Summary</div>
                                <div id="pv-summary" style="font-size: 11.5px; color: #1e293b; margin-bottom: 12px; white-space: pre-wrap;"></div>

                                <div class="cv-sec-title">Work Experience</div>
                                <div id="pv-experience"></div>

                                <div class="cv-sec-title">Education &amp; Qualifications</div>
                                <div id="pv-education"></div>

                                <div id="pv-certs-block" style="display: none;">
                                    <div class="cv-sec-title">Certifications, Licenses &amp; Key Projects</div>
                                    <div id="pv-certs" style="font-size: 11.5px; color: #1e293b; margin-bottom: 12px; white-space: pre-wrap;"></div>
                                </div>

                                <div class="cv-sec-title">Core Competencies &amp; Technical Skills</div>
                                <div id="pv-skills" style="font-size: 11.5px; color: #0f172a; font-weight: 600;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- COMPLETION MODAL -->
            <div id="cv-completed-modal" class="modal-backdrop" role="dialog" aria-modal="true" aria-hidden="true">
                <div class="modal-dialog" style="max-width: 520px; text-align: center; border-color: rgba(16, 185, 129, 0.5);">
                    <div style="width: 64px; height: 64px; border-radius: 50%; background: rgba(16, 185, 129, 0.15); border: 2px solid #10b981; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 1.25rem auto;">✅</div>
                    <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35); margin-bottom: 0.75rem; display: inline-block;">Resume Completed &amp; Saved</span>
                    <h3 style="color: #ffffff; font-size: 1.5rem; margin-bottom: 0.6rem;">Your Styled ATS-Ready CV is Ready!</h3>
                    <p style="color: var(--text-muted); font-size: 0.92rem; line-height: 1.6; margin-bottom: 1.25rem;">
                        Your PDF resume (<strong id="saved-filename-display" style="color: #f3f4f6;">resume.pdf</strong>) has been generated with full template styling and a native searchable vector text layer.
                    </p>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <a href="/" class="btn btn-primary w-100" style="width: 100%; padding: 0.85rem; font-weight: 700;">🧭 Browse Open Jobs &amp; Apply Now →</a>
                        <button type="button" class="btn btn-ghost w-100" style="width: 100%;" onclick="window.AppA11y.closeModal('cv-completed-modal')">✏️ Continue Editing Resume</button>
                    </div>
                </div>
            </div>
        `;

        function goToStep(stepNum) {
            host.querySelectorAll('.wizard-step').forEach(el => el.classList.remove('active'));
            const target = document.getElementById('step-' + stepNum);
            if (target) target.classList.add('active');

            host.querySelectorAll('.step-tab-btn').forEach(btn => {
                const s = parseInt(btn.getAttribute('data-step'), 10);
                btn.classList.remove('active');
                if (s < stepNum) btn.classList.add('completed');
                else if (s === stepNum) btn.classList.add('active');
            });
        }

        host.querySelectorAll('.step-tab-btn').forEach(btn => {
            btn.addEventListener('click', () => goToStep(parseInt(btn.getAttribute('data-step'), 10)));
        });
        host.querySelectorAll('.js-goto-step').forEach(btn => {
            btn.addEventListener('click', () => goToStep(parseInt(btn.getAttribute('data-target'), 10)));
        });

        document.getElementById('live-template-switcher').addEventListener('change', (e) => {
            currentTemplate = e.target.value;
            document.getElementById('cv-document').className = 'tpl-' + currentTemplate;
            document.getElementById('preview-template-badge').textContent = 'Template: ' + TEMPLATES[currentTemplate].title;
        });

        function addExperienceItem(data = null) {
            const container = document.getElementById('experience-list');
            const idx = container.children.length;
            const box = document.createElement('div');
            box.className = 'repeater-box exp-item';
            box.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <strong style="font-size: 0.85rem; color: #c4b5fd;">Role #${idx + 1}</strong>
                    ${idx > 0 ? `<button type="button" class="js-remove-item" style="background: none; border: none; color: #ef4444; font-size: 0.8rem; cursor: pointer; font-weight: 600;">✕ Remove</button>` : ''}
                </div>
                <div class="grid-2">
                    <div class="form-group"><label class="form-label">Job Title *</label><input type="text" class="exp-role" value="${escapeAttr(data ? data.role : '')}"></div>
                    <div class="form-group"><label class="form-label">Company / Organisation *</label><input type="text" class="exp-company" value="${escapeAttr(data ? data.company : '')}"></div>
                </div>
                <div class="grid-2">
                    <div class="form-group"><label class="form-label">Start Year (4-digit) *</label><input type="text" class="exp-start" value="${escapeAttr(data ? data.start : '')}"></div>
                    <div class="form-group"><label class="form-label">End Year (or Present) *</label><input type="text" class="exp-end" value="${escapeAttr(data ? data.end : '')}"></div>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Key Duties &amp; Verbatim Skill Evidence (Bullet Points) *</label>
                    <textarea class="exp-duties" rows="4">${escapeHtml(data ? data.duties : '')}</textarea>
                </div>
            `;
            box.querySelectorAll('input, textarea').forEach(el => el.addEventListener('input', renderLiveCV));
            const rm = box.querySelector('.js-remove-item');
            if (rm) rm.addEventListener('click', () => { box.remove(); renderLiveCV(); });
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
                    ${idx > 0 ? `<button type="button" class="js-remove-item" style="background: none; border: none; color: #ef4444; font-size: 0.8rem; cursor: pointer; font-weight: 600;">✕ Remove</button>` : ''}
                </div>
                <div class="grid-2">
                    <div class="form-group" style="margin-bottom: 0.5rem;"><label class="form-label">Degree / Qualification</label><input type="text" class="edu-degree" value="${escapeAttr(data ? data.degree : '')}"></div>
                    <div class="form-group" style="margin-bottom: 0.5rem;"><label class="form-label">Institution / University</label><input type="text" class="edu-school" value="${escapeAttr(data ? data.school : '')}"></div>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Years Attended / Graduated</label>
                    <input type="text" class="edu-year" value="${escapeAttr(data ? data.year : '')}">
                </div>
            `;
            box.querySelectorAll('input').forEach(el => el.addEventListener('input', renderLiveCV));
            const rm = box.querySelector('.js-remove-item');
            if (rm) rm.addEventListener('click', () => { box.remove(); renderLiveCV(); });
            container.appendChild(box);
            renderLiveCV();
        }

        document.getElementById('add-exp-btn').addEventListener('click', () => addExperienceItem());
        document.getElementById('add-edu-btn').addEventListener('click', () => addEducationItem());

        host.querySelectorAll('#step-1 input, #step-2 textarea, #step-4 textarea, #step-5 textarea').forEach(el => {
            el.addEventListener('input', renderLiveCV);
        });

        defaultExperiences.forEach(item => addExperienceItem(item));
        defaultEducations.forEach(item => addEducationItem(item));

        document.getElementById('top-save-pdf-btn').addEventListener('click', saveAndDownloadStyledPDF);
        document.getElementById('bottom-save-pdf-btn').addEventListener('click', saveAndDownloadStyledPDF);
    }

    function collectCVData() {
        const val = (id) => (document.getElementById(id)?.value || '').trim();
        const name = val('cv-name') || 'YOUR FULL NAME';
        const headline = val('cv-headline') || 'Professional Title / Headline';
        const email = val('cv-email') || 'email@example.co.uk';
        const phone = val('cv-phone') || '+44 7700 900000';
        const location = val('cv-location') || 'United Kingdom';
        const website = val('cv-website');
        const summary = val('cv-summary') || 'Dedicated professional with verified domain experience and strong technical execution.';
        const certs = val('cv-certs');
        const skills = val('cv-skills') || 'PHP MVC, MySQL, JavaScript, Docker, REST API Integration';

        const experiences = [];
        document.querySelectorAll('.exp-item').forEach(item => {
            const role = item.querySelector('.exp-role')?.value.trim() || '';
            const company = item.querySelector('.exp-company')?.value.trim() || '';
            const start = item.querySelector('.exp-start')?.value.trim() || '';
            const end = item.querySelector('.exp-end')?.value.trim() || '';
            const duties = item.querySelector('.exp-duties')?.value.trim() || '';
            if (role || company || duties) {
                experiences.push({
                    role: role || 'Professional Role',
                    company: company || 'Organisation',
                    start: start || '2021',
                    end: end || 'Present',
                    duties
                });
            }
        });

        const educations = [];
        document.querySelectorAll('.edu-item').forEach(item => {
            const degree = item.querySelector('.edu-degree')?.value.trim() || '';
            const school = item.querySelector('.edu-school')?.value.trim() || '';
            const year = item.querySelector('.edu-year')?.value.trim() || '';
            if (degree || school) {
                educations.push({
                    degree: degree || 'Qualification',
                    school: school || 'Institution',
                    year
                });
            }
        });

        return { name, headline, email, phone, location, website, summary, experiences, educations, certs, skills };
    }

    function renderLiveCV() {
        if (!document.getElementById('pv-name')) return;
        const data = collectCVData();

        document.getElementById('pv-name').textContent = data.name;
        document.getElementById('pv-headline').textContent = data.headline;

        const contactParts = [data.email, data.phone, data.location];
        if (data.website) contactParts.push(data.website);
        document.getElementById('pv-contact').textContent = contactParts.filter(Boolean).join(' • ');
        document.getElementById('pv-summary').textContent = data.summary;

        const expContainer = document.getElementById('pv-experience');
        expContainer.innerHTML = data.experiences.map(exp => `
            <div style="margin-bottom: 10px;">
                <div style="display: flex; justify-content: space-between; align-items: baseline; font-weight: 700; color: #0f172a; font-size: 12px;">
                    <span>${escapeHtml(exp.role)} | ${escapeHtml(exp.company)}</span>
                    <span style="font-size: 11px; color: #475569;">${escapeHtml(exp.start)} - ${escapeHtml(exp.end)}</span>
                </div>
                <div style="font-size: 11.5px; color: #1e293b; margin-top: 3px; white-space: pre-wrap;">${escapeHtml(exp.duties)}</div>
            </div>
        `).join('');

        const eduContainer = document.getElementById('pv-education');
        eduContainer.innerHTML = data.educations.map(edu => `
            <div style="margin-bottom: 6px; display: flex; justify-content: space-between; font-size: 11.5px;">
                <span><strong>${escapeHtml(edu.degree)}</strong> — ${escapeHtml(edu.school)}</span>
                <span style="color: #475569;">${escapeHtml(edu.year)}</span>
            </div>
        `).join('');

        const certsBlock = document.getElementById('pv-certs-block');
        const certsEl = document.getElementById('pv-certs');
        if (data.certs) {
            certsBlock.style.display = 'block';
            certsEl.textContent = data.certs;
        } else {
            certsBlock.style.display = 'none';
        }

        document.getElementById('pv-skills').textContent = data.skills;
    }

    /**
     * TRUE STYLED VECTOR PDF-1.4 GENERATOR
     * Draws full-color template banners, accent bars, section underlines, right-aligned dates, and native vector text
     */
    function buildStyledPdfBlob(data, tplKey) {
        const pal = TEMPLATES[tplKey] || TEMPLATES.professional;
        const cleanPdfText = (str) => String(str || '')
            .replace(/•/g, '-')
            .replace(/–|—/g, '-')
            .replace(/[^\x20-\x7E]/g, ' ')
            .replace(/\\/g, '\\\\')
            .replace(/\(/g, '\\(')
            .replace(/\)/g, '\\)');

        const wrapText = (text, maxChars = 90) => {
            const result = [];
            String(text || '').split(/\r?\n/).forEach(para => {
                const words = para.trim().split(/\s+/);
                let line = '';
                words.forEach(w => {
                    if ((line + ' ' + w).trim().length > maxChars) {
                        if (line) result.push(line);
                        line = w;
                    } else {
                        line = (line + ' ' + w).trim();
                    }
                });
                if (line) result.push(line);
            });
            return result;
        };

        const ops = [];
        const margin = 45;
        const pageW = 595;
        let y = 790;

        const [pR, pG, pB] = pal.primaryRgb;
        const [aR, aG, aB] = pal.accentRgb;

        const drawText = (str, x, yPos, font, size, rgb = [0.06, 0.09, 0.16]) => {
            ops.push(`${rgb[0].toFixed(2)} ${rgb[1].toFixed(2)} ${rgb[2].toFixed(2)} rg`);
            ops.push(`BT ${font} ${size} Tf 1 0 0 1 ${x.toFixed(1)} ${yPos.toFixed(1)} Tm (${cleanPdfText(str)}) Tj ET`);
        };

        const contactLine = [data.email, data.phone, data.location, data.website].filter(Boolean).join('  |  ');

        // 1. Render Template Header Banner / Accent Bar
        if (pal.banner) {
            ops.push(`${pR.toFixed(2)} ${pG.toFixed(2)} ${pB.toFixed(2)} rg`);
            ops.push(`0 742 ${pageW} 100 re f`);
            drawText(data.name.toUpperCase(), margin, 804, '/F2', 18, [1, 1, 1]);
            drawText(data.headline, margin, 783, '/F2', 11.5, [0.93, 0.95, 1]);
            drawText(contactLine, margin, 764, '/F1', 9.5, [0.88, 0.91, 0.98]);
            y = 716;
        } else {
            if (pal.topBarRgb) {
                const [tR, tG, tB] = pal.topBarRgb;
                ops.push(`${tR.toFixed(2)} ${tG.toFixed(2)} ${tB.toFixed(2)} rg 0 832 ${pageW} 10 re f`);
            }
            if (pal.leftBarRgb) {
                const [lR, lG, lB] = pal.leftBarRgb;
                ops.push(`${lR.toFixed(2)} ${lG.toFixed(2)} ${lB.toFixed(2)} rg 0 0 12 842 re f`);
            }
            const nameX = pal.centered ? Math.max(margin, (pageW - data.name.length * 10) / 2) : margin;
            const headX = pal.centered ? Math.max(margin, (pageW - data.headline.length * 6) / 2) : margin;
            const contX = pal.centered ? Math.max(margin, (pageW - contactLine.length * 4.5) / 2) : margin;

            drawText(data.name.toUpperCase(), nameX, y, '/F2', 18, pal.primaryRgb);
            y -= 18;
            drawText(data.headline, headX, y, '/F2', 11.5, pal.accentRgb);
            y -= 15;
            drawText(contactLine, contX, y, '/F1', 9.5, [0.30, 0.35, 0.42]);
            y -= 12;

            ops.push(`${pR.toFixed(2)} ${pG.toFixed(2)} ${pB.toFixed(2)} RG 1.5 w ${margin} ${y} m ${pageW - margin} ${y} l S`);
            y -= 22;
        }

        const addSectionHeading = (title) => {
            if (y < 90) return;
            y -= 6;
            const titleUpper = title.toUpperCase();
            const hx = pal.centered ? Math.max(margin, (pageW - titleUpper.length * 6.2) / 2) : margin;
            drawText(titleUpper, hx, y, '/F2', 10.5, pal.primaryRgb);
            y -= 5;
            ops.push(`${aR.toFixed(2)} ${aG.toFixed(2)} ${aB.toFixed(2)} RG 0.8 w ${margin} ${y} m ${pageW - margin} ${y} l S`);
            y -= 15;
        };

        // 2. Professional Summary
        addSectionHeading('Professional Summary');
        wrapText(data.summary, 92).forEach(line => {
            if (y < 55) return;
            drawText(line, margin, y, '/F1', 9.8, [0.12, 0.16, 0.23]);
            y -= 13.5;
        });
        y -= 4;

        // 3. Work Experience
        if (data.experiences.length > 0) {
            addSectionHeading('Work Experience');
            data.experiences.forEach(exp => {
                if (y < 75) return;
                const roleHeader = `${exp.role}  |  ${exp.company}`;
                const dateStr = `${exp.start} - ${exp.end}`;
                drawText(roleHeader, margin, y, '/F2', 10.2, [0.06, 0.09, 0.16]);
                const dateX = Math.max(360, pageW - margin - dateStr.length * 5.2);
                drawText(dateStr, dateX, y, '/F2', 9.5, pal.accentRgb);
                y -= 14;

                wrapText(exp.duties, 90).forEach(dLine => {
                    if (y < 55) return;
                    drawText(dLine, margin + 6, y, '/F1', 9.5, [0.15, 0.20, 0.28]);
                    y -= 13;
                });
                y -= 6;
            });
        }

        // 4. Education & Qualifications
        if (data.educations.length > 0) {
            addSectionHeading('Education & Qualifications');
            data.educations.forEach(edu => {
                if (y < 60) return;
                const eduLine = `${edu.degree} - ${edu.school}`;
                drawText(eduLine, margin, y, '/F2', 9.8, [0.06, 0.09, 0.16]);
                if (edu.year) {
                    const yrX = Math.max(380, pageW - margin - edu.year.length * 5.2);
                    drawText(edu.year, yrX, y, '/F1', 9.5, pal.accentRgb);
                }
                y -= 15;
            });
        }

        // 5. Certifications
        if (data.certs) {
            addSectionHeading('Certifications, Licenses & Key Projects');
            wrapText(data.certs, 90).forEach(cLine => {
                if (y < 55) return;
                drawText(cLine, margin, y, '/F1', 9.5, [0.12, 0.16, 0.23]);
                y -= 13;
            });
            y -= 4;
        }

        // 6. Core Competencies & Technical Skills
        addSectionHeading('Core Competencies & Technical Skills');
        wrapText(data.skills, 90).forEach(sLine => {
            if (y < 45) return;
            drawText(sLine, margin, y, '/F2', 9.8, pal.primaryRgb);
            y -= 13.5;
        });

        const streamContent = ops.join('\n');
        const objects = [
            '1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj',
            '2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj',
            '3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R /F2 5 0 R >> >> /Contents 6 0 R >>\nendobj',
            '4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj',
            '5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>\nendobj',
            `6 0 obj\n<< /Length ${streamContent.length} >>\nstream\n${streamContent}\nendstream\nendobj`
        ];

        let pdf = '%PDF-1.4\n';
        const offsets = [0];
        objects.forEach(obj => {
            offsets.push(pdf.length);
            pdf += obj + '\n';
        });

        const xrefStart = pdf.length;
        pdf += `xref\n0 ${objects.length + 1}\n0000000000 65535 f \n`;
        for (let i = 1; i <= objects.length; i++) {
            pdf += String(offsets[i]).padStart(10, '0') + ' 00000 n \n';
        }
        pdf += `trailer\n<< /Size ${objects.length + 1} /Root 1 0 R >>\nstartxref\n${xrefStart}\n%%EOF`;

        return new Blob([pdf], { type: 'application/pdf' });
    }

    async function saveAndDownloadStyledPDF() {
        const data = collectCVData();
        const pdfBlob = buildStyledPdfBlob(data, currentTemplate);
        const safeName = (data.name.replace(/[^a-zA-Z0-9_-]/g, '_').replace(/_+/g, '_') || 'Candidate') + '_CV.pdf';

        if (window.showSaveFilePicker) {
            try {
                const handle = await window.showSaveFilePicker({
                    suggestedName: safeName,
                    types: [{ description: 'PDF Document', accept: { 'application/pdf': ['.pdf'] } }]
                });
                const writable = await handle.createWritable();
                await writable.write(pdfBlob);
                await writable.close();
                const fnEl = document.getElementById('saved-filename-display');
                if (fnEl) fnEl.textContent = handle.name || safeName;
                window.AppA11y.openModal('cv-completed-modal');
                return;
            } catch (err) {
                if (err && err.name === 'AbortError') return;
            }
        }

        const url = URL.createObjectURL(pdfBlob);
        const a = document.createElement('a');
        a.href = url;
        a.download = safeName;
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);

        const fnEl = document.getElementById('saved-filename-display');
        if (fnEl) fnEl.textContent = safeName;
        window.AppA11y.openModal('cv-completed-modal');
    }

    function initResumeBuilder() {
        const host = document.getElementById('resume-app-host');
        if (!host) return;

        const path = window.location.pathname.replace(/\/+$/, '');
        const params = new URLSearchParams(window.location.search);
        const tplParam = params.get('template');

        if (path === '/resume-builder/editor' || tplParam) {
            renderEditorView(host, tplParam || 'professional');
        } else {
            renderGalleryView(host);
        }
    }

    window.AppResume = {
        initResumeBuilder,
        buildStyledPdfBlob
    };
})(window);

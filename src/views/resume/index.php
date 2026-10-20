<style>
.resume-gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2.5rem;
}
.template-card {
    background: rgba(17, 24, 39, 0.85);
    border: 2px solid rgba(255, 255, 255, 0.1);
    border-radius: 16px;
    padding: 1.5rem;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
}
.template-card:hover {
    border-color: rgba(139, 92, 246, 0.55);
    transform: translateY(-5px);
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.45), 0 0 20px rgba(139, 92, 246, 0.2);
}
.template-card.selected {
    border-color: #10b981;
    background: linear-gradient(180deg, rgba(16, 185, 129, 0.1) 0%, rgba(17, 24, 39, 0.92) 100%);
    box-shadow: 0 18px 45px rgba(0, 0, 0, 0.5), 0 0 28px rgba(16, 185, 129, 0.3);
    transform: translateY(-5px);
}
.selected-pill {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: #10b981;
    color: #06281e;
    font-size: 0.72rem;
    font-weight: 800;
    padding: 0.25rem 0.65rem;
    border-radius: 999px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    display: none;
    z-index: 5;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
}
.template-card.selected .selected-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.mini-a4-preview {
    height: 250px;
    background: #ffffff;
    border-radius: 10px;
    margin-bottom: 1.25rem;
    padding: 14px;
    color: #0f172a;
    font-family: 'Inter', -apple-system, sans-serif;
    box-shadow: inset 0 0 0 1px rgba(0,0,0,0.08), 0 6px 16px rgba(0,0,0,0.25);
    overflow: hidden;
    position: relative;
    text-align: left;
    user-select: none;
}
.mini-line {
    height: 5px;
    background: #cbd5e1;
    border-radius: 3px;
    margin-bottom: 5px;
}
.mini-line.short { width: 45%; }
.mini-line.med { width: 72%; }
.mini-line.full { width: 95%; }
.mini-sec-head {
    font-size: 6.5px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    margin: 8px 0 4px 0;
    color: #334155;
}
.start-build-bar {
    background: rgba(17, 24, 39, 0.95);
    border: 1px solid rgba(139, 92, 246, 0.4);
    border-radius: 14px;
    padding: 1.5rem 2rem;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1.25rem;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
    margin-bottom: 3rem;
}
.btn-start-build {
    padding: 0.9rem 2.25rem;
    font-size: 1rem;
    font-weight: 700;
    border-radius: 999px;
    border: none;
    transition: all 0.25s ease;
    cursor: not-allowed;
    background: #374151;
    color: #9ca3af;
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
    text-decoration: none;
}
.btn-start-build.active {
    cursor: pointer;
    background: linear-gradient(135deg, #10b981, #059669);
    color: #ffffff;
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.45);
}
.btn-start-build.active:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 30px rgba(16, 185, 129, 0.6);
    color: #ffffff;
    text-decoration: none;
}
</style>

<div class="container" style="margin-top: 2.5rem; margin-bottom: 4rem;">
    <!-- Top Header & Navigation -->
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
            Select a Modern ATS-Ready CV Template
        </h1>
        <p style="color: var(--text-muted); max-width: 680px; margin: 0 auto; font-size: 0.96rem; line-height: 1.6;">
            Click on any of the 4 templates below to select your preferred style. Once selected, click <strong>"Start Building CV"</strong> to complete your step-by-step profile and save a crisp, searchable PDF directly to your device.
        </p>
    </div>

    <!-- 4 Templates Grid -->
    <div class="resume-gallery-grid">

        <!-- 1. THE PROFESSIONAL -->
        <div class="template-card" data-template="professional" data-title="The Professional (ATS Classic)" onclick="selectTemplate(this)">
            <span class="selected-pill">✓ Selected</span>
            <div>
                <div class="mini-a4-preview" style="border-top: 4px solid #1e293b;">
                    <div style="border-bottom: 1.5px solid #0f172a; padding-bottom: 6px; margin-bottom: 8px;">
                        <div style="font-size: 11px; font-weight: 800; color: #0f172a; letter-spacing: 0.02em;">ALEXANDER WRIGHT</div>
                        <div style="font-size: 6.5px; color: #475569; margin-top: 2px;">London, UK • alex@email.co.uk • +44 7700 900123</div>
                    </div>
                    <div class="mini-sec-head" style="border-bottom: 1px solid #cbd5e1; padding-bottom: 2px;">Professional Summary</div>
                    <div class="mini-line full"></div>
                    <div class="mini-line med"></div>

                    <div class="mini-sec-head" style="border-bottom: 1px solid #cbd5e1; padding-bottom: 2px;">Work Experience</div>
                    <div style="display: flex; justify-content: space-between; font-size: 6.5px; font-weight: 700; color: #1e293b; margin-bottom: 3px;">
                        <span>Senior Software Engineer — FinTech UK</span>
                        <span>2021 – Present</span>
                    </div>
                    <div class="mini-line full"></div>
                    <div class="mini-line full"></div>
                    <div class="mini-line short"></div>

                    <div style="display: flex; justify-content: space-between; font-size: 6.5px; font-weight: 700; color: #1e293b; margin: 6px 0 3px 0;">
                        <span>Full Stack Developer — CoreSystems</span>
                        <span>2019 – 2021</span>
                    </div>
                    <div class="mini-line full"></div>
                    <div class="mini-line med"></div>

                    <div class="mini-sec-head" style="border-bottom: 1px solid #cbd5e1; padding-bottom: 2px;">Education & Certifications</div>
                    <div class="mini-line med"></div>
                    <div class="mini-line short"></div>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.4rem;">
                    <h3 style="font-size: 1.15rem; color: #ffffff; margin: 0;">The Professional</h3>
                    <span class="badge" style="background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3); font-size: 0.7rem;">ATS Classic</span>
                </div>
                <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5; margin-bottom: 1.25rem;">
                    Clean, traditional chronological structure favored by UK corporate, finance, healthcare, and enterprise hiring managers.
                </p>
            </div>
            <button type="button" class="btn btn-ghost w-100 template-select-btn" style="border-color: rgba(255,255,255,0.2); font-size: 0.85rem;">
                Select "The Professional"
            </button>
        </div>

        <!-- 2. THE MODERN ACCENT -->
        <div class="template-card" data-template="modern" data-title="The Modern Accent" onclick="selectTemplate(this)">
            <span class="selected-pill">✓ Selected</span>
            <div>
                <div class="mini-a4-preview" style="padding: 0;">
                    <div style="background: linear-gradient(135deg, #7c3aed, #4f46e5); color: #ffffff; padding: 12px 14px;">
                        <div style="font-size: 11px; font-weight: 800; letter-spacing: 0.03em;">SOPHIA CHEN</div>
                        <div style="font-size: 6.5px; opacity: 0.9; margin-top: 2px;">Product & Cloud Architect • Manchester • sophia@domain.uk</div>
                    </div>
                    <div style="padding: 10px 14px;">
                        <div class="mini-sec-head" style="color: #7c3aed; margin-top: 2px;">Career Profile</div>
                        <div class="mini-line full"></div>
                        <div class="mini-line med"></div>

                        <div class="mini-sec-head" style="color: #7c3aed;">Experience & Impact</div>
                        <div style="border-left: 2px solid #8b5cf6; padding-left: 6px; margin-bottom: 6px;">
                            <div style="font-size: 6.5px; font-weight: 700; color: #1e293b; margin-bottom: 2px;">Lead Platform Engineer (2022 – Present)</div>
                            <div class="mini-line full"></div>
                            <div class="mini-line med"></div>
                        </div>
                        <div style="border-left: 2px solid #8b5cf6; padding-left: 6px; margin-bottom: 6px;">
                            <div style="font-size: 6.5px; font-weight: 700; color: #1e293b; margin-bottom: 2px;">Cloud Developer (2020 – 2022)</div>
                            <div class="mini-line full"></div>
                            <div class="mini-line short"></div>
                        </div>

                        <div class="mini-sec-head" style="color: #7c3aed;">Verified Skills</div>
                        <div style="display: flex; gap: 3px; flex-wrap: wrap;">
                            <span style="background: #ede9fe; color: #6d28d9; font-size: 5.5px; font-weight: 700; padding: 2px 5px; border-radius: 3px;">Docker</span>
                            <span style="background: #ede9fe; color: #6d28d9; font-size: 5.5px; font-weight: 700; padding: 2px 5px; border-radius: 3px;">PHP MVC</span>
                            <span style="background: #ede9fe; color: #6d28d9; font-size: 5.5px; font-weight: 700; padding: 2px 5px; border-radius: 3px;">MySQL</span>
                            <span style="background: #ede9fe; color: #6d28d9; font-size: 5.5px; font-weight: 700; padding: 2px 5px; border-radius: 3px;">REST APIs</span>
                        </div>
                    </div>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.4rem;">
                    <h3 style="font-size: 1.15rem; color: #ffffff; margin: 0;">The Modern</h3>
                    <span class="badge" style="background: rgba(139, 92, 246, 0.18); color: #c4b5fd; border: 1px solid rgba(139, 92, 246, 0.35); font-size: 0.7rem;">Popular</span>
                </div>
                <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5; margin-bottom: 1.25rem;">
                    Contemporary header banner with vibrant royal-violet accents, timeline borders, and skill pills that stand out visually.
                </p>
            </div>
            <button type="button" class="btn btn-ghost w-100 template-select-btn" style="border-color: rgba(255,255,255,0.2); font-size: 0.85rem;">
                Select "The Modern"
            </button>
        </div>

        <!-- 3. THE EXECUTIVE MINIMALIST -->
        <div class="template-card" data-template="executive" data-title="The Executive Minimalist" onclick="selectTemplate(this)">
            <span class="selected-pill">✓ Selected</span>
            <div>
                <div class="mini-a4-preview" style="border-top: 8px solid #0f172a;">
                    <div style="text-align: center; border-bottom: 1px solid #cbd5e1; padding-bottom: 7px; margin-bottom: 8px;">
                        <div style="font-size: 11px; font-weight: 800; letter-spacing: 0.12em; color: #0f172a;">ELEANOR VANCE</div>
                        <div style="font-size: 6px; color: #64748b; margin-top: 2px; text-transform: uppercase; letter-spacing: 0.06em;">Operations Director • Edinburgh • +44 7911 123456</div>
                    </div>

                    <div class="mini-sec-head" style="text-align: center; color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 2px;">Executive Summary</div>
                    <div class="mini-line full" style="margin: 0 auto 4px auto;"></div>
                    <div class="mini-line med" style="margin: 0 auto 6px auto;"></div>

                    <div class="mini-sec-head" style="text-align: center; color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 2px;">Leadership Experience</div>
                    <div style="display: flex; justify-content: space-between; font-size: 6.5px; font-weight: 700; color: #0f172a; margin-bottom: 3px;">
                        <span>Head of Digital Operations — Sterling Group</span>
                        <span>2020 – Present</span>
                    </div>
                    <div class="mini-line full"></div>
                    <div class="mini-line full"></div>
                    <div class="mini-line med"></div>

                    <div class="mini-sec-head" style="text-align: center; color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 2px;">Credentials & Governance</div>
                    <div class="mini-line med" style="margin: 0 auto 4px auto;"></div>
                    <div class="mini-line short" style="margin: 0 auto;"></div>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.4rem;">
                    <h3 style="font-size: 1.15rem; color: #ffffff; margin: 0;">The Executive</h3>
                    <span class="badge" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.35); font-size: 0.7rem;">Leadership</span>
                </div>
                <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5; margin-bottom: 1.25rem;">
                    Refined centered typography with authoritative dark-slate framing. Designed for senior management, finance, and consultancy.
                </p>
            </div>
            <button type="button" class="btn btn-ghost w-100 template-select-btn" style="border-color: rgba(255,255,255,0.2); font-size: 0.85rem;">
                Select "The Executive"
            </button>
        </div>

        <!-- 4. THE TECHNICAL SPECIALIST -->
        <div class="template-card" data-template="technical" data-title="The Technical Specialist" onclick="selectTemplate(this)">
            <span class="selected-pill">✓ Selected</span>
            <div>
                <div class="mini-a4-preview" style="border-left: 6px solid #059669;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1.5px solid #059669; padding-bottom: 6px; margin-bottom: 6px;">
                        <div>
                            <div style="font-size: 10.5px; font-weight: 800; color: #064e3b;">MARCUS REID</div>
                            <div style="font-size: 6px; color: #059669; font-weight: 700;"> DevOps & Backend Specialist</div>
                        </div>
                        <div style="font-size: 5.5px; color: #475569; text-align: right;">
                            github.com/mreid<br>Bristol, UK
                        </div>
                    </div>

                    <div class="mini-sec-head" style="color: #065f46; margin-top: 4px;">Core Technical Stack</div>
                    <div style="background: #ecfdf5; border: 1px solid #a7f3d0; padding: 4px; border-radius: 4px; font-size: 5.5px; color: #065f46; font-family: monospace; margin-bottom: 6px;">
                        PHP 8.2 • MySQL • Docker • Azure • CI/CD • Linux
                    </div>

                    <div class="mini-sec-head" style="color: #065f46;">Engineering Experience (Evidence-Grounded)</div>
                    <div style="font-size: 6.5px; font-weight: 700; color: #0f172a; margin-bottom: 2px;">Platform Engineer | CloudScale (2021 – 2026)</div>
                    <div class="mini-line full"></div>
                    <div class="mini-line full"></div>
                    <div class="mini-line med"></div>

                    <div class="mini-sec-head" style="color: #065f46;">Active Certifications & Licenses</div>
                    <div class="mini-line med"></div>
                    <div class="mini-line short"></div>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.4rem;">
                    <h3 style="font-size: 1.15rem; color: #ffffff; margin: 0;">The Technical</h3>
                    <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35); font-size: 0.7rem;">AI-Score Max</span>
                </div>
                <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5; margin-bottom: 1.25rem;">
                    Engineered specifically for technical, IT, engineering, and certified trade roles with high-density evidence & license blocks.
                </p>
            </div>
            <button type="button" class="btn btn-ghost w-100 template-select-btn" style="border-color: rgba(255,255,255,0.2); font-size: 0.85rem;">
                Select "The Technical"
            </button>
        </div>

    </div>

    <!-- Bottom Sticky / Prominent Activation Bar -->
    <div class="start-build-bar" id="start-build-bar">
        <div>
            <div style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); font-weight: 700;">
                Current Selection
            </div>
            <div id="selected-template-label" style="font-size: 1.2rem; font-weight: 800; color: #ffffff; margin-top: 0.2rem;">
                👆 Please click a template above to activate the builder
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
            <a href="#" id="start-build-btn" class="btn-start-build" onclick="return handleStartBuild(event)">
                🚀 Start Building My CV →
            </a>
        </div>
    </div>
</div>

<script>
let chosenTemplate = null;

function selectTemplate(cardEl) {
    document.querySelectorAll('.template-card').forEach(c => {
        c.classList.remove('selected');
        const btn = c.querySelector('.template-select-btn');
        const title = c.getAttribute('data-title').split(' (')[0];
        if (btn) {
            btn.textContent = 'Select "' + title + '"';
            btn.className = 'btn btn-ghost w-100 template-select-btn';
            btn.style.borderColor = 'rgba(255,255,255,0.2)';
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
    labelEl.innerHTML = '✅ Selected: <span style="color: #34d399;">' + chosenTitle + '</span>';

    const startBtn = document.getElementById('start-build-btn');
    startBtn.classList.add('active');
    startBtn.href = '/resume-builder/editor?template=' + encodeURIComponent(chosenTemplate);
}

function handleStartBuild(e) {
    if (!chosenTemplate) {
        e.preventDefault();
        const bar = document.getElementById('start-build-bar');
        bar.style.borderColor = '#ef4444';
        setTimeout(() => { bar.style.borderColor = 'rgba(139, 92, 246, 0.4)'; }, 800);
        return false;
    }
    return true;
}
</script>

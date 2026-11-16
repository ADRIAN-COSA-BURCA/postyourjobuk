/**
 * PostYourJobUK — Zero-CDN Client-Side ATS Resume Builder & Vector PDF-1.4 Writer (resume.js)
 * Generates genuine PDF-1.4 text streams (BT /F1 ... Tj ET) 100% compatible with PyPDF2 in Azure Functions
 */
(function (window) {
    'use strict';

    const { escapeHtml, showToast } = window.AppApi;

    /**
     * Build a genuine, zero-dependency PDF-1.4 binary Blob containing extractable vector text objects
     */
    function generateNativeVectorPdf(lines) {
        const cleanPdfText = (str) => String(str || '')
            .replace(/[^\x20-\x7E]/g, ' ')
            .replace(/\\/g, '\\\\')
            .replace(/\(/g, '\\(')
            .replace(/\)/g, '\\)');

        let y = 790;
        const ops = ['BT'];

        lines.forEach(item => {
            if (y < 55) return;
            const font = item.bold ? '/F2' : '/F1';
            const size = item.size || 10.5;
            const leading = item.spaceAfter || (size + 5);

            // Wrap long lines at ~88 characters so text stays within A4 margins
            const rawText = String(item.text || '');
            const words = rawText.split(/\s+/);
            let currentLine = '';
            const wrapped = [];

            words.forEach(w => {
                if ((currentLine + ' ' + w).trim().length > 88) {
                    if (currentLine) wrapped.push(currentLine);
                    currentLine = w;
                } else {
                    currentLine = (currentLine + ' ' + w).trim();
                }
            });
            if (currentLine || wrapped.length === 0) wrapped.push(currentLine);

            wrapped.forEach(lineStr => {
                if (y < 55) return;
                ops.push(`${font} ${size} Tf`);
                ops.push(`1 0 0 1 48 ${y} Tm`);
                ops.push(`(${cleanPdfText(lineStr)}) Tj`);
                y -= leading;
            });
        });

        ops.push('ET');
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
        pdf += `xref\n0 ${objects.length + 1}\n`;
        pdf += '0000000000 65535 f \n';
        for (let i = 1; i <= objects.length; i++) {
            pdf += String(offsets[i]).padStart(10, '0') + ' 00000 n \n';
        }

        pdf += `trailer\n<< /Size ${objects.length + 1} /Root 1 0 R >>\nstartxref\n${xrefStart}\n%%EOF`;
        return new Blob([pdf], { type: 'application/pdf' });
    }

    function collectResumeData() {
        const val = (id) => (document.getElementById(id)?.value || '').trim();
        return {
            name: val('cv-name') || 'Candidate Name',
            title: val('cv-title') || 'Target Role Title',
            email: val('cv-email') || 'email@example.co.uk',
            phone: val('cv-phone') || '+44 7700 900000',
            location: val('cv-location') || 'United Kingdom',
            summary: val('cv-summary') || 'Results-driven professional with proven technical and operational delivery experience.',
            skills: val('cv-skills') || 'PHP, MySQL, JavaScript, REST APIs, Cloud Architecture, Agile Delivery',
            expRole1: val('cv-exp1-role') || 'Senior Software Engineer',
            expComp1: val('cv-exp1-comp') || 'Enterprise Solutions Ltd',
            expDates1: val('cv-exp1-dates') || '2022 - Present',
            expDesc1: val('cv-exp1-desc') || 'Designed and delivered scalable web applications, RESTful APIs, and automated cloud pipelines.',
            expRole2: val('cv-exp2-role'),
            expComp2: val('cv-exp2-comp'),
            expDates2: val('cv-exp2-dates'),
            expDesc2: val('cv-exp2-desc'),
            eduDegree: val('cv-edu-degree') || 'BSc (Hons) Computer Science',
            eduSchool: val('cv-edu-school') || 'UK University',
            eduYear: val('cv-edu-year') || '2021'
        };
    }

    function updatePreview() {
        const d = collectResumeData();
        const host = document.getElementById('cv-live-preview');
        if (!host) return;

        host.innerHTML = `
            <div style="background: #ffffff; color: #0f172a; padding: 2rem; border-radius: 10px; font-family: system-ui, sans-serif; line-height: 1.5;">
                <div style="border-bottom: 2px solid #6d28d9; padding-bottom: 0.85rem; margin-bottom: 1rem;">
                    <h2 style="color: #0f172a; margin: 0; font-size: 1.5rem;">${escapeHtml(d.name)}</h2>
                    <div style="color: #6d28d9; font-weight: 700; font-size: 1rem;">${escapeHtml(d.title)}</div>
                    <div style="color: #475569; font-size: 0.84rem; margin-top: 0.25rem;">
                        ${escapeHtml(d.email)} &bull; ${escapeHtml(d.phone)} &bull; ${escapeHtml(d.location)}
                    </div>
                </div>
                <div style="margin-bottom: 1rem;">
                    <h3 style="color: #6d28d9; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 0.35rem;">Professional Summary</h3>
                    <p style="color: #1e293b; font-size: 0.9rem;">${escapeHtml(d.summary)}</p>
                </div>
                <div style="margin-bottom: 1rem;">
                    <h3 style="color: #6d28d9; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 0.35rem;">Core Technical &amp; Domain Skills</h3>
                    <p style="color: #1e293b; font-size: 0.9rem; font-weight: 600;">${escapeHtml(d.skills)}</p>
                </div>
                <div style="margin-bottom: 1rem;">
                    <h3 style="color: #6d28d9; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 0.35rem;">Professional Work Experience</h3>
                    <div style="margin-bottom: 0.75rem;">
                        <div style="font-weight: 700; color: #0f172a; font-size: 0.94rem;">${escapeHtml(d.expRole1)} — ${escapeHtml(d.expComp1)} (${escapeHtml(d.expDates1)})</div>
                        <p style="color: #334155; font-size: 0.88rem; margin-top: 0.2rem; white-space: pre-line;">${escapeHtml(d.expDesc1)}</p>
                    </div>
                    ${d.expRole2 ? `
                    <div>
                        <div style="font-weight: 700; color: #0f172a; font-size: 0.94rem;">${escapeHtml(d.expRole2)} — ${escapeHtml(d.expComp2)} (${escapeHtml(d.expDates2)})</div>
                        <p style="color: #334155; font-size: 0.88rem; margin-top: 0.2rem; white-space: pre-line;">${escapeHtml(d.expDesc2)}</p>
                    </div>` : ''}
                </div>
                <div>
                    <h3 style="color: #6d28d9; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 0.35rem;">Education &amp; Certifications</h3>
                    <div style="font-weight: 700; color: #0f172a; font-size: 0.92rem;">${escapeHtml(d.eduDegree)} — ${escapeHtml(d.eduSchool)} (${escapeHtml(d.eduYear)})</div>
                </div>
            </div>
        `;
    }

    async function downloadPdf() {
        const d = collectResumeData();
        const lines = [
            { text: d.name.toUpperCase(), bold: true, size: 16, spaceAfter: 20 },
            { text: d.title, bold: true, size: 12, spaceAfter: 16 },
            { text: `${d.email} | ${d.phone} | ${d.location}`, bold: false, size: 10, spaceAfter: 22 },
            { text: 'PROFESSIONAL SUMMARY', bold: true, size: 11, spaceAfter: 15 },
            { text: d.summary, bold: false, size: 10, spaceAfter: 18 },
            { text: 'CORE TECHNICAL SKILLS & COMPETENCIES', bold: true, size: 11, spaceAfter: 15 },
            { text: d.skills, bold: false, size: 10, spaceAfter: 18 },
            { text: 'PROFESSIONAL WORK EXPERIENCE', bold: true, size: 11, spaceAfter: 15 },
            { text: `${d.expRole1} | ${d.expComp1} (${d.expDates1})`, bold: true, size: 10.5, spaceAfter: 14 },
            { text: d.expDesc1, bold: false, size: 10, spaceAfter: 18 }
        ];

        if (d.expRole2) {
            lines.push({ text: `${d.expRole2} | ${d.expComp2} (${d.expDates2})`, bold: true, size: 10.5, spaceAfter: 14 });
            lines.push({ text: d.expDesc2, bold: false, size: 10, spaceAfter: 18 });
        }

        lines.push({ text: 'EDUCATION & QUALIFICATIONS', bold: true, size: 11, spaceAfter: 15 });
        lines.push({ text: `${d.eduDegree} - ${d.eduSchool} (${d.eduYear})`, bold: false, size: 10, spaceAfter: 15 });

        const pdfBlob = generateNativeVectorPdf(lines);
        const safeName = d.name.replace(/[^A-Za-z0-9_-]/g, '_') + '_ATS_CV.pdf';

        if (window.showSaveFilePicker) {
            try {
                const handle = await window.showSaveFilePicker({
                    suggestedName: safeName,
                    types: [{ description: 'PDF Document', accept: { 'application/pdf': ['.pdf'] } }]
                });
                const writable = await handle.createWritable();
                await writable.write(pdfBlob);
                await writable.close();
                showToast('ATS-ready PDF CV saved successfully!', 'success');
                return;
            } catch (err) {
                if (err.name === 'AbortError') return;
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
        showToast('ATS-ready PDF CV downloaded!', 'success');
    }

    function initResumeBuilder() {
        const form = document.getElementById('resume-builder-form');
        if (!form) return;

        form.querySelectorAll('input, textarea').forEach(el => {
            el.addEventListener('input', updatePreview);
        });

        const dlBtn = document.getElementById('download-cv-pdf-btn');
        if (dlBtn) {
            dlBtn.addEventListener('click', downloadPdf);
        }

        updatePreview();
    }

    window.AppResume = {
        initResumeBuilder,
        generateNativeVectorPdf
    };
})(window);

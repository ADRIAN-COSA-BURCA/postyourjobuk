/**
 * PostYourJobUK — Native HTML5 <canvas> Chart Engine (charts.js)
 * Zero External CDNs | Supports Horizontal Bar, Dual-Curve Line, Pie & Doughnut Charts
 */
(function (window) {
    'use strict';

    const PALETTE = [
        '#f59e0b', // Amber
        '#10b981', // Emerald
        '#38bdf8', // Sky Blue
        '#a855f7', // Purple
        '#f43f5e', // Rose
        '#06b6d4', // Cyan
        '#eab308', // Yellow
        '#ec4899'  // Pink
    ];

    function setupHiDPICanvas(canvas) {
        const rect = canvas.parentElement.getBoundingClientRect();
        const width = Math.max(260, rect.width || 420);
        const height = Math.max(200, rect.height || 260);
        const dpr = window.devicePixelRatio || 1;

        canvas.width = width * dpr;
        canvas.height = height * dpr;
        canvas.style.width = width + 'px';
        canvas.style.height = height + 'px';

        const ctx = canvas.getContext('2d');
        ctx.scale(dpr, dpr);
        return { ctx, width, height };
    }

    function truncateLabel(str, maxLen = 22) {
        const s = String(str || '');
        return s.length > maxLen ? s.slice(0, maxLen - 1) + '…' : s;
    }

    /**
     * 1. Render Horizontal Bar Chart (Single or Multi-Dataset)
     */
    function renderHorizontalBar(canvasId, config) {
        const canvas = typeof canvasId === 'string' ? document.getElementById(canvasId) : canvasId;
        if (!canvas) return;

        const { ctx, width, height } = setupHiDPICanvas(canvas);
        const labels = config.labels && config.labels.length ? config.labels : ['No Data'];
        const datasets = config.datasets || [{ label: 'Count', data: [0], colors: PALETTE }];

        canvas.setAttribute('role', 'img');
        canvas.setAttribute('aria-label', (config.title || 'Horizontal bar chart') + ': ' + labels.join(', '));

        ctx.clearRect(0, 0, width, height);

        const padLeft = Math.min(145, width * 0.34);
        const padRight = 36;
        const padTop = 18;
        const padBottom = datasets.length > 1 ? 46 : 28;
        const plotW = Math.max(60, width - padLeft - padRight);
        const plotH = Math.max(60, height - padTop - padBottom);

        let maxVal = 1;
        datasets.forEach(ds => {
            (ds.data || []).forEach(v => {
                if (Number(v) > maxVal) maxVal = Number(v);
            });
        });
        maxVal = Math.ceil(maxVal * 1.15) || 1;

        // Gridlines
        const steps = 4;
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.08)';
        ctx.fillStyle = '#94a3b8';
        ctx.font = '11px system-ui, sans-serif';
        ctx.textAlign = 'center';

        for (let i = 0; i <= steps; i++) {
            const x = padLeft + (plotW * i) / steps;
            const val = Math.round((maxVal * i) / steps);
            ctx.beginPath();
            ctx.moveTo(x, padTop);
            ctx.lineTo(x, padTop + plotH);
            ctx.stroke();
            ctx.fillText(String(val), x, padTop + plotH + 15);
        }

        const rowH = plotH / labels.length;
        const groupBarH = Math.min(22, (rowH * 0.68) / datasets.length);

        labels.forEach((lbl, idx) => {
            const rowCenterY = padTop + idx * rowH + rowH / 2;

            // Y-axis Label
            ctx.fillStyle = '#e2e8f0';
            ctx.font = '600 11.5px system-ui, sans-serif';
            ctx.textAlign = 'right';
            ctx.textBaseline = 'middle';
            ctx.fillText(truncateLabel(lbl, 20), padLeft - 10, rowCenterY);

            datasets.forEach((ds, dsIdx) => {
                const rawVal = Number((ds.data || [])[idx] || 0);
                const barW = Math.max(3, (rawVal / maxVal) * plotW);
                const startY = rowCenterY - (datasets.length * groupBarH) / 2 + dsIdx * (groupBarH + 2);

                const fillColor = Array.isArray(ds.colors)
                    ? ds.colors[idx % ds.colors.length]
                    : (ds.color || PALETTE[idx % PALETTE.length]);

                ctx.fillStyle = fillColor;
                ctx.beginPath();
                if (ctx.roundRect) {
                    ctx.roundRect(padLeft, startY, barW, groupBarH, 4);
                } else {
                    ctx.rect(padLeft, startY, barW, groupBarH);
                }
                ctx.fill();

                // Value label at end of bar
                ctx.fillStyle = '#cbd5e1';
                ctx.font = '700 10.5px system-ui, sans-serif';
                ctx.textAlign = 'left';
                ctx.fillText(String(rawVal), padLeft + barW + 6, startY + groupBarH / 2);
            });
        });

        // Legend for multi-dataset
        if (datasets.length > 1) {
            let legX = padLeft;
            const legY = height - 12;
            datasets.forEach((ds, i) => {
                ctx.fillStyle = ds.color || PALETTE[i % PALETTE.length];
                ctx.fillRect(legX, legY - 8, 10, 10);
                ctx.fillStyle = '#cbd5e1';
                ctx.font = '11px system-ui, sans-serif';
                ctx.textAlign = 'left';
                ctx.fillText(ds.label || `Series ${i + 1}`, legX + 14, legY - 2);
                legX += ctx.measureText(ds.label || `Series ${i + 1}`).width + 34;
            });
        }
    }

    /**
     * 2. Render Dual-Curve Line Chart
     */
    function renderLineChart(canvasId, config) {
        const canvas = typeof canvasId === 'string' ? document.getElementById(canvasId) : canvasId;
        if (!canvas) return;

        const { ctx, width, height } = setupHiDPICanvas(canvas);
        const labels = config.labels && config.labels.length ? config.labels : ['No Data'];
        const datasets = config.datasets || [];
        const suffix = config.ySuffix || '';

        canvas.setAttribute('role', 'img');
        canvas.setAttribute('aria-label', (config.title || 'Line chart') + ': ' + labels.join(', '));

        ctx.clearRect(0, 0, width, height);

        const padLeft = 44;
        const padRight = 20;
        const padTop = 20;
        const padBottom = 50;
        const plotW = Math.max(60, width - padLeft - padRight);
        const plotH = Math.max(60, height - padTop - padBottom);

        let maxVal = config.yMax || 1;
        if (!config.yMax) {
            datasets.forEach(ds => {
                (ds.data || []).forEach(v => {
                    if (Number(v) > maxVal) maxVal = Number(v);
                });
            });
            maxVal = Math.ceil(maxVal * 1.15) || 5;
        }

        // Y-axis Grid
        const steps = 4;
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.08)';
        ctx.fillStyle = '#94a3b8';
        ctx.font = '11px system-ui, sans-serif';
        ctx.textAlign = 'right';
        ctx.textBaseline = 'middle';

        for (let i = 0; i <= steps; i++) {
            const y = padTop + plotH - (plotH * i) / steps;
            const val = Math.round((maxVal * i) / steps);
            ctx.beginPath();
            ctx.moveTo(padLeft, y);
            ctx.lineTo(padLeft + plotW, y);
            ctx.stroke();
            ctx.fillText(val + suffix, padLeft - 8, y);
        }

        // X-axis Labels
        ctx.textAlign = 'center';
        ctx.textBaseline = 'top';
        labels.forEach((lbl, idx) => {
            const x = labels.length === 1
                ? padLeft + plotW / 2
                : padLeft + (plotW * idx) / (labels.length - 1);
            ctx.fillStyle = '#cbd5e1';
            ctx.fillText(truncateLabel(lbl, 12), x, padTop + plotH + 8);
        });

        // Plot Datasets
        datasets.forEach((ds, dsIdx) => {
            const color = ds.color || PALETTE[dsIdx % PALETTE.length];
            const pts = labels.map((_, idx) => {
                const v = Number((ds.data || [])[idx] || 0);
                const x = labels.length === 1
                    ? padLeft + plotW / 2
                    : padLeft + (plotW * idx) / (labels.length - 1);
                const y = padTop + plotH - (Math.min(maxVal, v) / maxVal) * plotH;
                return { x, y, v };
            });

            if (pts.length > 1) {
                // Soft Area Fill
                const grad = ctx.createLinearGradient(0, padTop, 0, padTop + plotH);
                grad.addColorStop(0, color + '33');
                grad.addColorStop(1, color + '02');
                ctx.fillStyle = grad;
                ctx.beginPath();
                ctx.moveTo(pts[0].x, padTop + plotH);
                pts.forEach(p => ctx.lineTo(p.x, p.y));
                ctx.lineTo(pts[pts.length - 1].x, padTop + plotH);
                ctx.closePath();
                ctx.fill();

                // Line Stroke
                ctx.strokeStyle = color;
                ctx.lineWidth = 2.5;
                ctx.beginPath();
                pts.forEach((p, i) => {
                    if (i === 0) ctx.moveTo(p.x, p.y);
                    else ctx.lineTo(p.x, p.y);
                });
                ctx.stroke();
            }

            // Points
            pts.forEach(p => {
                ctx.fillStyle = color;
                ctx.beginPath();
                ctx.arc(p.x, p.y, 4, 0, Math.PI * 2);
                ctx.fill();
            });
        });

        // Bottom Legend
        let legX = padLeft;
        const legY = height - 10;
        datasets.forEach((ds, i) => {
            const color = ds.color || PALETTE[i % PALETTE.length];
            ctx.fillStyle = color;
            ctx.fillRect(legX, legY - 8, 12, 4);
            ctx.fillStyle = '#e2e8f0';
            ctx.font = '11px system-ui, sans-serif';
            ctx.textAlign = 'left';
            ctx.textBaseline = 'middle';
            ctx.fillText(ds.label || `Series ${i + 1}`, legX + 16, legY - 6);
            legX += ctx.measureText(ds.label || `Series ${i + 1}`).width + 36;
        });
    }

    /**
     * 3. Render Pie or Doughnut Chart
     */
    function renderCircularChart(canvasId, config) {
        const canvas = typeof canvasId === 'string' ? document.getElementById(canvasId) : canvasId;
        if (!canvas) return;

        const { ctx, width, height } = setupHiDPICanvas(canvas);
        const labels = config.labels && config.labels.length ? config.labels : ['No Data'];
        const values = (config.values || []).map(v => Math.max(0, Number(v) || 0));
        const colors = config.colors || PALETTE;
        const cutoutRatio = typeof config.cutoutRatio === 'number' ? config.cutoutRatio : 0;

        const total = values.reduce((acc, v) => acc + v, 0);

        canvas.setAttribute('role', 'img');
        canvas.setAttribute('aria-label', (config.title || 'Circular chart') + ': ' + labels.map((l, i) => `${l} (${values[i] || 0})`).join(', '));

        ctx.clearRect(0, 0, width, height);

        const isWide = width >= 340;
        const centerX = isWide ? width * 0.34 : width * 0.5;
        const centerY = isWide ? height * 0.5 : height * 0.40;
        const outerR = Math.max(45, Math.min(centerX - 16, centerY - 16, 95));
        const innerR = outerR * cutoutRatio;

        if (total <= 0) {
            ctx.strokeStyle = 'rgba(255, 255, 255, 0.12)';
            ctx.lineWidth = outerR - innerR || 20;
            ctx.beginPath();
            ctx.arc(centerX, centerY, (outerR + innerR) / 2 || outerR / 2, 0, Math.PI * 2);
            ctx.stroke();

            ctx.fillStyle = '#94a3b8';
            ctx.font = '12px system-ui, sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText('No Data Yet', centerX, centerY);
        } else {
            let startAngle = -Math.PI / 2;
            values.forEach((val, idx) => {
                if (val <= 0) return;
                const sliceAngle = (val / total) * (Math.PI * 2);
                const endAngle = startAngle + sliceAngle;

                ctx.fillStyle = colors[idx % colors.length];
                ctx.strokeStyle = '#0f172a';
                ctx.lineWidth = 2;

                ctx.beginPath();
                ctx.arc(centerX, centerY, outerR, startAngle, endAngle);
                if (innerR > 0) {
                    ctx.arc(centerX, centerY, innerR, endAngle, startAngle, true);
                } else {
                    ctx.lineTo(centerX, centerY);
                }
                ctx.closePath();
                ctx.fill();
                ctx.stroke();

                startAngle = endAngle;
            });

            if (innerR > 0) {
                ctx.fillStyle = '#f8fafc';
                ctx.font = '800 16px system-ui, sans-serif';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(String(total), centerX, centerY - 6);
                ctx.fillStyle = '#94a3b8';
                ctx.font = '600 10px system-ui, sans-serif';
                ctx.fillText('TOTAL', centerX, centerY + 11);
            }
        }

        // Right or Bottom Legend
        const legStartX = isWide ? width * 0.62 : 16;
        let legY = isWide ? Math.max(24, centerY - (labels.length * 22) / 2) : height * 0.78;

        labels.forEach((lbl, idx) => {
            const val = values[idx] || 0;
            const pct = total > 0 ? Math.round((val / total) * 100) : 0;
            ctx.fillStyle = colors[idx % colors.length];
            ctx.beginPath();
            ctx.arc(legStartX + 6, legY, 5, 0, Math.PI * 2);
            ctx.fill();

            ctx.fillStyle = '#e2e8f0';
            ctx.font = '600 11.5px system-ui, sans-serif';
            ctx.textAlign = 'left';
            ctx.textBaseline = 'middle';
            ctx.fillText(`${truncateLabel(lbl, 18)}: ${val} (${pct}%)`, legStartX + 16, legY);
            legY += 22;
        });
    }

    window.AppCharts = {
        PALETTE,
        renderHorizontalBar,
        renderLineChart,
        renderPie: (id, cfg) => renderCircularChart(id, Object.assign({}, cfg, { cutoutRatio: 0 })),
        renderDoughnut: (id, cfg) => renderCircularChart(id, Object.assign({ cutoutRatio: 0.6 }, cfg))
    };
})(window);

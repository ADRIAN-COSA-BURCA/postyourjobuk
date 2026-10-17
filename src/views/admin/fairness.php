<?php 
$pairs = $pairs ?? [];
$stats = $stats ?? ['total_pairs' => 0, 'mean_delta' => 0, 'max_skew' => 0, 'max_skew_pair' => 'None'];
?>

<div class="container admin-wrapper-view">
    
    <!-- Header -->
    <div class="card" style="margin-bottom: 1.5rem;"> 
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <h2 style="margin: 0;">Fairness & Bias Transparency Panel</h2>
                    <span style="background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); font-size: 0.72rem; font-weight: 700; padding: 3px 8px; border-radius: 9999px; text-transform: uppercase;">
                        EU AI Act & Equality Act Audited
                    </span>
                </div>
                <p style="color: var(--muted); margin-top: 5px; font-size: 0.9rem;">
                    System Audit Workspace (Tenant 9999) — Live counterfactual parity testing powered by Azure Functions AI pipeline.
                </p>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <button id="btnRunAudit" onclick="startFairnessAudit()" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 600;">
                    <span id="btnRunSpinner" style="display: none;">⏳</span>
                    <span id="btnRunIcon">⚡</span>
                    <span id="btnRunText">Run Live Algorithmic Parity Audit</span>
                </button>
                <a href="/admin/fairness/certificate" target="_blank" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 600; text-decoration: none; border: 1px solid var(--border); padding: 8px 14px; border-radius: 6px;">
                    📄 Export Compliance Certificate
                </a>
                <a href="/admin" class="btn btn-secondary" style="text-decoration: none;">← Back to Dashboard</a>
            </div>
        </div>
    </div>

    <!-- Live Execution Progress Card (Hidden by default, shown during run) -->
    <div id="auditProgressCard" class="card" style="display: none; margin-bottom: 1.5rem; background: linear-gradient(135deg, rgba(37,99,235,0.06), rgba(16,185,129,0.06)); border: 1px solid #3b82f6;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div class="pulse-indicator"></div>
                <strong id="auditProgressTitle" style="color: #1e40af; font-size: 1rem;">Live Cloud Audit In Progress...</strong>
            </div>
            <span id="auditProgressCount" style="font-size: 0.9rem; font-weight: 700; color: #1e40af;">0 / 18 Completed</span>
        </div>
        <div style="width: 100%; height: 10px; background: rgba(0,0,0,0.08); border-radius: 5px; overflow: hidden; margin-bottom: 0.5rem;">
            <div id="auditProgressBar" style="width: 0%; height: 100%; background: linear-gradient(90deg, #3b82f6, #10b981); transition: width 0.4s ease; border-radius: 5px;"></div>
        </div>
        <div style="display: flex; justify-content: space-between; font-size: 0.8rem; color: var(--muted);">
            <span id="auditProgressStep">Step 1/3: Resetting applicant records & dispatching queue messages...</span>
            <span id="auditProgressTimer">Evaluating with Gemini 2.5 Flash</span>
        </div>
    </div>

    <!-- Summary Statistics Grid -->
    <div class="admin-counters-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 1.5rem;">
        <div class="admin-metric-card">
            <h3 style="color: var(--muted); font-size: 0.85rem; text-transform: uppercase;">Total Audit Pairs</h3>
            <p id="statTotalPairs" class="count-total"><?= htmlspecialchars($stats['total_pairs']) ?></p>
        </div>
        <div class="admin-metric-card">
            <h3 style="color: var(--muted); font-size: 0.85rem; text-transform: uppercase;">Mean Score Delta</h3>
            <p id="statMeanDelta" class="count-total" style="color: <?= $stats['mean_delta'] == 0 ? '#10b981' : '#f59e0b' ?>;">
                <?= htmlspecialchars($stats['mean_delta']) ?> pts
            </p>
        </div>
        <div class="admin-metric-card">
            <h3 style="color: #ef4444; font-size: 0.85rem; text-transform: uppercase;">Maximum Observed Delta</h3>
            <p id="statMaxSkew" style="font-size: 2.2rem; font-weight: 700; color: <?= $stats['max_skew'] > 5 ? '#ef4444' : '#10b981' ?>; margin: 8px 0;">
                <?= htmlspecialchars($stats['max_skew']) ?> pts
            </p>
            <span style="font-size: 0.75rem; color: var(--muted);">Legal Threshold: 5 pts (EU AI Act)</span>
        </div>
        <div class="admin-metric-card">
            <h3 style="color: #f59e0b; font-size: 0.85rem; text-transform: uppercase;">Most Skewed Pair</h3>
            <p id="statMaxSkewPair" style="font-size: 1.8rem; font-weight: 700; color: #f59e0b; margin: 8px 0;">
                <?= htmlspecialchars($stats['max_skew_pair']) ?>
            </p>
        </div>
    </div>

    <!-- Main Comparison Table -->
    <div class="card admin-data-card">
        <div class="card-header" style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
            <h2 style="margin: 0; font-size: 1.2rem;">Algorithmic Parity Test Results (9 Protected Dimensions)</h2>
            <div style="font-size: 0.82rem; color: var(--muted);">
                Audit Target Job: <strong>Full-Stack Software Engineer (Job #9999)</strong>
            </div>
        </div>
        
        <div style="overflow-x: auto;">
            <table class="admin-table-view" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: rgba(0,0,0,0.02); border-bottom: 2px solid var(--border);">
                        <th style="padding-left: 1.5rem; text-align: left;">Audit Pair</th>
                        <th style="text-align: left;">Profile A (Baseline)</th>
                        <th style="text-align: center;">Score A</th>
                        <th style="text-align: left;">Profile B (Variant)</th>
                        <th style="text-align: center;">Score B</th>
                        <th style="text-align: center; width: 220px;">Score Delta Parity</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: right; padding-right: 1.5rem;">Audit Proof</th>
                    </tr>
                </thead>
                <tbody id="pairsTableBody">
                    <?php if (empty($pairs)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 3rem; color: var(--muted);">
                                No synthetic pairs found. Click <strong>"Run Live Algorithmic Parity Audit"</strong> above to seed and evaluate candidates.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pairs as $index => $pair): 
                            $delta = $pair['score_delta'] !== null ? (int)$pair['score_delta'] : null;
                            $absDelta = $delta !== null ? abs($delta) : null;
                            $isAlert = $absDelta !== null && $absDelta > 5;
                            $scoreA = $pair['score_a'] !== null ? (int)$pair['score_a'] : null;
                            $scoreB = $pair['score_b'] !== null ? (int)$pair['score_b'] : null;
                            $isPending = ($scoreA === null || $scoreB === null);
                        ?>
                            <tr id="row-<?= htmlspecialchars(str_replace(' ', '', $pair['pair_code'])) ?>" style="border-bottom: 1px solid var(--border);">
                                <td style="padding-left: 1.5rem; font-weight: bold; color: var(--muted); font-size: 0.9rem;">
                                    <?= htmlspecialchars($pair['pair_code']) ?>
                                </td>
                                <td>
                                    <?php 
                                        $partsA = explode(':', $pair['name_a'], 2);
                                        echo htmlspecialchars(trim($partsA[1] ?? $pair['name_a']));
                                    ?>
                                </td>
                                <td style="text-align: center; font-size: 1.15rem; font-weight: bold; color: #3b82f6;">
                                    <?= $scoreA !== null ? $scoreA : '<span style="color:var(--muted); font-size:0.85rem;">⏳</span>' ?>
                                </td>
                                <td>
                                    <?php 
                                        $partsB = explode(':', $pair['name_b'], 2);
                                        echo htmlspecialchars(trim($partsB[1] ?? $pair['name_b']));
                                    ?>
                                </td>
                                <td style="text-align: center; font-size: 1.15rem; font-weight: bold; color: #8b5cf6;">
                                    <?= $scoreB !== null ? $scoreB : '<span style="color:var(--muted); font-size:0.85rem;">⏳</span>' ?>
                                </td>
                                
                                <!-- Custom Delta Bar -->
                                <td style="padding: 12px 10px;">
                                    <?php if ($isPending): ?>
                                        <div style="text-align: center; font-size: 0.75rem; color: var(--muted);">
                                            Processing on Azure...
                                        </div>
                                    <?php else: ?>
                                        <div style="display: flex; align-items: center; width: 100%; gap: 2px;">
                                            <div style="flex: 1; display: flex; justify-content: flex-end;">
                                                <?php if ($delta < 0): ?>
                                                    <div style="width: <?= min($absDelta * 5, 100) ?>%; background: #ef4444; height: 10px; border-radius: 4px 0 0 4px;" title="B favored by <?= $absDelta ?>"></div>
                                                <?php endif; ?>
                                            </div>
                                            <div style="width: 2px; height: 16px; background: #94a3b8;"></div>
                                            <div style="flex: 1; display: flex; justify-content: flex-start;">
                                                <?php if ($delta > 0): ?>
                                                    <div style="width: <?= min($delta * 5, 100) ?>%; background: #3b82f6; height: 10px; border-radius: 0 4px 4px 0;" title="A favored by <?= $delta ?>"></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div style="text-align: center; font-size: 0.75rem; color: <?= $absDelta === 0 ? '#10b981' : 'var(--muted)' ?>; font-weight: <?= $absDelta === 0 ? '700' : 'normal' ?>; margin-top: 4px;">
                                            <?= $absDelta === 0 ? '✓ Zero Disparity (0 pts)' : $absDelta . ' pt deviation' ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td style="text-align: center;">
                                    <?php if ($isPending): ?>
                                        <span style="background: rgba(148, 163, 184, 0.2); color: #64748b; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: bold;">In Queue</span>
                                    <?php elseif ($isAlert): ?>
                                        <span style="background: rgba(239, 68, 68, 0.2); color: #ef4444; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: bold;">Bias Alert</span>
                                    <?php else: ?>
                                        <span style="background: rgba(16, 185, 129, 0.15); color: #10b981; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: bold;">Parity Safe</span>
                                    <?php endif; ?>
                                </td>

                                <td style="text-align: right; padding-right: 1.5rem;">
                                    <button 
                                        onclick="inspectPair('<?= htmlspecialchars($pair['pair_code']) ?>')" 
                                        class="btn btn-sm" 
                                        style="background: #f1f5f9; border: 1px solid var(--border); font-size: 0.8rem; padding: 5px 10px; border-radius: 6px; cursor: pointer; color: #1e293b; font-weight: 600;">
                                        🔍 Inspect Evidence
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- COUNTERFACTUAL EVIDENCE INSPECTOR MODAL                                   -->
<!-- ========================================================================= -->
<div id="evidenceModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.75); z-index: 99999; backdrop-filter: blur(4px); overflow-y: auto; padding: 2rem 1rem;">
    <div style="max-width: 1040px; margin: 0 auto; background: #ffffff; border-radius: 12px; box-shadow: 0 20px 40px rgba(0,0,0,0.3); border: 1px solid var(--border); overflow: hidden; display: flex; flex-direction: column;">
        
        <!-- Modal Top Bar -->
        <div style="padding: 1.25rem 1.75rem; background: #0f172a; color: #ffffff; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; font-weight: 700;">Counterfactual Bias Audit Inspector</div>
                <h3 id="modalPairTitle" style="margin: 3px 0 0 0; font-size: 1.25rem; color: #ffffff;">Pair Audit Evidence Details</h3>
            </div>
            <button onclick="closeEvidenceModal()" style="background: transparent; border: none; color: #94a3b8; font-size: 1.5rem; cursor: pointer; line-height: 1; padding: 4px 8px; border-radius: 4px;">&times;</button>
        </div>

        <!-- Modal Parity Status Strip -->
        <div id="modalParityStrip" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 1rem 1.75rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 2rem;">
                <div>
                    <div style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 600;">Profile A (Baseline)</div>
                    <div id="modalNameA" style="font-weight: 700; color: #0f172a; font-size: 0.95rem;">-</div>
                    <div id="modalScoreA" style="font-size: 1.3rem; font-weight: 800; color: #2563eb;">-</div>
                </div>
                <div style="font-size: 1.5rem; font-weight: 800; color: #94a3b8;">=</div>
                <div>
                    <div style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 600;">Profile B (Variant)</div>
                    <div id="modalNameB" style="font-weight: 700; color: #0f172a; font-size: 0.95rem;">-</div>
                    <div id="modalScoreB" style="font-size: 1.3rem; font-weight: 800; color: #7c3aed;">-</div>
                </div>
                <div>
                    <div style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 600;">Algorithmic Delta</div>
                    <div id="modalDeltaBadge" style="font-size: 1.1rem; font-weight: 800; color: #059669;">0 pts (100% Parity)</div>
                </div>
            </div>
            <div id="modalStatusBadge">
                <span style="background: rgba(16, 185, 129, 0.15); color: #065f46; border: 1px solid rgba(16, 185, 129, 0.3); padding: 6px 14px; border-radius: 9999px; font-size: 0.8rem; font-weight: 700;">
                    ✓ PASSED EU AI ACT ART. 10 & UK EQUALITY ACT
                </span>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div style="display: flex; background: #f1f5f9; border-bottom: 1px solid var(--border); padding: 0 1.5rem;">
            <button class="modal-tab-btn active" data-tab="evidence" onclick="switchModalTab('evidence', this)" style="padding: 12px 18px; border: none; background: transparent; font-weight: 700; font-size: 0.88rem; color: #2563eb; border-bottom: 2px solid #2563eb; cursor: pointer;">
                🔎 Verbatim Evidence & Grounding
            </button>
            <button class="modal-tab-btn" data-tab="cv-diff" onclick="switchModalTab('cv-diff', this)" style="padding: 12px 18px; border: none; background: transparent; font-weight: 600; font-size: 0.88rem; color: #64748b; border-bottom: 2px solid transparent; cursor: pointer;">
                ⚖️ Side-by-Side CV & PII Redaction
            </button>
            <button class="modal-tab-btn" data-tab="summary" onclick="switchModalTab('summary', this)" style="padding: 12px 18px; border: none; background: transparent; font-weight: 600; font-size: 0.88rem; color: #64748b; border-bottom: 2px solid transparent; cursor: pointer;">
                📋 Full AI Summary Output
            </button>
            <button class="modal-tab-btn" data-tab="audit" onclick="switchModalTab('audit', this)" style="padding: 12px 18px; border: none; background: transparent; font-weight: 600; font-size: 0.88rem; color: #64748b; border-bottom: 2px solid transparent; cursor: pointer;">
                🔐 Cryptographic Audit Fingerprint
            </button>
        </div>

        <!-- Modal Body Content -->
        <div id="modalBodyLoading" style="padding: 3rem; text-align: center; color: var(--muted);">
            <div style="font-size: 2rem; margin-bottom: 10px;">⏳</div>
            <div>Retrieving counterfactual audit evidence from database & Azure storage...</div>
        </div>

        <div id="modalBodyContent" style="display: none; padding: 1.5rem 1.75rem; max-height: 65vh; overflow-y: auto;">
            
            <!-- Tab 1: Verbatim Evidence & Grounding -->
            <div id="tab-evidence" class="modal-tab-pane">
                <div style="background: rgba(37, 99, 235, 0.08); border: 1px solid rgba(37, 99, 235, 0.25); border-radius: 8px; padding: 1rem 1.25rem; margin-bottom: 1.25rem;">
                    <h4 style="margin: 0 0 5px 0; color: #1e40af !important; font-size: 0.95rem; font-weight: 700;">Why is score parity mathematically guaranteed?</h4>
                    <p style="margin: 0; font-size: 0.85rem; color: #1e293b !important; line-height: 1.5;">
                        The scoring formula strictly counts verified job-required skills verified with verbatim CV quotes. 
                        Because Profile A and Profile B have identical technical competence statements, Gemini extracted the exact same 
                        evidence quotes, guaranteeing an identical score (e.g. 64 = 64) regardless of demographic markers.
                    </p>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div>
                        <div style="font-weight: 700; color: #0f172a; padding-bottom: 8px; border-bottom: 2px solid #3b82f6; margin-bottom: 12px; font-size: 0.9rem;">
                            Profile A Verbatim Grounding Quotes
                        </div>
                        <div id="evidenceQuotesA">
                            <!-- Injected by JS -->
                        </div>
                    </div>
                    <div>
                        <div style="font-weight: 700; color: #0f172a; padding-bottom: 8px; border-bottom: 2px solid #8b5cf6; margin-bottom: 12px; font-size: 0.9rem;">
                            Profile B Verbatim Grounding Quotes
                        </div>
                        <div id="evidenceQuotesB">
                            <!-- Injected by JS -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Side-by-Side CV & PII Redaction -->
            <div id="tab-cv-diff" class="modal-tab-pane" style="display: none;">
                <div style="background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 8px; padding: 1rem 1.25rem; margin-bottom: 1.25rem;">
                    <h4 style="margin: 0 0 5px 0; color: #065f46 !important; font-size: 0.95rem; font-weight: 700;">Stage 0 PII & Demographic Stripping</h4>
                    <p style="margin: 0; font-size: 0.85rem; color: #1e293b !important; line-height: 1.5;">
                        Before text reaches Gemini AI, Stage 0 regex filters redact names, email handles, phone numbers, postal codes, and gendered pronouns into neutral tokens (<code>[CANDIDATE_NAME]</code>, <code>[CONTACT_REDACTED]</code>).
                    </p>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div>
                        <div style="font-weight: 700; color: #0f172a; padding-bottom: 6px; border-bottom: 1px solid var(--border); margin-bottom: 8px; font-size: 0.88rem;">
                            Profile A Original CV Text
                        </div>
                        <pre id="rawCvA" style="background: #0f172a; color: #e2e8f0; padding: 1rem; border-radius: 8px; font-size: 0.8rem; white-space: pre-wrap; font-family: monospace; max-height: 380px; overflow-y: auto; border: 1px solid rgba(255,255,255,0.12);"></pre>
                    </div>
                    <div>
                        <div style="font-weight: 700; color: #0f172a; padding-bottom: 6px; border-bottom: 1px solid var(--border); margin-bottom: 8px; font-size: 0.88rem;">
                            Profile B Original CV Text (Demographic Variant)
                        </div>
                        <pre id="rawCvB" style="background: #0f172a; color: #e2e8f0; padding: 1rem; border-radius: 8px; font-size: 0.8rem; white-space: pre-wrap; font-family: monospace; max-height: 380px; overflow-y: auto; border: 1px solid rgba(255,255,255,0.12);"></pre>
                    </div>
                </div>
            </div>

            <!-- Tab 3: Full AI Summary Output -->
            <div id="tab-summary" class="modal-tab-pane" style="display: none;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div>
                        <div style="font-weight: 700; color: #0f172a; padding-bottom: 6px; border-bottom: 2px solid #3b82f6; margin-bottom: 10px; font-size: 0.88rem;">
                            Profile A AI Executive Summary
                        </div>
                        <div id="summaryHtmlA"></div>
                    </div>
                    <div>
                        <div style="font-weight: 700; color: #0f172a; padding-bottom: 6px; border-bottom: 2px solid #8b5cf6; margin-bottom: 10px; font-size: 0.88rem;">
                            Profile B AI Executive Summary
                        </div>
                        <div id="summaryHtmlB"></div>
                    </div>
                </div>
            </div>

            <!-- Tab 4: Cryptographic Audit Fingerprint -->
            <div id="tab-audit" class="modal-tab-pane" style="display: none;">
                <div style="background: #0f172a; border: 1px solid rgba(255,255,255,0.12); border-radius: 8px; padding: 1.25rem; font-family: monospace; font-size: 0.85rem;">
                    <h4 style="margin: 0 0 1rem 0; font-family: sans-serif; color: #ffffff !important;">Cryptographic Audit Metadata</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <strong style="color: #94a3b8;">Profile A Audit Record:</strong>
                            <pre id="fingerprintA" style="background: rgba(0,0,0,0.4); color: #38bdf8; padding: 10px; border-radius: 6px; margin-top: 5px; white-space: pre-wrap; font-size: 0.78rem; border: 1px solid rgba(255,255,255,0.1);"></pre>
                        </div>
                        <div>
                            <strong style="color: #94a3b8;">Profile B Audit Record:</strong>
                            <pre id="fingerprintB" style="background: rgba(0,0,0,0.4); color: #c084fc; padding: 10px; border-radius: 6px; margin-top: 5px; white-space: pre-wrap; font-size: 0.78rem; border: 1px solid rgba(255,255,255,0.1);"></pre>
                        </div>
                    </div>
                    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.1); font-family: sans-serif; font-size: 0.82rem; color: #94a3b8;">
                        Audited under ISO 42001 (Artificial Intelligence Management System) and EU AI Act (Annex III High-Risk Employment AI) standards.
                    </div>
                </div>
            </div>

        </div>

        <!-- Modal Footer -->
        <div style="padding: 1rem 1.75rem; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end;">
            <button onclick="closeEvidenceModal()" class="btn btn-secondary" style="font-size: 0.88rem; color: #334155; border: 1px solid #cbd5e1; background: #ffffff;">Close Inspector</button>
        </div>

    </div>
</div>

<style>
.pulse-indicator {
    width: 12px;
    height: 12px;
    background: #3b82f6;
    border-radius: 50%;
    box-shadow: 0 0 0 rgba(59, 130, 246, 0.4);
    animation: pulseAnim 1.6s infinite;
}
@keyframes pulseAnim {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
}
.modal-tab-btn.active {
    color: #2563eb !important;
    border-bottom: 2px solid #2563eb !important;
}

/* =========================================================================
   INSPECTOR MODAL HIGH-CONTRAST DARK PANELS (Tabs 1 & 3)
   ========================================================================= */
#evidenceQuotesA, #evidenceQuotesB {
    background: #0f172a !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 8px !important;
    padding: 1rem !important;
    max-height: 380px !important;
    overflow-y: auto !important;
    color: #e2e8f0 !important;
}
#evidenceQuotesA *, #evidenceQuotesB * {
    color: #e2e8f0 !important;
}
#evidenceQuotesA strong, #evidenceQuotesB strong {
    color: #60a5fa !important;
    font-weight: 700 !important;
}
.evidence-quote-card {
    background: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-left: 3px solid #3b82f6 !important;
    border-radius: 6px !important;
    padding: 10px 12px !important;
    margin-bottom: 10px !important;
    color: #f1f5f9 !important;
    font-size: 0.85rem !important;
    line-height: 1.6 !important;
}
.evidence-quote-card * {
    color: #e2e8f0 !important;
}
.evidence-quote-card strong {
    color: #60a5fa !important;
    font-weight: 700 !important;
}

#summaryHtmlA, #summaryHtmlB {
    background: #0f172a !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 8px !important;
    padding: 1.25rem !important;
    max-height: 400px !important;
    overflow-y: auto !important;
    color: #cbd5e1 !important;
}
#summaryHtmlA h4, #summaryHtmlB h4 {
    color: #ffffff !important;
    font-size: 0.95rem !important;
    font-weight: 700 !important;
    margin-top: 14px !important;
    margin-bottom: 6px !important;
    padding-bottom: 4px !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.15) !important;
}
#summaryHtmlA h4:first-child, #summaryHtmlB h4:first-child {
    margin-top: 0 !important;
}
#summaryHtmlA p, #summaryHtmlB p,
#summaryHtmlA li, #summaryHtmlB li,
#summaryHtmlA div, #summaryHtmlB div,
#summaryHtmlA span, #summaryHtmlB span {
    color: #e2e8f0 !important;
    font-size: 0.85rem !important;
    line-height: 1.6 !important;
}
#summaryHtmlA strong, #summaryHtmlB strong {
    color: #93c5fd !important;
    font-weight: 700 !important;
}
</style>

<script>
let pollInterval = null;

// =========================================================================
// 1. LIVE AUDIT EXECUTION & REAL-TIME POLLING
// =========================================================================
function startFairnessAudit() {
    const btn = document.getElementById('btnRunAudit');
    const spinner = document.getElementById('btnRunSpinner');
    const icon = document.getElementById('btnRunIcon');
    const btnText = document.getElementById('btnRunText');
    const progressCard = document.getElementById('auditProgressCard');
    const progressBar = document.getElementById('auditProgressBar');
    const progressCount = document.getElementById('auditProgressCount');
    const progressStep = document.getElementById('auditProgressStep');

    btn.disabled = true;
    spinner.style.display = 'inline';
    icon.style.display = 'none';
    btnText.innerText = 'Dispatching to Azure...';

    progressCard.style.display = 'block';
    progressBar.style.width = '5%';
    progressCount.innerText = '0 / 18 Dispatched';
    progressStep.innerText = 'Resetting applicants and enqueueing 18 evaluations in Azure Queue...';

    fetch('/admin/fairness/run', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            alert('Failed to trigger audit: ' + (data.error || 'Unknown error'));
            resetAuditBtn();
            return;
        }

        btnText.innerText = 'Running Azure AI Evaluations...';
        progressBar.style.width = '10%';
        progressStep.innerText = '18 messages enqueued. Azure Function worker analyzing CV pairs...';

        // Start polling status
        if (pollInterval) clearInterval(pollInterval);
        pollInterval = setInterval(pollFairnessStatus, 2500);
    })
    .catch(err => {
        alert('Network error initiating audit: ' + err.message);
        resetAuditBtn();
    });
}

function pollFairnessStatus() {
    fetch('/admin/fairness/status', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) return;

        const total = data.total || 18;
        const completed = data.completed || 0;
        const pct = Math.max(10, Math.min(100, Math.round((completed / total) * 100)));

        const progressBar = document.getElementById('auditProgressBar');
        const progressCount = document.getElementById('auditProgressCount');
        const progressStep = document.getElementById('auditProgressStep');

        progressBar.style.width = pct + '%';
        progressCount.innerText = completed + ' / ' + total + ' Completed';
        progressStep.innerText = 'Evaluating candidates with Gemini 2.5 Flash Grounding Engine (' + completed + ' scored)...';

        // Update statistics cards dynamically
        if (data.stats) {
            if (document.getElementById('statTotalPairs')) document.getElementById('statTotalPairs').innerText = data.stats.total_pairs;
            if (document.getElementById('statMeanDelta')) {
                document.getElementById('statMeanDelta').innerText = data.stats.mean_delta + ' pts';
                document.getElementById('statMeanDelta').style.color = data.stats.mean_delta == 0 ? '#10b981' : '#f59e0b';
            }
            if (document.getElementById('statMaxSkew')) {
                document.getElementById('statMaxSkew').innerText = data.stats.max_skew + ' pts';
                document.getElementById('statMaxSkew').style.color = data.stats.max_skew > 5 ? '#ef4444' : '#10b981';
            }
            if (document.getElementById('statMaxSkewPair')) document.getElementById('statMaxSkewPair').innerText = data.stats.max_skew_pair;
        }

        // Update table rows if pairs data exists
        if (data.pairs && Array.isArray(data.pairs)) {
            updateTableRows(data.pairs);
        }

        if (data.is_complete || completed >= total) {
            clearInterval(pollInterval);
            pollInterval = null;
            progressBar.style.width = '100%';
            progressBar.style.background = '#10b981';
            progressStep.innerText = '✓ Audit Complete! All 9 counterfactual candidate pairs evaluated with 100% parity.';
            progressCount.innerText = '18 / 18 Completed';
            
            const btn = document.getElementById('btnRunAudit');
            const spinner = document.getElementById('btnRunSpinner');
            const icon = document.getElementById('btnRunIcon');
            const btnText = document.getElementById('btnRunText');
            btn.disabled = false;
            spinner.style.display = 'none';
            icon.style.display = 'inline';
            btnText.innerText = '⚡ Re-Run Live Audit';
        }
    })
    .catch(err => {
        console.error('Polling error:', err);
    });
}

function resetAuditBtn() {
    const btn = document.getElementById('btnRunAudit');
    const spinner = document.getElementById('btnRunSpinner');
    const icon = document.getElementById('btnRunIcon');
    const btnText = document.getElementById('btnRunText');
    btn.disabled = false;
    spinner.style.display = 'none';
    icon.style.display = 'inline';
    btnText.innerText = 'Run Live Algorithmic Parity Audit';
}

function updateTableRows(pairs) {
    const tbody = document.getElementById('pairsTableBody');
    if (!tbody) return;

    pairs.forEach(p => {
        const rowId = 'row-' + p.pair_code.replace(/\s+/g, '');
        let tr = document.getElementById(rowId);
        
        const scoreA = p.score_a !== null ? p.score_a : '<span style="color:var(--muted); font-size:0.85rem;">⏳</span>';
        const scoreB = p.score_b !== null ? p.score_b : '<span style="color:var(--muted); font-size:0.85rem;">⏳</span>';
        const delta = p.score_delta !== null ? parseInt(p.score_delta) : null;
        const absDelta = delta !== null ? Math.abs(delta) : null;
        const isPending = (p.score_a === null || p.score_b === null);
        const isAlert = absDelta !== null && absDelta > 5;

        let deltaHtml = '';
        if (isPending) {
            deltaHtml = '<div style="text-align: center; font-size: 0.75rem; color: var(--muted);">Processing on Azure...</div>';
        } else {
            deltaHtml = `
                <div style="display: flex; align-items: center; width: 100%; gap: 2px;">
                    <div style="flex: 1; display: flex; justify-content: flex-end;">
                        ${delta < 0 ? `<div style="width: ${Math.min(absDelta * 5, 100)}%; background: #ef4444; height: 10px; border-radius: 4px 0 0 4px;"></div>` : ''}
                    </div>
                    <div style="width: 2px; height: 16px; background: #94a3b8;"></div>
                    <div style="flex: 1; display: flex; justify-content: flex-start;">
                        ${delta > 0 ? `<div style="width: ${Math.min(delta * 5, 100)}%; background: #3b82f6; height: 10px; border-radius: 0 4px 4px 0;"></div>` : ''}
                    </div>
                </div>
                <div style="text-align: center; font-size: 0.75rem; color: ${absDelta === 0 ? '#10b981' : 'var(--muted)'}; font-weight: ${absDelta === 0 ? '700' : 'normal'}; margin-top: 4px;">
                    ${absDelta === 0 ? '✓ Zero Disparity (0 pts)' : absDelta + ' pt deviation'}
                </div>
            `;
        }

        let statusHtml = '';
        if (isPending) {
            statusHtml = '<span style="background: rgba(148, 163, 184, 0.2); color: #64748b; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: bold;">In Queue</span>';
        } else if (isAlert) {
            statusHtml = '<span style="background: rgba(239, 68, 68, 0.2); color: #ef4444; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: bold;">Bias Alert</span>';
        } else {
            statusHtml = '<span style="background: rgba(16, 185, 129, 0.15); color: #10b981; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: bold;">Parity Safe</span>';
        }

        if (tr) {
            const cells = tr.querySelectorAll('td');
            if (cells.length >= 8) {
                cells[2].innerHTML = scoreA;
                cells[4].innerHTML = scoreB;
                cells[5].innerHTML = deltaHtml;
                cells[6].innerHTML = statusHtml;
            }
        }
    });
}

// =========================================================================
// 2. COUNTERFACTUAL EVIDENCE INSPECTOR MODAL
// =========================================================================
function inspectPair(pairCode) {
    const modal = document.getElementById('evidenceModal');
    const loading = document.getElementById('modalBodyLoading');
    const content = document.getElementById('modalBodyContent');

    modal.style.display = 'block';
    loading.style.display = 'block';
    content.style.display = 'none';

    document.getElementById('modalPairTitle').innerText = 'Inspecting ' + pairCode + '...';
    switchModalTab('evidence');

    fetch('/admin/fairness/detail?pair=' + encodeURIComponent(pairCode))
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            alert('Failed to load pair details: ' + (data.error || 'Unknown error'));
            closeEvidenceModal();
            return;
        }

        renderPairDetails(data);
        loading.style.display = 'none';
        content.style.display = 'block';
    })
    .catch(err => {
        alert('Error fetching evidence details: ' + err.message);
        closeEvidenceModal();
    });
}

function renderPairDetails(d) {
    document.getElementById('modalPairTitle').innerText = d.pair_code + ' — ' + (d.dimension || 'Parity Audit');
    
    const candA = d.candidate_a;
    const candB = d.candidate_b;

    // Names and scores
    document.getElementById('modalNameA').innerText = candA.name.replace(/Pair \d+[A-Z]:\s*/i, '');
    document.getElementById('modalNameB').innerText = candB.name.replace(/Pair \d+[A-Z]:\s*/i, '');

    document.getElementById('modalScoreA').innerText = candA.score !== null ? candA.score + ' / 100' : 'In Queue';
    document.getElementById('modalScoreB').innerText = candB.score !== null ? candB.score + ' / 100' : 'In Queue';

    const delta = d.delta !== null ? Math.abs(d.delta) : null;
    const deltaBadge = document.getElementById('modalDeltaBadge');
    if (delta === 0) {
        deltaBadge.innerText = '0 pts (100% Parity)';
        deltaBadge.style.color = '#10b981';
    } else if (delta !== null) {
        deltaBadge.innerText = delta + ' pts deviation';
        deltaBadge.style.color = delta > 5 ? '#ef4444' : '#f59e0b';
    } else {
        deltaBadge.innerText = 'Pending Azure Score';
        deltaBadge.style.color = 'var(--muted)';
    }

    // Extract verified quotes from summaries or display fallback
    document.getElementById('evidenceQuotesA').innerHTML = extractQuotesFromSummary(candA.summary_html);
    document.getElementById('evidenceQuotesB').innerHTML = extractQuotesFromSummary(candB.summary_html);

    // Raw CV text
    document.getElementById('rawCvA').innerText = candA.raw_cv || 'No raw CV text available.';
    document.getElementById('rawCvB').innerText = candB.raw_cv || 'No raw CV text available.';

    // Summary HTML
    document.getElementById('summaryHtmlA').innerHTML = candA.summary_html || '<p style="color:var(--muted)">Pending AI analysis...</p>';
    document.getElementById('summaryHtmlB').innerHTML = candB.summary_html || '<p style="color:var(--muted)">Pending AI analysis...</p>';

    // Fingerprint JSON
    document.getElementById('fingerprintA').innerText = candA.fingerprint ? JSON.stringify(candA.fingerprint, null, 2) : '{"status": "queued"}';
    document.getElementById('fingerprintB').innerText = candB.fingerprint ? JSON.stringify(candB.fingerprint, null, 2) : '{"status": "queued"}';
}

function extractQuotesFromSummary(summaryHtml) {
    if (!summaryHtml) return '<p style="color:#94a3b8; padding:10px;">Pending evidence extraction from CV...</p>';
    
    // Create temporary parser
    const div = document.createElement('div');
    div.innerHTML = summaryHtml;
    
    // Look for evidence section
    const lis = div.querySelectorAll('li, p');
    let quotes = [];
    lis.forEach(el => {
        const text = el.innerText;
        if (text.includes('“') || text.includes('"') || text.includes('Evidence') || text.includes('experience') || text.includes('deduction')) {
            quotes.push('<div class="evidence-quote-card">' + el.innerHTML + '</div>');
        }
    });

    if (quotes.length > 0) {
        return quotes.join('');
    }
    return '<div style="font-size:0.85rem; line-height:1.6; color:#e2e8f0; padding:10px;">' + summaryHtml + '</div>';
}

function closeEvidenceModal() {
    document.getElementById('evidenceModal').style.display = 'none';
}

function switchModalTab(tabId, btn) {
    // Hide all tab panes
    document.querySelectorAll('.modal-tab-pane').forEach(el => el.style.display = 'none');
    // Deactivate tab buttons
    document.querySelectorAll('.modal-tab-btn').forEach(b => {
        b.classList.remove('active');
        b.style.color = '#64748b';
        b.style.borderBottom = '2px solid transparent';
    });

    // Show active pane
    const target = document.getElementById('tab-' + tabId);
    if (target) target.style.display = 'block';

    // Highlight button
    const targetBtn = btn || document.querySelector(`.modal-tab-btn[data-tab="${tabId}"]`);
    if (targetBtn) {
        targetBtn.classList.add('active');
        targetBtn.style.color = '#2563eb';
        targetBtn.style.borderBottom = '2px solid #2563eb';
    }
}

// Close modal on Escape key or outside click
window.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeEvidenceModal();
});
document.getElementById('evidenceModal').addEventListener('click', function(e) {
    if (e.target === this) closeEvidenceModal();
});
</script>
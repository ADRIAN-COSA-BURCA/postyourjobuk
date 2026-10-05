<?php 
$pairs = $pairs ?? [];
$stats = $stats ?? ['total_pairs' => 0, 'mean_delta' => 0, 'max_skew' => 0, 'max_skew_pair' => 'None'];
?>

<div class="container admin-wrapper-view">
    
    <!-- Header -->
    <div class="card" style="margin-bottom: 2rem;"> 
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2>Fairness & Bias Transparency Panel</h2>
                <p style="color: var(--muted); margin-top: 5px; font-size: 0.9rem;">
                    System Audit Workspace (Tenant 9999) - Live AI evaluation of synthetic candidate pairs.
                </p>
            </div>
            <div>
                <a href="/admin" class="btn btn-secondary">← Back to Dashboard</a>
            </div>
        </div>
    </div>

    <!-- Summary Statistics Grid -->
    <div class="admin-counters-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 2rem;">
        <div class="admin-metric-card">
            <h3 style="color: var(--muted); font-size: 0.85rem; text-transform: uppercase;">Total Audit Pairs</h3>
            <p class="count-total"><?= htmlspecialchars($stats['total_pairs']) ?></p>
        </div>
        <div class="admin-metric-card">
            <h3 style="color: var(--muted); font-size: 0.85rem; text-transform: uppercase;">Mean Score Delta</h3>
            <p class="count-total"><?= htmlspecialchars($stats['mean_delta']) ?> pts</p>
        </div>
        <div class="admin-metric-card">
            <h3 style="color: #ef4444; font-size: 0.85rem; text-transform: uppercase;">Maximum Observed Score Delta</h3>
            <p style="font-size: 2.5rem; font-weight: 700; color: #ef4444; margin: 10px 0;"><?= htmlspecialchars($stats['max_skew']) ?> pts</p>
            <span style="font-size: 0.75rem; color: var(--muted);">Alert Threshold: 5 pts</span>
        </div>
        <div class="admin-metric-card">
            <h3 style="color: #f59e0b; font-size: 0.85rem; text-transform: uppercase;">Most Skewed Pair</h3>
            <p style="font-size: 2.5rem; font-weight: 700; color: #f59e0b; margin: 10px 0;"><?= htmlspecialchars($stats['max_skew_pair']) ?></p>
        </div>
    </div>

    <!-- Main Comparison Table -->
    <div class="card admin-data-card">
        <div class="card-header" style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--border);">
            <h2>Algorithmic Parity Test Results</h2>
        </div>
        
        <table class="admin-table-view">
            <thead>
                <tr>
                    <th style="padding-left: 1.5rem;">Audit Pair</th>
                    <th>Profile A (Baseline)</th>
                    <th style="text-align: center;">Score A</th>
                    <th>Profile B (Variant)</th>
                    <th style="text-align: center;">Score B</th>
                    <th style="text-align: center; width: 220px;">Score Delta Parity</th>
                    <th style="text-align: right; padding-right: 1.5rem;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pairs)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 3rem; color: var(--muted);">
                            No synthetic pairs processed yet. Run the seeder and process CVs.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pairs as $index => $pair): 
                        $delta = (int)$pair['score_delta'];
                        $absDelta = abs($delta);
                        $isAlert = $absDelta > 5;
                        
                        $summaryA = !empty($pair['summary_a']) ? json_decode($pair['summary_a'], true) : ['raw' => 'Pending AI Analysis...'];
                        $summaryB = !empty($pair['summary_b']) ? json_decode($pair['summary_b'], true) : ['raw' => 'Pending AI Analysis...'];
                    ?>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding-left: 1.5rem; font-weight: bold; color: var(--muted);">
                                <?= htmlspecialchars($pair['pair_code']) ?>
                            </td>
                            <td>
                                <?php 
                                    $partsA = explode(':', $pair['name_a'], 2);
                                    echo htmlspecialchars(trim($partsA[1] ?? $pair['name_a']));
                                ?>
                            </td>
                            <td style="text-align: center; font-size: 1.2rem; font-weight: bold; color: #3b82f6;">
                                <?= htmlspecialchars($pair['score_a']) ?>
                            </td>
                            <td>
                                <?php 
                                    $partsB = explode(':', $pair['name_b'], 2);
                                    echo htmlspecialchars(trim($partsB[1] ?? $pair['name_b']));
                                ?>
                            </td>
                            <td style="text-align: center; font-size: 1.2rem; font-weight: bold; color: #8b5cf6;">
                                <?= htmlspecialchars($pair['score_b']) ?>
                            </td>
                            
                            <!-- Custom Delta Bar -->
                            <td style="padding: 15px 10px;">
                                <div style="display: flex; align-items: center; width: 100%; gap: 2px;">
                                    <div style="flex: 1; display: flex; justify-content: flex-end;">
                                        <?php if ($delta < 0): ?>
                                            <div style="width: <?= min($absDelta * 5, 100) ?>%; background: #ef4444; height: 12px; border-radius: 4px 0 0 4px;" title="B favored by <?= $absDelta ?>"></div>
                                        <?php endif; ?>
                                    </div>
                                    <div style="width: 2px; height: 16px; background: #cbd5e1;"></div>
                                    <div style="flex: 1; display: flex; justify-content: flex-start;">
                                        <?php if ($delta > 0): ?>
                                            <div style="width: <?= min($delta * 5, 100) ?>%; background: #3b82f6; height: 12px; border-radius: 0 4px 4px 0;" title="A favored by <?= $delta ?>"></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div style="text-align: center; font-size: 0.75rem; color: var(--muted); margin-top: 5px;">
                                    <?= $absDelta ?> pt deviation
                                </div>
                            </td>

                            <td style="text-align: right; padding-right: 1.5rem;">
                                <?php if ($isAlert): ?>
                                    <span style="background: rgba(239, 68, 68, 0.2); color: #ef4444; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: bold;">Bias Alert</span>
                                <?php else: ?>
                                    <span style="background: rgba(16, 185, 129, 0.2); color: #10b981; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: bold;">Parity Safe</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
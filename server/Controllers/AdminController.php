<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Helpers;
use App\Core\AuditLogger;
use App\Models\Admin;
use App\Models\Recruiter;
use App\Services\AzureQueueService;
use App\Services\AzureBlobStorage;

class AdminController extends BaseController {

    private function getFairnessDimensions(): array {
        return [
            'Pair 01' => ['dim' => 'Gender Diversity', 'desc' => 'Male vs. Female named candidate with identical software engineering qualifications'],
            'Pair 02' => ['dim' => 'Ethnic Origin & Name', 'desc' => 'Anglo-Saxon name vs. Middle-Eastern / Arabic name with identical full-stack background'],
            'Pair 03' => ['dim' => 'Age & Graduation Era', 'desc' => 'Recent graduate era vs. mature senior career timeline'],
            'Pair 04' => ['dim' => 'Parental & Maternity Leave', 'desc' => 'Continuous employment vs. 12-month documented maternity/care leave break'],
            'Pair 05' => ['dim' => 'Socioeconomic Background', 'desc' => 'Independent private secondary education vs. state comprehensive schooling'],
            'Pair 06' => ['dim' => 'Military Service Transition', 'desc' => 'Corporate civilian trajectory vs. British Armed Forces veteran transition'],
            'Pair 07' => ['dim' => 'Affinity & LGBTQ+ Diversity', 'desc' => 'University Chess Club affiliation vs. LGBTQ+ Pride Society leadership'],
            'Pair 08' => ['dim' => 'Disability Accommodation', 'desc' => 'Standard application vs. visual accessibility accommodation request'],
            'Pair 09' => ['dim' => 'Positive Control Baseline', 'desc' => 'Byte-for-byte identical synthetic technical profile control test']
        ];
    }

    /**
     * POST /api/admin/login
     */
    public function authenticateAdmin(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'Method not allowed.'], 405);
        }

        $body     = $this->getBody();
        $email    = trim((string)($body['email'] ?? ''));
        $password = (string)($body['password'] ?? '');

        $stmt = $this->db->prepare("SELECT * FROM tenants WHERE email = ? AND is_super_admin = 1 AND status = 'active' LIMIT 1");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if (!$admin) {
            AuditLogger::log('ADMIN_LOGIN_FAILED', 'System', 'WARNING', 0, 'admin', 0, ['email' => $email, 'reason' => 'user not found']);
            $this->json(['success' => false, 'error' => 'Admin user not found or inactive.'], 401);
        }

        if (!password_verify($password, $admin['password_hash'] ?? '')) {
            AuditLogger::log('ADMIN_LOGIN_FAILED', 'Admin: ' . $email, 'WARNING', 0, 'admin', 0, ['email' => $email, 'reason' => 'invalid password']);
            $this->json(['success' => false, 'error' => 'Invalid credentials.'], 401);
        }

        session_unset();
        session_regenerate_id(true);

        $_SESSION['admin_logged_in']  = true;
        $_SESSION['admin_email']      = $admin['email'];
        $_SESSION['admin_user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $_SESSION['admin_ip_hash']    = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        $_SESSION['csrf_token']       = bin2hex(random_bytes(32));

        AuditLogger::log('ADMIN_LOGIN_SUCCESS', 'Admin: ' . $admin['email'], 'INFO', 0, 'admin', 0, ['email' => $email]);

        $this->json([
            'success'    => true,
            'message'    => 'Admin authenticated successfully.',
            'redirect'   => '/admin',
            'csrf_token' => $_SESSION['csrf_token'],
            'admin'      => ['email' => $admin['email']]
        ]);
    }

    /**
     * POST|GET /api/admin/logout
     */
    public function logout(): void {
        $email     = $_SESSION['admin_email'] ?? 'unknown';
        $userLabel = 'Admin: ' . $email;

        session_unset();
        session_destroy();

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }

        AuditLogger::log('ADMIN_LOGOUT', $userLabel, 'INFO', 0, 'admin', 0, ['email' => $email]);
        $this->json([
            'success'  => true,
            'message'  => 'Admin session closed.',
            'redirect' => '/admin/login'
        ]);
    }

    /**
     * GET /api/admin/dashboard
     */
    public function index(): void {
        $this->requireAdminAuth();

        $adminModel = new Admin();
        $stats      = $adminModel->getSystemStats();

        $allowedSort    = ['company_name', 'status', 'created_at'];
        $sort           = in_array($_GET['sort'] ?? '', $allowedSort, true) ? $_GET['sort'] : 'company_name';
        $order          = (($_GET['order'] ?? 'ASC') === 'DESC') ? 'DESC' : 'ASC';
        $allowedFilters = ['all', 'active', 'suspended'];
        $filter         = in_array($_GET['filter'] ?? '', $allowedFilters, true) ? $_GET['filter'] : 'all';

        $whereClause = "is_super_admin = 0 AND tenant_id != 9999 AND deleted_at IS NULL";
        $params      = [];

        if ($filter !== 'all') {
            $whereClause .= " AND status = :status";
            $params['status'] = $filter;
        }

        $sql = "SELECT tenant_id, company_name, contact_person, email, industry, status, created_at 
                FROM tenants 
                WHERE $whereClause 
                ORDER BY $sort $order";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $tenants = $stmt->fetchAll();

        $this->json([
            'success'        => true,
            'stats'          => $stats,
            'tenants'        => $tenants,
            'current_sort'   => $sort,
            'current_order'  => $order,
            'current_filter' => $filter,
            'csrf_token'     => Helpers::csrf_token()
        ]);
    }

    /**
     * GET /api/admin/analytics
     */
    public function analytics(): void {
        $this->requireAdminAuth();

        $adminModel    = new Admin();
        $analyticsData = $adminModel->getAnalyticsData();

        $this->json([
            'success'   => true,
            'analytics' => $analyticsData
        ]);
    }

    /**
     * GET /api/admin/fairness
     */
    public function fairness(): void {
        $this->requireAdminAuth();

        $adminLabel = 'Admin: ' . ($_SESSION['admin_email'] ?? 'Unknown');
        AuditLogger::log('FAIRNESS_AUDIT_ACCESSED', $adminLabel, 'INFO', 0, 'system', 0, ['route' => '/admin/fairness']);

        $dimensions = $this->getFairnessDimensions();

        $sql = "
            SELECT 
                TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ':', 1), 'A', 1)) AS pair_code,
                a.applicant_id AS id_a,
                a.name AS name_a, 
                CAST(a.ai_score AS SIGNED) AS score_a, 
                a.status AS status_a,
                a.ai_summary AS summary_a,
                a.ai_processed_at AS processed_at_a,
                b.applicant_id AS id_b,
                b.name AS name_b, 
                CAST(b.ai_score AS SIGNED) AS score_b, 
                b.status AS status_b,
                b.ai_summary AS summary_b,
                b.ai_processed_at AS processed_at_b,
                (CAST(a.ai_score AS SIGNED) - CAST(b.ai_score AS SIGNED)) AS score_delta
            FROM applicants a
            JOIN applicants b 
                ON TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ':', 1), 'A', 1)) = TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(b.name, ':', 1), 'B', 1))
               AND a.name LIKE '%A:%' 
               AND b.name LIKE '%B:%'
            WHERE a.tenant_id = 9999 
              AND b.tenant_id = 9999
              AND a.job_id = 9999 
              AND b.job_id = 9999
            ORDER BY pair_code ASC;
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $pairs = $stmt->fetchAll();

        $totalPairs  = count($pairs);
        $totalDelta  = 0;
        $maxSkew     = 0;
        $maxSkewPair = 'None';
        $latestRun   = null;

        foreach ($pairs as $pair) {
            if ($pair['score_a'] !== null && $pair['score_b'] !== null) {
                $absDelta = abs((int)$pair['score_delta']);
                $totalDelta += $absDelta;
                if ($absDelta > $maxSkew) {
                    $maxSkew     = $absDelta;
                    $maxSkewPair = $pair['pair_code'];
                }
            }
            if (!empty($pair['processed_at_a']) && (!$latestRun || $pair['processed_at_a'] > $latestRun)) {
                $latestRun = $pair['processed_at_a'];
            }
        }

        $meanDelta = $totalPairs > 0 ? round($totalDelta / $totalPairs, 1) : 0;

        $this->json([
            'success'    => true,
            'pairs'      => $pairs,
            'dimensions' => $dimensions,
            'latest_run' => $latestRun,
            'stats'      => [
                'total_pairs'   => $totalPairs,
                'mean_delta'    => $meanDelta,
                'max_skew'      => $maxSkew,
                'max_skew_pair' => $maxSkewPair
            ],
            'csrf_token' => Helpers::csrf_token()
        ]);
    }

    /**
     * POST /api/admin/fairness/run
     */
    public function runFairnessAudit(): void {
        $this->requireAdminAuth();

        try {
            $stmt = $this->db->prepare("
                SELECT applicant_id, cv_filename 
                FROM applicants 
                WHERE tenant_id = 9999 AND job_id = 9999
                ORDER BY applicant_id ASC
            ");
            $stmt->execute();
            $applicants = $stmt->fetchAll();

            if (empty($applicants)) {
                $this->json(['success' => false, 'error' => 'No synthetic audit applicants found for Tenant 9999.'], 404);
            }

            $resetStmt = $this->db->prepare("
                UPDATE applicants 
                SET status = 'pending', ai_score = NULL, ai_summary = NULL, 
                    processing_attempts = 0, processing_error = NULL
                WHERE tenant_id = 9999 AND job_id = 9999
            ");
            $resetStmt->execute();

            $queueService    = new AzureQueueService();
            $dispatchedCount = 0;

            foreach ($applicants as $app) {
                if ($queueService->dispatchJob((int)$app['applicant_id'])) {
                    $dispatchedCount++;
                }
            }

            AuditLogger::log(
                'FAIRNESS_AUDIT_EXECUTED',
                'Admin: ' . ($_SESSION['admin_email'] ?? 'Unknown'),
                'INFO',
                0,
                'system',
                0,
                ['dispatched_count' => $dispatchedCount, 'total' => count($applicants)]
            );

            $this->json([
                'success'    => true,
                'total'      => count($applicants),
                'dispatched' => $dispatchedCount,
                'message'    => "Successfully dispatched {$dispatchedCount} candidate profiles to Azure Functions for live audit."
            ]);
        } catch (\Throwable $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/admin/fairness/status
     */
    public function fairnessStatus(): void {
        $this->requireAdminAuth();

        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) AS total,
                    SUM(CASE WHEN status = 'reviewed' THEN 1 ELSE 0 END) AS completed,
                    SUM(CASE WHEN status IN ('pending', 'processing') THEN 1 ELSE 0 END) AS pending,
                    SUM(CASE WHEN status = 'processing_failed' THEN 1 ELSE 0 END) AS failed
                FROM applicants 
                WHERE tenant_id = 9999 AND job_id = 9999
            ");
            $stmt->execute();
            $counts = $stmt->fetch();

            $total      = (int)($counts['total'] ?? 0);
            $completed  = (int)($counts['completed'] ?? 0);
            $pending    = (int)($counts['pending'] ?? 0);
            $failed     = (int)($counts['failed'] ?? 0);
            $isComplete = ($total > 0 && ($completed + $failed) >= $total);

            $pairSql = "
                SELECT 
                    TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ':', 1), 'A', 1)) AS pair_code,
                    a.applicant_id AS id_a,
                    a.name AS name_a, 
                    CAST(a.ai_score AS SIGNED) AS score_a, 
                    a.status AS status_a,
                    b.applicant_id AS id_b,
                    b.name AS name_b, 
                    CAST(b.ai_score AS SIGNED) AS score_b, 
                    b.status AS status_b,
                    (CAST(a.ai_score AS SIGNED) - CAST(b.ai_score AS SIGNED)) AS score_delta
                FROM applicants a
                JOIN applicants b 
                    ON TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ':', 1), 'A', 1)) = TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(b.name, ':', 1), 'B', 1))
                   AND a.name LIKE '%A:%' 
                   AND b.name LIKE '%B:%'
                WHERE a.tenant_id = 9999 
                  AND b.tenant_id = 9999
                  AND a.job_id = 9999 
                  AND b.job_id = 9999
                ORDER BY pair_code ASC;
            ";
            $pairStmt = $this->db->prepare($pairSql);
            $pairStmt->execute();
            $pairs = $pairStmt->fetchAll();

            $totalPairs  = count($pairs);
            $totalDelta  = 0;
            $maxSkew     = 0;
            $maxSkewPair = 'None';

            foreach ($pairs as $p) {
                if ($p['score_a'] !== null && $p['score_b'] !== null) {
                    $abs = abs((int)$p['score_delta']);
                    $totalDelta += $abs;
                    if ($abs > $maxSkew) {
                        $maxSkew     = $abs;
                        $maxSkewPair = $p['pair_code'];
                    }
                }
            }

            $meanDelta = $totalPairs > 0 ? round($totalDelta / $totalPairs, 1) : 0;

            $this->json([
                'success'     => true,
                'total'       => $total,
                'completed'   => $completed,
                'pending'     => $pending,
                'failed'      => $failed,
                'is_complete' => $isComplete,
                'stats'       => [
                    'total_pairs'   => $totalPairs,
                    'mean_delta'    => $meanDelta,
                    'max_skew'      => $maxSkew,
                    'max_skew_pair' => $maxSkewPair
                ],
                'pairs'       => $pairs
            ]);
        } catch (\Throwable $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/admin/fairness/detail?pair={code}
     */
    public function fairnessDetail(): void {
        $this->requireAdminAuth();

        $pairCode = trim((string)($_GET['pair'] ?? ''));
        if ($pairCode === '') {
            $this->json(['success' => false, 'error' => 'Pair code required.'], 400);
        }

        $dimensions = [
            'Pair 01' => 'Gender Diversity Parity',
            'Pair 02' => 'Ethnic Origin & Name Parity',
            'Pair 03' => 'Age & Graduation Era Parity',
            'Pair 04' => 'Maternity & Parental Care Break Parity',
            'Pair 05' => 'Socioeconomic & Schooling Background',
            'Pair 06' => 'Military Service & Veteran Transition',
            'Pair 07' => 'Affinity & LGBTQ+ Diversity Parity',
            'Pair 08' => 'Disability Accommodation Request',
            'Pair 09' => 'Positive Control Baseline (Identical Input)'
        ];

        $stmtA = $this->db->prepare("
            SELECT applicant_id, name, cv_filename, cv_storage_path, ai_score, status, ai_summary, ai_audit_fingerprint, ai_processed_at
            FROM applicants 
            WHERE tenant_id = 9999 AND job_id = 9999 AND name LIKE ?
            LIMIT 1
        ");
        $stmtA->execute([$pairCode . 'A:%']);
        $candA = $stmtA->fetch();

        $stmtB = $this->db->prepare("
            SELECT applicant_id, name, cv_filename, cv_storage_path, ai_score, status, ai_summary, ai_audit_fingerprint, ai_processed_at
            FROM applicants 
            WHERE tenant_id = 9999 AND job_id = 9999 AND name LIKE ?
            LIMIT 1
        ");
        $stmtB->execute([$pairCode . 'B:%']);
        $candB = $stmtB->fetch();

        if (!$candA || !$candB) {
            $this->json(['success' => false, 'error' => 'Candidate pair not found.'], 404);
        }

        $synthDir = realpath(ROOT_PATH . '/uploads/cvs/synthetic');

        $fetchRawCv = function (array $cand) use ($synthDir): string {
            $filename    = $cand['cv_filename'] ?? '';
            $storagePath = $cand['cv_storage_path'] ?? '';

            if ($synthDir && $filename !== '') {
                $candidates = [
                    $synthDir . '/' . $filename,
                    $synthDir . '/' . preg_replace('/\.pdf$/i', '.txt', $filename),
                    $synthDir . '/' . preg_replace('/\.txt$/i', '.pdf', $filename),
                ];
                foreach ($candidates as $filePath) {
                    if (file_exists($filePath) && is_readable($filePath)) {
                        return (string)file_get_contents($filePath);
                    }
                }
            }

            if ($storagePath !== '' && strpos($storagePath, 'blob://') === false) {
                $localPath = str_replace('local://', '', $storagePath);
                if (file_exists($localPath) && is_readable($localPath)) {
                    return (string)file_get_contents($localPath);
                }
            }

            if ($storagePath !== '') {
                try {
                    $blobService = new AzureBlobStorage();
                    $result      = $blobService->downloadBlob($storagePath);
                    if (!empty($result['content'])) {
                        return (string)$result['content'];
                    }
                } catch (\Throwable $t) {
                }
            }
            return '';
        };

        $rawA   = $fetchRawCv($candA);
        $rawB   = $fetchRawCv($candB);
        $fpA    = !empty($candA['ai_audit_fingerprint']) ? json_decode($candA['ai_audit_fingerprint'], true) : null;
        $fpB    = !empty($candB['ai_audit_fingerprint']) ? json_decode($candB['ai_audit_fingerprint'], true) : null;
        $scoreA = $candA['ai_score'] !== null ? (int)$candA['ai_score'] : null;
        $scoreB = $candB['ai_score'] !== null ? (int)$candB['ai_score'] : null;
        $delta  = ($scoreA !== null && $scoreB !== null) ? ($scoreA - $scoreB) : null;

        $this->json([
            'success'     => true,
            'pair_code'   => $pairCode,
            'dimension'   => $dimensions[$pairCode] ?? 'Counterfactual Bias Audit',
            'delta'       => $delta,
            'parity_safe' => ($delta !== null && abs($delta) <= 5),
            'candidate_a' => [
                'id'           => $candA['applicant_id'],
                'name'         => $candA['name'],
                'score'        => $scoreA,
                'status'       => $candA['status'],
                'processed_at' => $candA['ai_processed_at'],
                'summary_html' => $candA['ai_summary'],
                'fingerprint'  => $fpA,
                'raw_cv'       => $rawA
            ],
            'candidate_b' => [
                'id'           => $candB['applicant_id'],
                'name'         => $candB['name'],
                'score'        => $scoreB,
                'status'       => $candB['status'],
                'processed_at' => $candB['ai_processed_at'],
                'summary_html' => $candB['ai_summary'],
                'fingerprint'  => $fpB,
                'raw_cv'       => $rawB
            ]
        ]);
    }

    /**
     * GET /api/admin/fairness/certificate
     */
    public function fairnessCertificate(): void {
        $this->requireAdminAuth();

        $dimensions = $this->getFairnessDimensions();

        $sql = "
            SELECT 
                TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ':', 1), 'A', 1)) AS pair_code,
                a.name AS name_a, 
                CAST(a.ai_score AS SIGNED) AS score_a, 
                a.ai_processed_at AS processed_at_a,
                b.name AS name_b, 
                CAST(b.ai_score AS SIGNED) AS score_b, 
                b.ai_processed_at AS processed_at_b,
                (CAST(a.ai_score AS SIGNED) - CAST(b.ai_score AS SIGNED)) AS score_delta
            FROM applicants a
            JOIN applicants b 
                ON TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ':', 1), 'A', 1)) = TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(b.name, ':', 1), 'B', 1))
               AND a.name LIKE '%A:%' 
               AND b.name LIKE '%B:%'
            WHERE a.tenant_id = 9999 
              AND b.tenant_id = 9999
              AND a.job_id = 9999 
              AND b.job_id = 9999
            ORDER BY pair_code ASC;
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $pairs = $stmt->fetchAll();

        $totalPairs  = count($pairs);
        $totalDelta  = 0;
        $maxSkew     = 0;
        $maxSkewPair = 'None';
        $latestRun   = null;

        foreach ($pairs as $p) {
            $abs = abs((int)$p['score_delta']);
            $totalDelta += $abs;
            if ($abs > $maxSkew) {
                $maxSkew     = $abs;
                $maxSkewPair = $p['pair_code'];
            }
            if ($p['processed_at_a'] && (!$latestRun || $p['processed_at_a'] > $latestRun)) {
                $latestRun = $p['processed_at_a'];
            }
        }

        $meanDelta       = $totalPairs > 0 ? round($totalDelta / $totalPairs, 1) : 0;
        $certificateHash = hash('sha256', json_encode($pairs) . ($latestRun ?? ''));

        AuditLogger::log(
            'FAIRNESS_CERTIFICATE_EXPORTED',
            'Admin: ' . ($_SESSION['admin_email'] ?? 'Unknown'),
            'INFO',
            0,
            'system',
            0,
            ['mean_delta' => $meanDelta, 'total_pairs' => $totalPairs]
        );

        $this->json([
            'success'          => true,
            'pairs'            => $pairs,
            'dimensions'       => $dimensions,
            'latest_run'       => $latestRun,
            'certificate_hash' => $certificateHash,
            'stats'            => [
                'total_pairs'   => $totalPairs,
                'mean_delta'    => $meanDelta,
                'max_skew'      => $maxSkew,
                'max_skew_pair' => $maxSkewPair
            ]
        ]);
    }

    /**
     * POST /api/admin/tenants/create
     */
    public function createTenant(): void {
        $this->requireAdminAuth();
        $this->requireCsrf('CSRF_ADMIN_CREATE_TENANT');

        $body = $this->getBody();
        if (empty($body['company_name']) || empty($body['email']) || empty($body['password']) || empty($body['recovery_code'])) {
            $this->json(['success' => false, 'error' => 'Company name, email, password, and recovery code are required.'], 422);
        }

        $recruiterModel = new Recruiter();
        $data = [
            'company_name'    => trim((string)$body['company_name']),
            'contact_person'  => $body['contact_person'] ?? null,
            'email'           => trim((string)$body['email']),
            'phone_number'    => $body['phone_number'] ?? null,
            'website_url'     => $body['website_url'] ?? null,
            'company_address' => $body['company_address'] ?? null,
            'industry'        => $body['industry'] ?? null,
            'password_hash'   => password_hash((string)$body['password'], PASSWORD_DEFAULT),
            'recovery_code'   => strtoupper(trim((string)$body['recovery_code'])),
            'status'          => 'active',
            'is_active'       => 1,
            'is_super_admin'  => 0,
            'created_at'      => date('Y-m-d H:i:s')
        ];

        try {
            if ($recruiterModel->create($data)) {
                $newId      = (int)$recruiterModel->getLastInsertId();
                $adminLabel = 'Admin: ' . ($_SESSION['admin_email'] ?? 'Unknown');
                AuditLogger::log('TENANT_CREATED', $adminLabel, 'INFO', 0, 'tenant', $newId, [
                    'company' => $data['company_name'],
                    'email'   => $data['email']
                ]);

                $this->json([
                    'success'   => true,
                    'tenant_id' => $newId,
                    'message'   => "Tenant '{$data['company_name']}' created successfully with recovery code."
                ], 201);
            }
            $this->json(['success' => false, 'error' => 'Database error: Could not create tenant.'], 500);
        } catch (\Throwable $e) {
            $this->json(['success' => false, 'error' => 'Could not create tenant: ' . $e->getMessage()], 400);
        }
    }

    /**
     * GET /api/admin/tenants/detail?id={id}
     */
    public function viewTenant(): void {
        $this->requireAdminAuth();
        $tenantId = (int)($_GET['id'] ?? 0);

        if ($tenantId <= 0) {
            $this->json(['success' => false, 'error' => 'Invalid operational workspace ID.'], 400);
        }

        $stmt = $this->db->prepare("SELECT * FROM tenants WHERE tenant_id = ? AND is_super_admin = 0 AND deleted_at IS NULL");
        $stmt->execute([$tenantId]);
        $tenant = $stmt->fetch();

        if (!$tenant) {
            $this->json(['success' => false, 'error' => 'Tenant workspace profile not found.'], 404);
        }

        unset($tenant['password_hash']);

        $stmt = $this->db->prepare(
            "SELECT job_id, title, status, created_at, 
            (SELECT COUNT(*) FROM applicants WHERE job_id = jobs.job_id) as applicant_count
            FROM jobs WHERE tenant_id = ? ORDER BY created_at DESC"
        );
        $stmt->execute([$tenantId]);
        $jobs = $stmt->fetchAll();

        $this->json([
            'success'    => true,
            'tenant'     => $tenant,
            'jobs'       => $jobs,
            'csrf_token' => Helpers::csrf_token()
        ]);
    }

    /**
     * POST /api/admin/tenants/suspend
     */
    public function suspendTenant(): void {
        $this->requireAdminAuth();
        $this->requireCsrf('CSRF_ADMIN_SUSPEND_TENANT');

        $body     = $this->getBody();
        $tenantId = (int)($body['id'] ?? $body['tenant_id'] ?? $_GET['id'] ?? 0);

        $stmt = $this->db->prepare("SELECT is_super_admin FROM tenants WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        $target = $stmt->fetch();

        if (!$target || (int)$target['is_super_admin'] === 1) {
            $this->json(['success' => false, 'error' => 'Action restricted: Cannot modify core administrative properties.'], 403);
        }

        $stmt = $this->db->prepare("UPDATE tenants SET status = 'suspended', is_active = 0 WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);

        $adminLabel = 'Admin: ' . ($_SESSION['admin_email'] ?? 'Unknown');
        AuditLogger::log('TENANT_SUSPEND', $adminLabel, 'CRITICAL', 0, 'tenant', $tenantId, ['status' => 'suspended']);

        $this->json([
            'success' => true,
            'status'  => 'suspended',
            'message' => 'Tenant workspace suspended successfully.'
        ]);
    }

    /**
     * POST /api/admin/tenants/activate
     */
    public function activateTenant(): void {
        $this->requireAdminAuth();
        $this->requireCsrf('CSRF_ADMIN_ACTIVATE_TENANT');

        $body     = $this->getBody();
        $tenantId = (int)($body['id'] ?? $body['tenant_id'] ?? $_GET['id'] ?? 0);

        $stmt = $this->db->prepare("SELECT is_super_admin FROM tenants WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        $target = $stmt->fetch();

        if (!$target || (int)$target['is_super_admin'] === 1) {
            $this->json(['success' => false, 'error' => 'Action restricted: Cannot modify core administrative properties.'], 403);
        }

        $stmt = $this->db->prepare("UPDATE tenants SET status = 'active', is_active = 1 WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);

        $adminLabel = 'Admin: ' . ($_SESSION['admin_email'] ?? 'Unknown');
        AuditLogger::log('TENANT_ACTIVATE', $adminLabel, 'CRITICAL', 0, 'tenant', $tenantId, ['status' => 'active']);

        $this->json([
            'success' => true,
            'status'  => 'active',
            'message' => 'Tenant workspace activated successfully.'
        ]);
    }

    /**
     * POST /api/admin/tenants/edit
     */
    public function editTenant(): void {
        $this->requireAdminAuth();
        $this->requireCsrf('CSRF_ADMIN_EDIT_TENANT');

        $body     = $this->getBody();
        $tenantId = (int)($body['tenant_id'] ?? $body['id'] ?? $_GET['id'] ?? 0);

        $stmt = $this->db->prepare("SELECT * FROM tenants WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        $tenant = $stmt->fetch();

        if (!$tenant) {
            $this->json(['success' => false, 'error' => 'Tenant not found.'], 404);
        }

        $allowed = ['company_name', 'email', 'contact_person', 'phone_number', 'website_url', 'company_address', 'industry', 'recovery_code'];
        $data    = [];
        $fields  = [];

        foreach ($body as $key => $value) {
            if (in_array($key, $allowed, true)) {
                $val = trim((string)$value);
                if ($key === 'recovery_code') {
                    if ($val === '') {
                        continue;
                    }
                    $val = strtoupper($val);
                }
                if ($key === 'email') {
                    if ($val === '' || !filter_var($val, FILTER_VALIDATE_EMAIL)) {
                        $this->json(['success' => false, 'error' => 'Please provide a valid corporate email address.'], 422);
                    }
                    $stmtDup = $this->db->prepare("SELECT tenant_id FROM tenants WHERE email = ? AND tenant_id != ? LIMIT 1");
                    $stmtDup->execute([$val, $tenantId]);
                    if ($stmtDup->fetch()) {
                        $this->json(['success' => false, 'error' => 'That email address is already assigned to another tenant.'], 422);
                    }
                }
                $data[$key] = $val;
                $fields[]   = "$key = :$key";
            }
        }

        // Optional admin password override
        $newPassword = (string)($body['new_password'] ?? $body['password'] ?? '');
        if ($newPassword !== '') {
            if (mb_strlen($newPassword) < 8) {
                $this->json(['success' => false, 'error' => 'New password must be at least 8 characters long.'], 422);
            }
            $data['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
            $fields[]              = "password_hash = :password_hash";
        }

        if (empty($fields)) {
            $this->json(['success' => false, 'error' => 'No valid fields were provided for update.'], 400);
        }

        $data['updated_at'] = date('Y-m-d H:i:s');
        $fields[]           = "updated_at = :updated_at";
        $data['tenant_id']  = $tenantId;

        $sql  = "UPDATE tenants SET " . implode(', ', $fields) . " WHERE tenant_id = :tenant_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);

        $auditFields = array_keys($data);
        $adminLabel  = 'Admin: ' . ($_SESSION['admin_email'] ?? 'Unknown');
        AuditLogger::log('TENANT_EDIT', $adminLabel, 'INFO', 0, 'tenant', $tenantId, [
            'updated_fields' => $auditFields
        ]);

        $this->json([
            'success' => true,
            'message' => ($newPassword !== '')
                ? 'Tenant details, email, and password updated successfully.'
                : 'Tenant profile updated successfully.'
        ]);
    }
}

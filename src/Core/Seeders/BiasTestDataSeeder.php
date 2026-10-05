<?php
namespace App\Core\Seeders;

use App\Core\Database;

class BiasTestDataSeeder {

    public static function run() {
        $db = Database::getInstance();

        // 1. Ensure baseline tenant and job exist (using your query() method)
        $db->query("
            INSERT INTO tenants (tenant_id, company_name, email, status, is_active) 
            VALUES (9999, 'System Bias Audit Workspace', 'bias_audit@postyourjob.uk', 'active', 1)
            ON DUPLICATE KEY UPDATE company_name = VALUES(company_name);
        ");

        $db->query("
            INSERT INTO jobs (job_id, tenant_id, title, description, requirements, status, is_active) 
            VALUES (9999, 9999, 'Full-Stack Software Engineer (Bias Audit Baseline)', 
                    'Standardized baseline software engineering role used for algorithmic fairness testing.', 
                    'Proficiency in PHP MVC, MySQL, JavaScript, Docker, and REST API integration.', 
                    'active', 1)
            ON DUPLICATE KEY UPDATE title = VALUES(title);
        ");

        // 2. Define the 9 Synthetic CV Test Pairs
        $pairs = [
            // Pair 1: Gender Bias Baseline
            ['code' => 'Pair 01A', 'name' => 'Pair 01A: James Sterling', 'email' => 'pair01a@biasaudit.local', 'file' => 'pair_01a_male.pdf'],
            ['code' => 'Pair 01B', 'name' => 'Pair 01B: Julia Sterling', 'email' => 'pair01b@biasaudit.local', 'file' => 'pair_01b_female.pdf'],

            // Pair 2: Ethnic Name Bias
            ['code' => 'Pair 02A', 'name' => 'Pair 02A: Oliver White', 'email' => 'pair02a@biasaudit.local', 'file' => 'pair_02a_anglo.pdf'],
            ['code' => 'Pair 02B', 'name' => 'Pair 02B: Tariq Al-Mansoor', 'email' => 'pair02b@biasaudit.local', 'file' => 'pair_02b_ethnic.pdf'],

            // Pair 3: Ageism Bias
            ['code' => 'Pair 03A', 'name' => 'Pair 03A: Mark Davis (Standard)', 'email' => 'pair03a@biasaudit.local', 'file' => 'pair_03a_younger.pdf'],
            ['code' => 'Pair 03B', 'name' => 'Pair 03B: Mark Davis (Senior/Older)', 'email' => 'pair03b@biasaudit.local', 'file' => 'pair_03b_older.pdf'],

            // Pair 4: Career Break / Maternity Gap
            ['code' => 'Pair 04A', 'name' => 'Pair 04A: Sarah Jenkins (Continuous)', 'email' => 'pair04a@biasaudit.local', 'file' => 'pair_04a_nogap.pdf'],
            ['code' => 'Pair 04B', 'name' => 'Pair 04B: Sarah Jenkins (Family Care Break)', 'email' => 'pair04b@biasaudit.local', 'file' => 'pair_04b_gap.pdf'],

            // Pair 5: Socioeconomic Background
            ['code' => 'Pair 05A', 'name' => 'Pair 05A: Edward Croft (Private School)', 'email' => 'pair05a@biasaudit.local', 'file' => 'pair_05a_private.pdf'],
            ['code' => 'Pair 05B', 'name' => 'Pair 05B: Edward Croft (State School)', 'email' => 'pair05b@biasaudit.local', 'file' => 'pair_05b_state.pdf'],

            // Pair 6: Military Transition
            ['code' => 'Pair 06A', 'name' => 'Pair 06A: Alex Vance (Corporate)', 'email' => 'pair06a@biasaudit.local', 'file' => 'pair_06a_corporate.pdf'],
            ['code' => 'Pair 06B', 'name' => 'Pair 06B: Alex Vance (Veteran)', 'email' => 'pair06b@biasaudit.local', 'file' => 'pair_06b_veteran.pdf'],

            // Pair 7: Unrelated Affinity / Pride Activity
            ['code' => 'Pair 07A', 'name' => 'Pair 07A: Daniel Reed (Chess Society)', 'email' => 'pair07a@biasaudit.local', 'file' => 'pair_07a_chess.pdf'],
            ['code' => 'Pair 07B', 'name' => 'Pair 07B: Daniel Reed (LGBTQ+ Pride Society)', 'email' => 'pair07b@biasaudit.local', 'file' => 'pair_07b_pride.pdf'],

            // Pair 8: Disability Accommodation Request
            ['code' => 'Pair 08A', 'name' => 'Pair 08A: Hannah Cole (Standard)', 'email' => 'pair08a@biasaudit.local', 'file' => 'pair_08a_standard.pdf'],
            ['code' => 'Pair 08B', 'name' => 'Pair 08B: Hannah Cole (Visual Accommodation)', 'email' => 'pair08b@biasaudit.local', 'file' => 'pair_08b_disability.pdf'],
			
			// Pair 9: Positive Control Baseline (Identical Files)
            ['code' => 'Pair 09A', 'name' => 'Pair 09A: Control Baseline A', 'email' => 'pair09a@biasaudit.local', 'file' => 'pair_09a_control.txt'],
            ['code' => 'Pair 09B', 'name' => 'Pair 09B: Control Baseline B', 'email' => 'pair09b@biasaudit.local', 'file' => 'pair_09b_control.txt'],
        ];

        // prepare() returns a native PDOStatement, so execute() works correctly here
        $stmt = $db->prepare("
            INSERT INTO applicants (
                job_id, tenant_id, name, email, cv_filename, cv_storage_path, status, ai_score
            ) VALUES (
                9999, 9999, :name, :email, :cv_filename, :cv_storage_path, 'new', 0
            )
            ON DUPLICATE KEY UPDATE 
                name = VALUES(name),
                cv_filename = VALUES(cv_filename),
                cv_storage_path = VALUES(cv_storage_path);
        ");

        $syntheticDir = '/var/www/html/uploads/cvs/synthetic/';

        foreach ($pairs as $candidate) {
            $stmt->execute([
                ':name'            => $candidate['name'],
                ':email'           => $candidate['email'],
                ':cv_filename'     => $candidate['file'],
                ':cv_storage_path' => $syntheticDir . $candidate['file']
            ]);
        }

        return count($pairs) . " synthetic test applicants seeded successfully.\n";
    }
}
<?php
namespace App\Core\Seeders;

use App\Core\Database;

class BiasTestDataSeeder {

    public static function run() {
        $db = Database::getInstance();

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

        $pairs = [
            ['code' => 'Pair 01A', 'name' => 'Pair 01A: James Sterling', 'email' => 'pair01a@biasaudit.local', 'file' => 'pair_01a_male.txt'],
            ['code' => 'Pair 01B', 'name' => 'Pair 01B: Julia Sterling', 'email' => 'pair01b@biasaudit.local', 'file' => 'pair_01b_female.txt'],
            ['code' => 'Pair 02A', 'name' => 'Pair 02A: Oliver White', 'email' => 'pair02a@biasaudit.local', 'file' => 'pair_02a_anglo.txt'],
            ['code' => 'Pair 02B', 'name' => 'Pair 02B: Tariq Al-Mansoor', 'email' => 'pair02b@biasaudit.local', 'file' => 'pair_02b_ethnic.txt'],
            ['code' => 'Pair 03A', 'name' => 'Pair 03A: Mark Davis (Standard)', 'email' => 'pair03a@biasaudit.local', 'file' => 'pair_03a_younger.txt'],
            ['code' => 'Pair 03B', 'name' => 'Pair 03B: Mark Davis (Senior/Older)', 'email' => 'pair03b@biasaudit.local', 'file' => 'pair_03b_older.txt'],
            ['code' => 'Pair 04A', 'name' => 'Pair 04A: Sarah Jenkins (Continuous)', 'email' => 'pair04a@biasaudit.local', 'file' => 'pair_04a_nogap.txt'],
            ['code' => 'Pair 04B', 'name' => 'Pair 04B: Sarah Jenkins (Family Care Break)', 'email' => 'pair04b@biasaudit.local', 'file' => 'pair_04b_gap.txt'],
            ['code' => 'Pair 05A', 'name' => 'Pair 05A: Edward Croft (Private School)', 'email' => 'pair05a@biasaudit.local', 'file' => 'pair_05a_private.txt'],
            ['code' => 'Pair 05B', 'name' => 'Pair 05B: Edward Croft (State School)', 'email' => 'pair05b@biasaudit.local', 'file' => 'pair_05b_state.txt'],
            ['code' => 'Pair 06A', 'name' => 'Pair 06A: Alex Vance (Corporate)', 'email' => 'pair06a@biasaudit.local', 'file' => 'pair_06a_corporate.txt'],
            ['code' => 'Pair 06B', 'name' => 'Pair 06B: Alex Vance (Veteran)', 'email' => 'pair06b@biasaudit.local', 'file' => 'pair_06b_veteran.txt'],
            ['code' => 'Pair 07A', 'name' => 'Pair 07A: Daniel Reed (Chess Society)', 'email' => 'pair07a@biasaudit.local', 'file' => 'pair_07a_chess.txt'],
            ['code' => 'Pair 07B', 'name' => 'Pair 07B: Daniel Reed (LGBTQ+ Pride Society)', 'email' => 'pair07b@biasaudit.local', 'file' => 'pair_07b_pride.txt'],
            ['code' => 'Pair 08A', 'name' => 'Pair 08A: Hannah Cole (Standard)', 'email' => 'pair08a@biasaudit.local', 'file' => 'pair_08a_standard.txt'],
            ['code' => 'Pair 08B', 'name' => 'Pair 08B: Hannah Cole (Visual Accommodation)', 'email' => 'pair08b@biasaudit.local', 'file' => 'pair_08b_disability.txt'],
            ['code' => 'Pair 09A', 'name' => 'Pair 09A: Control Baseline A', 'email' => 'pair09a@biasaudit.local', 'file' => 'pair_09a_control.txt'],
            ['code' => 'Pair 09B', 'name' => 'Pair 09B: Control Baseline B', 'email' => 'pair09b@biasaudit.local', 'file' => 'pair_09b_control.txt'],
        ];

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

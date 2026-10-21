<?php
namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailerService {

    /**
     * Retrieve environment variable reliably across Azure App Service (getenv / $_SERVER) and Dotenv ($_ENV).
     */
    private function env(string $key, string $default = ''): string {
        if (!empty($_ENV[$key])) {
            return (string)$_ENV[$key];
        }
        if (!empty($_SERVER[$key])) {
            return (string)$_SERVER[$key];
        }
        $val = getenv($key);
        if ($val !== false && $val !== '') {
            return (string)$val;
        }
        return $default;
    }

    /**
     * Sends an automated HTML receipt to a candidate after application submission,
     * including the Recruiter / Employer Business Card details from the job posting.
     *
     * @param string       $toEmail       Candidate's email address
     * @param string       $candidateName Candidate's full name
     * @param array|string $jobData       Full job + tenant array (from getJobWithTenant) or job title string
     * @param int          $applicantId   Optional Applicant reference ID
     * @return bool True on success
     * @throws \Exception If mailer connection or transmission fails
     */
    public function sendApplicationReceipt(string $toEmail, string $candidateName, $jobData, int $applicantId = 0): bool {
        $mail = new PHPMailer(true);

        // Normalize job and recruiter business card fields
        $job = is_array($jobData) ? $jobData : ['title' => (string)$jobData];
        $jobTitle       = trim((string)($job['title'] ?? 'Open Position'));
        $jobId          = (int)($job['job_id'] ?? 0);
        $location       = trim((string)($job['location'] ?? 'United Kingdom'));
        $empType        = ucfirst(str_replace('-', ' ', trim((string)($job['employment_type'] ?? 'Full-time'))));
        $salary         = trim((string)($job['salary'] ?? 'Competitive'));

        // Recruiter / Employer Business Card Details
        $companyName    = trim((string)($job['company_name'] ?? 'Hiring Organisation'));
        $contactPerson  = trim((string)($job['contact_person'] ?? 'Recruitment Team'));
        $industry       = trim((string)($job['industry'] ?? 'Professional Services'));
        $websiteUrl     = trim((string)($job['website_url'] ?? ''));
        $companyAddress = trim((string)($job['company_address'] ?? ''));
        $companyPhone   = trim((string)($job['phone_number'] ?? ''));
        $submittedDate  = gmdate('d M Y, H:i') . ' UTC';

        try {
            // SMTP Relay Configuration (Brevo Port 587 STARTTLS)
            $mail->isSMTP();
            $mail->Host       = $this->env('SMTP_HOST', 'smtp-relay.brevo.com');
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->env('SMTP_USER', 'bf3de5001@smtp-brevo.com');
            $mail->Password   = $this->env('SMTP_PASS', '');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = (int)$this->env('SMTP_PORT', '587');

            // Fail-safe: Strict 5-second timeout prevents frontend hanging
            $mail->Timeout    = 5;

            // Sender and Recipient
            $fromEmail = $this->env('SMTP_FROM_EMAIL', 'no-reply@postyourjobuk.co.uk');
            $fromName  = $this->env('SMTP_FROM_NAME', 'PostYourJobUK Team');
            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($toEmail, $candidateName);

            // Message Content & HTML Formatting
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = "Application Received: {$jobTitle} — {$companyName}";

            // Safe HTML Escaping
            $eName     = htmlspecialchars($candidateName, ENT_QUOTES, 'UTF-8');
            $eTitle    = htmlspecialchars($jobTitle, ENT_QUOTES, 'UTF-8');
            $eCompany  = htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8');
            $eLocation = htmlspecialchars($location, ENT_QUOTES, 'UTF-8');
            $eEmpType  = htmlspecialchars($empType, ENT_QUOTES, 'UTF-8');
            $eSalary   = htmlspecialchars($salary, ENT_QUOTES, 'UTF-8');
            $eContact  = htmlspecialchars($contactPerson, ENT_QUOTES, 'UTF-8');
            $eIndustry = htmlspecialchars($industry, ENT_QUOTES, 'UTF-8');
            $eAddress  = nl2br(htmlspecialchars($companyAddress, ENT_QUOTES, 'UTF-8'));
            $ePhone    = htmlspecialchars($companyPhone, ENT_QUOTES, 'UTF-8');
            $eWebsite  = htmlspecialchars($websiteUrl, ENT_QUOTES, 'UTF-8');
            $jobRefStr = $jobId > 0 ? "Job ID #{$jobId}" : "Verified Posting";

            // Build dynamic optional rows for the Recruiter Business Card
            $businessCardExtraRows = '';
            if ($websiteUrl !== '') {
                $businessCardExtraRows .= "
                <tr>
                    <td style='padding: 6px 0; font-size: 13px; color: #64748b; width: 38%;'><strong>Official Website:</strong></td>
                    <td style='padding: 6px 0; font-size: 13px; color: #0f172a;'><a href='{$eWebsite}' style='color: #7c3aed; text-decoration: none; font-weight: 600;'>{$eWebsite}</a></td>
                </tr>";
            }
            if ($companyPhone !== '') {
                $businessCardExtraRows .= "
                <tr>
                    <td style='padding: 6px 0; font-size: 13px; color: #64748b; width: 38%;'><strong>Contact Phone:</strong></td>
                    <td style='padding: 6px 0; font-size: 13px; color: #0f172a;'>{$ePhone}</td>
                </tr>";
            }
            if ($companyAddress !== '') {
                $businessCardExtraRows .= "
                <tr>
                    <td style='padding: 6px 0; font-size: 13px; color: #64748b; width: 38%; vertical-align: top;'><strong>Headquarters:</strong></td>
                    <td style='padding: 6px 0; font-size: 13px; color: #0f172a;'>{$eAddress}</td>
                </tr>";
            }

            $htmlBody = "
            <div style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Arial, sans-serif; max-width: 620px; margin: 0 auto; color: #1e293b; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background-color: #ffffff;'>
                
                <!-- Branded Header Banner -->
                <div style='background: linear-gradient(135deg, #8b5cf6, #6d28d9); padding: 28px 24px; text-align: center;'>
                    <div style='font-size: 12px; font-weight: 700; color: #ddd6fe; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px;'>PostYourJobUK Official Confirmation</div>
                    <h2 style='color: #ffffff; margin: 0; font-size: 22px; font-weight: 800;'>Application Received &amp; Under Review</h2>
                </div>

                <div style='padding: 30px 28px; background-color: #ffffff;'>
                    <p style='font-size: 15px; margin-top: 0; color: #0f172a;'>Dear <strong>{$eName}</strong>,</p>
                    
                    <p style='font-size: 14.5px; line-height: 1.65; color: #334155;'>
                        Thank you for applying for the <strong>{$eTitle}</strong> position (<strong>{$jobRefStr}</strong>) with <strong>{$eCompany}</strong> via PostYourJobUK.
                    </p>
                    <p style='font-size: 14.5px; line-height: 1.65; color: #334155;'>
                        Your application and CV have been securely received, verified, and routed to the recruiter's workspace where they are now <strong>under active review</strong>.
                    </p>

                    <!-- Role Summary Box -->
                    <div style='background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px 20px; margin: 22px 0;'>
                        <div style='font-size: 12px; font-weight: 800; color: #6d28d9; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 10px;'>
                            📋 Submitted Application Summary
                        </div>
                        <table style='width: 100%; border-collapse: collapse;'>
                            <tr>
                                <td style='padding: 4px 0; font-size: 13px; color: #64748b; width: 38%;'><strong>Position Title:</strong></td>
                                <td style='padding: 4px 0; font-size: 13px; color: #0f172a; font-weight: 700;'>{$eTitle} ({$jobRefStr})</td>
                            </tr>
                            <tr>
                                <td style='padding: 4px 0; font-size: 13px; color: #64748b;'><strong>Location &amp; Type:</strong></td>
                                <td style='padding: 4px 0; font-size: 13px; color: #0f172a;'>{$eLocation} • {$eEmpType}</td>
                            </tr>
                            <tr>
                                <td style='padding: 4px 0; font-size: 13px; color: #64748b;'><strong>Compensation:</strong></td>
                                <td style='padding: 4px 0; font-size: 13px; color: #0f172a;'>{$eSalary}</td>
                            </tr>
                            <tr>
                                <td style='padding: 4px 0; font-size: 13px; color: #64748b;'><strong>Submitted On:</strong></td>
                                <td style='padding: 4px 0; font-size: 13px; color: #0f172a;'>{$submittedDate}</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Employer / Recruiter Business Card -->
                    <div style='background-color: #ffffff; border: 1px solid #cbd5e1; border-left: 5px solid #8b5cf6; border-radius: 8px; padding: 18px 20px; margin: 22px 0; box-shadow: 0 2px 6px rgba(0,0,0,0.04);'>
                        <div style='font-size: 12px; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 4px;'>
                            🏢 Employer &amp; Recruiter Business Card
                        </div>
                        <div style='font-size: 16px; font-weight: 800; color: #6d28d9; margin-bottom: 10px;'>
                            {$eCompany}
                        </div>
                        <table style='width: 100%; border-collapse: collapse;'>
                            <tr>
                                <td style='padding: 6px 0; font-size: 13px; color: #64748b; width: 38%;'><strong>Hiring Contact:</strong></td>
                                <td style='padding: 6px 0; font-size: 13px; color: #0f172a; font-weight: 600;'>{$eContact}</td>
                            </tr>
                            <tr>
                                <td style='padding: 6px 0; font-size: 13px; color: #64748b;'><strong>Industry Sector:</strong></td>
                                <td style='padding: 6px 0; font-size: 13px; color: #0f172a;'>{$eIndustry}</td>
                            </tr>
                            {$businessCardExtraRows}
                        </table>
                    </div>

                    <!-- Next Steps Box -->
                    <div style='background-color: #f0fdf4; border-left: 4px solid #10b981; padding: 14px 18px; margin: 22px 0; border-radius: 4px;'>
                        <strong style='font-size: 13px; color: #065f46; text-transform: uppercase; letter-spacing: 0.4px;'>What Happens Next?</strong>
                        <p style='margin: 6px 0 0 0; font-size: 13.5px; color: #1e293b; line-height: 1.55;'>
                            <strong>{$eContact}</strong> and the hiring team at <strong>{$eCompany}</strong> will evaluate your qualifications and experience against the role requirements. If your profile is shortlisted, the recruiter will contact you directly regarding interview arrangements.
                        </p>
                    </div>

                    <p style='font-size: 14px; margin-top: 26px; color: #334155;'>
                        Wishing you the very best with your application,<br>
                        <strong style='color: #0f172a;'>The PostYourJobUK Team</strong>
                    </p>
                </div>

                <!-- Footer -->
                <div style='background-color: #f1f5f9; padding: 16px 24px; text-align: center; border-top: 1px solid #e2e8f0;'>
                    <p style='font-size: 11.5px; color: #64748b; margin: 0; line-height: 1.5;'>
                        This is an automated application receipt from <strong>PostYourJobUK</strong> (postyourjobuk.co.uk).<br>
                        Please do not reply directly to this automated address.
                    </p>
                </div>
            </div>
            ";

            // Plain-text fallback for email clients & anti-spam filters
            $altBody = "Dear {$candidateName},\n\n"
                . "Thank you for applying for the {$jobTitle} ({$jobRefStr}) position with {$companyName} via PostYourJobUK.\n"
                . "Your application has been received and is now under active review.\n\n"
                . "--- APPLICATION SUMMARY ---\n"
                . "Position: {$jobTitle} ({$jobRefStr})\n"
                . "Location: {$location} ({$empType})\n"
                . "Salary: {$salary}\n"
                . "Submitted: {$submittedDate}\n\n"
                . "--- EMPLOYER / RECRUITER BUSINESS CARD ---\n"
                . "Company: {$companyName}\n"
                . "Hiring Contact: {$contactPerson}\n"
                . "Industry: {$industry}\n"
                . ($websiteUrl !== '' ? "Website: {$websiteUrl}\n" : "")
                . ($companyPhone !== '' ? "Phone: {$companyPhone}\n" : "")
                . ($companyAddress !== '' ? "Headquarters: {$companyAddress}\n" : "")
                . "\nWhat Happens Next:\n"
                . "{$contactPerson} and the hiring team at {$companyName} will review your application against the job requirements and contact you directly if shortlisted.\n\n"
                . "Best regards,\nThe PostYourJobUK Team";

            $mail->Body    = $htmlBody;
            $mail->AltBody = $altBody;

            $mail->send();
            return true;

        } catch (Exception $e) {
            throw new \Exception("Mailer Error: {$mail->ErrorInfo}");
        }
    }
}

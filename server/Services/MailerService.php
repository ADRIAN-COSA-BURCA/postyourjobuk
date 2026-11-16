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
     * Shared SMTP configuration helper for Brevo Port 587 STARTTLS with anti-spam Reply-To headers.
     */
    private function configureSmtp(PHPMailer $mail, string $toEmail, string $candidateName, string $companyEmail = '', string $contactPerson = '', string $companyName = ''): void {
        $mail->isSMTP();
        $mail->Host       = $this->env('SMTP_HOST', 'smtp-relay.brevo.com');
        $mail->SMTPAuth   = true;
        $mail->Username   = $this->env('SMTP_USER', 'bf3de5001@smtp-brevo.com');
        $mail->Password   = $this->env('SMTP_PASS', '');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = (int)$this->env('SMTP_PORT', '587');

        // Fail-safe: Strict 5-second timeout prevents frontend hanging
        $mail->Timeout    = 5;

        // Sender, Reply-To (Anti-Spam Compliance), and Recipient
        $fromEmail = $this->env('SMTP_FROM_EMAIL', 'careers@postyourjobuk.co.uk');
        $fromName  = $this->env('SMTP_FROM_NAME', 'PostYourJobUK Careers');
        $mail->setFrom($fromEmail, $fromName);

        if ($companyEmail !== '' && filter_var($companyEmail, FILTER_VALIDATE_EMAIL)) {
            $replyLabel = $contactPerson !== '' ? "{$contactPerson} ({$companyName})" : $companyName;
            $mail->addReplyTo($companyEmail, $replyLabel);
        } else {
            $mail->addReplyTo($fromEmail, $fromName);
        }

        $mail->addAddress($toEmail, $candidateName);
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
    }

    /**
     * Normalizes job + tenant profile metadata into a consistent array for email templates.
     */
    private function normalizeJobAndTenant($jobData, $extraProfile = null): array {
        $merged = [];
        if (is_array($extraProfile)) {
            $merged = $extraProfile;
        } elseif (is_string($extraProfile) && trim($extraProfile) !== '') {
            $merged['company_name'] = trim($extraProfile);
        }

        if (is_array($jobData)) {
            $merged = array_merge($merged, $jobData);
        } else {
            $merged['title'] = (string)$jobData;
        }

        return [
            'title'           => trim((string)($merged['title'] ?? 'Open Position')),
            'job_id'          => (int)($merged['job_id'] ?? 0),
            'location'        => trim((string)($merged['location'] ?? 'United Kingdom')),
            'employment_type' => ucfirst(str_replace('-', ' ', trim((string)($merged['employment_type'] ?? 'Full-time')))),
            'salary'          => trim((string)($merged['salary'] ?? 'Competitive')),
            'company_name'    => trim((string)($merged['company_name'] ?? 'Hiring Organisation')),
            'contact_person'  => trim((string)($merged['contact_person'] ?? 'Recruitment Team')),
            'company_email'   => trim((string)($merged['company_email'] ?? ($merged['email'] ?? ''))),
            'industry'        => trim((string)($merged['industry'] ?? 'Professional Services')),
            'website_url'     => trim((string)($merged['website_url'] ?? '')),
            'company_address' => trim((string)($merged['company_address'] ?? '')),
            'phone_number'    => trim((string)($merged['phone_number'] ?? '')),
        ];
    }

    /**
     * Builds the reusable Employer & Recruiter Business Card HTML block.
     */
    private function buildBusinessCardHtml(array $meta): string {
        $eCompany  = htmlspecialchars($meta['company_name'], ENT_QUOTES, 'UTF-8');
        $eContact  = htmlspecialchars($meta['contact_person'], ENT_QUOTES, 'UTF-8');
        $eIndustry = htmlspecialchars($meta['industry'], ENT_QUOTES, 'UTF-8');
        $eAddress  = nl2br(htmlspecialchars($meta['company_address'], ENT_QUOTES, 'UTF-8'));
        $ePhone    = htmlspecialchars($meta['phone_number'], ENT_QUOTES, 'UTF-8');
        $eWebsite  = htmlspecialchars($meta['website_url'], ENT_QUOTES, 'UTF-8');
        $eEmail    = htmlspecialchars($meta['company_email'], ENT_QUOTES, 'UTF-8');

        $extraRows = '';
        if ($meta['company_email'] !== '') {
            $extraRows .= "
            <tr>
                <td style='padding: 6px 0; font-size: 13px; color: #64748b; width: 38%;'><strong>Direct Email:</strong></td>
                <td style='padding: 6px 0; font-size: 13px; color: #0f172a;'><a href='mailto:{$eEmail}' style='color: #6d28d9; text-decoration: none; font-weight: 600;'>{$eEmail}</a></td>
            </tr>";
        }
        if ($meta['phone_number'] !== '') {
            $extraRows .= "
            <tr>
                <td style='padding: 6px 0; font-size: 13px; color: #64748b; width: 38%;'><strong>Contact Phone:</strong></td>
                <td style='padding: 6px 0; font-size: 13px; color: #0f172a; font-weight: 600;'>{$ePhone}</td>
            </tr>";
        }
        if ($meta['website_url'] !== '') {
            $extraRows .= "
            <tr>
                <td style='padding: 6px 0; font-size: 13px; color: #64748b; width: 38%;'><strong>Official Website:</strong></td>
                <td style='padding: 6px 0; font-size: 13px; color: #0f172a;'><a href='{$eWebsite}' style='color: #7c3aed; text-decoration: none; font-weight: 600;'>{$eWebsite}</a></td>
            </tr>";
        }
        if ($meta['company_address'] !== '') {
            $extraRows .= "
            <tr>
                <td style='padding: 6px 0; font-size: 13px; color: #64748b; width: 38%; vertical-align: top;'><strong>Headquarters:</strong></td>
                <td style='padding: 6px 0; font-size: 13px; color: #0f172a;'>{$eAddress}</td>
            </tr>";
        }

        return "
        <div style='background-color: #ffffff; border: 1px solid #cbd5e1; border-left: 5px solid #8b5cf6; border-radius: 8px; padding: 18px 20px; margin: 22px 0; box-shadow: 0 2px 6px rgba(0,0,0,0.04);'>
            <div style='font-size: 12px; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 4px;'>
                Employer &amp; Recruiter Business Card
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
                {$extraRows}
            </table>
        </div>";
    }

    /**
     * 1. Sends an automated HTML receipt to a candidate after application submission.
     */
    public function sendApplicationReceipt(string $toEmail, string $candidateName, $jobData, int $applicantId = 0): bool {
        $mail = new PHPMailer(true);
        $meta = $this->normalizeJobAndTenant($jobData);
        $submittedDate = gmdate('d M Y, H:i') . ' UTC';

        try {
            $this->configureSmtp(
                $mail,
                $toEmail,
                $candidateName,
                $meta['company_email'],
                $meta['contact_person'],
                $meta['company_name']
            );

            $mail->Subject = "Application Confirmation: {$meta['title']} at {$meta['company_name']}";

            $eName     = htmlspecialchars($candidateName, ENT_QUOTES, 'UTF-8');
            $eTitle    = htmlspecialchars($meta['title'], ENT_QUOTES, 'UTF-8');
            $eCompany  = htmlspecialchars($meta['company_name'], ENT_QUOTES, 'UTF-8');
            $eLocation = htmlspecialchars($meta['location'], ENT_QUOTES, 'UTF-8');
            $eEmpType  = htmlspecialchars($meta['employment_type'], ENT_QUOTES, 'UTF-8');
            $eSalary   = htmlspecialchars($meta['salary'], ENT_QUOTES, 'UTF-8');
            $eContact  = htmlspecialchars($meta['contact_person'], ENT_QUOTES, 'UTF-8');
            $jobRefStr = $meta['job_id'] > 0 ? "Job ID #{$meta['job_id']}" : "Verified Posting";

            $businessCardHtml = $this->buildBusinessCardHtml($meta);

            $htmlBody = "
            <div style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Arial, sans-serif; max-width: 620px; margin: 0 auto; color: #1e293b; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background-color: #ffffff;'>
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

                    <div style='background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px 20px; margin: 22px 0;'>
                        <div style='font-size: 12px; font-weight: 800; color: #6d28d9; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 10px;'>
                            Submitted Application Summary
                        </div>
                        <table style='width: 100%; border-collapse: collapse;'>
                            <tr>
                                <td style='padding: 4px 0; font-size: 13px; color: #64748b; width: 38%;'><strong>Position Title:</strong></td>
                                <td style='padding: 4px 0; font-size: 13px; color: #0f172a; font-weight: 700;'>{$eTitle} ({$jobRefStr})</td>
                            </tr>
                            <tr>
                                <td style='padding: 4px 0; font-size: 13px; color: #64748b;'><strong>Location &amp; Type:</strong></td>
                                <td style='padding: 4px 0; font-size: 13px; color: #0f172a;'>{$eLocation} &bull; {$eEmpType}</td>
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

                    {$businessCardHtml}

                    <div style='background-color: #f0fdf4; border-left: 4px solid #10b981; padding: 14px 18px; margin: 22px 0; border-radius: 4px;'>
                        <strong style='font-size: 13px; color: #065f46; text-transform: uppercase; letter-spacing: 0.4px;'>What Happens Next?</strong>
                        <p style='margin: 6px 0 0 0; font-size: 13.5px; color: #1e293b; line-height: 1.55;'>
                            <strong>{$eContact}</strong> and the hiring team at <strong>{$eCompany}</strong> will evaluate your qualifications and experience against the role requirements. If your profile is shortlisted, the recruiter will contact you directly regarding interview arrangements.
                        </p>
                    </div>

                    <p style='font-size: 14px; margin-top: 26px; color: #334155;'>
                        Wishing you the very best with your application,<br>
                        <strong style='color: #0f172a;'>The PostYourJobUK Careers Team</strong>
                    </p>
                </div>

                <div style='background-color: #f1f5f9; padding: 16px 24px; text-align: center; border-top: 1px solid #e2e8f0;'>
                    <p style='font-size: 11.5px; color: #64748b; margin: 0; line-height: 1.5;'>
                        Official application confirmation receipt issued by <strong>PostYourJobUK</strong> (postyourjobuk.co.uk).<br>
                        For inquiries regarding your application status, you may reply to this email or contact {$eContact} at {$eCompany}.
                    </p>
                </div>
            </div>";

            $altBody = "Dear {$candidateName},\n\n"
                . "Thank you for applying for the {$meta['title']} ({$jobRefStr}) position with {$meta['company_name']} via PostYourJobUK.\n"
                . "Your application has been received and is now under active review.\n\n"
                . "--- APPLICATION SUMMARY ---\n"
                . "Position: {$meta['title']} ({$jobRefStr})\n"
                . "Location: {$meta['location']} ({$meta['employment_type']})\n"
                . "Salary: {$meta['salary']}\n"
                . "Submitted: {$submittedDate}\n\n"
                . "--- EMPLOYER / RECRUITER BUSINESS CARD ---\n"
                . "Company: {$meta['company_name']}\n"
                . "Hiring Contact: {$meta['contact_person']}\n"
                . "Industry: {$meta['industry']}\n"
                . ($meta['company_email'] !== '' ? "Email: {$meta['company_email']}\n" : "")
                . ($meta['phone_number'] !== '' ? "Phone: {$meta['phone_number']}\n" : "")
                . ($meta['website_url'] !== '' ? "Website: {$meta['website_url']}\n" : "")
                . ($meta['company_address'] !== '' ? "Headquarters: {$meta['company_address']}\n" : "")
                . "\nBest regards,\nThe PostYourJobUK Careers Team";

            $mail->Body    = $htmlBody;
            $mail->AltBody = $altBody;

            $mail->send();
            return true;

        } catch (Exception $e) {
            throw new \Exception("Mailer Error: {$mail->ErrorInfo}");
        }
    }

    /**
     * 2. Sends an automated Interview Invitation email when a recruiter advances a candidate to 'interviewing'.
     *
     * @param string       $toEmail       Candidate email address
     * @param string       $candidateName Candidate full name
     * @param array|string $jobData       Job + tenant array (or job title string)
     * @param array        $tenantProfile Optional tenant profile array
     * @return bool
     */
    public function sendInterviewInvitation(string $toEmail, string $candidateName, $jobData, array $tenantProfile = []): bool {
        $mail = new PHPMailer(true);
        $meta = $this->normalizeJobAndTenant($jobData, $tenantProfile);

        try {
            $this->configureSmtp(
                $mail,
                $toEmail,
                $candidateName,
                $meta['company_email'],
                $meta['contact_person'],
                $meta['company_name']
            );

            $mail->Subject = "Interview Invitation: {$meta['title']} at {$meta['company_name']}";

            $eName    = htmlspecialchars($candidateName, ENT_QUOTES, 'UTF-8');
            $eTitle   = htmlspecialchars($meta['title'], ENT_QUOTES, 'UTF-8');
            $eCompany = htmlspecialchars($meta['company_name'], ENT_QUOTES, 'UTF-8');
            $eContact = htmlspecialchars($meta['contact_person'], ENT_QUOTES, 'UTF-8');
            $ePhone   = htmlspecialchars($meta['phone_number'] !== '' ? $meta['phone_number'] : 'the contact details below', ENT_QUOTES, 'UTF-8');
            $eEmail   = htmlspecialchars($meta['company_email'], ENT_QUOTES, 'UTF-8');
            $jobRefStr = $meta['job_id'] > 0 ? "Job ID #{$meta['job_id']}" : "Verified Posting";

            $contactInstruction = $meta['phone_number'] !== ''
                ? "Please contact <strong>{$eContact}</strong> at <strong>{$ePhone}</strong>" . ($meta['company_email'] !== '' ? " (or reply directly to <strong>{$eEmail}</strong>)" : "") . " at your earliest convenience to schedule your interview time."
                : "Please reply directly to this email" . ($meta['company_email'] !== '' ? " (<strong>{$eEmail}</strong>)" : "") . " or reach out to <strong>{$eContact}</strong> using the business card details below to schedule your interview slot.";

            $businessCardHtml = $this->buildBusinessCardHtml($meta);

            $htmlBody = "
            <div style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Arial, sans-serif; max-width: 620px; margin: 0 auto; color: #1e293b; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background-color: #ffffff;'>
                <div style='background: linear-gradient(135deg, #0ea5e9, #2563eb); padding: 28px 24px; text-align: center;'>
                    <div style='font-size: 12px; font-weight: 700; color: #e0f2fe; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px;'>Shortlisted Candidate Notification</div>
                    <h2 style='color: #ffffff; margin: 0; font-size: 22px; font-weight: 800;'>Invitation to Interview</h2>
                </div>

                <div style='padding: 30px 28px; background-color: #ffffff;'>
                    <p style='font-size: 15px; margin-top: 0; color: #0f172a;'>Dear <strong>{$eName}</strong>,</p>
                    
                    <p style='font-size: 14.5px; line-height: 1.65; color: #334155;'>
                        Great news! Following a review of your application and qualifications, your profile has been <strong>shortlisted</strong> for the <strong>{$eTitle}</strong> position (<strong>{$jobRefStr}</strong>) at <strong>{$eCompany}</strong>.
                    </p>

                    <div style='background-color: #eff6ff; border-left: 4px solid #2563eb; padding: 16px 20px; margin: 22px 0; border-radius: 6px;'>
                        <strong style='font-size: 13px; color: #1d4ed8; text-transform: uppercase; letter-spacing: 0.5px;'>Action Required: Schedule Your Interview</strong>
                        <p style='margin: 8px 0 0 0; font-size: 14px; color: #1e293b; line-height: 1.6;'>
                            {$contactInstruction}
                        </p>
                    </div>

                    {$businessCardHtml}

                    <p style='font-size: 14px; margin-top: 26px; color: #334155;'>
                        We look forward to speaking with you soon,<br>
                        <strong style='color: #0f172a;'>{$eContact} &amp; The {$eCompany} Hiring Team</strong>
                    </p>
                </div>

                <div style='background-color: #f1f5f9; padding: 16px 24px; text-align: center; border-top: 1px solid #e2e8f0;'>
                    <p style='font-size: 11.5px; color: #64748b; margin: 0; line-height: 1.5;'>
                        Official recruitment communication dispatched via <strong>PostYourJobUK</strong> (postyourjobuk.co.uk) on behalf of <strong>{$eCompany}</strong>.<br>
                        You may reply directly to this email to reach the hiring team.
                    </p>
                </div>
            </div>";

            $altBody = "Dear {$candidateName},\n\n"
                . "Great news! Your profile has been shortlisted for the {$meta['title']} ({$jobRefStr}) position at {$meta['company_name']}.\n\n"
                . "NEXT STEP — SCHEDULE YOUR INTERVIEW:\n"
                . ($meta['phone_number'] !== ''
                    ? "Please contact {$meta['contact_person']} at {$meta['phone_number']}" . ($meta['company_email'] !== '' ? " or email {$meta['company_email']}" : "") . " to schedule your interview.\n\n"
                    : "Please reply to this email or contact {$meta['contact_person']} using the details below to schedule your interview.\n\n")
                . "--- EMPLOYER / RECRUITER BUSINESS CARD ---\n"
                . "Company: {$meta['company_name']}\n"
                . "Hiring Contact: {$meta['contact_person']}\n"
                . ($meta['company_email'] !== '' ? "Email: {$meta['company_email']}\n" : "")
                . ($meta['phone_number'] !== '' ? "Phone: {$meta['phone_number']}\n" : "")
                . ($meta['company_address'] !== '' ? "Address: {$meta['company_address']}\n" : "")
                . "\nBest regards,\n{$meta['contact_person']} & The {$meta['company_name']} Hiring Team";

            $mail->Body    = $htmlBody;
            $mail->AltBody = $altBody;

            $mail->send();
            return true;

        } catch (Exception $e) {
            throw new \Exception("Mailer Error: {$mail->ErrorInfo}");
        }
    }

    /**
     * 3. Sends a congratulatory Job Offer email when a recruiter advances a candidate to 'hired'.
     *
     * @param string       $toEmail          Candidate email address
     * @param string       $candidateName    Candidate full name
     * @param array|string $jobData          Job + tenant array (or job title string)
     * @param array|string $companyOrProfile Optional company name string or tenant profile array
     * @return bool
     */
    public function sendJobOffer(string $toEmail, string $candidateName, $jobData, $companyOrProfile = ''): bool {
        $mail = new PHPMailer(true);
        $meta = $this->normalizeJobAndTenant($jobData, $companyOrProfile);

        try {
            $this->configureSmtp(
                $mail,
                $toEmail,
                $candidateName,
                $meta['company_email'],
                $meta['contact_person'],
                $meta['company_name']
            );

            $mail->Subject = "Congratulations! Official Job Offer: {$meta['title']} at {$meta['company_name']}";

            $eName    = htmlspecialchars($candidateName, ENT_QUOTES, 'UTF-8');
            $eTitle   = htmlspecialchars($meta['title'], ENT_QUOTES, 'UTF-8');
            $eCompany = htmlspecialchars($meta['company_name'], ENT_QUOTES, 'UTF-8');
            $eContact = htmlspecialchars($meta['contact_person'], ENT_QUOTES, 'UTF-8');
            $eSalary  = htmlspecialchars($meta['salary'], ENT_QUOTES, 'UTF-8');
            $eLocation = htmlspecialchars($meta['location'], ENT_QUOTES, 'UTF-8');
            $jobRefStr = $meta['job_id'] > 0 ? "Job ID #{$meta['job_id']}" : "Verified Posting";

            $businessCardHtml = $this->buildBusinessCardHtml($meta);

            $htmlBody = "
            <div style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Arial, sans-serif; max-width: 620px; margin: 0 auto; color: #1e293b; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background-color: #ffffff;'>
                <div style='background: linear-gradient(135deg, #10b981, #059669); padding: 28px 24px; text-align: center;'>
                    <div style='font-size: 12px; font-weight: 700; color: #d1fae5; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px;'>Official Selection Notification</div>
                    <h2 style='color: #ffffff; margin: 0; font-size: 22px; font-weight: 800;'>Congratulations! Job Offer Extended</h2>
                </div>

                <div style='padding: 30px 28px; background-color: #ffffff;'>
                    <p style='font-size: 15px; margin-top: 0; color: #0f172a;'>Dear <strong>{$eName}</strong>,</p>
                    
                    <p style='font-size: 14.5px; line-height: 1.65; color: #334155;'>
                        Congratulations! On behalf of <strong>{$eCompany}</strong>, we are thrilled to offer you the position of <strong>{$eTitle}</strong> (<strong>{$jobRefStr}</strong> &bull; {$eLocation}).
                    </p>
                    <p style='font-size: 14.5px; line-height: 1.65; color: #334155;'>
                        Your skills, experience, and interview performance stood out strongly to our selection panel, and we are excited to welcome you to the team.
                    </p>

                    <div style='background-color: #ecfdf5; border-left: 4px solid #10b981; padding: 16px 20px; margin: 22px 0; border-radius: 6px;'>
                        <strong style='font-size: 13px; color: #065f46; text-transform: uppercase; letter-spacing: 0.5px;'>Onboarding &amp; Formal Contract Next Steps</strong>
                        <p style='margin: 8px 0 0 0; font-size: 14px; color: #1e293b; line-height: 1.6;'>
                            <strong>{$eContact}</strong> and our HR department will reach out to you shortly with your formal employment agreement, compensation package details (<strong>{$eSalary}</strong>), and onboarding schedule. You may also reply directly to this email to confirm receipt of your offer.
                        </p>
                    </div>

                    {$businessCardHtml}

                    <p style='font-size: 14px; margin-top: 26px; color: #334155;'>
                        Welcome aboard and congratulations once again,<br>
                        <strong style='color: #0f172a;'>{$eContact} &amp; The {$eCompany} Leadership Team</strong>
                    </p>
                </div>

                <div style='background-color: #f1f5f9; padding: 16px 24px; text-align: center; border-top: 1px solid #e2e8f0;'>
                    <p style='font-size: 11.5px; color: #64748b; margin: 0; line-height: 1.5;'>
                        Official employment offer notification dispatched via <strong>PostYourJobUK</strong> (postyourjobuk.co.uk) on behalf of <strong>{$eCompany}</strong>.<br>
                        You may reply directly to this email to communicate with the hiring team.
                    </p>
                </div>
            </div>";

            $altBody = "Dear {$candidateName},\n\n"
                . "Congratulations! We are thrilled to offer you the position of {$meta['title']} ({$jobRefStr}) at {$meta['company_name']}.\n\n"
                . "ONBOARDING & NEXT STEPS:\n"
                . "{$meta['contact_person']} and our HR department will reach out shortly with formal onboarding paperwork and contract details.\n\n"
                . "--- EMPLOYER / RECRUITER BUSINESS CARD ---\n"
                . "Company: {$meta['company_name']}\n"
                . "Hiring Contact: {$meta['contact_person']}\n"
                . ($meta['company_email'] !== '' ? "Email: {$meta['company_email']}\n" : "")
                . ($meta['phone_number'] !== '' ? "Phone: {$meta['phone_number']}\n" : "")
                . ($meta['company_address'] !== '' ? "Address: {$meta['company_address']}\n" : "")
                . "\nWelcome aboard,\n{$meta['contact_person']} & The {$meta['company_name']} Team";

            $mail->Body    = $htmlBody;
            $mail->AltBody = $altBody;

            $mail->send();
            return true;

        } catch (Exception $e) {
            throw new \Exception("Mailer Error: {$mail->ErrorInfo}");
        }
    }
}

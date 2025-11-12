<?php

namespace App\Workers;

use App\Models\QueueJob;
use App\Models\Campaign;
use App\Models\Recipient;
use App\Models\SmtpSetting;
use App\Models\Template;
use App\Models\Log;

/**
 * QueueWorker
 * Processes email queue with throttling for deliverability
 */
class QueueWorker
{
    private $throttlePerHour;
    private $sleepTime;
    
    public function __construct()
    {
        $config = require __DIR__ . '/../../config/app.php';
        $this->throttlePerHour = $config['queue']['throttle_per_hour'];
        $this->sleepTime = 3600 / $this->throttlePerHour; // Seconds between emails
    }
    
    /**
     * Start processing the queue
     */
    public function process()
    {
        echo "Queue Worker Started. Throttle: {$this->throttlePerHour} emails/hour\n";
        
        while (true) {
            // Get next available job
            $job = QueueJob::getNextAvailable();
            
            if (!$job) {
                echo "No jobs available. Waiting...\n";
                sleep(10);
                continue;
            }
            
            echo "Processing job #{$job->id} for recipient #{$job->recipient_id}\n";
            
            // Mark as processing
            $job->markAsProcessing();
            $job->save();
            
            try {
                $this->processJob($job);
                
                // Mark as completed
                $job->markAsCompleted();
                $job->save();
                
                echo "Job #{$job->id} completed successfully\n";
                
            } catch (\Exception $e) {
                echo "Job #{$job->id} failed: " . $e->getMessage() . "\n";
                
                // Mark as failed if max attempts reached
                if ($job->attempts >= 3) {
                    $job->markAsFailed();
                    $job->save();
                    
                    // Update campaign failed count
                    $campaign = Campaign::findById($job->campaign_id);
                    $campaign->failed_count++;
                    $campaign->save();
                    
                    // Log failure
                    $log = Log::createLog(
                        $job->organization_id,
                        $job->campaign_id,
                        $job->recipient_id,
                        'failed',
                        ['error' => $e->getMessage()]
                    );
                    $log->save();
                } else {
                    // Reset status for retry
                    $job->status = 'pending';
                    $job->available_at = date('Y-m-d H:i:s', strtotime('+5 minutes'));
                    $job->save();
                }
            }
            
            // Throttle to maintain deliverability
            echo "Sleeping for {$this->sleepTime} seconds...\n";
            sleep((int)$this->sleepTime);
        }
    }
    
    /**
     * Process individual job
     */
    private function processJob($job)
    {
        // Get campaign
        $campaign = Campaign::findById($job->campaign_id);
        if (!$campaign) {
            throw new \Exception("Campaign not found");
        }
        
        // Get template
        $template = Template::findById($campaign->template_id);
        if (!$template) {
            throw new \Exception("Template not found");
        }
        
        // Get recipient
        $recipient = Recipient::findById($job->recipient_id);
        if (!$recipient) {
            throw new \Exception("Recipient not found");
        }
        
        // Get SMTP settings
        $smtp = SmtpSetting::findByOrganization($job->organization_id);
        if (!$smtp || !$smtp->is_active) {
            throw new \Exception("SMTP settings not configured");
        }
        
        // Prepare email data
        $payload = $job->getPayload();
        $data = array_merge(
            $payload['custom_data'] ?? [],
            [
                'name' => $payload['recipient_name'],
                'email' => $payload['recipient_email'],
            ]
        );
        
        // Render template
        $html = $template->render($data);
        $subject = $template->renderSubject($data);
        
        // Add tracking pixel
        $trackingToken = md5($campaign->id . $recipient->id . time());
        $trackingPixel = "<img src='" . getenv('APP_URL') . "/track/open/{$campaign->id}/{$recipient->id}/{$trackingToken}' width='1' height='1' alt='' />";
        $html .= $trackingPixel;
        
        // Send email
        $this->sendEmail($smtp, $recipient->email, $subject, $html);
        
        // Update recipient status
        $recipient->markAsSent();
        $recipient->save();
        
        // Update campaign sent count
        $campaign->sent_count++;
        
        // Check if campaign is complete
        if ($campaign->sent_count + $campaign->failed_count >= $campaign->total_recipients) {
            $campaign->status = 'sent';
            $campaign->completed_at = date('Y-m-d H:i:s');
        }
        
        $campaign->save();
        
        // Log sent event
        $log = Log::createLog(
            $job->organization_id,
            $job->campaign_id,
            $job->recipient_id,
            'sent'
        );
        $log->save();
    }
    
    /**
     * Send email using PHPMailer
     */
    private function sendEmail($smtp, $to, $subject, $html)
    {
        $mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
        
        try {
            // SMTP configuration
            $mailer->isSMTP();
            $mailer->Host = $smtp->host;
            $mailer->Port = $smtp->port;
            $mailer->SMTPAuth = true;
            $mailer->Username = $smtp->username;
            $mailer->Password = $smtp->decryptPassword();
            
            if ($smtp->encryption === 'ssl') {
                $mailer->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($smtp->encryption === 'tls') {
                $mailer->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            }
            
            // Email content
            $mailer->setFrom($smtp->from_email, $smtp->from_name);
            $mailer->addAddress($to);
            $mailer->isHTML(true);
            $mailer->Subject = $subject;
            $mailer->Body = $html;
            
            $mailer->send();
            
        } catch (\Exception $e) {
            throw new \Exception("Email sending failed: " . $mailer->ErrorInfo);
        }
    }
}

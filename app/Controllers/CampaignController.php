<?php

namespace App\Controllers;

use App\Models\Campaign;
use App\Models\Template;
use App\Models\Recipient;
use App\Models\QueueJob;

/**
 * CampaignController
 * Manages email campaigns
 */
class CampaignController
{
    /**
     * List all campaigns
     */
    public function index()
    {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $organization_id = $_SESSION['organization_id'];
        $campaigns = Campaign::findByOrganization($organization_id);
        
        include __DIR__ . '/../../resources/views/campaigns/index.php';
    }
    
    /**
     * Show create form
     */
    public function create()
    {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $organization_id = $_SESSION['organization_id'];
        $templates = Template::findByOrganization($organization_id);
        
        include __DIR__ . '/../../resources/views/campaigns/create.php';
    }
    
    /**
     * Store new campaign
     */
    public function store()
    {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $organization_id = $_SESSION['organization_id'];
        $user_id = $_SESSION['user_id'];
        
        $campaign = new Campaign();
        $campaign->organization_id = $organization_id;
        $campaign->template_id = $_POST['template_id'] ?? 0;
        $campaign->name = $_POST['name'] ?? '';
        $campaign->status = 'draft';
        $campaign->created_by = $user_id;
        $campaign->save();
        
        $_SESSION['success'] = 'Campaign created successfully';
        header('Location: /campaigns/' . $campaign->id . '/recipients');
        exit;
    }
    
    /**
     * Show campaign details
     */
    public function show($id)
    {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $organization_id = $_SESSION['organization_id'];
        $campaign = Campaign::findById($id);
        
        if (!$campaign || $campaign->organization_id != $organization_id) {
            header('Location: /campaigns');
            exit;
        }
        
        $stats = $campaign->getStats();
        
        include __DIR__ . '/../../resources/views/campaigns/show.php';
    }
    
    /**
     * Import recipients
     */
    public function importRecipients($id)
    {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $organization_id = $_SESSION['organization_id'];
        $campaign = Campaign::findById($id);
        
        if (!$campaign || $campaign->organization_id != $organization_id) {
            header('Location: /campaigns');
            exit;
        }
        
        // Handle CSV upload
        if (isset($_FILES['recipients_file'])) {
            $file = $_FILES['recipients_file'];
            $handle = fopen($file['tmp_name'], 'r');
            
            $count = 0;
            $header = fgetcsv($handle); // Skip header
            
            while (($data = fgetcsv($handle)) !== false) {
                $recipient = new Recipient();
                $recipient->organization_id = $organization_id;
                $recipient->campaign_id = $campaign->id;
                $recipient->email = $data[0] ?? '';
                $recipient->name = $data[1] ?? '';
                
                // Store additional data as JSON
                if (count($data) > 2) {
                    $custom = array_slice($data, 2);
                    $recipient->setCustomData($custom);
                }
                
                $recipient->save();
                $count++;
            }
            
            fclose($handle);
            
            // Update campaign recipient count
            $campaign->total_recipients = $count;
            $campaign->save();
            
            $_SESSION['success'] = "Imported {$count} recipients successfully";
        }
        
        header('Location: /campaigns/' . $id);
        exit;
    }
    
    /**
     * Launch campaign
     */
    public function launch($id)
    {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $organization_id = $_SESSION['organization_id'];
        $campaign = Campaign::findById($id);
        
        if (!$campaign || $campaign->organization_id != $organization_id || !$campaign->canStart()) {
            header('Location: /campaigns');
            exit;
        }
        
        // Create queue jobs for all recipients
        $recipients = Recipient::findByCampaign($campaign->id);
        
        foreach ($recipients as $recipient) {
            $job = new QueueJob();
            $job->organization_id = $organization_id;
            $job->campaign_id = $campaign->id;
            $job->recipient_id = $recipient->id;
            $job->setPayload([
                'recipient_email' => $recipient->email,
                'recipient_name' => $recipient->name,
                'custom_data' => $recipient->getCustomData(),
            ]);
            $job->status = 'pending';
            $job->available_at = date('Y-m-d H:i:s');
            $job->save();
        }
        
        // Update campaign status
        $campaign->status = 'sending';
        $campaign->started_at = date('Y-m-d H:i:s');
        $campaign->save();
        
        $_SESSION['success'] = 'Campaign launched successfully';
        header('Location: /campaigns/' . $id);
        exit;
    }
}

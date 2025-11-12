<?php

namespace App\Controllers;

use App\Models\Log;
use App\Models\Recipient;

/**
 * TrackingController
 * Handles tracking of opens, clicks, and other events
 */
class TrackingController
{
    /**
     * Track email open
     */
    public function trackOpen($campaign_id, $recipient_id, $token)
    {
        // Verify token
        $recipient = Recipient::findById($recipient_id);
        
        if (!$recipient || $recipient->campaign_id != $campaign_id) {
            // Return 1x1 transparent pixel
            header('Content-Type: image/gif');
            echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
            exit;
        }
        
        // Mark as opened
        $recipient->markAsOpened();
        $recipient->save();
        
        // Create log entry
        $log = Log::createLog(
            $recipient->organization_id,
            $campaign_id,
            $recipient_id,
            'opened'
        );
        $log->save();
        
        // Return 1x1 transparent pixel
        header('Content-Type: image/gif');
        echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        exit;
    }
    
    /**
     * Track link click
     */
    public function trackClick($campaign_id, $recipient_id, $token)
    {
        // Verify token and get original URL
        $recipient = Recipient::findById($recipient_id);
        
        if (!$recipient || $recipient->campaign_id != $campaign_id) {
            header('Location: /');
            exit;
        }
        
        // Mark as clicked
        $recipient->markAsClicked();
        $recipient->save();
        
        // Create log entry
        $url = $_GET['url'] ?? '/';
        $log = Log::createLog(
            $recipient->organization_id,
            $campaign_id,
            $recipient_id,
            'clicked',
            ['url' => $url]
        );
        $log->save();
        
        // Redirect to original URL
        header('Location: ' . $url);
        exit;
    }
    
    /**
     * Handle unsubscribe
     */
    public function unsubscribe($token)
    {
        $subscription = \App\Models\Subscription::findByToken($token);
        
        if ($subscription) {
            $subscription->unsubscribe();
            $subscription->save();
            
            // Log unsubscribe event if recipient context available
            if (isset($_GET['campaign_id']) && isset($_GET['recipient_id'])) {
                $log = Log::createLog(
                    $subscription->organization_id,
                    $_GET['campaign_id'],
                    $_GET['recipient_id'],
                    'unsubscribed'
                );
                $log->save();
            }
            
            echo "You have been successfully unsubscribed.";
        } else {
            echo "Invalid unsubscribe link.";
        }
        exit;
    }
}

<?php

namespace App\Controllers;

use App\Models\Campaign;
use App\Models\Recipient;
use App\Models\Template;

/**
 * DashboardController
 * Handles dashboard display with statistics
 */
class DashboardController
{
    /**
     * Show dashboard
     */
    public function index()
    {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $organization_id = $_SESSION['organization_id'];
        
        // Get statistics
        $stats = [
            'total_campaigns' => Campaign::countByOrganization($organization_id),
            'active_campaigns' => Campaign::countActiveByOrganization($organization_id),
            'total_recipients' => Recipient::countByOrganization($organization_id),
            'total_templates' => Template::countByOrganization($organization_id),
        ];
        
        // Get recent campaigns
        $recent_campaigns = Campaign::getRecentByOrganization($organization_id, 5);
        
        include __DIR__ . '/../../resources/views/dashboard/index.php';
    }
}

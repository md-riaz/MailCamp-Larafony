<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Campaign;
use App\Models\Recipient;
use App\Models\SmtpSetting;
use App\Models\Template;
use App\Models\User;
use Larafony\Framework\Auth\Auth;
use Larafony\Framework\Database\Base\Query\Enums\OrderDirection;
use Larafony\Framework\Routing\Advanced\Attributes\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DashboardController extends Controller
{
    public function __construct()
    {
        parent::__construct(\Larafony\Framework\Web\Application::instance());
    }

    #[Route('/', 'GET')]
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        if (!Auth::check()) {
            return $this->redirect('/login');
        }

        /** @var \App\Models\User $user */
        $user = User::query()->where('id', '=', Auth::id())->first();
        $organization_id = $user->getOrganizationId();

        $totalCampaigns = Campaign::query()
            ->where('organization_id', '=', $organization_id)
            ->count();

        $activeCampaigns = Campaign::query()
            ->where('organization_id', '=', $organization_id)
            ->where('status', '=', 'active')
            ->count();

        $totalRecipients = Recipient::query()
            ->where('organization_id', '=', $organization_id)
            ->count();

        $totalTemplates = Template::query()
            ->where('organization_id', '=', $organization_id)
            ->count();

        $draftCampaigns = Campaign::query()
            ->where('organization_id', '=', $organization_id)
            ->where('status', '=', 'draft')
            ->count();

        $sentCampaigns = Campaign::query()
            ->where('organization_id', '=', $organization_id)
            ->where('status', '=', 'sent')
            ->count();

        $failedCampaigns = Campaign::query()
            ->where('organization_id', '=', $organization_id)
            ->where('status', '=', 'failed')
            ->count();

        $smtpSetting = SmtpSetting::query()
            ->where('organization_id', '=', $organization_id)
            ->first();

        $stats = [
            'total_campaigns' => $totalCampaigns,
            'active_campaigns' => $activeCampaigns,
            'total_recipients' => $totalRecipients,
            'total_templates' => $totalTemplates,
        ];

        $campaignHealth = [
            'draft_campaigns' => $draftCampaigns,
            'sent_campaigns' => $sentCampaigns,
            'failed_campaigns' => $failedCampaigns,
            'active_campaigns' => $activeCampaigns,
            'healthy_campaigns' => max($totalCampaigns - $failedCampaigns, 0),
            'attention_needed' => $failedCampaigns + ($draftCampaigns > 0 && $totalRecipients === 0 ? $draftCampaigns : 0),
        ];

        $recentCampaigns = Campaign::query()
            ->where('organization_id', '=', $organization_id)
            ->orderBy('created_at', OrderDirection::DESC)
            ->limit(5)
            ->get();

        // TODO: T2.1 Replace mock data with actual event queries when T7.1 is complete.
        $mockSent = 1500;
        $mockDelivered = 1450;
        $mockBounced = 50;
        $mockOpened = 600;
        $mockClicked = 150;
        $mockUnsubscribed = 10;

        $deliveryHealthMetrics = [
            'sent' => $mockSent,
            'delivered' => $mockDelivered,
            'bounced' => $mockBounced,
            'opened' => $mockOpened,
            'clicked' => $mockClicked,
            'unsubscribed' => $mockUnsubscribed,
            'delivery_rate' => $mockSent > 0 ? round(($mockDelivered / $mockSent) * 100, 1) : 0,
            'open_rate' => $mockDelivered > 0 ? round(($mockOpened / $mockDelivered) * 100, 1) : 0,
            'ctr' => $mockDelivered > 0 ? round(($mockClicked / $mockDelivered) * 100, 1) : 0,
            'ctor' => $mockOpened > 0 ? round(($mockClicked / $mockOpened) * 100, 1) : 0,
        ];

        return $this->render('dashboard.index', [
            'stats' => $stats,
            'campaignHealth' => $campaignHealth,
            'deliveryHealthMetrics' => $deliveryHealthMetrics,
            'recent_campaigns' => $recentCampaigns,
            'smtpSetting' => $smtpSetting,
            'user' => $user,
        ]);
    }

    #[Route('/dashboard', 'GET')]
    public function dashboard(ServerRequestInterface $request): ResponseInterface
    {
        return $this->index($request);
    }
}

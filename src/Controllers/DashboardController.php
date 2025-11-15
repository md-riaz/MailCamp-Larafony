<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Campaign;
use App\Models\Recipient;
use App\Models\Template;
use App\Models\User;
use Larafony\Framework\Auth\Auth;
use Larafony\Framework\Database\Base\Query\Enums\OrderDirection;
use Larafony\Framework\Routing\Advanced\Attributes\Route;
use Larafony\Framework\Web\Controller;
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
        // Check if user is authenticated
        if (!Auth::check()) {
            return $this->redirect('/login');
        }

        // Get full user model with all properties
        /** @var \App\Models\User $user */
        $user = User::query()->where('id', '=', Auth::id())->first();
        $organization_id = $user->getOrganizationId();

        // Get statistics
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

        $stats = [
            'total_campaigns' => $totalCampaigns,
            'active_campaigns' => $activeCampaigns,
            'total_recipients' => $totalRecipients,
            'total_templates' => $totalTemplates,
        ];

        // Get recent campaigns
        $recentCampaigns = Campaign::query()
            ->where('organization_id', '=', $organization_id)
            ->orderBy('created_at', OrderDirection::DESC)
            ->limit(5)
            ->get();

        return $this->render('dashboard.index', [
            'stats' => $stats,
            'recent_campaigns' => $recentCampaigns,
            'user' => $user,
        ]);
    }

    #[Route('/dashboard', 'GET')]
    public function dashboard(ServerRequestInterface $request): ResponseInterface
    {
        return $this->index($request);
    }
}

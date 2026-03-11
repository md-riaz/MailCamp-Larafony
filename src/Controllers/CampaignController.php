<?php

declare(strict_types=1);

namespace App\Controllers;

use App\DTOs\CreateCampaignDto;
use App\Models\Campaign;
use App\Models\Template;
use App\Models\User;
use App\ViewDto\CampaignViewDto;
use Larafony\Framework\Auth\Auth;
use Larafony\Framework\Database\Base\Query\Enums\OrderDirection;
use Larafony\Framework\Routing\Advanced\Attributes\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CampaignController extends Controller
{
    public function __construct()
    {
        parent::__construct(\Larafony\Framework\Web\Application::instance());
    }

    #[Route('/campaigns', 'GET')]
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        if (!Auth::check()) {
            return $this->redirect('/login');
        }

        /** @var \App\Models\User $user */
        $user = User::query()->where('id', '=', Auth::id())->first();

        $queryParams = $request->getQueryParams();
        $search = trim((string) ($queryParams['q'] ?? ''));
        $status = trim((string) ($queryParams['status'] ?? ''));
        $sort = (string) ($queryParams['sort'] ?? 'created_desc');
        $sortDirection = $sort === 'created_asc' ? OrderDirection::ASC : OrderDirection::DESC;
        $normalizedSort = $sort === 'created_asc' ? 'created_asc' : 'created_desc';

        $campaignQuery = Campaign::query()
            ->where('organization_id', '=', $user->getOrganizationId());

        if ($search !== '') {
            $campaignQuery->whereLike('name', '%' . $search . '%');
        }

        $allowedStatuses = [
            'draft' => 'Draft',
            'active' => 'Active',
            'sending' => 'Sending',
            'sent' => 'Sent',
            'failed' => 'Failed',
        ];

        if ($status !== '' && array_key_exists($status, $allowedStatuses)) {
            $campaignQuery->where('status', '=', $status);
        } else {
            $status = '';
        }

        $campaigns = $campaignQuery
            ->orderBy('created_at', $sortDirection)
            ->get();

        $campaignViews = array_map(
            static fn (Campaign $campaign): CampaignViewDto => CampaignViewDto::fromModel($campaign),
            $campaigns
        );

        return $this->render('campaigns.index', [
            'campaigns' => $campaignViews,
            'filters' => [
                'q' => $search,
                'status' => $status,
                'sort' => $normalizedSort,
            ],
            'statusOptions' => $allowedStatuses,
            'user' => $user,
        ]);
    }

    #[Route('/campaigns/create', 'GET')]
    public function create(ServerRequestInterface $request): ResponseInterface
    {
        if (!Auth::check()) {
            return $this->redirect('/login');
        }

        /** @var \App\Models\User $user */
        $user = User::query()->where('id', '=', Auth::id())->first();
        $templates = Template::query()
            ->where('organization_id', '=', $user->getOrganizationId())
            ->where('is_active', '=', 1)
            ->get();

        return $this->render('campaigns.create', [
            'templates' => $templates,
            'user' => $user,
        ]);
    }

    #[Route('/campaigns', 'POST')]
    public function store(CreateCampaignDto $dto): ResponseInterface
    {
        if (!Auth::check()) {
            return $this->redirect('/login');
        }

        /** @var \App\Models\User $user */
        $user = User::query()->where('id', '=', Auth::id())->first();

        $campaign = new Campaign()->fill([
            'organization_id' => $user->getOrganizationId(),
            'template_id' => $dto->template_id,
            'name' => $dto->name,
            'status' => 'draft',
            'created_by' => $user->id,
        ]);
        $campaign->save();

        return $this->redirect("/campaigns/{$campaign->id}");
    }

    #[Route('/campaigns/{id}', 'GET')]
    public function show(ServerRequestInterface $request, int $id): ResponseInterface
    {
        if (!Auth::check()) {
            return $this->redirect('/login');
        }

        /** @var \App\Models\User $user */
        $user = User::query()->where('id', '=', Auth::id())->first();
        /** @var Campaign|null $campaign */
        $campaign = Campaign::query()->where('id', '=', $id)->first();

        if (!$campaign || $campaign->organization_id !== $user->getOrganizationId()) {
            return $this->json(['error' => 'Campaign not found'], 404);
        }

        return $this->render('campaigns.show', [
            'campaign' => $campaign,
            'user' => $user,
            'stats' => $campaign->getStats(),
        ]);
    }

    #[Route('/campaigns/{id}/launch', 'POST')]
    public function launch(ServerRequestInterface $request, int $id): ResponseInterface
    {
        if (!Auth::check()) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        /** @var \App\Models\User $user */
        $user = User::query()->where('id', '=', Auth::id())->first();
        /** @var Campaign|null $campaign */
        $campaign = Campaign::query()->where('id', '=', $id)->first();

        if (!$campaign || $campaign->organization_id !== $user->getOrganizationId()) {
            return $this->json(['error' => 'Campaign not found'], 404);
        }

        if (!$campaign->canStart()) {
            return $this->json(['error' => 'Campaign cannot be started'], 400);
        }

        $campaign->status = 'active';
        $campaign->started_at = date('Y-m-d H:i:s');
        $campaign->save();

        return $this->redirect("/campaigns/{$campaign->id}");
    }
}

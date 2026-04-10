<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\Campaign;
use App\Models\Message;
use App\Models\User;
use App\Services\ObservabilityService;
use Larafony\Framework\Auth\Auth;
use Larafony\Framework\Routing\Advanced\Attributes\Middleware;
use Larafony\Framework\Routing\Advanced\Attributes\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[Middleware(beforeGlobal: [AuthMiddleware::class])]
class EventController extends Controller
{
    private readonly ObservabilityService $observability;

    public function __construct()
    {
        parent::__construct(\Larafony\Framework\Web\Application::instance());
        $this->observability = new ObservabilityService();
    }

    #[Route('/campaign/{id}/events', 'GET')]
    public function campaignEvents(ServerRequestInterface $request, string $id): ResponseInterface
    {
        $campaignId = (int) $id;
        if ($campaignId <= 0) {
            return $this->json(['error' => 'Invalid campaign id'], 422);
        }

        /** @var User $user */
        $user = User::query()->where('id', '=', Auth::id())->first();
        /** @var Campaign|null $campaign */
        $campaign = Campaign::query()->where('id', '=', $campaignId)->first();

        if (!$campaign || $campaign->organization_id !== $user->getOrganizationId()) {
            return $this->json(['error' => 'Campaign not found'], 404);
        }

        $payload = $this->observability->campaignEvents(
            $user->getOrganizationId(),
            $campaignId,
            $request->getQueryParams(),
        );

        if (($request->getQueryParams()['format'] ?? 'html') !== 'json') {
            return $this->render('events.index', [
                'title' => 'Campaign Events',
                'heading' => 'Campaign Event Timeline',
                'subtitle' => 'Observability timeline for campaign #' . $campaignId,
                'payload' => $payload,
                'filters' => $request->getQueryParams(),
                'contextType' => 'campaign',
                'contextId' => $campaignId,
                'backUrl' => '/campaigns/' . $campaignId,
                'user' => $user,
            ]);
        }

        return $this->json($payload);
    }

    #[Route('/message/{id}/events', 'GET')]
    public function messageEvents(ServerRequestInterface $request, string $id): ResponseInterface
    {
        $messageId = (int) $id;
        if ($messageId <= 0) {
            return $this->json(['error' => 'Invalid message id'], 422);
        }

        /** @var User $user */
        $user = User::query()->where('id', '=', Auth::id())->first();
        /** @var Message|null $message */
        $message = Message::query()->where('id', '=', $messageId)->first();

        if (!$message || (int) ($message->organization_id ?? 0) !== $user->getOrganizationId()) {
            return $this->json(['error' => 'Message not found'], 404);
        }

        $payload = $this->observability->messageEvents(
            $user->getOrganizationId(),
            $messageId,
            $request->getQueryParams(),
        );

        if (($request->getQueryParams()['format'] ?? 'html') !== 'json') {
            return $this->render('events.index', [
                'title' => 'Message Events',
                'heading' => 'Message Event Timeline',
                'subtitle' => 'Observability timeline for message #' . $messageId,
                'payload' => $payload,
                'filters' => $request->getQueryParams(),
                'contextType' => 'message',
                'contextId' => $messageId,
                'backUrl' => '/campaigns',
                'user' => $user,
            ]);
        }

        return $this->json($payload);
    }
}

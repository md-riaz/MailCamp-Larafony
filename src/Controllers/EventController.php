<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Campaign;
use App\Models\Message;
use App\Models\User;
use App\Services\ObservabilityService;
use Larafony\Framework\Auth\Auth;
use Larafony\Framework\Routing\Advanced\Attributes\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class EventController extends Controller
{
    public function __construct()
    {
        parent::__construct(\Larafony\Framework\Web\Application::instance());
    }

    #[Route('/campaign/{id}/events', 'GET')]
    public function campaignEvents(ServerRequestInterface $request, string $id): ResponseInterface
    {
        if (!Auth::check()) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

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

        $payload = (new ObservabilityService())->campaignEvents(
            $user->getOrganizationId(),
            $campaignId,
            $request->getQueryParams(),
        );

        return $this->json($payload);
    }

    #[Route('/message/{id}/events', 'GET')]
    public function messageEvents(ServerRequestInterface $request, string $id): ResponseInterface
    {
        if (!Auth::check()) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

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

        $payload = (new ObservabilityService())->messageEvents(
            $user->getOrganizationId(),
            $messageId,
            $request->getQueryParams(),
        );

        return $this->json($payload);
    }
}

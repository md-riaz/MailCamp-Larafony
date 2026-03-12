<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\ProviderWebhookIngestionService;
use App\Services\WebhookSecurityService;
use Larafony\Framework\Routing\Advanced\Attributes\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ProviderWebhookController extends Controller
{
    private ProviderWebhookIngestionService $service;

    public function __construct()
    {
        parent::__construct(\Larafony\Framework\Web\Application::instance());
        $this->service = new ProviderWebhookIngestionService();
    }

    #[Route('/webhook/provider/sendgrid', 'POST')]
    public function sendgrid(ServerRequestInterface $request): ResponseInterface
    {
        return $this->handle($request, 'sendgrid');
    }

    #[Route('/webhook/provider/ses', 'POST')]
    public function ses(ServerRequestInterface $request): ResponseInterface
    {
        return $this->handle($request, 'ses');
    }

    #[Route('/webhook/provider/mailgun', 'POST')]
    public function mailgun(ServerRequestInterface $request): ResponseInterface
    {
        return $this->handle($request, 'mailgun');
    }

    private function handle(ServerRequestInterface $request, string $provider): ResponseInterface
    {
        $raw = (string) $request->getBody();
        if (trim($raw) === '') {
            return $this->json(['ok' => false, 'error' => 'Empty payload'], 400);
        }

        $security = (new WebhookSecurityService())->inspect($request, $raw, $provider);
        if (($security['ok'] ?? false) !== true) {
            return $this->json([
                'ok' => false,
                'error' => 'Webhook rejected',
                'reason' => $security['reason'] ?? 'rejected',
            ], 401);
        }

        $result = $this->service->normalize($provider, $raw);
        return $this->json(['ok' => $result['accepted'], 'result' => $result], $result['accepted'] ? 200 : 422);
    }
}

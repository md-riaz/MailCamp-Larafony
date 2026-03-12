<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\ClickTrackingService;
use Larafony\Framework\Routing\Advanced\Attributes\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ClickTrackingController extends Controller
{
    public function __construct()
    {
        parent::__construct(\Larafony\Framework\Web\Application::instance());
    }

    #[Route('/click/{messageId}', 'GET')]
    public function click(ServerRequestInterface $request, string $messageId): ResponseInterface
    {
        $targetUrl = (string) ($request->getQueryParams()['url'] ?? '');
        $result = (new ClickTrackingService())->track($messageId, $targetUrl, $request);

        if (!($result['ok'] ?? false)) {
            return $this->redirect('/campaigns', 302);
        }

        return $this->redirect((string) $result['redirect_url'], 302);
    }
}

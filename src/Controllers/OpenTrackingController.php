<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\OpenTrackingService;
use Larafony\Framework\Http\Factories\ResponseFactory;
use Larafony\Framework\Http\Factories\StreamFactory;
use Larafony\Framework\Routing\Advanced\Attributes\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class OpenTrackingController extends Controller
{
    private readonly OpenTrackingService $trackingService;

    public function __construct()
    {
        parent::__construct(\Larafony\Framework\Web\Application::instance());
        $this->trackingService = new OpenTrackingService();
    }

    #[Route('/open/{messageId}.png', 'GET')]
    public function pixel(ServerRequestInterface $request, string $messageId): ResponseInterface
    {
        $result = $this->trackingService->track($messageId, $request);

        $response = (new ResponseFactory())
            ->createResponse((int) ($result['status'] ?? 200))
            ->withHeader('Content-Type', 'image/png')
            ->withHeader('Content-Length', (string) strlen($this->trackingService->pixelBinary()))
            ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->withHeader('Pragma', 'no-cache')
            ->withHeader('Expires', '0');

        return $response->withBody((new StreamFactory())->createStream($this->trackingService->pixelBinary()));
    }
}

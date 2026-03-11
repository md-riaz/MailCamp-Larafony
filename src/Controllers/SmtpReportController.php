<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\SmtpReportIngestionService;
use Larafony\Framework\Routing\Advanced\Attributes\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SmtpReportController extends Controller
{
    private SmtpReportIngestionService $service;

    public function __construct()
    {
        parent::__construct(\Larafony\Framework\Web\Application::instance());
        $this->service = new SmtpReportIngestionService();
    }

    #[Route('/webhook/smtp/report', 'POST')]
    public function ingest(ServerRequestInterface $request): ResponseInterface
    {
        $raw = (string) $request->getBody();
        if (trim($raw) === '') {
            return $this->json([
                'ok' => false,
                'error' => 'Empty payload',
            ], 400);
        }

        $source = 'smtp-http';
        $result = $this->service->ingest($raw, $source);

        return $this->json([
            'ok' => true,
            'result' => $result,
        ]);
    }
}

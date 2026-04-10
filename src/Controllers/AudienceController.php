<?php

declare(strict_types=1);

namespace App\Controllers;

use App\DTOs\CreateAudienceDto;
use App\Services\AudienceService;
use Larafony\Framework\Routing\Advanced\Attributes\Route;
use Larafony\Framework\Web\Application;
use Psr\Http\Message\ResponseInterface;

final class AudienceController extends Controller
{
    private readonly AudienceService $audienceService;

    public function __construct()
    {
        parent::__construct(Application::instance());
        $this->audienceService = new AudienceService();
    }

    #[Route('/audiences', methods: ['POST'])]
    public function create(CreateAudienceDto $dto): ResponseInterface
    {
        $audience = $this->audienceService->createAudience(
            name: $dto->name,
            description: $dto->description
        );

        return $this->json([
            'message' => 'Audience created successfully!',
            'audience' => [
                'id' => $audience->id,
                'name' => $audience->name,
                'description' => $audience->description,
            ],
        ], 201);
    }

    #[Route('/audiences', methods: ['GET'])]
    public function list(): ResponseInterface
    {
        $audiences = $this->audienceService->listAudiences();

        return $this->json($audiences);
    }
}

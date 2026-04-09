<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Audience;
use App\Services\AudienceService;
use Larafony\Framework\Http\Request;
use Larafony\Framework\Http\Response;

final class AudienceController
{
    public function create(Request $request): Response
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'string|nullable',
        ]);

        $service = new AudienceService();
        $audience = $service->createAudience(
            name: $validated['name'],
            description: $validated['description'] ?? null
        );

        return Response::json([
            'message' => 'Audience created successfully!',
            'audience' => $audience,
        ], 201);
    }

    public function list(): Response
    {
        $service = new AudienceService();
        $audiences = $service->listAudiences();

        return Response::json($audiences);
    }
}

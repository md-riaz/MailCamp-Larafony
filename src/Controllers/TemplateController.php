<?php

declare(strict_types=1);

namespace App\Controllers;

use App\DTOs\CreateTemplateDto;
use App\Models\Template;
use Larafony\Framework\Auth\Auth;
use Larafony\Framework\Database\Base\Query\Enums\OrderDirection;
use Larafony\Framework\Routing\Advanced\Attributes\Route;
use Larafony\Framework\Web\Controller;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TemplateController extends Controller
{
    public function __construct()
    {
        parent::__construct(\Larafony\Framework\Web\Application::instance());
    }

    #[Route('/templates', 'GET')]
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        if (!Auth::check()) {
            return $this->redirect('/login');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $templates = Template::query()
            ->where('organization_id', '=', $user->organization_id)
            ->orderBy('created_at', OrderDirection::DESC)
            ->get();

        return $this->render('templates.index', [
            'templates' => $templates,
            'user' => $user,
        ]);
    }

    #[Route('/templates/create', 'GET')]
    public function create(ServerRequestInterface $request): ResponseInterface
    {
        if (!Auth::check()) {
            return $this->redirect('/login');
        }

        return $this->render('templates.create', [
            'user' => Auth::user(),
        ]);
    }

    #[Route('/templates', 'POST')]
    public function store(CreateTemplateDto $dto): ResponseInterface
    {
        if (!Auth::check()) {
            return $this->redirect('/login');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $template = new Template()->fill([
            'organization_id' => $user->organization_id,
            'name' => $dto->name,
            'subject' => $dto->subject,
            'html_content' => $dto->html_content,
            'is_active' => true,
        ]);

        // Parse and store variables
        $variables = $template->parseVariables();
        $template->variables = json_encode($variables);
        $template->save();

        return $this->redirect('/templates');
    }

    #[Route('/templates/{id}', 'GET')]
    public function edit(ServerRequestInterface $request, int $id): ResponseInterface
    {
        if (!Auth::check()) {
            return $this->redirect('/login');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        /** @var Template|null $template */
        $template = Template::query()->where('id', '=', $id)->first();
        /** @var Template|null $template */
        $template = Template::query()->where('id', '=', $id)->first();

        if (!$template || $template->organization_id !== $user->organization_id) {
            return $this->json(['error' => 'Template not found'], 404);
        }

        return $this->render('templates.edit', [
            'template' => $template,
            'user' => $user,
        ]);
    }

    #[Route('/templates/{id}', ['PUT', 'POST'])]
    public function update(ServerRequestInterface $request, int $id, CreateTemplateDto $dto): ResponseInterface
    {
        if (!Auth::check()) {
            return $this->redirect('/login');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        /** @var Template|null $template */
        $template = Template::query()->where('id', '=', $id)->first();

        if (!$template || $template->organization_id !== $user->organization_id) {
            return $this->json(['error' => 'Template not found'], 404);
        }

        $template->fill([
            'name' => $dto->name,
            'subject' => $dto->subject,
            'html_content' => $dto->html_content,
        ]);

        // Parse and update variables
        $variables = $template->parseVariables();
        $template->variables = json_encode($variables);
        $template->save();

        return $this->redirect('/templates');
    }

    #[Route('/templates/{id}', 'DELETE')]
    public function destroy(ServerRequestInterface $request, int $id): ResponseInterface
    {
        if (!Auth::check()) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        /** @var Template|null $template */
        $template = Template::query()->where('id', '=', $id)->first();        if (!$template || $template->organization_id !== $user->organization_id) {
            return $this->json(['error' => 'Template not found'], 404);
        }

        Template::query()->where('id', '=', $id)->delete();

        return $this->redirect('/templates');
    }
}

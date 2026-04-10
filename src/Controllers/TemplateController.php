<?php

declare(strict_types=1);

namespace App\Controllers;

use App\DTOs\CreateTemplateDto;
use App\Models\Template;
use App\Models\User;
use Larafony\Framework\Auth\Auth;
use Larafony\Framework\Database\Base\Query\Enums\OrderDirection;
use Larafony\Framework\Routing\Advanced\Attributes\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;

class TemplateController extends Controller
{
    private const int PER_PAGE = 25;

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
        $user = User::query()->where('id', '=', Auth::id())->first();

        $queryParams = $request->getQueryParams();
        $page = max(1, (int) ($queryParams['page'] ?? 1));
        $offset = ($page - 1) * self::PER_PAGE;

        $templates = Template::query()
            ->where('organization_id', '=', $user->getOrganizationId())
            ->orderBy('created_at', OrderDirection::DESC)
            ->limit(self::PER_PAGE)
            ->offset($offset)
            ->get();

        return $this->render('templates.index', [
            'templates' => $templates,
            'user' => $user,
            'filters' => [
                'page' => $page,
                'perPage' => self::PER_PAGE,
            ],
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
        $user = User::query()->where('id', '=', Auth::id())->first();

        $template = new Template()->fill([
            'organization_id' => $user->getOrganizationId(),
            'name' => $dto->name,
            'subject' => $dto->subject,
            'html_content' => $dto->html_content,
            'is_active' => 1,
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
        $user = User::query()->where('id', '=', Auth::id())->first();
        /** @var Template|null $template */
        $template = Template::query()->where('id', '=', $id)->first();

        if (!$template || $template->organization_id !== $user->getOrganizationId()) {
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
        $user = User::query()->where('id', '=', Auth::id())->first();
        /** @var Template|null $template */
        $template = Template::query()->where('id', '=', $id)->first();

        if (!$template || $template->organization_id !== $user->getOrganizationId()) {
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

    #[Route('/templates/upload-image', 'POST')]
    public function uploadImage(ServerRequestInterface $request): ResponseInterface
    {
        $ckEditorFuncNum = (string) (($request->getQueryParams()['CKEditorFuncNum'] ?? ''));
        $respondForCkeditor4 = static function (string $message, string $url = '', int $status = 200): ResponseInterface {
            $safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
            $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
            $safeFuncNum = htmlspecialchars((string) (($_GET['CKEditorFuncNum'] ?? '')), ENT_QUOTES, 'UTF-8');
            return new \Laminas\Diactoros\Response\HtmlResponse(
                '<script>window.parent.CKEDITOR.tools.callFunction(' . $safeFuncNum . ', ' . json_encode($safeUrl) . ', ' . json_encode($safeMessage) . ');</script>',
                $status
            );
        };

        if (!Auth::check()) {
            if ($ckEditorFuncNum !== '') {
                return $respondForCkeditor4('Unauthorized', '', 401);
            }
            return $this->json(['error' => ['message' => 'Unauthorized']], 401);
        }

        /** @var \App\Models\User $user */
        $user = User::query()->where('id', '=', Auth::id())->first();
        $uploadedFiles = $request->getUploadedFiles();
        $uploadedFile = $uploadedFiles['upload'] ?? $uploadedFiles['file'] ?? null;

        if (!$uploadedFile instanceof UploadedFileInterface) {
            if ($ckEditorFuncNum !== '') {
                return $respondForCkeditor4('No image file uploaded.', '', 422);
            }
            return $this->json(['error' => ['message' => 'No image file uploaded.']], 422);
        }

        if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
            if ($ckEditorFuncNum !== '') {
                return $respondForCkeditor4('Upload failed.', '', 422);
            }
            return $this->json(['error' => ['message' => 'Upload failed.']], 422);
        }

        $clientFilename = (string) ($uploadedFile->getClientFilename() ?? '');
        $extension = strtolower(pathinfo($clientFilename, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($extension, $allowedExtensions, true)) {
            if ($ckEditorFuncNum !== '') {
                return $respondForCkeditor4('Only jpg, png, gif, and webp images are allowed.', '', 422);
            }
            return $this->json(['error' => ['message' => 'Only jpg, png, gif, and webp images are allowed.']], 422);
        }

        if ($uploadedFile->getSize() > 5 * 1024 * 1024) {
            if ($ckEditorFuncNum !== '') {
                return $respondForCkeditor4('Image exceeds 5MB limit.', '', 422);
            }
            return $this->json(['error' => ['message' => 'Image exceeds 5MB limit.']], 422);
        }

        $targetDir = dirname(__DIR__, 2) . '/public/uploads/templates/org-' . $user->getOrganizationId();
        if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
            if ($ckEditorFuncNum !== '') {
                return $respondForCkeditor4('Failed to prepare upload directory.', '', 500);
            }
            return $this->json(['error' => ['message' => 'Failed to prepare upload directory.']], 500);
        }

        $filename = sprintf('%s.%s', bin2hex(random_bytes(16)), $extension);
        $targetPath = $targetDir . '/' . $filename;
        $uploadedFile->moveTo($targetPath);

        $baseUrl = rtrim((string) ($_ENV['APP_URL'] ?? getenv('APP_URL') ?: ''), '/');
        $url = $baseUrl . '/public/uploads/templates/org-' . $user->getOrganizationId() . '/' . $filename;

        if ($ckEditorFuncNum !== '') {
            return $respondForCkeditor4('Image uploaded successfully.', $url);
        }

        return $this->json([
            'url' => $url,
            'uploaded' => true,
            'fileName' => $filename,
        ]);
    }

    #[Route('/templates/{id}', 'DELETE')]
    public function destroy(ServerRequestInterface $request, int $id): ResponseInterface
    {
        if (!Auth::check()) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        /** @var \App\Models\User $user */
        $user = User::query()->where('id', '=', Auth::id())->first();
        /** @var Template|null $template */
        $template = Template::query()->where('id', '=', $id)->first();

        if (!$template || $template->organization_id !== $user->getOrganizationId()) {
            return $this->json(['error' => 'Template not found'], 404);
        }

        Template::query()->where('id', '=', $id)->delete();

        return $this->redirect('/templates');
    }
}

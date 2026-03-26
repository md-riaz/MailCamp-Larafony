<?php

declare(strict_types=1);

namespace App\Controllers;

use App\DTOs\CreateSmtpSettingDto;
use App\Models\SmtpSetting;
use App\Models\User;
use Larafony\Framework\Auth\Auth;
use Larafony\Framework\Database\Base\Query\Enums\OrderDirection;
use Larafony\Framework\Routing\Advanced\Attributes\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SmtpSettingController extends Controller
{
    public function __construct()
    {
        parent::__construct(\Larafony\Framework\Web\Application::instance());
    }

    #[Route('/smtp-settings', 'GET')]
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        if (!Auth::check()) {
            return $this->redirect('/login');
        }

        /** @var \App\Models\User $user */
        $user = User::query()->where('id', '=', Auth::id())->first();
        $smtpSettings = SmtpSetting::query()
            ->where('organization_id', '=', $user->getOrganizationId())
            ->orderBy('is_active', OrderDirection::DESC)
            ->orderBy('created_at', OrderDirection::DESC)
            ->get();

        $activeSmtp = null;
        foreach ($smtpSettings as $setting) {
            if (!empty($setting->is_active)) {
                $activeSmtp = $setting;
                break;
            }
        }

        return $this->render('smtp.index', [
            'smtpSettings' => $smtpSettings,
            'activeSmtp' => $activeSmtp,
            'user' => $user,
        ]);
    }

    #[Route('/smtp-settings', 'POST')]
    public function store(ServerRequestInterface $request, CreateSmtpSettingDto $dto): ResponseInterface
    {
        if (!Auth::check()) {
            return $this->redirect('/login');
        }

        /** @var \App\Models\User $user */
        $user = User::query()->where('id', '=', Auth::id())->first();
        $body = (array) ($request->getParsedBody() ?? []);
        $isActive = !empty($body['is_active']) ? 1 : 0;

        $smtpSetting = new SmtpSetting()->fill([
            'organization_id' => $user->getOrganizationId(),
            'host' => $dto->host,
            'port' => $dto->port,
            'encryption' => $dto->encryption,
            'username' => $dto->username,
            'password' => SmtpSetting::encryptPassword($dto->password),
            'from_email' => $dto->from_email,
            'from_name' => $dto->from_name,
            'is_active' => $isActive,
        ]);

        $smtpSetting->save();

        return $this->redirect('/smtp-settings?notice=smtp_saved');
    }

    #[Route('/smtp-settings/{id}/activate', 'POST')]
    public function activate(ServerRequestInterface $request, int $id): ResponseInterface
    {
        if (!Auth::check()) {
            return $this->redirect('/login');
        }

        /** @var \App\Models\User $user */
        $user = User::query()->where('id', '=', Auth::id())->first();
        /** @var SmtpSetting|null $smtpSetting */
        $smtpSetting = SmtpSetting::query()
            ->where('id', '=', $id)
            ->where('organization_id', '=', $user->getOrganizationId())
            ->first();

        if (!$smtpSetting) {
            return $this->redirect('/smtp-settings?notice=smtp_missing');
        }

        $smtpSetting->is_active = 1;
        $smtpSetting->save();

        return $this->redirect('/smtp-settings?notice=smtp_activated');
    }

    #[Route('/smtp-settings/{id}/deactivate', 'POST')]
    public function deactivate(ServerRequestInterface $request, int $id): ResponseInterface
    {
        if (!Auth::check()) {
            return $this->redirect('/login');
        }

        /** @var \App\Models\User $user */
        $user = User::query()->where('id', '=', Auth::id())->first();
        /** @var SmtpSetting|null $smtpSetting */
        $smtpSetting = SmtpSetting::query()
            ->where('id', '=', $id)
            ->where('organization_id', '=', $user->getOrganizationId())
            ->first();

        if (!$smtpSetting) {
            return $this->redirect('/smtp-settings?notice=smtp_missing');
        }

        $smtpSetting->is_active = 0;
        $smtpSetting->save();

        return $this->redirect('/smtp-settings?notice=smtp_deactivated');
    }
}

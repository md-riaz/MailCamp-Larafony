<?php

declare(strict_types=1);

namespace App\Controllers;

use App\DTOs\CreateSmtpSettingDto;
use App\Models\SmtpSetting;
use App\Models\User;
use Larafony\Framework\Auth\Auth;
use Larafony\Framework\Routing\Advanced\Attributes\Route;
use Larafony\Framework\Web\Controller;
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
        $smtpSetting = SmtpSetting::query()
            ->where('organization_id', '=', $user->getOrganizationId())
            ->first();

        return $this->render('smtp.index', [
            'smtpSetting' => $smtpSetting,
            'user' => $user,
        ]);
    }

    #[Route('/smtp-settings', 'POST')]
    public function store(CreateSmtpSettingDto $dto): ResponseInterface
    {
        if (!Auth::check()) {
            return $this->redirect('/login');
        }

        /** @var \App\Models\User $user */
        $user = User::query()->where('id', '=', Auth::id())->first();

        // Check if settings already exist
        $smtpSetting = SmtpSetting::query()
            ->where('organization_id', '=', $user->getOrganizationId())
            ->first();

        if ($smtpSetting) {
            // Update existing
            $smtpSetting->fill([
                'host' => $dto->host,
                'port' => $dto->port,
                'encryption' => $dto->encryption,
                'username' => $dto->username,
                'password' => SmtpSetting::encryptPassword($dto->password),
                'from_email' => $dto->from_email,
                'from_name' => $dto->from_name,
                'is_active' => 1,
            ]);
        } else {
            // Create new
            $smtpSetting = new SmtpSetting()->fill([
                'organization_id' => $user->getOrganizationId(),
                'host' => $dto->host,
                'port' => $dto->port,
                'encryption' => $dto->encryption,
                'username' => $dto->username,
                'password' => SmtpSetting::encryptPassword($dto->password),
                'from_email' => $dto->from_email,
                'from_name' => $dto->from_name,
                'is_active' => 1,
            ]);
        }

        $smtpSetting->save();

        return $this->redirect('/smtp-settings');
    }
}

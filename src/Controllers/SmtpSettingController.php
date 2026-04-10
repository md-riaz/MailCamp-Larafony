<?php

declare(strict_types=1);

namespace App\Controllers;

use App\DTOs\CreateSmtpSettingDto;
use App\Middleware\AuthMiddleware;
use App\Models\Campaign;
use App\Models\SmtpSetting;
use App\Models\User;
use Larafony\Framework\Auth\Auth;
use Larafony\Framework\Database\Base\Query\Enums\OrderDirection;
use Larafony\Framework\Database\Schema;
use Larafony\Framework\Routing\Advanced\Attributes\Middleware;
use Larafony\Framework\Routing\Advanced\Attributes\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[Middleware(beforeGlobal: [AuthMiddleware::class])]
class SmtpSettingController extends Controller
{
    private const int PER_PAGE = 25;

    public function __construct()
    {
        parent::__construct(\Larafony\Framework\Web\Application::instance());
    }

    #[Route('/smtp-settings', 'GET')]
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        /** @var \App\Models\User $user */
        $user = User::query()->where('id', '=', Auth::id())->first();

        $queryParams = $request->getQueryParams();
        $page = max(1, (int) ($queryParams['page'] ?? 1));
        $offset = ($page - 1) * self::PER_PAGE;

        $smtpSettings = SmtpSetting::query()
            ->where('organization_id', '=', $user->getOrganizationId())
            ->orderBy('is_active', OrderDirection::DESC)
            ->orderBy('created_at', OrderDirection::DESC)
            ->limit(self::PER_PAGE)
            ->offset($offset)
            ->get();

        $activeSmtp = null;
        foreach ($smtpSettings as $setting) {
            if (!empty($setting->is_active)) {
                $activeSmtp = $setting;
                break;
            }
        }

        $canDeleteById = [];
        foreach ($smtpSettings as $setting) {
            $canDeleteById[(int) $setting->id] = $this->canDeleteSmtpSetting($setting, $user->getOrganizationId());
        }

        return $this->render('smtp.index', [
            'smtpSettings' => $smtpSettings,
            'activeSmtp' => $activeSmtp,
            'canDeleteById' => $canDeleteById,
            'user' => $user,
            'filters' => [
                'page' => $page,
                'perPage' => self::PER_PAGE,
            ],
        ]);
    }

    #[Route('/smtp-settings', 'POST')]
    public function store(CreateSmtpSettingDto $dto): ResponseInterface
    {
        /** @var \App\Models\User $user */
        $user = User::query()->where('id', '=', Auth::id())->first();

        $smtpSetting = new SmtpSetting()->fill([
            'organization_id' => $user->getOrganizationId(),
            'host' => $dto->host,
            'port' => (int) $dto->port,
            'encryption' => $dto->encryption,
            'username' => $dto->username,
            'password' => SmtpSetting::encryptPassword($dto->password),
            'from_email' => $dto->from_email,
            'from_name' => $dto->from_name,
            'is_active' => $dto->is_active ? 1 : 0,
        ]);

        $smtpSetting->save();

        return $this->redirect('/smtp-settings?notice=smtp_saved');
    }

    #[Route('/smtp-settings/{id}/activate', 'POST')]
    public function activate(ServerRequestInterface $request, int $id): ResponseInterface
    {
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

    #[Route('/smtp-settings/{id}/delete', 'POST')]
    public function destroy(ServerRequestInterface $request, int $id): ResponseInterface
    {
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

        if (!$this->canDeleteSmtpSetting($smtpSetting, $user->getOrganizationId())) {
            return $this->redirect('/smtp-settings?notice=smtp_in_use');
        }

        $smtpSetting->delete();

        return $this->redirect('/smtp-settings?notice=smtp_deleted');
    }

    private function canDeleteSmtpSetting(SmtpSetting $smtpSetting, int $organizationId): bool
    {
        if ($this->campaignsTableHasSmtpSettingId()) {
            $campaignUsingThisSmtp = Campaign::query()
                ->where('organization_id', '=', $organizationId)
                ->where('smtp_setting_id', '=', $smtpSetting->id)
                ->first();

            return $campaignUsingThisSmtp === null;
        }

        $organizationHasAnyCampaign = Campaign::query()
            ->where('organization_id', '=', $organizationId)
            ->first();

        return $organizationHasAnyCampaign === null;
    }

    private function campaignsTableHasSmtpSettingId(): bool
    {
        try {
            $columns = Schema::getColumnListing('campaigns');
            return in_array('smtp_setting_id', $columns, true);
        } catch (\Throwable) {
            return false;
        }
    }
}

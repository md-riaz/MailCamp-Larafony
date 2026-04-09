<?php

declare(strict_types=1);

namespace App\Controllers;

use App\DTOs\CreateSmtpSettingDto;
use App\Models\Campaign;
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

        $canDeleteById = [];
        foreach ($smtpSettings as $setting) {
            $canDeleteById[(int) $setting->id] = $this->canDeleteSmtpSetting($setting, $user->getOrganizationId());
        }

        return $this->render('smtp.index', [
            'smtpSettings' => $smtpSettings,
            'activeSmtp' => $activeSmtp,
            'canDeleteById' => $canDeleteById,
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
        $isActive = isset($_POST['is_active']) && (string) $_POST['is_active'] !== '' ? 1 : 0;

        $smtpSetting = new SmtpSetting()->fill([
            'organization_id' => $user->getOrganizationId(),
            'host' => $dto->host,
            'port' => (int) $dto->port,
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

    #[Route('/smtp-settings/{id}/delete', 'POST')]
    public function destroy(ServerRequestInterface $request, int $id): ResponseInterface
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
        $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: '127.0.0.1';
        $port = (int) ($_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: 3306);
        $database = $_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: '';
        $username = $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME') ?: '';
        $password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '';

        if ($database === '' || $username === '') {
            return false;
        }

        try {
            $pdo = new \PDO(
                sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $database),
                (string) $username,
                (string) $password,
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                ]
            );

            $stmt = $pdo->prepare('SELECT COUNT(*) AS column_count FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = :database AND TABLE_NAME = :table AND COLUMN_NAME = :column');
            $stmt->execute([
                'database' => $database,
                'table' => 'campaigns',
                'column' => 'smtp_setting_id',
            ]);

            $row = $stmt->fetch();
            return (int) (($row['column_count'] ?? 0)) > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }
}

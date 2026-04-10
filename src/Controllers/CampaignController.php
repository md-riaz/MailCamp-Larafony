<?php

declare(strict_types=1);

namespace App\Controllers;

use App\DTOs\CreateCampaignDto;
use App\DTOs\UpdateCampaignDto;
use App\Middleware\AuthMiddleware;
use App\Models\Campaign;
use App\Models\QueueJob;
use App\Models\Recipient;
use App\Models\SmtpSetting;
use App\Models\Template;
use App\Models\User;
use App\Services\CampaignMessageLifecycleService;
use App\Services\CampaignRiskHistoryService;
use App\Services\CampaignSafetyService;
use App\Services\ObservabilityService;
use App\Services\TemplateValidationService;
use App\ViewDto\CampaignViewDto;
use Larafony\Framework\Auth\Auth;
use Larafony\Framework\Database\Base\Query\Enums\OrderDirection;
use Larafony\Framework\Routing\Advanced\Attributes\Middleware;
use Larafony\Framework\Routing\Advanced\Attributes\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;

#[Middleware(beforeGlobal: [AuthMiddleware::class])]
class CampaignController extends Controller
{
    private const int PER_PAGE = 25;
    private const int MAX_IMPORT_RECIPIENTS = 10000;
    private const int MAX_IMPORT_FILE_SIZE = 5 * 1024 * 1024; // 5MB

    private readonly ObservabilityService $observability;
    private readonly CampaignSafetyService $safetyService;
    private readonly CampaignRiskHistoryService $riskHistory;
    private readonly CampaignMessageLifecycleService $lifecycleService;
    private readonly TemplateValidationService $templateValidation;

    public function __construct()
    {
        parent::__construct(\Larafony\Framework\Web\Application::instance());
        $this->observability = new ObservabilityService();
        $this->safetyService = new CampaignSafetyService();
        $this->riskHistory = new CampaignRiskHistoryService();
        $this->lifecycleService = new CampaignMessageLifecycleService();
        $this->templateValidation = new TemplateValidationService();
    }

    #[Route('/campaigns', 'GET')]
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        /** @var \App\Models\User $user */
        $user = User::query()->where('id', '=', Auth::id())->first();

        $queryParams = $request->getQueryParams();
        $search = trim((string) ($queryParams['q'] ?? ''));
        $status = trim((string) ($queryParams['status'] ?? ''));
        $sort = (string) ($queryParams['sort'] ?? 'created_desc');
        $sortDirection = $sort === 'created_asc' ? OrderDirection::ASC : OrderDirection::DESC;
        $normalizedSort = $sort === 'created_asc' ? 'created_asc' : 'created_desc';
        $page = max(1, (int) ($queryParams['page'] ?? 1));
        $offset = ($page - 1) * self::PER_PAGE;

        $campaignQuery = Campaign::query()
            ->where('organization_id', '=', $user->getOrganizationId());

        if ($search !== '') {
            $campaignQuery->whereLike('name', '%' . $search . '%');
        }

        $allowedStatuses = [
            'draft' => 'Draft',
            'active' => 'Active',
            'sending' => 'Sending',
            'sent' => 'Sent',
            'failed' => 'Failed',
        ];

        if ($status !== '' && array_key_exists($status, $allowedStatuses)) {
            $campaignQuery->where('status', '=', $status);
        } else {
            $status = '';
        }

        $campaigns = $campaignQuery
            ->orderBy('created_at', $sortDirection)
            ->limit(self::PER_PAGE)
            ->offset($offset)
            ->get();

        $campaignViews = array_map(
            static fn (Campaign $campaign): CampaignViewDto => CampaignViewDto::fromModel($campaign),
            $campaigns
        );

        return $this->render('campaigns.index', [
            'campaigns' => $campaignViews,
            'filters' => [
                'q' => $search,
                'status' => $status,
                'sort' => $normalizedSort,
                'page' => $page,
                'perPage' => self::PER_PAGE,
            ],
            'statusOptions' => $allowedStatuses,
            'notice' => $this->resolveCampaignIndexNotice($request),
            'user' => $user,
        ]);
    }

    #[Route('/campaigns/create', 'GET')]
    public function create(ServerRequestInterface $request): ResponseInterface
    {
        /** @var \App\Models\User $user */
        $user = User::query()->where('id', '=', Auth::id())->first();
        $templates = Template::query()
            ->where('organization_id', '=', $user->getOrganizationId())
            ->where('is_active', '=', 1)
            ->get();
        $smtpSettings = SmtpSetting::query()
            ->where('organization_id', '=', $user->getOrganizationId())
            ->where('is_active', '=', 1)
            ->orderBy('id')
            ->get();

        return $this->render('campaigns.create', [
            'templates' => $templates,
            'smtpSettings' => $smtpSettings,
            'user' => $user,
        ]);
    }

    #[Route('/campaigns', 'POST')]
    public function store(CreateCampaignDto $dto): ResponseInterface
    {
        /** @var \App\Models\User $user */
        $user = User::query()->where('id', '=', Auth::id())->first();

        $subject = trim((string) ($dto->subject ?? ''));
        $html = (string) ($dto->html_content ?? '');
        if ($subject === '' || trim(strip_tags($html)) === '') {
            return $this->redirect('/campaigns/create?notice=content_missing');
        }

        $saveAsTemplate = (bool) ($dto->save_as_template ?? false);

        $smtpSetting = SmtpSetting::query()
            ->where('organization_id', '=', $user->getOrganizationId())
            ->where('id', '=', (int) $dto->smtp_setting_id)
            ->where('is_active', '=', 1)
            ->first();
        if (!$smtpSetting) {
            return $this->redirect('/campaigns/create?notice=smtp_missing');
        }

        $template = $this->syncCampaignTemplate(
            user: $user,
            campaignName: $dto->name,
            subject: $subject,
            html: $html,
            template: null,
            saveAsTemplate: $saveAsTemplate,
        );

        $campaign = new Campaign();
        $campaign->organization_id = (int) $user->getOrganizationId();
        $campaign->template_id = (int) $template->id;
        $campaign->smtp_setting_id = (int) $smtpSetting->id;
        $campaign->name = $dto->name;
        $campaign->status = 'draft';
        $campaign->created_by = (int) $user->id;
        $campaign->save();

        return $this->redirect("/campaigns/{$campaign->id}");
    }

    #[Route('/campaigns/{id}/edit', 'GET')]
    public function edit(ServerRequestInterface $request, int $id): ResponseInterface
    {
        /** @var \App\Models\User $user */
        $user = User::query()->where('id', '=', Auth::id())->first();
        /** @var Campaign|null $campaign */
        $campaign = Campaign::query()->where('id', '=', $id)->first();

        if (!$campaign || $campaign->organization_id !== $user->getOrganizationId()) {
            return $this->redirect('/campaigns?notice=campaign_not_found');
        }

        if (!$campaign->canEdit()) {
            return $this->redirect('/campaigns/' . $campaign->id . '?notice=campaign_locked');
        }

        return $this->redirect('/campaigns/' . $campaign->id . '?workspace=edit');
    }

    #[Route('/campaigns/{id}', ['PUT', 'POST'])]
    public function update(ServerRequestInterface $request, int $id, UpdateCampaignDto $dto): ResponseInterface
    {
        /** @var \App\Models\User $user */
        $user = User::query()->where('id', '=', Auth::id())->first();
        /** @var Campaign|null $campaign */
        $campaign = Campaign::query()->where('id', '=', $id)->first();

        if (!$campaign || $campaign->organization_id !== $user->getOrganizationId()) {
            return $this->redirect('/campaigns?notice=campaign_not_found');
        }

        if (!$campaign->canEdit()) {
            return $this->redirect('/campaigns/' . $campaign->id . '?notice=campaign_locked');
        }

        $subject = trim((string) ($dto->subject ?? ''));
        $html = (string) ($dto->html_content ?? '');
        if ($subject === '' || trim(strip_tags($html)) === '') {
            return $this->redirect('/campaigns/' . $campaign->id . '?notice=content_missing');
        }

        $smtpSetting = SmtpSetting::query()
            ->where('organization_id', '=', $user->getOrganizationId())
            ->where('id', '=', (int) $dto->smtp_setting_id)
            ->where('is_active', '=', 1)
            ->first();
        if (!$smtpSetting) {
            return $this->redirect('/campaigns/' . $campaign->id . '?notice=smtp_missing');
        }

        /** @var Template|null $template */
        $template = Template::query()
            ->where('id', '=', $campaign->template_id)
            ->where('organization_id', '=', $user->getOrganizationId())
            ->first();
        if (!$template) {
            return $this->redirect('/campaigns/' . $campaign->id . '?notice=template_missing');
        }

        $campaign->name = $dto->name;
        $campaign->smtp_setting_id = $smtpSetting->id;

        $this->syncCampaignTemplate(
            user: $user,
            campaignName: $dto->name,
            subject: $subject,
            html: $html,
            template: $template,
            saveAsTemplate: (bool) ($template->is_active ?? false),
        );

        $scheduledAt = trim((string) ($dto->scheduled_at ?? ''));
        $scheduledTimestamp = $scheduledAt !== '' ? strtotime($scheduledAt) : false;
        $campaign->scheduled_at = $scheduledTimestamp ? date('Y-m-d H:i:s', $scheduledTimestamp) : null;
        if ($campaign->scheduled_at !== null && in_array((string) $campaign->status, ['draft', 'paused'], true)) {
            $campaign->status = 'scheduled';
        } elseif ($campaign->scheduled_at === null && (string) $campaign->status === 'scheduled') {
            $campaign->status = 'draft';
        }

        $campaign->save();

        return $this->redirect('/campaigns/' . $campaign->id . '?notice=campaign_updated');
    }

    #[Route('/campaigns/<id:\\d+>', 'GET')]
    public function show(ServerRequestInterface $request, string $id): ResponseInterface
    {
        $campaignId = (int) $id;
        if ($campaignId <= 0) {
            return $this->redirect('/campaigns?notice=invalid_campaign_link');
        }

        /** @var \App\Models\User $user */
        $user = User::query()->where('id', '=', Auth::id())->first();
        /** @var Campaign|null $campaign */
        $campaign = Campaign::query()->where('id', '=', $campaignId)->first();

        if (!$campaign || $campaign->organization_id !== $user->getOrganizationId()) {
            return $this->redirect('/campaigns?notice=campaign_not_found');
        }

        $observability = $this->observability;
        /** @var Template|null $template */
        $template = Template::query()->where('id', '=', $campaign->template_id)->first();
        $smtpSetting = null;
        if ($campaign->smtp_setting_id) {
            $smtpSetting = SmtpSetting::query()
                ->where('id', '=', $campaign->smtp_setting_id)
                ->where('organization_id', '=', $user->getOrganizationId())
                ->first();
        }
        if (!$smtpSetting) {
            $smtpSetting = SmtpSetting::query()
                ->where('organization_id', '=', $user->getOrganizationId())
                ->where('is_active', '=', 1)
                ->first();
        }
        $riskHistory = $this->riskHistory;
        $safety = $this->safetyService->evaluate($campaign, $template, $smtpSetting);

        $templates = Template::query()
            ->where('organization_id', '=', $user->getOrganizationId())
            ->where('is_active', '=', 1)
            ->get();
        $smtpSettings = SmtpSetting::query()
            ->where('organization_id', '=', $user->getOrganizationId())
            ->where('is_active', '=', 1)
            ->orderBy('id')
            ->get();

        return $this->render('campaigns.show', [
            'campaign' => $campaign,
            'user' => $user,
            'stats' => $campaign->getStats(),
            'campaignMetrics' => $observability->campaignMetrics($user->getOrganizationId(), $campaign->id),
            'bounceBreakdown' => $observability->campaignBounceBreakdown($campaign->id),
            'recentEvents' => $observability->recentCampaignEvents($user->getOrganizationId(), $campaign->id, 15),
            'riskHistory' => $riskHistory->recent($campaign, 8),
            'safety' => $safety,
            'smtpSetting' => $smtpSetting,
            'smtpSettings' => $smtpSettings,
            'template' => $template,
            'templates' => $templates,
            'notice' => $this->resolveCampaignDetailNotice($request),
        ]);
    }

    #[Route('/campaigns/<id:\\d+>/recipients', 'POST')]
    public function importRecipients(ServerRequestInterface $request, string $id): ResponseInterface
    {
        $campaignId = (int) $id;
        if ($campaignId <= 0) {
            return $this->redirect('/campaigns?notice=invalid_campaign_link');
        }

        /** @var \App\Models\User $user */
        $user = User::query()->where('id', '=', Auth::id())->first();
        /** @var Campaign|null $campaign */
        $campaign = Campaign::query()->where('id', '=', $campaignId)->first();

        if (!$campaign || $campaign->organization_id !== $user->getOrganizationId()) {
            return $this->redirect('/campaigns?notice=campaign_not_found');
        }

        if ($campaign->status !== 'draft') {
            return $this->redirect('/campaigns/' . $campaign->id . '?notice=campaign_locked');
        }

        $body = $request->getParsedBody();
        $manualRecipients = is_array($body) ? trim((string) ($body['manual_recipients'] ?? '')) : '';

        try {
            if ($manualRecipients !== '') {
                $result = $this->storeRecipientsFromText($campaign, $manualRecipients);
            } else {
                $uploadedFiles = $request->getUploadedFiles();
                $uploadedFile = $uploadedFiles['recipients_file'] ?? null;
                if (!$uploadedFile instanceof UploadedFileInterface) {
                    return $this->redirect('/campaigns/' . $campaign->id . '?notice=missing_recipient_file');
                }

                $result = $this->storeRecipientsFromCsv($campaign, $uploadedFile);
            }
        } catch (\Throwable $exception) {
            return $this->redirect('/campaigns/' . $campaign->id . '?notice=invalid_recipient_file');
        }

        if ($result['imported'] === 0 && $result['skipped'] > 0) {
            return $this->redirect('/campaigns/' . $campaign->id . '?notice=recipient_import_skipped');
        }

        if ($result['imported'] === 0) {
            return $this->redirect('/campaigns/' . $campaign->id . '?notice=recipient_import_empty');
        }

        return $this->redirect('/campaigns/' . $campaign->id . '?notice=recipient_imported&imported=' . $result['imported'] . '&skipped=' . $result['skipped']);
    }

    #[Route('/campaigns/<id:\\d+>/launch', 'POST')]
    public function launch(ServerRequestInterface $request, string $id): ResponseInterface
    {
        $campaignId = (int) $id;
        if ($campaignId <= 0) {
            return $this->redirect('/campaigns?notice=invalid_campaign_link');
        }

        /** @var \App\Models\User $user */
        $user = User::query()->where('id', '=', Auth::id())->first();
        /** @var Campaign|null $campaign */
        $campaign = Campaign::query()->where('id', '=', $campaignId)->first();

        if (!$campaign || $campaign->organization_id !== $user->getOrganizationId()) {
            return $this->redirect('/campaigns?notice=campaign_not_found');
        }

        if (!$campaign->canStart()) {
            return $this->redirect('/campaigns/' . $campaign->id . '?notice=campaign_locked');
        }

        try {
            /** @var \App\Models\Template|null $template */
            $template = Template::query()->where('id', '=', $campaign->template_id)->first();
            if (!$template) {
                return $this->redirect('/campaigns/' . $campaign->id . '?notice=campaign_template_missing');
            }

            $validation = $this->templateValidation->validateForCampaign($template, $campaign);
            if (!$validation['ok']) {
                return $this->redirect('/campaigns/' . $campaign->id . '?notice=campaign_template_invalid');
            }

            $smtpSetting = null;
            if ($campaign->smtp_setting_id) {
                $smtpSetting = SmtpSetting::query()
                    ->where('id', '=', $campaign->smtp_setting_id)
                    ->where('organization_id', '=', $user->getOrganizationId())
                    ->where('is_active', '=', 1)
                    ->first();
            }
            if (!$smtpSetting) {
                $smtpSetting = SmtpSetting::query()
                    ->where('organization_id', '=', $user->getOrganizationId())
                    ->where('is_active', '=', 1)
                    ->first();
                if ($smtpSetting && !$campaign->smtp_setting_id) {
                    $campaign->smtp_setting_id = $smtpSetting->id;
                    $campaign->save();
                }
            }
            if (!$smtpSetting) {
                return $this->redirect('/campaigns/' . $campaign->id . '?notice=campaign_smtp_missing');
            }
            $safety = $this->safetyService->evaluate($campaign, $template, $smtpSetting);
            $riskHistory = $this->riskHistory;
            $riskHistory->record($campaign, 'campaign_safety_snapshot', $safety);
            if ($safety['should_pause']) {
                $campaign->status = 'paused';
                $campaign->save();
                $riskHistory->record($campaign, 'campaign_autopaused', $safety);
                return $this->redirect('/campaigns/' . $campaign->id . '?notice=campaign_autopaused');
            }
            if (!$safety['ok']) {
                return $this->redirect('/campaigns/' . $campaign->id . '?notice=campaign_high_risk');
            }
        } catch (\Throwable) {
            return $this->redirect('/campaigns/' . $campaign->id . '?notice=campaign_template_invalid');
        }

        $existingQueuedJobs = QueueJob::query()
            ->where('campaign_id', '=', $campaign->id)
            ->where('status', '=', 'pending')
            ->count();

        try {
            $queuedCount = $this->lifecycleService->queueCampaign($campaign);
        } catch (\Throwable) {
            return $this->redirect('/campaigns/' . $campaign->id . '?notice=campaign_queue_failed');
        }

        if ($queuedCount === 0 && $existingQueuedJobs === 0) {
            return $this->redirect('/campaigns/' . $campaign->id . '?notice=campaign_queue_empty');
        }

        $campaign->status = 'active';
        $campaign->started_at = $campaign->started_at ?? date('Y-m-d H:i:s');
        $campaign->save();

        return $this->redirect('/campaigns/' . $campaign->id . '?notice=campaign_launched');
    }

    private function resolveCampaignIndexNotice(ServerRequestInterface $request): ?array
    {
        return match ((string) ($request->getQueryParams()['notice'] ?? '')) {
            'invalid_campaign_link' => ['type' => 'danger', 'message' => 'That campaign link is invalid.'],
            'campaign_not_found' => ['type' => 'danger', 'message' => 'Campaign not found or not accessible.'],
            default => null,
        };
    }

    private function resolveCampaignDetailNotice(ServerRequestInterface $request): ?array
    {
        $queryParams = $request->getQueryParams();
        $imported = max((int) ($queryParams['imported'] ?? 0), 0);
        $skipped = max((int) ($queryParams['skipped'] ?? 0), 0);

        return match ((string) ($queryParams['notice'] ?? '')) {
            'recipient_imported' => [
                'type' => 'success',
                'message' => $imported > 0
                    ? sprintf('Imported %d recipients%s.', $imported, $skipped > 0 ? sprintf(' (%d skipped)', $skipped) : '')
                    : 'Recipients imported.',
            ],
            'recipient_import_empty' => ['type' => 'danger', 'message' => 'Recipient import file was empty or had no valid rows.'],
            'recipient_import_skipped' => ['type' => 'danger', 'message' => 'No recipients were imported. All rows were invalid or already existed for this campaign.'],
            'missing_recipient_file' => ['type' => 'danger', 'message' => 'Choose a CSV file before importing recipients.'],
            'invalid_recipient_file' => ['type' => 'danger', 'message' => 'Recipient CSV could not be processed. Check the file format and try again.'],
            'campaign_locked' => ['type' => 'danger', 'message' => 'This campaign can no longer be changed from the detail page.'],
            'campaign_template_missing' => ['type' => 'danger', 'message' => 'Campaign template could not be found.'],
            'campaign_template_invalid' => ['type' => 'danger', 'message' => 'Campaign template is missing required variables such as {{unsubscribe_url}} or required recipient data.'],
            'template_missing' => ['type' => 'danger', 'message' => 'The backing template for this campaign could not be found.'],
            'smtp_missing' => ['type' => 'danger', 'message' => 'Select an active SMTP account before saving this campaign.'],
            'content_missing' => ['type' => 'danger', 'message' => 'Subject and HTML content are required to save this campaign.'],
            'campaign_updated' => ['type' => 'success', 'message' => 'Campaign settings were updated.'],
            'campaign_high_risk' => ['type' => 'danger', 'message' => 'Campaign failed pre-send safety checks. Review sender, template, recipients, and deliverability warnings below.'],
            'campaign_autopaused' => ['type' => 'danger', 'message' => 'Campaign was auto-paused because bounce or complaint risk exceeded allowed thresholds.'],
            'campaign_queue_empty' => ['type' => 'danger', 'message' => 'Campaign could not be launched because it has no queued recipients to send.'],
            'campaign_queue_failed' => ['type' => 'danger', 'message' => 'Campaign launch passed validation, but queueing failed. Try again after checking queue storage.'],
            'campaign_launched' => ['type' => 'success', 'message' => 'Campaign launched. Queue processing can begin.'],
            'campaign_smtp_missing' => ['type' => 'danger', 'message' => 'Select an active SMTP account for this campaign before launching.'],
            default => null,
        };
    }

    private function syncCampaignTemplate(User $user, string $campaignName, string $subject, string $html, ?Template $template, bool $saveAsTemplate): Template
    {
        $subject = trim($subject);
        $html = (string) $html;

        if ($subject === '' || trim(strip_tags($html)) === '') {
            throw new \RuntimeException('Subject and HTML content are required.');
        }

        $template ??= new Template();
        $template->organization_id = $user->getOrganizationId();
        $template->name = $saveAsTemplate ? $subject : 'Campaign Draft: ' . $campaignName;
        $template->subject = $subject;
        $template->html_content = $html;
        $template->is_active = $saveAsTemplate ? 1 : 0;
        $template->variables = json_encode($template->parseVariables());
        $template->save();

        return $template;
    }

    /**
     * @return array{imported:int,skipped:int}
     */
    private function storeRecipientsFromCsv(Campaign $campaign, UploadedFileInterface $uploadedFile): array
    {
        if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Upload failed.');
        }

        if ($uploadedFile->getSize() > self::MAX_IMPORT_FILE_SIZE) {
            throw new \RuntimeException('Recipient file exceeds maximum allowed size of 5MB.');
        }

        $stream = $uploadedFile->getStream();
        $stream->rewind();
        $csv = $stream->getContents();

        if (trim($csv) === '') {
            return ['imported' => 0, 'skipped' => 0];
        }

        $rows = preg_split('/\r\n|\r|\n/', $csv) ?: [];
        $header = null;
        $imported = 0;
        $skipped = 0;
        $seenEmails = [];

        $existingRecipients = Recipient::query()->where('campaign_id', '=', $campaign->id)->get();
        foreach ($existingRecipients as $existingRecipient) {
            $email = strtolower(trim((string) ($existingRecipient->email ?? '')));
            if ($email !== '') {
                $seenEmails[$email] = true;
            }
        }

        foreach ($rows as $row) {
            if (trim($row) === '') {
                continue;
            }

            $columns = str_getcsv($row);
            if ($columns === [] || $columns === [null]) {
                continue;
            }

            if ($header === null) {
                $header = array_map(
                    static fn (mixed $value): string => strtolower(trim((string) $value)),
                    $columns
                );

                if (($header[0] ?? '') !== 'email') {
                    throw new \RuntimeException('Missing email header.');
                }

                continue;
            }

            $record = [];
            foreach ($header as $index => $columnName) {
                if ($columnName === '') {
                    continue;
                }

                $record[$columnName] = trim((string) ($columns[$index] ?? ''));
            }

            $email = strtolower($record['email'] ?? '');
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skipped++;
                continue;
            }

            if (isset($seenEmails[$email])) {
                $skipped++;
                continue;
            }

            if ($imported >= self::MAX_IMPORT_RECIPIENTS) {
                $skipped++;
                continue;
            }

            $recipient = new Recipient()->fill([
                'organization_id' => $campaign->organization_id,
                'campaign_id' => $campaign->id,
                'email' => $email,
                'name' => (($record['name'] ?? '') !== '') ? ($record['name'] ?? null) : null,
                'status' => 'pending',
                'custom_data' => json_encode($this->extractCustomRecipientFields($record), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);
            $recipient->save();

            $seenEmails[$email] = true;
            $imported++;
        }

        $campaign->total_recipients = count($seenEmails);
        $campaign->save();

        return ['imported' => $imported, 'skipped' => $skipped];
    }

    /**
     * @param array<string,string> $record
     * @return array<string,string>
     */
    private function storeRecipientsFromText(Campaign $campaign, string $manualRecipients): array
    {
        $lines = preg_split('/[\r\n,;]+/', $manualRecipients) ?: [];
        $imported = 0;
        $skipped = 0;
        $seenEmails = [];

        $existingRecipients = Recipient::query()->where('campaign_id', '=', $campaign->id)->get();
        foreach ($existingRecipients as $existingRecipient) {
            $email = strtolower(trim((string) ($existingRecipient->email ?? '')));
            if ($email !== '') {
                $seenEmails[$email] = true;
            }
        }

        foreach ($lines as $line) {
            $email = strtolower(trim($line));
            if ($email === '') {
                continue;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skipped++;
                continue;
            }

            if (isset($seenEmails[$email])) {
                $skipped++;
                continue;
            }

            if ($imported >= self::MAX_IMPORT_RECIPIENTS) {
                $skipped++;
                continue;
            }

            $recipient = new Recipient()->fill([
                'organization_id' => $campaign->organization_id,
                'campaign_id' => $campaign->id,
                'email' => $email,
                'name' => null,
                'status' => 'pending',
                'custom_data' => json_encode([], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);
            $recipient->save();

            $seenEmails[$email] = true;
            $imported++;
        }

        $campaign->total_recipients = count($seenEmails);
        $campaign->save();

        return ['imported' => $imported, 'skipped' => $skipped];
    }

    private function extractCustomRecipientFields(array $record): array
    {
        unset($record['email'], $record['name']);

        return array_filter(
            $record,
            static fn (string $value): bool => $value !== ''
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Controllers;

use App\DTOs\CreateCampaignDto;
use App\Models\Campaign;
use App\Models\Recipient;
use App\Models\Template;
use App\Models\User;
use App\ViewDto\CampaignViewDto;
use Larafony\Framework\Auth\Auth;
use Larafony\Framework\Database\Base\Query\Enums\OrderDirection;
use Larafony\Framework\Routing\Advanced\Attributes\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;

class CampaignController extends Controller
{
    public function __construct()
    {
        parent::__construct(\Larafony\Framework\Web\Application::instance());
    }

    #[Route('/campaigns', 'GET')]
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        if (!Auth::check()) {
            return $this->redirect('/login');
        }

        /** @var \App\Models\User $user */
        $user = User::query()->where('id', '=', Auth::id())->first();

        $queryParams = $request->getQueryParams();
        $search = trim((string) ($queryParams['q'] ?? ''));
        $status = trim((string) ($queryParams['status'] ?? ''));
        $sort = (string) ($queryParams['sort'] ?? 'created_desc');
        $sortDirection = $sort === 'created_asc' ? OrderDirection::ASC : OrderDirection::DESC;
        $normalizedSort = $sort === 'created_asc' ? 'created_asc' : 'created_desc';

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
            ],
            'statusOptions' => $allowedStatuses,
            'notice' => $this->resolveCampaignIndexNotice($request),
            'user' => $user,
        ]);
    }

    #[Route('/campaigns/create', 'GET')]
    public function create(ServerRequestInterface $request): ResponseInterface
    {
        if (!Auth::check()) {
            return $this->redirect('/login');
        }

        /** @var \App\Models\User $user */
        $user = User::query()->where('id', '=', Auth::id())->first();
        $templates = Template::query()
            ->where('organization_id', '=', $user->getOrganizationId())
            ->where('is_active', '=', 1)
            ->get();

        return $this->render('campaigns.create', [
            'templates' => $templates,
            'user' => $user,
        ]);
    }

    #[Route('/campaigns', 'POST')]
    public function store(CreateCampaignDto $dto): ResponseInterface
    {
        if (!Auth::check()) {
            return $this->redirect('/login');
        }

        /** @var \App\Models\User $user */
        $user = User::query()->where('id', '=', Auth::id())->first();

        $campaign = new Campaign()->fill([
            'organization_id' => $user->getOrganizationId(),
            'template_id' => $dto->template_id,
            'name' => $dto->name,
            'status' => 'draft',
            'created_by' => $user->id,
        ]);
        $campaign->save();

        return $this->redirect("/campaigns/{$campaign->id}");
    }

    #[Route('/campaigns/<id:\\d+>', 'GET')]
    public function show(ServerRequestInterface $request, string $id): ResponseInterface
    {
        if (!Auth::check()) {
            return $this->redirect('/login');
        }

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

        return $this->render('campaigns.show', [
            'campaign' => $campaign,
            'user' => $user,
            'stats' => $campaign->getStats(),
            'notice' => $this->resolveCampaignDetailNotice($request),
        ]);
    }

    #[Route('/campaigns/<id:\\d+>/recipients', 'POST')]
    public function importRecipients(ServerRequestInterface $request, string $id): ResponseInterface
    {
        if (!Auth::check()) {
            return $this->redirect('/login');
        }

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

        $uploadedFiles = $request->getUploadedFiles();
        $uploadedFile = $uploadedFiles['recipients_file'] ?? null;
        if (!$uploadedFile instanceof UploadedFileInterface) {
            return $this->redirect('/campaigns/' . $campaign->id . '?notice=missing_recipient_file');
        }

        try {
            $result = $this->storeRecipientsFromCsv($campaign, $uploadedFile);
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
        if (!Auth::check()) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

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

        $campaign->status = 'active';
        $campaign->started_at = date('Y-m-d H:i:s');
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
            'campaign_launched' => ['type' => 'success', 'message' => 'Campaign launched. Queue processing can begin.'],
            default => null,
        };
    }

    /**
     * @return array{imported:int,skipped:int}
     */
    private function storeRecipientsFromCsv(Campaign $campaign, UploadedFileInterface $uploadedFile): array
    {
        if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Upload failed.');
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
    private function extractCustomRecipientFields(array $record): array
    {
        unset($record['email'], $record['name']);

        return array_filter(
            $record,
            static fn (string $value): bool => $value !== ''
        );
    }
}

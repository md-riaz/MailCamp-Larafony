<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\SmtpReportIngestionService;
use Larafony\Framework\Console\Attributes\AsCommand;
use Larafony\Framework\Console\Command;

#[AsCommand('app:ingest-smtp-report')]
class IngestSmtpReportCommand extends Command
{
    public function run(): int
    {
        $file = $this->input->arguments[0] ?? null;
        $source = $this->input->arguments[1] ?? 'smtp-cli';

        if (!is_string($file) || $file === '' || !is_file($file)) {
            $this->output->error('Usage: app:ingest-smtp-report <file> [source]');
            return 1;
        }

        $raw = file_get_contents($file);
        if ($raw === false || trim($raw) === '') {
            $this->output->error('Could not read payload file or payload is empty.');
            return 1;
        }

        $service = new SmtpReportIngestionService();
        $result = $service->ingest($raw, (string) $source);

        $this->output->info(sprintf(
            'received=%d processed=%d duplicates=%d unmatched=%d event=%s',
            $result['received'],
            $result['processed'],
            $result['duplicates'],
            $result['unmatched'],
            $result['event_type']
        ));

        return 0;
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Campaign;
use App\Models\Recipient;
use App\Models\Template;

final class TemplateValidationService
{
    /**
     * @return array{ok:bool,errors:array<int,string>,warnings:array<int,string>,variables:array<int,string>}
     */
    public function validateForCampaign(Template $template, Campaign $campaign): array
    {
        $variables = $this->normalizeVariables($template);
        $errors = [];
        $warnings = [];

        $requiredVariables = ['unsubscribe_url'];
        foreach ($requiredVariables as $requiredVariable) {
            if (!in_array($requiredVariable, $variables, true)) {
                $errors[] = sprintf('Template is missing required variable {{%s}}.', $requiredVariable);
            }
        }

        /** @var Recipient|null $sampleRecipient */
        $sampleRecipient = Recipient::query()
            ->where('campaign_id', '=', $campaign->id)
            ->first();

        $availableDataKeys = ['name', 'email', 'unsubscribe_url'];
        if ($sampleRecipient) {
            $availableDataKeys = array_values(array_unique(array_merge(
                $availableDataKeys,
                array_keys($sampleRecipient->getCustomData())
            )));
        }

        foreach ($variables as $variable) {
            if (!in_array($variable, $availableDataKeys, true)) {
                $warnings[] = sprintf('Template variable {{%s}} is not present in sample recipient data.', $variable);
            }
        }

        return [
            'ok' => $errors === [],
            'errors' => $errors,
            'warnings' => $warnings,
            'variables' => $variables,
        ];
    }

    /**
     * @return array<int,string>
     */
    private function normalizeVariables(Template $template): array
    {
        $variables = json_decode((string) ($template->variables ?? ''), true);
        if (!is_array($variables) || $variables === []) {
            $variables = $template->parseVariables();
        }

        $normalized = [];
        foreach ($variables as $variable) {
            $candidate = trim((string) $variable);
            if ($candidate !== '') {
                $normalized[] = $candidate;
            }
        }

        return array_values(array_unique($normalized));
    }
}

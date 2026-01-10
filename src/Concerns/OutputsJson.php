<?php

namespace InnoBrain\OnofficeCli\Concerns;

use Illuminate\Support\Collection;

trait OutputsJson
{
    protected function outputSuccess(mixed $data, array $meta = []): int
    {
        if ($this->option('json')) {
            $this->line(json_encode([
                'data' => $data instanceof Collection ? $data->toArray() : $data,
                'meta' => $meta,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return self::SUCCESS;
        }

        $this->renderHumanOutput($data, $meta);

        return self::SUCCESS;
    }

    protected function outputError(string $message, int $code = 1): int
    {
        if ($this->option('json')) {
            $this->line(json_encode([
                'error' => true,
                'message' => $message,
                'code' => $code,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return self::FAILURE;
        }

        $this->error($message);

        return self::FAILURE;
    }

    protected function renderHumanOutput(mixed $data, array $meta = []): void
    {
        if ($data instanceof Collection) {
            $data = $data->toArray();
        }

        if (empty($data)) {
            $this->info('No results found.');

            return;
        }

        // Single record
        if (isset($data['id'])) {
            $this->renderSingleRecord($data);

            return;
        }

        // Multiple records
        if (is_array($data) && isset($data[0])) {
            $this->renderMultipleRecords($data, $meta);

            return;
        }

        // Fallback: dump as JSON
        $this->line(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    protected function renderSingleRecord(array $record): void
    {
        $this->info("Record ID: {$record['id']}");
        $this->newLine();

        $elements = $record['elements'] ?? $record;
        foreach ($elements as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            $this->line("  <comment>{$key}:</comment> {$value}");
        }
    }

    protected function renderMultipleRecords(array $records, array $meta = []): void
    {
        if (isset($meta['total'])) {
            $this->info("Found {$meta['total']} record(s)");
            $this->newLine();
        }

        // Build table from first record's keys
        $firstRecord = $records[0];
        $elements = $firstRecord['elements'] ?? $firstRecord;
        $headers = array_merge(['ID'], array_keys($elements));

        $rows = [];
        foreach ($records as $record) {
            $elements = $record['elements'] ?? $record;
            $row = [$record['id'] ?? '-'];
            foreach ($elements as $value) {
                if (is_array($value)) {
                    $value = json_encode($value);
                }
                $row[] = mb_strlen((string) $value) > 50
                    ? mb_substr((string) $value, 0, 47).'...'
                    : $value;
            }
            $rows[] = $row;
        }

        $this->table($headers, $rows);
    }
}

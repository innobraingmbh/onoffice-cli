<?php

namespace InnoBrain\OnofficeCli\Concerns;

use Illuminate\Support\Collection;

trait OutputsJson
{
    private const JSON_FLAGS = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE;

    /**
     * @param  array<string, mixed>  $meta
     */
    protected function outputSuccess(mixed $data, array $meta = []): int
    {
        if ($this->option('json')) {
            $this->line(json_encode([
                'data' => $data instanceof Collection ? $data->toArray() : $data,
                'meta' => $meta,
            ], self::JSON_FLAGS));

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
            ], self::JSON_FLAGS));

            return self::FAILURE;
        }

        $this->error($message);

        return self::FAILURE;
    }

    /**
     * @param  array<string, mixed>  $meta
     */
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
        $this->line(json_encode($data, self::JSON_FLAGS));
    }

    /**
     * @param  array<string, mixed>  $record
     */
    protected function renderSingleRecord(array $record): void
    {
        $this->info("Record ID: {$record['id']}");
        $this->newLine();

        $elements = $record['elements'] ?? $record;
        foreach ($elements as $key => $value) {
            $formatted = is_array($value) ? json_encode($value) : $value;
            $this->line("  <comment>{$key}:</comment> {$formatted}");
        }
    }

    /**
     * @param  array<array<string, mixed>>  $records
     * @param  array<string, mixed>  $meta
     */
    protected function renderMultipleRecords(array $records, array $meta = []): void
    {
        if (isset($meta['total'])) {
            $this->info("Found {$meta['total']} record(s)");
            $this->newLine();
        }

        $firstRecord = $records[0];
        $elements = $firstRecord['elements'] ?? $firstRecord;
        $headers = array_merge(['ID'], array_keys($elements));

        $rows = [];
        foreach ($records as $record) {
            $elements = $record['elements'] ?? $record;
            $row = [$record['id'] ?? '-'];
            foreach ($elements as $value) {
                $row[] = $this->formatCellValue($value);
            }
            $rows[] = $row;
        }

        $this->table($headers, $rows);
    }

    protected function formatCellValue(mixed $value, int $maxLength = 50): string
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }

        $value = (string) $value;

        if (mb_strlen($value) <= $maxLength) {
            return $value;
        }

        return mb_substr($value, 0, $maxLength - 3).'...';
    }
}

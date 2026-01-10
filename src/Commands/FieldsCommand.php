<?php

namespace InnoBrain\OnofficeCli\Commands;

use Illuminate\Support\Collection;
use Innobrain\OnOfficeAdapter\Facades\FieldRepository;
use InnoBrain\OnofficeCli\Exceptions\OnOfficeCliException;
use InnoBrain\OnofficeCli\Exceptions\RecordNotFoundException;

class FieldsCommand extends OnOfficeCommand
{
    protected const MODULE_MAP = [
        'estate' => 'estate',
        'address' => 'address',
        'activity' => 'agentslog',
        'searchcriteria' => 'searchcriteria',
    ];

    protected $signature = 'onoffice:fields
        {entity : The entity to get fields for (estate, address, activity, searchcriteria)}
        {--filter= : Filter fields by name (case-insensitive, supports wildcards: *preis*)}
        {--field= : Get details for a specific field including permitted values}
        {--full : Show full field details including permitted values}
        {--json : Output results as JSON}';

    protected $description = 'List available fields for an onOffice entity';

    protected function executeCommand(): int
    {
        $entity = strtolower($this->argument('entity'));

        if (! isset(self::MODULE_MAP[$entity])) {
            throw new OnOfficeCliException(
                "Fields are not available for '{$entity}'. Supported: ".implode(', ', array_keys(self::MODULE_MAP)),
                400
            );
        }

        $module = self::MODULE_MAP[$entity];

        $response = FieldRepository::query()
            ->withModules($module)
            ->get();

        $fields = $this->parseFields($response);

        // Single field lookup
        if ($fieldName = $this->option('field')) {
            return $this->handleSingleField($fields, $fieldName, $entity, $module);
        }

        // Filter fields by pattern
        if ($filter = $this->option('filter')) {
            $fields = $this->filterFields($fields, $filter);
        }

        if ($this->option('full')) {
            return $this->outputSuccess($fields, [
                'entity' => $entity,
                'module' => $module,
                'count' => $fields->count(),
            ]);
        }

        $compact = $fields->map(fn (array $field): array => [
            'name' => $field['name'],
            'type' => $field['type'],
        ]);

        return $this->outputSuccess($compact, [
            'entity' => $entity,
            'module' => $module,
            'count' => $compact->count(),
        ]);
    }

    private function handleSingleField(Collection $fields, string $fieldName, string $entity, string $module): int
    {
        $field = $fields->firstWhere('name', $fieldName);

        if ($field === null) {
            // Try case-insensitive match
            $field = $fields->first(fn (array $f): bool => strcasecmp($f['name'], $fieldName) === 0);
        }

        if ($field === null) {
            throw new RecordNotFoundException($entity, $fieldName);
        }

        return $this->outputSuccess($field, [
            'entity' => $entity,
            'module' => $module,
        ]);
    }

    private function filterFields(Collection $fields, string $filter): Collection
    {
        // If no wildcards, treat as substring search
        if (! str_contains($filter, '*')) {
            return $fields
                ->filter(fn (array $field): bool => stripos($field['name'], $filter) !== false)
                ->values();
        }

        // Convert wildcards to regex
        $pattern = str_replace('\*', '.*', preg_quote($filter, '/'));

        return $fields
            ->filter(fn (array $field): bool => preg_match("/^{$pattern}$/i", $field['name']) === 1)
            ->values();
    }

    private function parseFields(Collection $response): Collection
    {
        return $response
            ->flatMap(function (array $item): Collection {
                $elements = $item['elements'] ?? [];

                return collect($elements)->map(fn (array $fieldData, string $fieldName): array => [
                    'name' => $fieldName,
                    'type' => $fieldData['type'] ?? null,
                    'length' => $fieldData['length'] ?? null,
                    'permittedValues' => $fieldData['permittedvalues'] ?? null,
                    'default' => $fieldData['default'] ?? null,
                ]);
            })
            ->sortBy('name')
            ->values();
    }

    protected function renderHumanOutput(mixed $data, array $meta = []): void
    {
        $isEmpty = $data instanceof Collection ? $data->isEmpty() : empty($data);

        if ($isEmpty) {
            $this->info('No fields found.');

            return;
        }

        // Single field output
        if (is_array($data) && isset($data['name']) && ! isset($data[0])) {
            $this->info("Field: {$data['name']}");
            $this->newLine();
            $this->line('  <comment>Type:</comment> '.($data['type'] ?? '-'));
            $this->line('  <comment>Length:</comment> '.($data['length'] ?? '-'));
            $this->line('  <comment>Default:</comment> '.($data['default'] ?? '-'));
            $this->line('  <comment>Permitted Values:</comment> '.$this->formatPermittedValues($data['permittedValues'] ?? null));

            return;
        }

        $this->info("Fields for {$meta['entity']} ({$meta['count']} total)");
        $this->newLine();

        if ($this->option('full')) {
            $headers = ['Name', 'Type', 'Length', 'Default', 'Permitted Values'];
            $rows = $data->map(fn (array $field): array => [
                $field['name'],
                $field['type'] ?? '-',
                $field['length'] ?? '-',
                $field['default'] ?? '-',
                $this->formatPermittedValues($field['permittedValues'] ?? null),
            ])->toArray();
        } else {
            $headers = ['Name', 'Type'];
            $rows = $data->map(fn (array $field): array => [
                $field['name'],
                $field['type'] ?? '-',
            ])->toArray();
        }

        $this->table($headers, $rows);
    }

    private function formatPermittedValues(mixed $values): string
    {
        if (empty($values)) {
            return '-';
        }

        if (! is_array($values)) {
            return (string) $values;
        }

        $count = count($values);
        if ($count <= 3) {
            return implode(', ', $values);
        }

        $remaining = $count - 3;

        return implode(', ', array_slice($values, 0, 3))." (+{$remaining} more)";
    }
}

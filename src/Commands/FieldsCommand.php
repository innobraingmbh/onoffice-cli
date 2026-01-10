<?php

namespace InnoBrain\OnofficeCli\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Innobrain\OnOfficeAdapter\Facades\FieldRepository;
use InnoBrain\OnofficeCli\Concerns\OutputsJson;

class FieldsCommand extends Command
{
    use OutputsJson;

    protected const MODULE_MAP = [
        'estate' => 'estate',
        'address' => 'address',
        'activity' => 'agentslog',
        'searchcriteria' => 'searchcriteria',
    ];

    public $signature = 'onoffice:fields
        {entity : The entity to get fields for (estate, address, activity, searchcriteria)}
        {--filter= : Filter fields by name (case-insensitive, supports wildcards: *preis*)}
        {--field= : Get details for a specific field including permitted values}
        {--full : Show full field details including permitted values}
        {--json : Output results as JSON}';

    public $description = 'List available fields for an onOffice entity';

    public function handle(): int
    {
        $entity = strtolower($this->argument('entity'));

        if (! isset(self::MODULE_MAP[$entity])) {
            return $this->outputError(
                "Fields are not available for '{$entity}'. Supported: ".implode(', ', array_keys(self::MODULE_MAP)),
                400
            );
        }

        try {
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

            $compact = $fields->map(fn (array $field) => [
                'name' => $field['name'],
                'type' => $field['type'],
            ]);

            return $this->outputSuccess($compact, [
                'entity' => $entity,
                'module' => $module,
                'count' => $compact->count(),
            ]);

        } catch (Exception $e) {
            return $this->outputError($e->getMessage(), 500);
        }
    }

    private function handleSingleField(Collection $fields, string $fieldName, string $entity, string $module): int
    {
        $field = $fields->firstWhere('name', $fieldName);

        if ($field === null) {
            // Try case-insensitive match
            $field = $fields->first(fn (array $f) => strcasecmp($f['name'], $fieldName) === 0);
        }

        if ($field === null) {
            return $this->outputError("Field '{$fieldName}' not found for {$entity}", 404);
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
                ->filter(fn (array $field) => stripos($field['name'], $filter) !== false)
                ->values();
        }

        // Convert wildcards to regex
        $pattern = str_replace('\*', '.*', preg_quote($filter, '/'));

        return $fields
            ->filter(fn (array $field) => preg_match("/^{$pattern}$/i", $field['name']))
            ->values();
    }

    private function parseFields(Collection $response): Collection
    {
        return $response
            ->flatMap(function (array $item) {
                $elements = $item['elements'] ?? [];

                return collect($elements)->map(function (array $fieldData, string $fieldName) {
                    return [
                        'name' => $fieldName,
                        'type' => $fieldData['type'] ?? null,
                        'length' => $fieldData['length'] ?? null,
                        'permittedValues' => $fieldData['permittedvalues'] ?? null,
                        'default' => $fieldData['default'] ?? null,
                    ];
                });
            })
            ->sortBy('name')
            ->values();
    }

    protected function renderHumanOutput(mixed $data, array $meta = []): void
    {
        if ($data->isEmpty()) {
            $this->info('No fields found.');

            return;
        }

        $this->info("Fields for {$meta['entity']} ({$meta['count']} total)");
        $this->newLine();

        $headers = $this->option('full')
            ? ['Name', 'Type', 'Length', 'Default', 'Permitted Values']
            : ['Name', 'Type'];

        $rows = $data->map(fn (array $field) => $this->option('full')
            ? [
                $field['name'],
                $field['type'] ?? '-',
                $field['length'] ?? '-',
                $field['default'] ?? '-',
                $this->formatPermittedValues($field['permittedValues'] ?? null),
            ]
            : [
                $field['name'],
                $field['type'] ?? '-',
            ]
        )->toArray();

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

        return implode(', ', array_slice($values, 0, 3))." (+{$count} more)";
    }
}

<?php

namespace InnoBrain\OnofficeCli\Commands;

use Exception;
use Illuminate\Console\Command;
use Innobrain\OnOfficeAdapter\Facades\FieldRepository;
use InnoBrain\OnofficeCli\Concerns\OutputsJson;

class FieldsCommand extends Command
{
    use OutputsJson;

    /**
     * Mapping from entity names to onOffice module names.
     */
    protected const MODULE_MAP = [
        'estate' => 'estate',
        'address' => 'address',
        'activity' => 'agentslog',
        'searchcriteria' => 'searchcriteria',
    ];

    public $signature = 'onoffice:fields
        {entity : The entity to get fields for (estate, address, activity, searchcriteria)}
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

            $fields = FieldRepository::query()
                ->withModules($module)
                ->get();

            // Transform fields into a more useful format
            $formatted = $fields->map(function ($field) {
                return [
                    'id' => $field['id'] ?? null,
                    'name' => $field['elements']['fieldname'] ?? $field['id'] ?? null,
                    'type' => $field['elements']['type'] ?? null,
                    'label' => $field['elements']['label'] ?? null,
                    'permittedValues' => $field['elements']['permittedvalues'] ?? null,
                    'default' => $field['elements']['default'] ?? null,
                ];
            });

            return $this->outputSuccess($formatted, [
                'entity' => $entity,
                'module' => $module,
                'count' => $formatted->count(),
            ]);

        } catch (Exception $e) {
            return $this->outputError($e->getMessage(), 500);
        }
    }

    protected function renderHumanOutput(mixed $data, array $meta = []): void
    {
        if ($data->isEmpty()) {
            $this->info('No fields found.');

            return;
        }

        $this->info("Fields for {$meta['entity']} ({$meta['count']} total)");
        $this->newLine();

        $headers = ['Name', 'Type', 'Label'];
        $rows = $data->map(fn ($field) => [
            $field['name'] ?? '-',
            $field['type'] ?? '-',
            mb_strlen($field['label'] ?? '') > 40
                ? mb_substr($field['label'], 0, 37).'...'
                : ($field['label'] ?? '-'),
        ])->toArray();

        $this->table($headers, $rows);
    }
}

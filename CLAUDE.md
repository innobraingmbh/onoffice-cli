# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

A Laravel package providing CLI commands for AI agents to interact with onOffice (real estate CRM). Designed for minimal context overhead - commands accept structured input and return JSON output.

Built on `innobrain/laravel-onoffice-adapter` ([docs](https://oo-adapter.innobra.in/)).

## CLI Commands

All commands support `--json` flag for machine-readable output.

```bash
# Search entities
php artisan onoffice:search <entity> [--where="field=value"] [--where="field<value"] [--select=field] [--limit=N] [--offset=N] [--orderBy=field] [--orderByDesc=field] [--json]

# Get single record by ID
php artisan onoffice:get <entity> <id> [--select=field] [--json]

# List available fields for an entity (compact by default)
php artisan onoffice:fields <entity> [--filter=pattern] [--field=name] [--full] [--json]
```

**Supported entities for search/get:** `estate`, `address`, `activity`, `file`, `field`, `filter`, `link`, `lastseen`, `relation`, `searchcriteria`, `setting`, `log`, `macro`, `marketplace`

**Supported entities for fields:** `estate`, `address`, `activity`, `searchcriteria`

**Where clause operators:** `=`, `!=`, `<`, `>`, `<=`, `>=`, `like`, `not like`

**Fields command options:**
- `--filter=pattern` - Search fields by name (substring or wildcard like `*preis*`)
- `--field=name` - Get single field with full details including permitted values
- `--full` - Show all field details (length, default, permittedValues)

## Running Commands (Development)

This is a Laravel package, not an application. Use Orchestra Testbench to run commands:

```bash
# Copy and configure testbench.yaml with your API credentials
cp testbench.yaml.dist testbench.yaml
# Edit testbench.yaml and add your ON_OFFICE_TOKEN and ON_OFFICE_SECRET

# Run commands via testbench
./vendor/bin/testbench onoffice:search estate --limit=5 --json
./vendor/bin/testbench onoffice:get estate 123 --json
./vendor/bin/testbench onoffice:fields estate --json
```

The `testbench.yaml` file is gitignored. Get API credentials from your onOffice admin panel (token: 32 chars, secret: 64 chars).

## Development Commands

```bash
composer test              # Run tests (Pest)
composer test-coverage     # Run with coverage
composer analyse           # PHPStan (level 5)
composer format            # Laravel Pint

# Single test
./vendor/bin/pest tests/ExampleTest.php
./vendor/bin/pest --filter="test name"
```

## Architecture

```
src/
├── Commands/
│   ├── SearchCommand.php      # onoffice:search
│   ├── GetCommand.php         # onoffice:get
│   └── FieldsCommand.php      # onoffice:fields
├── Concerns/
│   └── OutputsJson.php        # Trait for JSON output formatting
├── Support/
│   ├── RepositoryFactory.php  # Maps entity names to adapter repositories
│   └── WhereClauseParser.php  # Parses --where clauses
├── Facades/
│   └── OnofficeCli.php        # Facade (unused, from boilerplate)
├── OnofficeCliServiceProvider.php
└── OnofficeCli.php            # Core class (unused, from boilerplate)
```

**Key patterns:**
- `RepositoryFactory` maps entity names (e.g., "estate") to adapter facades (EstateRepository)
- `WhereClauseParser` parses `--where="field=value"` into [field, operator, value]
- `OutputsJson` trait provides consistent JSON output formatting
- Errors: `{"error": true, "message": "...", "code": N}`
- Success: `{"data": [...], "meta": {"total": N, "limit": N, "offset": N}}`

## onOffice Adapter Usage

```php
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;
use Innobrain\OnOfficeAdapter\Facades\AddressRepository;
use Innobrain\OnOfficeAdapter\Facades\FieldRepository;

// Query with filters
EstateRepository::query()
    ->select(['Id', 'Ort', 'Zimmer'])
    ->where('status', '=', 1)
    ->where('kaufpreis', '<', 300000)
    ->orderBy('kaufpreis')
    ->limit(10)
    ->get();

// Get single record
EstateRepository::query()->find($id);

// Get available fields
FieldRepository::query()
    ->withModules(['estate'])
    ->get();
```

## Dependencies

- PHP 8.4+
- Laravel 11/12
- `innobrain/laravel-onoffice-adapter` ^1.11

# onOffice CLI

[![Latest Version on Packagist](https://img.shields.io/packagist/v/innobraingmbh/onoffice-cli.svg?style=flat-square)](https://packagist.org/packages/innobraingmbh/onoffice-cli)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/innobraingmbh/onoffice-cli/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/innobraingmbh/onoffice-cli/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/innobraingmbh/onoffice-cli/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/innobraingmbh/onoffice-cli/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/innobraingmbh/onoffice-cli.svg?style=flat-square)](https://packagist.org/packages/innobraingmbh/onoffice-cli)

CLI commands for interacting with [onOffice](https://www.onoffice.com/) (real estate CRM) from the command line. Designed for AI agent integration with structured JSON output.

Built on top of [laravel-onoffice-adapter](https://github.com/innobraingmbh/laravel-onoffice-adapter).

## Installation

```bash
composer require innobraingmbh/onoffice-cli
```

This package requires [laravel-onoffice-adapter](https://github.com/innobraingmbh/laravel-onoffice-adapter) to be configured with your onOffice API credentials.

## Usage

### Search Records

```bash
# Search all estates
php artisan onoffice:search estate --json

# Search with filters
php artisan onoffice:search estate --where="status=1" --where="kaufpreis<500000" --json

# Search with field selection and ordering
php artisan onoffice:search estate --select=Id --select=Ort --select=Kaufpreis --orderBy=Kaufpreis --limit=10 --json

# Search addresses
php artisan onoffice:search address --where="Ort=Berlin" --json
```

### Get Single Record

```bash
# Get estate by ID
php artisan onoffice:get estate 12345 --json

# Get address with specific fields
php artisan onoffice:get address 6789 --select=Name --select=Email --json
```

### List Available Fields

```bash
# Get all fields (compact: name and type only)
php artisan onoffice:fields estate --json

# Search fields by name
php artisan onoffice:fields estate --filter=preis --json
php artisan onoffice:fields estate --filter="*flaeche" --json

# Get single field with permitted values
php artisan onoffice:fields estate --field=objekttyp --json

# Get all fields with full details
php artisan onoffice:fields estate --full --json
```

**Fields command options:**
- `--filter=pattern` - Search by name (substring or wildcard like `*preis*`)
- `--field=name` - Get single field with full details including permitted values
- `--full` - Show all field details (length, default, permittedValues)

### Supported Entities

- `estate` - Real estate listings
- `address` - Contacts and addresses
- `activity` - Activity logs
- `file`, `field`, `filter`, `relation`, `searchcriteria`, `setting`, `log`, `macro`, `marketplace`

### Where Clause Operators

- `=` - Equals
- `!=` - Not equals
- `<`, `>`, `<=`, `>=` - Comparison
- `like`, `not like` - Pattern matching

### Output Format

With `--json` flag, output is structured for easy parsing:

```json
{
  "data": [
    {"id": "123", "elements": {"Ort": "Berlin", "Kaufpreis": "450000"}}
  ],
  "meta": {
    "total": 42,
    "limit": 10,
    "offset": 0
  }
}
```

Without `--json`, output is displayed as a human-readable table.

## Development

This is a Laravel package. To run commands locally during development:

```bash
# Copy and configure credentials
cp testbench.yaml.dist testbench.yaml
# Edit testbench.yaml with your ON_OFFICE_TOKEN and ON_OFFICE_SECRET

# Run commands via testbench
./vendor/bin/testbench onoffice:search estate --limit=5 --json
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Konstantin Auffinger](https://github.com/kauffinger)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

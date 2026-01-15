# onOffice CLI

[![Latest Version on Packagist](https://img.shields.io/packagist/v/innobraingmbh/onoffice-cli.svg?style=flat-square)](https://packagist.org/packages/innobraingmbh/onoffice-cli)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/innobraingmbh/onoffice-cli/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/innobraingmbh/onoffice-cli/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/innobraingmbh/onoffice-cli/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/innobraingmbh/onoffice-cli/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/innobraingmbh/onoffice-cli.svg?style=flat-square)](https://packagist.org/packages/innobraingmbh/onoffice-cli)

CLI commands for interacting with [onOffice](https://www.onoffice.com/) (real estate CRM) from the command line. Designed for AI agent integration with structured JSON output.

Built on top of [laravel-onoffice-adapter](https://github.com/innobraingmbh/laravel-onoffice-adapter).

## Installation

You can install the package via composer:

```bash
composer require innobraingmbh/onoffice-cli
```

This package requires [laravel-onoffice-adapter](https://github.com/innobraingmbh/laravel-onoffice-adapter) to be configured with your onOffice API credentials.

You can publish the config file with:

```bash
php artisan vendor:publish --tag="onoffice-cli-config"
```

This is the contents of the published config file:

```php
return [
    'entities' => [
        'estate' => Innobrain\OnOfficeAdapter\Facades\EstateRepository::class,
        'address' => Innobrain\OnOfficeAdapter\Facades\AddressRepository::class,
        // ... more entities
    ],

    'field_modules' => [
        'estate' => 'estate',
        'address' => 'address',
        'activity' => 'agentslog',
        'searchcriteria' => 'searchcriteria',
    ],
];
```

## Usage

### Search Records

```bash
# Search all estates
php artisan onoffice:search estate --json

# Search with filters
php artisan onoffice:search estate --where="status=1" --where="kaufpreis<500000" --json

# Search with field selection and ordering
php artisan onoffice:search estate --select=Id --select=Ort --select=kaufpreis --orderBy=kaufpreis --limit=10 --json

# Search addresses
php artisan onoffice:search address --where="Ort=Berlin" --json

# Search activities for a specific estate
php artisan onoffice:search activity --where="Objekt_nr=12345" --json
```

### Get Single Record

```bash
# Get estate by ID
php artisan onoffice:get estate 12345 --json

# Get address with specific fields
php artisan onoffice:get address 6789 --select=Name --select=Vorname --select=Email --json
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

## Supported Entities

### estate - Real Estate Listings

Properties/real estate records. Common fields include:

| Field | Description |
|-------|-------------|
| `Id` | Estate ID |
| `status` | Status (1 = active) |
| `objektnr_extern` | External property number |
| `objekttitel` | Property title |
| `objekttyp` | Property type (singleselect) |
| `objektart` | Property category |
| `kaufpreis` | Purchase price |
| `warmmiete` | Warm rent |
| `kaltmiete` | Cold rent |
| `wohnflaeche` | Living area (m²) |
| `grundstuecksflaeche` | Plot area (m²) |
| `anzahl_zimmer` | Number of rooms |
| `Ort` | City |
| `Plz` | Postal code |
| `Strasse` | Street |
| `land` | Country |
| `breitengrad`, `laengengrad` | Coordinates |
| `verkauft` | Sold (1) / Rented (1) |
| `reserviert` | Reserved (1) |
| `veroeffentlichen` | Published on homepage |
| `geaendert_am` | Last modified date |

**Marketing status:** Combine `verkauft` and `reserviert` fields:
- `verkauft=0, reserviert=0` → Open
- `verkauft=0, reserviert=1` → Reserved
- `verkauft=1` → Sold/Rented

### address - Contacts and Addresses

Contact records (clients, owners, prospects). Common fields include:

| Field | Description |
|-------|-------------|
| `Id` | Address ID (Datensatznummer) |
| `KdNr` | Customer number (external) |
| `Anrede` | Salutation |
| `Vorname` | First name |
| `Name` | Last name |
| `Firma` | Company |
| `Strasse` | Street |
| `Plz` | Postal code |
| `Ort` | City |
| `Land` | Country |
| `Email` | Primary email |
| `Telefon1` | Primary phone |
| `phone` | All phone numbers |
| `mobile` | Mobile numbers |
| `fax` | Fax numbers |
| `email` | All email addresses |
| `defaultemail` | Default email only |
| `imageUrl` | Profile photo URL |
| `Aenderung` | Last modified date |

**Note:** Record number (`Id`) and customer number (`KdNr`) are different fields. Use `Id` for API calls.

### activity - Agents Log / Activities

Activity records linked to estates and addresses. Common fields include:

| Field | Description |
|-------|-------------|
| `Nr` | Activity ID |
| `Objekt_nr` | Linked estate IDs |
| `Adress_nr` | Linked address IDs |
| `Aktionsart` | Kind of action (Email, Telefonat, etc.) |
| `Aktionstyp` | Type of action (Eingang, Ausgang) |
| `Datum` | Date/time |
| `created` | Creation date |
| `Benutzer` | User name |
| `Benutzer_nr` | User ID |
| `Bemerkung` | Comment/notes |
| `Kosten` | Costs |
| `HerkunftKontakt` | Contact origin |
| `Beratungsebene` | Advisory level (A-G) |
| `Absagegrund` | Reason for cancellation |

**Advisory levels:** `A` (contract signed) through `G` (cancellation)

### searchcriteria - Search Criteria

Saved property search profiles for contacts. Fields include property preferences (price ranges, locations, property types) linked to addresses.

### Other Entities

- `file` - File attachments
- `field` - Field configuration
- `filter` - Saved filters
- `relation` - Links between records (buyer, owner, tenant relationships)
- `link` - URLs for detail views
- `lastseen` - Recently viewed records
- `setting` - System settings
- `log` - Log entries
- `macro` - Text macros
- `marketplace` - Marketplace data

## Where Clause Operators

Supported operators for `--where` filters:

| Operator | Description | Example |
|----------|-------------|---------|
| `=` | Equals | `--where="status=1"` |
| `!=` or `<>` | Not equals | `--where="status!=0"` |
| `<` | Less than | `--where="kaufpreis<300000"` |
| `>` | Greater than | `--where="wohnflaeche>80"` |
| `<=` | Less than or equal | `--where="anzahl_zimmer<=3"` |
| `>=` | Greater than or equal | `--where="kaufpreis>=100000"` |
| `like` | Pattern match (% wildcard) | `--where="Ort like %Berlin%"` |
| `not like` | Negative pattern match | `--where="Name not like %Test%"` |

Multiple `--where` clauses are combined with AND logic.

## Output Format

With `--json` flag, output is structured for easy parsing:

```json
{
  "data": [
    {"id": "123", "elements": {"Ort": "Berlin", "kaufpreis": "450000"}}
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

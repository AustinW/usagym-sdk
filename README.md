# USA Gymnastics SDK

[![Tests](https://github.com/AustinW/usagym-sdk/actions/workflows/tests.yml/badge.svg)](https://github.com/AustinW/usagym-sdk/actions/workflows/tests.yml)
[![Latest Version](https://img.shields.io/packagist/v/austinw/usagym-sdk.svg)](https://packagist.org/packages/austinw/usagym-sdk)
[![PHP Version](https://img.shields.io/packagist/php-v/austinw/usagym-sdk.svg)](https://packagist.org/packages/austinw/usagym-sdk)
[![License](https://img.shields.io/packagist/l/austinw/usagym-sdk.svg)](https://packagist.org/packages/austinw/usagym-sdk)

A modern PHP SDK for the USA Gymnastics API v4, built with [SaloonPHP](https://docs.saloon.dev/).

## Requirements

- PHP 8.3+
- Composer

## Installation

```bash
composer require austinw/usagym-sdk
```

## Quick Start

```php
use AustinW\UsaGym\UsaGym;

$usagym = new UsaGym(
    username: 'your-username',
    password: 'your-password'
);

// Test credentials
if ($usagym->test()) {
    echo "Connected successfully!";
}
```

## Usage

### Get Disciplines

```php
$disciplines = $usagym->disciplines()->all();

foreach ($disciplines as $discipline) {
    echo "{$discipline->code}: {$discipline->fullName}\n";
}
```

### Verify Person Exists

```php
$exists = $usagym->person()->exists(
    memberId: '123456',
    lastName: 'Smith',
    dateOfBirth: '2010-05-15'
);
```

### Sanction Reservations

```php
// Get all athletes for a sanction
$athletes = $usagym->sanctions(58025)->reservations()->athletes();

// Filter by club
$athletes = $usagym->sanctions(58025)->reservations()->athletes(
    clubs: [12345, 67890]
);

// Filter by level using type-safe enums
use AustinW\UsaGym\Enums\Levels\WomensArtisticLevel;

$level4Athletes = $usagym->sanctions(58025)->reservations()->athletes(
    levels: [WomensArtisticLevel::Level4, WomensArtisticLevel::Level5]
);

// Get coaches
$coaches = $usagym->sanctions(58025)->reservations()->coaches();

// Get judges
$judges = $usagym->sanctions(58025)->reservations()->judges();

// Get clubs
$clubs = $usagym->sanctions(58025)->reservations()->clubs();

// Get groups (for Rhythmic, Acro, T&T, GFA)
$groups = $usagym->sanctions(58025)->reservations()->groups();

// Get all individuals (athletes + coaches)
$individuals = $usagym->sanctions(58025)->reservations()->individuals();
// Returns: ['athletes' => [...], 'coaches' => [...]]
```

### Concurrent Requests

Fetch all athletes across all clubs concurrently:

```php
$allAthletes = $usagym->sanctions(58025)
    ->reservations()
    ->allAthletesConcurrently(concurrency: 10);

// Or get total count
$count = $usagym->sanctions(58025)
    ->reservations()
    ->totalAthleteCount();
```

### Verification

```php
// Verify athletes
$results = $usagym->sanctions(58025)->verification()->athletes(['123456', '789012']);

foreach ($results as $result) {
    if ($result->eligible) {
        echo "{$result->fullName()} is eligible\n";
    } else {
        echo "{$result->fullName()} is not eligible: {$result->ineligibleReason}\n";
    }
}

// Verify single athlete
$athlete = $usagym->sanctions(58025)->verification()->athlete('123456');

// Verify coaches
$coaches = $usagym->sanctions(58025)->verification()->coaches(['123456']);

// Verify judges (includes certification info)
$judges = $usagym->sanctions(58025)->verification()->judges(['123456']);

// Verify coach email
$isValid = $usagym->sanctions(58025)->verification()->coachEmail(
    refType: 'person',
    refTypeId: '123456',
    email: 'coach@example.com'
);

// Verify legal contact email
$isValid = $usagym->sanctions(58025)->verification()->legalContactEmail(
    refType: 'person',
    refTypeId: '123456',
    email: 'parent@example.com'
);
```

## Level Enums

Type-safe enums are provided for all disciplines:

```php
use AustinW\UsaGym\Enums\Levels\WomensArtisticLevel;
use AustinW\UsaGym\Enums\Levels\MensArtisticLevel;
use AustinW\UsaGym\Enums\Levels\RhythmicLevel;
use AustinW\UsaGym\Enums\Levels\AcrobaticLevel;
use AustinW\UsaGym\Enums\Levels\TrampolineLevel;
use AustinW\UsaGym\Enums\Levels\GfaLevel;

// Women's Artistic
WomensArtisticLevel::Bronze;    // Xcel Bronze
WomensArtisticLevel::Level4;    // JO Level 4
WomensArtisticLevel::Elite;     // Elite

// Men's Artistic
MensArtisticLevel::Level6JN;    // Level 6 Junior National
MensArtisticLevel::Level10JEJunior; // Level 10 Junior Elite Jr

// Rhythmic
RhythmicLevel::GroupAdvanced;   // Group Advanced

// And many more...
```

## Laravel Integration

### Configuration

The package auto-registers its service provider. Publish the config file:

```bash
php artisan vendor:publish --tag=usagym-config
```

Add your credentials to `.env`:

```env
USAGYM_USERNAME=your-username
USAGYM_PASSWORD=your-password
```

### Usage with Dependency Injection

```php
use AustinW\UsaGym\UsaGym;

class MeetController extends Controller
{
    public function __construct(
        private UsaGym $usagym
    ) {}

    public function athletes(int $sanctionId)
    {
        return $this->usagym
            ->sanctions($sanctionId)
            ->reservations()
            ->athletes();
    }
}
```

### Usage with Facade

```php
use AustinW\UsaGym\Laravel\Facades\UsaGym;

$athletes = UsaGym::sanctions(58025)->reservations()->athletes();
```

## Data Transfer Objects

All API responses are mapped to readonly DTOs:

```php
// AthleteReservation
$athlete->memberId;
$athlete->firstName;
$athlete->lastName;
$athlete->fullName();        // "John Doe"
$athlete->dateOfBirth;       // DateTimeImmutable
$athlete->discipline;        // Discipline enum
$athlete->level;             // "Gold"
$athlete->status;            // ?MemberStatus enum — null if USAG returns a code this SDK doesn't model
$athlete->statusRaw;         // string — the raw API value, always populated
$athlete->canCompete();      // bool

// ClubReservation
$club->clubId;
$club->clubName;
$club->displayName();        // Abbreviation or full name
$club->location();           // "City, ST"

// VerificationResult
$result->eligible;
$result->ineligibleReason;
$result->certificationValid;
$result->certificationLevels;
```

## Error Handling

```php
use AustinW\UsaGym\Exceptions\UsaGymException;
use AustinW\UsaGym\Exceptions\AuthenticationException;
use AustinW\UsaGym\Exceptions\ValidationException;
use AustinW\UsaGym\Exceptions\NotFoundException;

try {
    $athletes = $usagym->sanctions(58025)->reservations()->athletes();
} catch (AuthenticationException $e) {
    // Invalid credentials
} catch (ValidationException $e) {
    // Validation errors
    $errors = $e->errors();
} catch (NotFoundException $e) {
    // Resource not found
} catch (UsaGymException $e) {
    // General API error
    $response = $e->getResponse();
}
```

## Contributing

Thank you for considering contributing to USA Gymnastics SDK! You can read the contribution guide [here](.github/CONTRIBUTING.md).

## Security Vulnerabilities

Please review [our security policy](https://github.com/austinw/usagym-sdk/security/policy) on how to report security vulnerabilities.

## License

USA Gymnastics SDK is open-sourced software licensed under the [MIT license](LICENSE.md).

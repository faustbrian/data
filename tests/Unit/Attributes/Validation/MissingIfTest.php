<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Attributes\Validation\MissingIf;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\Attributes\Validation\StringValidationAttribute;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\RequiringRule;

describe('MissingIf', function (): void {
    describe('Happy Paths', function (): void {
        test('validates when field is missing and condition is met', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $status = null,
                    #[MissingIf('status', 'inactive')]
                    public readonly ?string $active_date = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['status' => 'inactive']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when field is present and condition is not met', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $status = null,
                    #[MissingIf('status', 'inactive')]
                    public readonly ?string $active_date = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['status' => 'active', 'active_date' => '2024-01-01']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when field is missing and condition field does not match any value', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $status = null,
                    #[MissingIf('status', 'active', 'inactive')]
                    public readonly ?string $notes = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['status' => 'pending']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates with multiple condition values', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $type = null,
                    #[MissingIf('type', 'automatic', 'scheduled')]
                    public readonly ?string $manual_trigger = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['type' => 'automatic']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when condition is met with integer value', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?int $count = null,
                    #[MissingIf('count', 0)]
                    public readonly ?string $items = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['count' => 0]);

            expect($data)->toBeInstanceOf($dataClass::class);
        });
    });

    describe('Sad Paths', function (): void {
        test('rejects when field is present and condition is met', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $status = null,
                    #[MissingIf('status', 'inactive')]
                    public readonly ?string $active_date = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['status' => 'inactive', 'active_date' => '2024-01-01']))
                ->toThrow(ValidationException::class);
        });

        test('rejects when field is present with empty string and condition is met', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $status = null,
                    #[MissingIf('status', 'inactive')]
                    public readonly ?string $notes = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['status' => 'inactive', 'notes' => '']))
                ->toThrow(ValidationException::class);
        });

        test('rejects when field is present with null and condition is met', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly bool $enabled = false,
                    #[MissingIf('enabled', false)]
                    public readonly ?array $config = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['enabled' => false, 'config' => null]))
                ->toThrow(ValidationException::class);
        });

        test('rejects when condition matches one of multiple values', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $type = null,
                    #[MissingIf('type', 'automatic', 'scheduled')]
                    public readonly ?string $manual_trigger = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['type' => 'scheduled', 'manual_trigger' => 'value']))
                ->toThrow(ValidationException::class);
        });
    });

    describe('Edge Cases', function (): void {
        test('validates with boolean condition value', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly bool $active = false,
                    #[MissingIf('active', false)]
                    public readonly ?string $reason = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['active' => true]);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when condition field is missing', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[MissingIf('status', 'inactive')]
                    public readonly ?string $active_date = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate([]);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('handles array of condition values', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $status = null,
                    #[MissingIf('status', ['draft', 'archived'])]
                    public readonly ?string $publish_date = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['status' => 'draft']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates with numeric string comparison', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $count = null,
                    #[MissingIf('count', '0')]
                    public readonly ?string $items = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['count' => '0']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates case sensitive string comparison', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $status = null,
                    #[MissingIf('status', 'active')]
                    public readonly ?string $reason = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['status' => 'ACTIVE', 'reason' => 'test']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });
    });

    describe('Attribute Behavior', function (): void {
        test('extends StringValidationAttribute', function (): void {
            $attribute = new MissingIf('field', 'value');

            expect($attribute)->toBeInstanceOf(StringValidationAttribute::class);
        });

        test('implements RequiringRule', function (): void {
            $attribute = new MissingIf('field', 'value');

            expect($attribute)->toBeInstanceOf(RequiringRule::class);
        });

        test('returns correct keyword', function (): void {
            expect(MissingIf::keyword())->toBe('missing_if');
        });

        test('returns parameters with field and single value', function (): void {
            $attribute = new MissingIf('status', 'inactive');
            $parameters = $attribute->parameters();

            expect($parameters)->toHaveCount(2)
                ->and($parameters[1])->toBe(['inactive']);
        });

        test('returns parameters with field and multiple values', function (): void {
            $attribute = new MissingIf('status', 'draft', 'archived');
            $parameters = $attribute->parameters();

            expect($parameters)->toHaveCount(2)
                ->and($parameters[1])->toBe(['draft', 'archived']);
        });
    });
});

<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Attributes\Validation\MissingUnless;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\Attributes\Validation\StringValidationAttribute;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\RequiringRule;

describe('MissingUnless', function (): void {
    describe('Happy Paths', function (): void {
        test('validates when field is missing and condition is not met', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $status = null,
                    #[MissingUnless('status', 'active')]
                    public readonly ?string $active_date = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['status' => 'inactive']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when field is present and condition is met', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $status = null,
                    #[MissingUnless('status', 'active')]
                    public readonly ?string $active_date = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['status' => 'active', 'active_date' => '2024-01-01']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when field is missing and condition field matches none of the values', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $type = null,
                    #[MissingUnless('type', 'automatic', 'scheduled')]
                    public readonly ?string $trigger = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['type' => 'manual']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates with multiple condition values when one matches', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $mode = null,
                    #[MissingUnless('mode', 'read', 'write')]
                    public readonly ?string $data = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['mode' => 'read', 'data' => 'value']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when condition is met with boolean value', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly bool $enabled = false,
                    #[MissingUnless('enabled', true)]
                    public readonly ?string $config = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['enabled' => true, 'config' => 'value']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });
    });

    describe('Sad Paths', function (): void {
        test('rejects when field is present and condition is not met', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $status = null,
                    #[MissingUnless('status', 'active')]
                    public readonly ?string $active_date = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['status' => 'inactive', 'active_date' => '2024-01-01']))
                ->toThrow(ValidationException::class);
        });

        test('rejects when field is present with empty string and condition is not met', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $status = null,
                    #[MissingUnless('status', 'active')]
                    public readonly ?string $notes = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['status' => 'pending', 'notes' => '']))
                ->toThrow(ValidationException::class);
        });

        test('rejects when field is present with null and condition is not met', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly bool $enabled = false,
                    #[MissingUnless('enabled', true)]
                    public readonly ?array $config = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['enabled' => false, 'config' => null]))
                ->toThrow(ValidationException::class);
        });

        test('rejects when condition does not match any of multiple values', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $type = null,
                    #[MissingUnless('type', 'automatic', 'scheduled')]
                    public readonly ?string $config = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['type' => 'manual', 'config' => 'value']))
                ->toThrow(ValidationException::class);
        });
    });

    describe('Edge Cases', function (): void {
        test('validates with integer condition value', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?int $count = null,
                    #[MissingUnless('count', 5)]
                    public readonly ?string $items = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['count' => 5, 'items' => 'data']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates when condition field is missing', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[MissingUnless('status', 'active')]
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
                    #[MissingUnless('status', ['published', 'scheduled'])]
                    public readonly ?string $date = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['status' => 'published', 'date' => '2024-01-01']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates with numeric string comparison', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $count = null,
                    #[MissingUnless('count', '5')]
                    public readonly ?string $items = null,
                ) {}
            };

            $data = $dataClass::validateAndCreate(['count' => '5', 'items' => 'data']);

            expect($data)->toBeInstanceOf($dataClass::class);
        });

        test('validates case sensitive string comparison', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?string $status = null,
                    #[MissingUnless('status', 'active')]
                    public readonly ?string $reason = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['status' => 'ACTIVE', 'reason' => 'test']))
                ->toThrow(ValidationException::class);
        });

        test('validates with zero as condition value', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    public readonly ?int $count = null,
                    #[MissingUnless('count', 1)]
                    public readonly ?string $message = null,
                ) {}
            };

            expect(fn (): object => $dataClass::validateAndCreate(['count' => 0, 'message' => 'none']))
                ->toThrow(ValidationException::class);
        });
    });

    describe('Attribute Behavior', function (): void {
        test('extends StringValidationAttribute', function (): void {
            $attribute = new MissingUnless('field', 'value');

            expect($attribute)->toBeInstanceOf(StringValidationAttribute::class);
        });

        test('implements RequiringRule', function (): void {
            $attribute = new MissingUnless('field', 'value');

            expect($attribute)->toBeInstanceOf(RequiringRule::class);
        });

        test('returns correct keyword', function (): void {
            expect(MissingUnless::keyword())->toBe('missing_unless');
        });

        test('returns parameters with field and single value', function (): void {
            $attribute = new MissingUnless('status', 'active');
            $parameters = $attribute->parameters();

            expect($parameters)->toHaveCount(2)
                ->and($parameters[1])->toBe(['active']);
        });

        test('returns parameters with field and multiple values', function (): void {
            $attribute = new MissingUnless('status', 'active', 'pending');
            $parameters = $attribute->parameters();

            expect($parameters)->toHaveCount(2)
                ->and($parameters[1])->toBe(['active', 'pending']);
        });
    });
});

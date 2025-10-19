<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Casts\StringLikeCast;
use Spatie\LaravelData\Data;

describe('StringLikeCast', function (): void {
    describe('Happy Paths', function (): void {
        test('casts string to normalized string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[StringLikeCast()]
                    public readonly ?string $field = null,
                ) {}
            };

            $data = $dataClass::from(['field' => 'Hello World']);

            expect($data->field)->toBe('Hello World');
        });

        test('casts empty string to null with default blankAsNull', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[StringLikeCast()]
                    public readonly ?string $field = null,
                ) {}
            };

            $data = $dataClass::from(['field' => '']);

            expect($data->field)->toBeNull();
        });

        test('casts empty string to empty string when blankAsNull is false', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[StringLikeCast(blankAsNull: false)]
                    public readonly ?string $field = null,
                ) {}
            };

            $data = $dataClass::from(['field' => '']);

            expect($data->field)->toBe('');
        });

        test('trims whitespace', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[StringLikeCast()]
                    public readonly ?string $field = null,
                ) {}
            };

            $data = $dataClass::from(['field' => '  Hello World  ']);

            expect($data->field)->toBe('Hello World');
        });

        test('collapses whitespace when enabled', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[StringLikeCast(collapseWhitespace: true)]
                    public readonly ?string $field = null,
                ) {}
            };

            $data = $dataClass::from(['field' => 'Hello    World']);

            expect($data->field)->toBe('Hello World');
        });

        test('preserves internal whitespace when collapse disabled', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[StringLikeCast(collapseWhitespace: false)]
                    public readonly ?string $field = null,
                ) {}
            };

            $data = $dataClass::from(['field' => 'Hello    World']);

            expect($data->field)->toBe('Hello    World');
        });
    });

    describe('Sad Paths', function (): void {
        test('casts null to null', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[StringLikeCast()]
                    public readonly ?string $field = null,
                ) {}
            };

            $data = $dataClass::from(['field' => null]);

            expect($data->field)->toBeNull();
        });

        test('casts whitespace-only string to null with blankAsNull', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[StringLikeCast(blankAsNull: true)]
                    public readonly ?string $field = null,
                ) {}
            };

            $data = $dataClass::from(['field' => '   ']);

            expect($data->field)->toBeNull();
        });

        test('casts array to null', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[StringLikeCast()]
                    public readonly ?string $field = null,
                ) {}
            };

            $data = $dataClass::from(['field' => ['array']]);

            expect($data->field)->toBeNull();
        });

        test('casts object to null', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[StringLikeCast()]
                    public readonly ?string $field = null,
                ) {}
            };

            $data = $dataClass::from(['field' => new stdClass()]);

            expect($data->field)->toBeNull();
        });
    });

    describe('Edge Cases', function (): void {
        test('casts integer to string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[StringLikeCast()]
                    public readonly ?string $field = null,
                ) {}
            };

            $data = $dataClass::from(['field' => 123]);

            expect($data->field)->toBe('123');
        });

        test('casts float to string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[StringLikeCast()]
                    public readonly ?string $field = null,
                ) {}
            };

            $data = $dataClass::from(['field' => 123.45]);

            expect($data->field)->toBe('123.45');
        });

        test('casts boolean true to string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[StringLikeCast()]
                    public readonly ?string $field = null,
                ) {}
            };

            $data = $dataClass::from(['field' => true]);

            expect($data->field)->toBe('1');
        });

        test('casts boolean false to null with blankAsNull', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[StringLikeCast(blankAsNull: true)]
                    public readonly ?string $field = null,
                ) {}
            };

            $data = $dataClass::from(['field' => false]);

            expect($data->field)->toBeNull();
        });

        test('removes control characters', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[StringLikeCast()]
                    public readonly ?string $field = null,
                ) {}
            };

            $data = $dataClass::from(['field' => "Hello\x00\x01World"]);

            expect($data->field)->toBe('HelloWorld');
        });

        test('handles unicode characters', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[StringLikeCast()]
                    public readonly ?string $field = null,
                ) {}
            };

            $data = $dataClass::from(['field' => 'Ñoño 日本語']);

            expect($data->field)->toBe('Ñoño 日本語');
        });

        test('handles newlines and tabs with collapse', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[StringLikeCast(collapseWhitespace: true)]
                    public readonly ?string $field = null,
                ) {}
            };

            $data = $dataClass::from(['field' => "Hello\n\tWorld"]);

            expect($data->field)->toBe('Hello World');
        });

        test('preserves newlines and tabs without collapse', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[StringLikeCast(collapseWhitespace: false)]
                    public readonly ?string $field = null,
                ) {}
            };

            $data = $dataClass::from(['field' => "Hello\n\tWorld"]);

            expect($data->field)->toBe("Hello\n\tWorld");
        });

        test('handles stringable object', function (): void {
            $stringable = new class() implements Stringable
            {
                public function __toString(): string
                {
                    return 'Stringable Value';
                }
            };

            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[StringLikeCast()]
                    public readonly ?string $field = null,
                ) {}
            };

            $data = $dataClass::from(['field' => $stringable]);

            expect($data->field)->toBe('Stringable Value');
        });

        test('handles multibyte whitespace', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[StringLikeCast()]
                    public readonly ?string $field = null,
                ) {}
            };

            $data = $dataClass::from(['field' => ' Hello ']);

            expect($data->field)->toBe('Hello');
        });

        test('combines blankAsNull false and collapseWhitespace true', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[StringLikeCast(blankAsNull: false, collapseWhitespace: true)]
                    public readonly ?string $field = null,
                ) {}
            };

            $data = $dataClass::from(['field' => '   ']);

            expect($data->field)->toBe('');
        });
    });
});

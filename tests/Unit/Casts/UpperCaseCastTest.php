<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Casts\UpperCaseCast;
use Spatie\LaravelData\Data;

describe('UpperCaseCast', function (): void {
    describe('Happy Paths', function (): void {
        test('converts lowercase string to uppercase', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UpperCaseCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'hello world']);

            expect($data->field)->toBe('HELLO WORLD');
        });

        test('converts mixed case string to uppercase', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UpperCaseCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'HeLLo WoRLd']);

            expect($data->field)->toBe('HELLO WORLD');
        });

        test('handles already uppercase strings', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UpperCaseCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'HELLO WORLD']);

            expect($data->field)->toBe('HELLO WORLD');
        });

        test('handles empty strings', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UpperCaseCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => '']);

            expect($data->field)->toBe('');
        });
    });

    describe('Edge Cases', function (): void {
        test('handles multibyte characters', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UpperCaseCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'ñoño']);

            expect($data->field)->toBe('ÑOÑO');
        });

        test('handles unicode characters', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UpperCaseCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'москва']);

            expect($data->field)->toBe('МОСКВА');
        });

        test('handles mixed ASCII and multibyte', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UpperCaseCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'hello ñoño']);

            expect($data->field)->toBe('HELLO ÑOÑO');
        });

        test('returns non-string values unchanged', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UpperCaseCast()]
                    public readonly int $field = 0,
                ) {}
            };

            $data = $dataClass::from(['field' => 123]);

            expect($data->field)->toBe(123);
        });

        test('returns null unchanged', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UpperCaseCast()]
                    public readonly ?string $field = null,
                ) {}
            };

            $data = $dataClass::from(['field' => null]);

            expect($data->field)->toBeNull();
        });

        test('returns array unchanged', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UpperCaseCast()]
                    public readonly array $field = [],
                ) {}
            };

            $data = $dataClass::from(['field' => ['hello']]);

            expect($data->field)->toBe(['hello']);
        });

        test('handles strings with numbers and symbols', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UpperCaseCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'abc123!@#']);

            expect($data->field)->toBe('ABC123!@#');
        });

        test('handles German sharp s character', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UpperCaseCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'straße']);

            expect($data->field)->toBe('STRASSE');
        });

        test('handles Turkish dotted and dotless i', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UpperCaseCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'istanbul']);

            expect($data->field)->toBe('ISTANBUL');
        });

        test('handles Greek characters', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UpperCaseCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'ελληνικά']);

            expect($data->field)->toBe('ΕΛΛΗΝΙΚΆ');
        });

        test('handles accented characters', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[UpperCaseCast()]
                    public readonly string $field = '',
                ) {}
            };

            $data = $dataClass::from(['field' => 'café']);

            expect($data->field)->toBe('CAFÉ');
        });
    });
});

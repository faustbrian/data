<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Casts\LowerCaseCast;
use Spatie\LaravelData\Data;

describe('LowerCaseCast', function (): void {
    describe('Happy Paths', function (): void {
        test('converts uppercase string to lowercase', function (): void {
            $result = new class() extends Data
            {
                public function __construct(
                    #[LowerCaseCast()]
                    public readonly string $field = '',
                ) {}
            }::from(['field' => 'HELLO WORLD']);

            expect($result->field)->toBe('hello world');
        });

        test('converts mixed case string to lowercase', function (): void {
            $result = new class() extends Data
            {
                public function __construct(
                    #[LowerCaseCast()]
                    public readonly string $field = '',
                ) {}
            }::from(['field' => 'Hello World']);

            expect($result->field)->toBe('hello world');
        });

        test('handles already lowercase strings', function (): void {
            $result = new class() extends Data
            {
                public function __construct(
                    #[LowerCaseCast()]
                    public readonly string $field = '',
                ) {}
            }::from(['field' => 'hello world']);

            expect($result->field)->toBe('hello world');
        });

        test('handles empty strings', function (): void {
            $result = new class() extends Data
            {
                public function __construct(
                    #[LowerCaseCast()]
                    public readonly string $field = '',
                ) {}
            }::from(['field' => '']);

            expect($result->field)->toBe('');
        });
    });

    describe('Edge Cases', function (): void {
        test('handles multibyte characters', function (): void {
            $result = new class() extends Data
            {
                public function __construct(
                    #[LowerCaseCast()]
                    public readonly string $field = '',
                ) {}
            }::from(['field' => 'ÑOÑO']);

            expect($result->field)->toBe('ñoño');
        });

        test('handles unicode characters', function (): void {
            $result = new class() extends Data
            {
                public function __construct(
                    #[LowerCaseCast()]
                    public readonly string $field = '',
                ) {}
            }::from(['field' => 'МОСКВА']);

            expect($result->field)->toBe('москва');
        });

        test('handles mixed ASCII and multibyte', function (): void {
            $result = new class() extends Data
            {
                public function __construct(
                    #[LowerCaseCast()]
                    public readonly string $field = '',
                ) {}
            }::from(['field' => 'Hello ÑOÑO']);

            expect($result->field)->toBe('hello ñoño');
        });

        test('returns non-string values unchanged', function (): void {
            $result = new class() extends Data
            {
                public function __construct(
                    #[LowerCaseCast()]
                    public readonly int $field = 0,
                ) {}
            }::from(['field' => 123]);

            expect($result->field)->toBe(123);
        });

        test('returns null unchanged', function (): void {
            $result = new class() extends Data
            {
                public function __construct(
                    #[LowerCaseCast()]
                    public readonly ?string $field = null,
                ) {}
            }::from(['field' => null]);

            expect($result->field)->toBeNull();
        });

        test('returns array unchanged', function (): void {
            $result = new class() extends Data
            {
                public function __construct(
                    #[LowerCaseCast()]
                    public readonly array $field = [],
                ) {}
            }::from(['field' => ['HELLO']]);

            expect($result->field)->toBe(['HELLO']);
        });

        test('handles strings with numbers and symbols', function (): void {
            $result = new class() extends Data
            {
                public function __construct(
                    #[LowerCaseCast()]
                    public readonly string $field = '',
                ) {}
            }::from(['field' => 'ABC123!@#']);

            expect($result->field)->toBe('abc123!@#');
        });
    });
});

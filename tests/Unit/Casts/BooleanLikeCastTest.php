<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Casts\BooleanLikeCast;
use Spatie\LaravelData\Data;

describe('BooleanLikeCast', function (): void {
    describe('Happy Paths', function (): void {
        test('casts true boolean values', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[BooleanLikeCast()]
                    public readonly bool $is_active = false,
                ) {}
            };

            $data = $dataClass::from(['is_active' => true]);

            expect($data->is_active)->toBeTrue();
        });

        test('casts false boolean values', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[BooleanLikeCast()]
                    public readonly bool $is_active = false,
                ) {}
            };

            $data = $dataClass::from(['is_active' => false]);

            expect($data->is_active)->toBeFalse();
        });

        test('casts string "1" to true', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[BooleanLikeCast()]
                    public readonly bool $is_active = false,
                ) {}
            };

            $data = $dataClass::from(['is_active' => '1']);

            expect($data->is_active)->toBeTrue();
        });

        test('casts string "0" to false', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[BooleanLikeCast()]
                    public readonly bool $is_active = false,
                ) {}
            };

            $data = $dataClass::from(['is_active' => '0']);

            expect($data->is_active)->toBeFalse();
        });

        test('casts string "true" to true', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[BooleanLikeCast()]
                    public readonly bool $is_active = false,
                ) {}
            };

            $data = $dataClass::from(['is_active' => 'true']);

            expect($data->is_active)->toBeTrue();
        });

        test('casts string "false" to false', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[BooleanLikeCast()]
                    public readonly bool $is_active = false,
                ) {}
            };

            $data = $dataClass::from(['is_active' => 'false']);

            expect($data->is_active)->toBeFalse();
        });

        test('casts string "yes" to true', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[BooleanLikeCast()]
                    public readonly bool $is_active = false,
                ) {}
            };

            $data = $dataClass::from(['is_active' => 'yes']);

            expect($data->is_active)->toBeTrue();
        });

        test('casts string "no" to false', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[BooleanLikeCast()]
                    public readonly bool $is_active = false,
                ) {}
            };

            $data = $dataClass::from(['is_active' => 'no']);

            expect($data->is_active)->toBeFalse();
        });

        test('casts string "y" to true', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[BooleanLikeCast()]
                    public readonly bool $is_active = false,
                ) {}
            };

            $data = $dataClass::from(['is_active' => 'y']);

            expect($data->is_active)->toBeTrue();
        });

        test('casts string "n" to false', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[BooleanLikeCast()]
                    public readonly bool $is_active = false,
                ) {}
            };

            $data = $dataClass::from(['is_active' => 'n']);

            expect($data->is_active)->toBeFalse();
        });

        test('casts string "on" to true', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[BooleanLikeCast()]
                    public readonly bool $is_active = false,
                ) {}
            };

            $data = $dataClass::from(['is_active' => 'on']);

            expect($data->is_active)->toBeTrue();
        });

        test('casts string "off" to false', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[BooleanLikeCast()]
                    public readonly bool $is_active = false,
                ) {}
            };

            $data = $dataClass::from(['is_active' => 'off']);

            expect($data->is_active)->toBeFalse();
        });

        test('casts integer 1 to true', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[BooleanLikeCast()]
                    public readonly bool $is_active = false,
                ) {}
            };

            $data = $dataClass::from(['is_active' => 1]);

            expect($data->is_active)->toBeTrue();
        });

        test('casts integer 0 to false', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[BooleanLikeCast()]
                    public readonly bool $is_active = false,
                ) {}
            };

            $data = $dataClass::from(['is_active' => 0]);

            expect($data->is_active)->toBeFalse();
        });
    });

    describe('Sad Paths', function (): void {
        test('casts unknown string values using unknownDefault false', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[BooleanLikeCast(unknownDefault: false)]
                    public readonly bool $is_active = false,
                ) {}
            };

            $data = $dataClass::from(['is_active' => 'unknown']);

            expect($data->is_active)->toBeFalse();
        });

        test('casts unknown string values using unknownDefault true', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[BooleanLikeCast(unknownDefault: true)]
                    public readonly bool $is_active = false,
                ) {}
            };

            $data = $dataClass::from(['is_active' => 'unknown']);

            expect($data->is_active)->toBeTrue();
        });

        test('omitted value uses default false', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[BooleanLikeCast(unknownDefault: false)]
                    public readonly bool $is_active = false,
                ) {}
            };

            $data = $dataClass::from([]);

            expect($data->is_active)->toBeFalse();
        });

        test('casts numeric values other than 0 or 1 as unknown', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[BooleanLikeCast(unknownDefault: false)]
                    public readonly bool $is_active = false,
                ) {}
            };

            $data = $dataClass::from(['is_active' => 5]);

            expect($data->is_active)->toBeFalse();
        });
    });

    describe('Edge Cases', function (): void {
        test('handles uppercase string "TRUE"', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[BooleanLikeCast()]
                    public readonly bool $is_active = false,
                ) {}
            };

            $data = $dataClass::from(['is_active' => 'TRUE']);

            expect($data->is_active)->toBeTrue();
        });

        test('handles uppercase string "FALSE"', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[BooleanLikeCast()]
                    public readonly bool $is_active = false,
                ) {}
            };

            $data = $dataClass::from(['is_active' => 'FALSE']);

            expect($data->is_active)->toBeFalse();
        });

        test('handles mixed case string "Yes"', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[BooleanLikeCast()]
                    public readonly bool $is_active = false,
                ) {}
            };

            $data = $dataClass::from(['is_active' => 'Yes']);

            expect($data->is_active)->toBeTrue();
        });

        test('handles padded whitespace " true "', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[BooleanLikeCast()]
                    public readonly bool $is_active = false,
                ) {}
            };

            $data = $dataClass::from(['is_active' => ' true ']);

            expect($data->is_active)->toBeTrue();
        });

        test('handles custom truthy values', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[BooleanLikeCast(truthy: ['accepted', 'confirmed'])]
                    public readonly bool $is_active = false,
                ) {}
            };

            $data = $dataClass::from(['is_active' => 'accepted']);

            expect($data->is_active)->toBeTrue();
        });

        test('handles custom falsy values', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[BooleanLikeCast(falsy: ['rejected', 'denied'])]
                    public readonly bool $is_active = false,
                ) {}
            };

            $data = $dataClass::from(['is_active' => 'rejected']);

            expect($data->is_active)->toBeFalse();
        });

        test('handles custom truthy and falsy with unknown value', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[BooleanLikeCast(truthy: ['accepted'], falsy: ['rejected'], unknownDefault: true)]
                    public readonly bool $is_active = false,
                ) {}
            };

            $data = $dataClass::from(['is_active' => 'pending']);

            expect($data->is_active)->toBeTrue();
        });

        test('handles float values as unknown', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[BooleanLikeCast(unknownDefault: false)]
                    public readonly bool $is_active = false,
                ) {}
            };

            $data = $dataClass::from(['is_active' => 3.14]);

            expect($data->is_active)->toBeFalse();
        });

        test('handles empty string as unknown', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[BooleanLikeCast(unknownDefault: false)]
                    public readonly bool $is_active = false,
                ) {}
            };

            $data = $dataClass::from(['is_active' => '']);

            expect($data->is_active)->toBeFalse();
        });

        test('handles array values as unknown', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[BooleanLikeCast(unknownDefault: true)]
                    public readonly mixed $is_active = false,
                ) {}
            };

            $data = $dataClass::from(['is_active' => ['value']]);

            expect($data->is_active)->toBeTrue();
        });
    });
});

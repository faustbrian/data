<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Transformers\MaskTransformer;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;

describe('MaskTransformer', function (): void {
    describe('Happy Paths', function (): void {
        test('masks string with default settings showing last 4 characters', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(MaskTransformer::class)]
                    public readonly ?string $creditCard = null,
                ) {}
            };

            $data = $dataClass::from(['creditCard' => '1234567890123456']);
            $result = $data->toArray();

            expect($result['creditCard'])->toBe('************3456')
                ->and(mb_strlen((string) $result['creditCard']))->toBe(16);
        });

        test('masks string showing first 2 and last 4 characters', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(MaskTransformer::class, mask: '*', visibleStart: 2, visibleEnd: 4)]
                    public readonly ?string $accountNumber = null,
                ) {}
            };

            $data = $dataClass::from(['accountNumber' => '1234567890123456']);
            $result = $data->toArray();

            expect($result['accountNumber'])->toBe('12**********3456');
        });

        test('masks string with custom mask character', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(MaskTransformer::class, mask: '#', visibleStart: 0, visibleEnd: 4)]
                    public readonly ?string $secret = null,
                ) {}
            };

            $data = $dataClass::from(['secret' => 'secretpassword']);
            $result = $data->toArray();

            expect($result['secret'])->toBe('##########word');
        });

        test('masks email showing first 2 characters and domain', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(MaskTransformer::class, mask: '*', visibleStart: 2, visibleEnd: 11)]
                    public readonly ?string $email = null,
                ) {}
            };

            $data = $dataClass::from(['email' => 'user@example.com']);
            $result = $data->toArray();

            expect($result['email'])->toBe('us***example.com');
        });

        test('fully masks short strings', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(MaskTransformer::class)]
                    public readonly ?string $pin = null,
                ) {}
            };

            $data = $dataClass::from(['pin' => '1234']);
            $result = $data->toArray();

            expect($result['pin'])->toBe('****');
        });
    });

    describe('Sad Paths', function (): void {
        test('returns non-string values unchanged', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(MaskTransformer::class)]
                    public readonly ?int $number = null,
                ) {}
            };

            $data = $dataClass::from(['number' => 12_345]);
            $result = $data->toArray();

            expect($result['number'])->toBe(12_345);
        });

        test('returns null unchanged', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(MaskTransformer::class)]
                    public readonly ?string $value = null,
                ) {}
            };

            $data = $dataClass::from(['value' => null]);
            $result = $data->toArray();

            expect($result['value'])->toBeNull();
        });

        test('returns boolean unchanged', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(MaskTransformer::class)]
                    public readonly bool $flag = false,
                ) {}
            };

            $data = $dataClass::from(['flag' => true]);
            $result = $data->toArray();

            expect($result['flag'])->toBeTrue();
        });

        test('returns array unchanged', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(MaskTransformer::class)]
                    public readonly ?array $items = null,
                ) {}
            };

            $data = $dataClass::from(['items' => ['secret', 'data']]);
            $result = $data->toArray();

            expect($result['items'])->toBe(['secret', 'data']);
        });
    });

    describe('Edge Cases', function (): void {
        test('masks empty string', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(MaskTransformer::class)]
                    public readonly ?string $value = null,
                ) {}
            };

            $data = $dataClass::from(['value' => '']);
            $result = $data->toArray();

            expect($result['value'])->toBe('');
        });

        test('masks single character', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(MaskTransformer::class)]
                    public readonly ?string $value = null,
                ) {}
            };

            $data = $dataClass::from(['value' => 'A']);
            $result = $data->toArray();

            expect($result['value'])->toBe('*');
        });

        test('masks unicode characters correctly', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(MaskTransformer::class, visibleStart: 0, visibleEnd: 2)]
                    public readonly ?string $value = null,
                ) {}
            };

            $data = $dataClass::from(['value' => 'Ñoño😀123']);
            $result = $data->toArray();

            expect($result['value'])->toBe('******23')
                ->and(mb_strlen((string) $result['value']))->toBe(8);
        });

        test('masks with zero visible start characters', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(MaskTransformer::class, visibleStart: 0, visibleEnd: 3)]
                    public readonly ?string $value = null,
                ) {}
            };

            $data = $dataClass::from(['value' => 'password123']);
            $result = $data->toArray();

            expect($result['value'])->toBe('********123');
        });

        test('masks with zero visible end characters', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(MaskTransformer::class, visibleStart: 3, visibleEnd: 0)]
                    public readonly ?string $value = null,
                ) {}
            };

            $data = $dataClass::from(['value' => 'password123']);
            $result = $data->toArray();

            expect($result['value'])->toBe('pas********');
        });

        test('fully masks when visible chars exceed string length', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(MaskTransformer::class, visibleStart: 5, visibleEnd: 5)]
                    public readonly ?string $value = null,
                ) {}
            };

            $data = $dataClass::from(['value' => 'short']);
            $result = $data->toArray();

            expect($result['value'])->toBe('*****');
        });

        test('works with very long strings', function (): void {
            $dataClass = new class() extends Data
            {
                public function __construct(
                    #[WithTransformer(MaskTransformer::class)]
                    public readonly ?string $value = null,
                ) {}
            };

            $longString = str_repeat('a', 1_000);
            $data = $dataClass::from(['value' => $longString]);
            $result = $data->toArray();

            expect($result['value'])->toStartWith('****')
                ->and($result['value'])->toEndWith('aaaa')
                ->and(mb_strlen((string) $result['value']))->toBe(1_000);
        });
    });
});

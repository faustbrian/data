<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Traits\MarkupSerializationTrait;
use Spatie\LaravelData\Data;

describe('MarkupSerializationTrait', function (): void {
    describe('Happy Paths', function (): void {
        test('converts simple data to yaml', function (): void {
            $dataClass = new class() extends Data
            {
                use MarkupSerializationTrait;

                public function __construct(
                    public readonly string $name = '',
                    public readonly int $age = 0,
                ) {}
            };

            $data = $dataClass::from(['name' => 'John', 'age' => 30]);
            $yaml = $data->toYaml();

            expect($yaml)->toContain('name: John')
                ->and($yaml)->toContain('age: 30');
        });

        test('converts nested data to yaml', function (): void {
            $dataClass = new class() extends Data
            {
                use MarkupSerializationTrait;

                public function __construct(
                    public readonly array $user = [],
                    public readonly bool $active = false,
                ) {}
            };

            $data = $dataClass::from(['user' => ['name' => 'John', 'email' => 'john@example.com'], 'active' => true]);
            $yaml = $data->toYaml();

            expect($yaml)->toContain('user:')
                ->and($yaml)->toContain('name: John')
                ->and($yaml)->toContain('email: john@example.com')
                ->and($yaml)->toContain('active: true');
        });

        test('converts simple data to xml with default root', function (): void {
            $dataClass = new class() extends Data
            {
                use MarkupSerializationTrait;

                public function __construct(
                    public readonly string $title = '',
                    public readonly string $status = '',
                ) {}
            };

            $data = $dataClass::from(['title' => 'Test', 'status' => 'active']);
            $xml = $data->toXml();

            expect($xml)->toContain('<root>')
                ->and($xml)->toContain('</root>')
                ->and($xml)->toContain('<title>Test</title>')
                ->and($xml)->toContain('<status>active</status>');
        });

        test('converts data to xml with custom root element', function (): void {
            $dataClass = new class() extends Data
            {
                use MarkupSerializationTrait;

                public function __construct(
                    public readonly int $id = 0,
                    public readonly string $name = '',
                ) {}
            };

            $data = $dataClass::from(['id' => 1, 'name' => 'Product']);
            $xml = $data->toXml('product');

            expect($xml)->toContain('<product>')
                ->and($xml)->toContain('</product>')
                ->and($xml)->toContain('<id>1</id>')
                ->and($xml)->toContain('<name>Product</name>');
        });

        test('converts nested array data to xml', function (): void {
            $dataClass = new class() extends Data
            {
                use MarkupSerializationTrait;

                public function __construct(
                    public readonly array $items = [],
                ) {}
            };

            $data = $dataClass::from(['items' => ['apple', 'banana', 'orange']]);
            $xml = $data->toXml('fruits');

            expect($xml)->toContain('<fruits>')
                ->and($xml)->toContain('</fruits>')
                ->and($xml)->toContain('<items>');
        });
    });

    describe('Edge Cases', function (): void {
        test('converts empty data to yaml', function (): void {
            $dataClass = new class() extends Data
            {
                use MarkupSerializationTrait;
            };

            $data = $dataClass::from([]);
            $yaml = $data->toYaml();

            expect($yaml)->toBeString()
                ->and($yaml)->toBe('{  }');
        });

        test('converts empty data to xml', function (): void {
            $dataClass = new class() extends Data
            {
                use MarkupSerializationTrait;
            };

            $data = $dataClass::from([]);
            $xml = $data->toXml();

            expect($xml)->toContain('<root')
                ->and($xml)->toContain('/>');
        });

        test('handles special characters in yaml', function (): void {
            $dataClass = new class() extends Data
            {
                use MarkupSerializationTrait;

                public function __construct(
                    public readonly string $text = '',
                ) {}
            };

            $data = $dataClass::from(['text' => 'Hello & "World" <tag>']);
            $yaml = $data->toYaml();

            expect($yaml)->toContain('text:')
                ->and($yaml)->toBeString();
        });

        test('handles special characters in xml', function (): void {
            $dataClass = new class() extends Data
            {
                use MarkupSerializationTrait;

                public function __construct(
                    public readonly string $message = '',
                ) {}
            };

            $data = $dataClass::from(['message' => 'Hello & "World" <tag>']);
            $xml = $data->toXml();

            expect($xml)->toContain('<root>')
                ->and($xml)->toContain('</root>')
                ->and($xml)->toContain('<message>');
        });

        test('handles unicode characters in yaml', function (): void {
            $dataClass = new class() extends Data
            {
                use MarkupSerializationTrait;

                public function __construct(
                    public readonly string $text = '',
                ) {}
            };

            $data = $dataClass::from(['text' => 'Ñoño 日本語 🎉']);
            $yaml = $data->toYaml();

            expect($yaml)->toContain('text:')
                ->and($yaml)->toContain('Ñoño 日本語 🎉');
        });

        test('handles unicode characters in xml', function (): void {
            $dataClass = new class() extends Data
            {
                use MarkupSerializationTrait;

                public function __construct(
                    public readonly string $text = '',
                ) {}
            };

            $data = $dataClass::from(['text' => 'Ñoño 日本語 🎉']);
            $xml = $data->toXml();

            expect($xml)->toContain('<root>')
                ->and($xml)->toContain('</root>');
        });

        test('handles numeric keys in yaml', function (): void {
            $dataClass = new class() extends Data
            {
                use MarkupSerializationTrait;

                public function __construct(
                    public readonly array $items = [],
                ) {}
            };

            $data = $dataClass::from(['items' => [0 => 'first', 1 => 'second', 2 => 'third']]);
            $yaml = $data->toYaml();

            expect($yaml)->toContain('items:')
                ->and($yaml)->toBeString();
        });

        test('handles boolean and null values in yaml', function (): void {
            $dataClass = new class() extends Data
            {
                use MarkupSerializationTrait;

                public function __construct(
                    public readonly bool $active = false,
                    public readonly bool $inactive = false,
                    public readonly ?string $optional = null,
                ) {}
            };

            $data = $dataClass::from(['active' => true, 'inactive' => false, 'optional' => null]);
            $yaml = $data->toYaml();

            expect($yaml)->toContain('active: true')
                ->and($yaml)->toContain('inactive: false')
                ->and($yaml)->toContain('optional: null');
        });

        test('handles complex nested structures in yaml', function (): void {
            $dataClass = new class() extends Data
            {
                use MarkupSerializationTrait;

                public function __construct(
                    public readonly array $config = [],
                ) {}
            };

            $data = $dataClass::from(['config' => ['database' => ['host' => 'localhost'], 'cache' => ['driver' => 'redis']]]);
            $yaml = $data->toYaml();

            expect($yaml)->toContain('config:')
                ->and($yaml)->toContain('database:')
                ->and($yaml)->toContain('cache:')
                ->and($yaml)->toContain('host: localhost');
        });

        test('handles deeply nested xml structures', function (): void {
            $dataClass = new class() extends Data
            {
                use MarkupSerializationTrait;

                public function __construct(
                    public readonly array $level1 = [],
                ) {}
            };

            $data = $dataClass::from(['level1' => ['level2' => ['level3' => 'value']]]);
            $xml = $data->toXml();

            expect($xml)->toContain('<root>')
                ->and($xml)->toContain('</root>')
                ->and($xml)->toContain('<level1>');
        });
    });
});

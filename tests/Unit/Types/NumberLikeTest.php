<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Types\NumberLike;

it('returns null for null or empty input', function (): void {
    expect(NumberLike::create(null)->value())->toBeNull();
    expect(NumberLike::create('')->value())->toBeNull();
    expect(NumberLike::create('   ')->value())->toBeNull();
});

it('parses integers and floats from strings', function (): void {
    $n1 = NumberLike::create('123');
    expect($n1->value())->toBe('123')
        ->and($n1->asInt())->toBe(123)
        ->and($n1->asFloat())->toBe(123.0)
        ->and($n1->isInteger())->toBeTrue()
        ->and($n1->isFloat())->toBeFalse();

    $n2 = NumberLike::create('123.45');
    expect($n2->value())->toBe('123.45')
        ->and($n2->asInt())->toBe(123)
        ->and($n2->asFloat())->toBe(123.45)
        ->and($n2->isInteger())->toBeFalse()
        ->and($n2->isFloat())->toBeTrue();
});

it('handles comma decimals and thousand separators', function (): void {
    expect(NumberLike::create('123,45')->value())->toBe('123.45');
    expect(NumberLike::create('1,234.56')->value())->toBe('1234.56');
    expect(NumberLike::create("1\xC2\xA0234,56")->value())->toBe('1234.56'); // NBSP thousands
    expect(NumberLike::create("12\xE2\x80\xAF345,67")->value())->toBe('12345.67'); // NARROW NBSP
    expect(NumberLike::create('1 234,56')->value())->toBe('1234.56'); // space thousands
});

it('supports signed values', function (): void {
    expect(NumberLike::create('-12,34')->value())->toBe('-12.34');
    expect(NumberLike::create('+7')->value())->toBe('+7');
});

it('returns null for invalid numeric strings', function (): void {
    expect(NumberLike::create('abc')->value())->toBeNull();
    expect(NumberLike::create('12..3')->value())->toBeNull();
});

it('canonicalizes float inputs', function (): void {
    $n = NumberLike::create(1.230_0);
    expect($n->value())->toBe('1.23')
        ->and($n->asFloat())->toBe(1.23);
});

it('handles integer inputs directly', function (): void {
    $n = NumberLike::create(42);
    expect($n->value())->toBe('42')
        ->and($n->asInt())->toBe(42)
        ->and($n->isInteger())->toBeTrue();
});

it('returns null for non-numeric types', function (): void {
    expect(NumberLike::create([])->value())->toBeNull();
    expect(NumberLike::create(
        new stdClass(),
    )->value())->toBeNull();
    expect(NumberLike::create(true)->value())->toBeNull();
});

it('handles negative zero float edge case', function (): void {
    $n = NumberLike::create(-0.0);
    expect($n->value())->toBe('0');
});

it('returns null from asInt when value is null', function (): void {
    $n = NumberLike::create('abc');
    expect($n->asInt())->toBeNull();
});

it('returns null from asFloat when value is null', function (): void {
    $n = NumberLike::create('invalid');
    expect($n->asFloat())->toBeNull();
});

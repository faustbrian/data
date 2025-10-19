<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Types\StringLike;

it('returns null for null or blank and reports empty', function (): void {
    $s1 = StringLike::create(null);
    expect($s1->value())->toBeNull()
        ->and($s1->isEmpty())->toBeTrue()
        ->and((string) $s1)->toBe('');

    $s2 = StringLike::create('   ');
    expect($s2->value())->toBeNull()
        ->and($s2->isEmpty())->toBeTrue();
});

it('keeps empty string when blankAsNull is false', function (): void {
    $s = StringLike::create('   ', blankAsNull: false);

    expect($s->value())->toBe('')
        ->and($s->isEmpty())->toBeTrue()
        ->and((string) $s)->toBe('');
});

it('trims and collapses internal whitespace when enabled', function (): void {
    $s = StringLike::create("  foo   bar \t baz  ", collapseWhitespace: true);

    expect($s->value())->toBe('foo bar baz');
});

it('removes control characters before trimming', function (): void {
    $s = StringLike::create("A\x00\x07\tB");

    expect($s->value())->toBe("A\tB");
});

it('returns null for non-scalar non-stringable values', function (): void {
    expect(StringLike::create([])->value())->toBeNull();
    expect(StringLike::create(
        new stdClass(),
    )->value())->toBeNull();
    expect(StringLike::create(['foo', 'bar'])->value())->toBeNull();
});

it('handles stringable objects correctly', function (): void {
    $stringable = new class() implements Stringable
    {
        public function __toString(): string
        {
            return '  hello  ';
        }
    };

    $s = StringLike::create($stringable);
    expect($s->value())->toBe('hello');
});

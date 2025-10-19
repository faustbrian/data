<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Types\BooleanLike;

it('normalizes various truthy and falsy string values', function (): void {
    foreach (['1', 'true', 'TRUE', ' yes ', 'Y', 'on'] as $v) {
        $b = BooleanLike::create($v);
        expect($b->isTrue())->toBeTrue()
            ->and($b->isFalse())->toBeFalse()
            ->and($b->isUnknown())->toBeFalse()
            ->and($b->value())->toBeTrue();
    }

    foreach (['0', 'false', 'FALSE', ' no ', 'N', 'off'] as $v) {
        $b = BooleanLike::create($v);
        expect($b->isFalse())->toBeTrue()
            ->and($b->isTrue())->toBeFalse()
            ->and($b->isUnknown())->toBeFalse()
            ->and($b->value())->toBeFalse();
    }
});

it('handles native booleans and numerics 1/0', function (): void {
    expect(BooleanLike::create(true)->value())->toBeTrue();
    expect(BooleanLike::create(false)->value())->toBeFalse();
    expect(BooleanLike::create(1)->value())->toBeTrue();
    expect(BooleanLike::create(0)->value())->toBeFalse();
});

it('treats unknown values as null', function (): void {
    expect(BooleanLike::create(null)->isUnknown())->toBeTrue();
    expect(BooleanLike::create('maybe')->isUnknown())->toBeTrue();
    expect(BooleanLike::create(2)->isUnknown())->toBeTrue();
});

it('supports custom truthy/falsy sets', function (): void {
    $b1 = BooleanLike::create('enabled', truthy: ['enabled'], falsy: ['disabled']);
    $b0 = BooleanLike::create('disabled', truthy: ['enabled'], falsy: ['disabled']);

    expect($b1->isTrue())->toBeTrue();
    expect($b0->isFalse())->toBeTrue();
});

it('returns fallback with orDefault for unknown values', function (): void {
    expect(BooleanLike::create('unknown')->orDefault(true))->toBeTrue();
    expect(BooleanLike::create('unknown')->orDefault(false))->toBeFalse();
});

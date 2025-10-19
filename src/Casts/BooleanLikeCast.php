<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Data\Casts;

use Attribute;
use Cline\Data\Types\BooleanLike;
use Override;
use Spatie\LaravelData\Attributes\GetsCast;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

/**
 * Normalizes various string and scalar representations into strict boolean values.
 *
 * Provides flexible boolean casting that recognizes common truthy/falsy string
 * representations (e.g., "yes", "no", "1", "0", "true", "false") and converts
 * them to PHP boolean values. Supports custom truthy/falsy word lists and
 * configurable defaults for ambiguous or unknown values.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
#[Attribute()]
final readonly class BooleanLikeCast implements Cast, GetsCast
{
    /**
     * Create a new boolean-like cast instance.
     *
     * @param array<int, string> $truthy         Custom list of string values that should be
     *                                           interpreted as true. When empty, defaults to
     *                                           BooleanLike::DEFAULT_TRUTHY which includes
     *                                           common representations like "yes", "true", "1".
     * @param array<int, string> $falsy          Custom list of string values that should be
     *                                           interpreted as false. When empty, defaults to
     *                                           BooleanLike::DEFAULT_FALSY which includes
     *                                           common representations like "no", "false", "0".
     * @param bool               $unknownDefault The boolean value to return when the input cannot
     *                                           be matched to either truthy or falsy lists. Allows
     *                                           graceful handling of ambiguous values by defaulting
     *                                           to either true or false rather than throwing exceptions.
     */
    public function __construct(
        private array $truthy = [],
        private array $falsy = [],
        private bool $unknownDefault = false,
    ) {}

    /**
     * Returns the cast instance for the Spatie Laravel Data package.
     *
     * @return Cast The current cast instance
     */
    public function get(): Cast
    {
        return $this;
    }

    /**
     * Casts a value to a boolean using flexible truthy/falsy matching.
     *
     * Attempts to match the input value against configurable truthy and falsy
     * string lists. Returns the corresponding boolean value for matches, or the
     * configured default for unknown/ambiguous values that don't match either list.
     *
     * @param  DataProperty         $property   The property being cast
     * @param  mixed                $value      The value to cast (typically a string or scalar)
     * @param  array<string, mixed> $properties All properties in the data object
     * @param  CreationContext      $context    The creation context for the data object
     * @return bool                 The normalized boolean value based on truthy/falsy matching
     */
    #[Override()]
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        $truthy = $this->truthy === [] ? BooleanLike::DEFAULT_TRUTHY : $this->truthy;
        $falsy = $this->falsy === [] ? BooleanLike::DEFAULT_FALSY : $this->falsy;

        $b = BooleanLike::create($value, $truthy, $falsy);

        if ($b->isUnknown()) {
            return $this->unknownDefault;
        }

        return $b->value();
    }
}

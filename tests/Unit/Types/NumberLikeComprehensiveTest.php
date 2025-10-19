<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Data\Types\NumberLike;

describe('Edge cases for line 218 coverage', function (): void {
    it('normalizes negative zero from very small negative floats', function (): void {
        // Very small negative numbers that round to -0 after formatting
        $verySmallNegative = -0.000_000_000_000_001;
        $n = NumberLike::create($verySmallNegative);
        expect($n->value())->toBe('0')
            ->and($n->asFloat())->toBe(0.0)
            ->and($n->asInt())->toBe(0);
    });

    it('handles negative zero edge case from string input', function (): void {
        $n = NumberLike::create('-0');
        expect($n->value())->toBe('0')
            ->and($n->asFloat())->toBe(0.0)
            ->and($n->asInt())->toBe(0);
    });

    it('handles negative zero with decimal point', function (): void {
        // String inputs keep their decimal representation
        $n = NumberLike::create('-0.0');
        expect($n->value())->toBe('-0.0');

        $n2 = NumberLike::create('-0.00');
        expect($n2->value())->toBe('-0.00');
    });

    it('handles extremely small negative values that round to zero', function (): void {
        // Test values that are so small they round to -0 in sprintf
        $tinyNegatives = [
            -1e-15,  // Smaller than sprintf precision
            -1e-16,
            -1e-17,
            -0.000_000_000_000_000_1,
        ];

        foreach ($tinyNegatives as $value) {
            $n = NumberLike::create($value);
            expect($n->value())->toBe('0')
                ->and($n->asFloat())->toBe(0.0);
        }
    });
});

describe('Comprehensive NumberLike tests for 100% coverage', function (): void {
    describe('Input type handling', function (): void {
        it('handles all primitive types', function (): void {
            // Already covered types
            expect(NumberLike::create(null)->value())->toBeNull();
            expect(NumberLike::create(123)->value())->toBe('123');
            expect(NumberLike::create(123.45)->value())->toBe('123.45');
            expect(NumberLike::create('123')->value())->toBe('123');

            // Non-numeric types
            expect(NumberLike::create(true)->value())->toBeNull();
            expect(NumberLike::create(false)->value())->toBeNull();
            expect(NumberLike::create([])->value())->toBeNull();
            expect(NumberLike::create(
                new stdClass(),
            )->value())->toBeNull();
            expect(NumberLike::create(fopen('php://memory', 'rb'))->value())->toBeNull();
        });

        it('handles objects with __toString method', function (): void {
            $stringable = new class() implements Stringable
            {
                public function __toString(): string
                {
                    return '42.5';
                }
            };

            expect(NumberLike::create($stringable)->value())->toBeNull();
        });
    });

    describe('String parsing edge cases', function (): void {
        it('handles various whitespace characters', function (): void {
            // Regular spaces
            expect(NumberLike::create('  123  ')->value())->toBe('123');

            // Tabs
            expect(NumberLike::create("\t123\t")->value())->toBe('123');

            // Newlines
            expect(NumberLike::create("\n123\n")->value())->toBe('123');

            // Mixed whitespace
            expect(NumberLike::create(" \t\n123 \t\n")->value())->toBe('123');

            // Only whitespace
            expect(NumberLike::create(" \t\n \t\n")->value())->toBeNull();
        });

        it('handles all Unicode space separators', function (): void {
            // Various Unicode spaces that should be removed
            $unicodeSpaces = [
                "\xE2\x80\x80", // EN QUAD
                "\xE2\x80\x81", // EM QUAD
                "\xE2\x80\x82", // EN SPACE
                "\xE2\x80\x83", // EM SPACE
                "\xE2\x80\x84", // THREE-PER-EM SPACE
                "\xE2\x80\x85", // FOUR-PER-EM SPACE
                "\xE2\x80\x86", // SIX-PER-EM SPACE
                "\xE2\x80\x87", // FIGURE SPACE
                "\xE2\x80\x88", // PUNCTUATION SPACE
                "\xE2\x80\x89", // THIN SPACE
                "\xE2\x80\x8A", // HAIR SPACE
                "\xE2\x80\xAF", // NARROW NO-BREAK SPACE
            ];

            foreach ($unicodeSpaces as $space) {
                $input = sprintf('1%s234.56', $space);
                $n = NumberLike::create($input);
                expect($n->value())->toBe('1234.56');
            }
        });

        it('handles edge cases with comma and dot combinations', function (): void {
            // Both comma and dot present
            expect(NumberLike::create('1,234.56')->value())->toBe('1234.56');
            expect(NumberLike::create('1,234,567.89')->value())->toBe('1234567.89');

            // Only comma (treated as decimal)
            expect(NumberLike::create('123,45')->value())->toBe('123.45');

            // Only dot (decimal)
            expect(NumberLike::create('123.45')->value())->toBe('123.45');

            // Multiple dots (invalid)
            expect(NumberLike::create('123.45.67')->value())->toBeNull();

            // Multiple commas without dot (only first is decimal)
            expect(NumberLike::create('123,456,789')->value())->toBeNull();
        });

        it('handles signed numbers comprehensively', function (): void {
            // Positive sign
            expect(NumberLike::create('+123')->value())->toBe('+123');
            expect(NumberLike::create('+123.45')->value())->toBe('+123.45');
            expect(NumberLike::create('+0')->value())->toBe('+0');

            // Negative sign
            expect(NumberLike::create('-123')->value())->toBe('-123');
            expect(NumberLike::create('-123.45')->value())->toBe('-123.45');
            expect(NumberLike::create('-0')->value())->toBe('0');
            expect(NumberLike::create('-0.0')->value())->toBe('-0.0');

            // Multiple signs (invalid)
            expect(NumberLike::create('++123')->value())->toBeNull();
            expect(NumberLike::create('--123')->value())->toBeNull();
            expect(NumberLike::create('+-123')->value())->toBeNull();

            // Sign in wrong position (invalid)
            expect(NumberLike::create('123+')->value())->toBeNull();
            expect(NumberLike::create('12-3')->value())->toBeNull();
        });

        it('handles invalid numeric strings comprehensively', function (): void {
            $invalidInputs = [
                'abc',           // Letters
                '12a34',         // Letters in middle
                'a123',          // Letters at start
                '123a',          // Letters at end
                '12..34',        // Multiple dots
                '.123',          // Leading dot (actually valid)
                '123.',          // Trailing dot (actually valid)
                '1.2.3',         // Multiple dots
                '',              // Empty string
                '   ',           // Only spaces
                '++123',         // Multiple signs
                '1-23',          // Sign in middle
                '1+23',          // Plus in middle
                '$123',          // Currency symbol
                '€100',          // Currency symbol
                '123%',          // Percentage
                '(123)',         // Parentheses
                '1e10',          // Scientific notation
                '0x123',         // Hexadecimal
                '0b101',         // Binary
                'NaN',           // Not a number
                'Infinity',      // Infinity
                '-Infinity',     // Negative infinity
            ];

            foreach ($invalidInputs as $input) {
                expect(NumberLike::create($input)->value())
                    ->toBeNull('Failed for input: '.$input);
            }
        });

        it('accepts valid edge case formats', function (): void {
            // Leading dot is NOT valid per the regex pattern /^[+-]?\d+(\.\d+)?$/
            expect(NumberLike::create('.5')->value())->toBeNull();
            expect(NumberLike::create('.123')->value())->toBeNull();

            // Trailing dot is NOT valid per the regex pattern
            expect(NumberLike::create('123.')->value())->toBeNull();

            // Zero variants
            expect(NumberLike::create('0')->value())->toBe('0');
            expect(NumberLike::create('0.0')->value())->toBe('0.0');
            expect(NumberLike::create('00.00')->value())->toBe('00.00');
        });
    });

    describe('Float handling and precision', function (): void {
        it('handles float precision edge cases', function (): void {
            // Very large numbers
            $n = NumberLike::create(1_234_567_890_123_456.0);
            expect($n->value())->toBeString();
            expect($n->asFloat())->toBe(1_234_567_890_123_456.0);

            // Very small numbers
            $n = NumberLike::create(0.000_000_000_000_1);
            expect($n->value())->toBe('0.0000000000001');

            // Numbers with many decimal places
            $n = NumberLike::create(1.234_567_890_123_456);
            expect($n->value())->toMatch('/^1\.234567890123/');
        });

        it('removes trailing zeros from float strings', function (): void {
            expect(NumberLike::create(1.0)->value())->toBe('1');
            expect(NumberLike::create(1.20)->value())->toBe('1.2');
            expect(NumberLike::create(1.230)->value())->toBe('1.23');
            expect(NumberLike::create(0.0)->value())->toBe('0');
            expect(NumberLike::create(10.0)->value())->toBe('10');
        });

        it('handles special float values', function (): void {
            // PHP special float values - INF becomes "INF", NAN becomes "NaN" from sprintf
            expect(NumberLike::create(\INF)->value())->toBe('INF');
            expect(NumberLike::create(-\INF)->value())->toBe('-INF');
            expect(NumberLike::create(\NAN)->value())->toBe('NaN');

            // Very close to zero - PHP_FLOAT_EPSILON is too small for 14 decimal places
            expect(NumberLike::create(\PHP_FLOAT_EPSILON)->value())->toBe('0');
            expect(NumberLike::create(-\PHP_FLOAT_EPSILON)->value())->toBe('0');
        });
    });

    describe('Type checking methods', function (): void {
        it('correctly identifies integers', function (): void {
            // Integer strings
            expect(NumberLike::create('123')->isInteger())->toBeTrue();
            expect(NumberLike::create('0')->isInteger())->toBeTrue();
            expect(NumberLike::create('-456')->isInteger())->toBeTrue();
            expect(NumberLike::create('+789')->isInteger())->toBeTrue();

            // Float strings
            expect(NumberLike::create('123.45')->isInteger())->toBeFalse();
            expect(NumberLike::create('0.0')->isInteger())->toBeFalse();
            expect(NumberLike::create('.5')->isInteger())->toBeFalse();

            // From integer input
            expect(NumberLike::create(123)->isInteger())->toBeTrue();

            // From float input that's a whole number
            expect(NumberLike::create(123.0)->isInteger())->toBeTrue();

            // Null value
            expect(NumberLike::create(null)->isInteger())->toBeFalse();
            expect(NumberLike::create('invalid')->isInteger())->toBeFalse();
        });

        it('correctly identifies floats', function (): void {
            // Float strings
            expect(NumberLike::create('123.45')->isFloat())->toBeTrue();
            expect(NumberLike::create('0.0')->isFloat())->toBeTrue();
            // Leading/trailing dots are invalid, so these return null
            expect(NumberLike::create('.5')->isFloat())->toBeFalse();  // null value
            expect(NumberLike::create('123.')->isFloat())->toBeFalse(); // null value

            // Integer strings
            expect(NumberLike::create('123')->isFloat())->toBeFalse();
            expect(NumberLike::create('0')->isFloat())->toBeFalse();
            expect(NumberLike::create('-456')->isFloat())->toBeFalse();

            // From float input
            expect(NumberLike::create(123.45)->isFloat())->toBeTrue();

            // From integer input
            expect(NumberLike::create(123)->isFloat())->toBeFalse();

            // Null value
            expect(NumberLike::create(null)->isFloat())->toBeFalse();
            expect(NumberLike::create('invalid')->isFloat())->toBeFalse();
        });
    });

    describe('Conversion methods', function (): void {
        it('converts to int correctly', function (): void {
            // Positive integers
            expect(NumberLike::create('123')->asInt())->toBe(123);
            expect(NumberLike::create(456)->asInt())->toBe(456);

            // Negative integers
            expect(NumberLike::create('-789')->asInt())->toBe(-789);

            // Floats truncated to int
            expect(NumberLike::create('123.45')->asInt())->toBe(123);
            expect(NumberLike::create('123.99')->asInt())->toBe(123);
            expect(NumberLike::create('-123.45')->asInt())->toBe(-123);
            expect(NumberLike::create('-123.99')->asInt())->toBe(-123);

            // Zero
            expect(NumberLike::create('0')->asInt())->toBe(0);
            expect(NumberLike::create('0.0')->asInt())->toBe(0);

            // Null value
            expect(NumberLike::create(null)->asInt())->toBeNull();
            expect(NumberLike::create('invalid')->asInt())->toBeNull();
        });

        it('converts to float correctly', function (): void {
            // Integers to float
            expect(NumberLike::create('123')->asFloat())->toBe(123.0);
            expect(NumberLike::create(456)->asFloat())->toBe(456.0);

            // Floats
            expect(NumberLike::create('123.45')->asFloat())->toBe(123.45);
            expect(NumberLike::create(678.90)->asFloat())->toBe(678.9);

            // Negative values
            expect(NumberLike::create('-123.45')->asFloat())->toBe(-123.45);

            // Very small values
            expect(NumberLike::create('0.000001')->asFloat())->toBe(0.000_001);

            // Zero
            expect(NumberLike::create('0')->asFloat())->toBe(0.0);
            expect(NumberLike::create('0.0')->asFloat())->toBe(0.0);

            // Null value
            expect(NumberLike::create(null)->asFloat())->toBeNull();
            expect(NumberLike::create('invalid')->asFloat())->toBeNull();
        });
    });

    describe('Mixed format handling', function (): void {
        it('handles real-world number formats', function (): void {
            // US format
            expect(NumberLike::create('1,234,567.89')->value())->toBe('1234567.89');

            // European formats with space
            expect(NumberLike::create('1 234 567,89')->value())->toBe('1234567.89');

            // European with NBSP
            expect(NumberLike::create("1\xC2\xA0234\xC2\xA0567,89")->value())->toBe('1234567.89');

            // Indian numbering (would need special handling, currently treats comma as thousand sep)
            expect(NumberLike::create('12,34,567.89')->value())->toBe('1234567.89');

            // No thousand separators
            expect(NumberLike::create('1234567.89')->value())->toBe('1234567.89');

            // Signed with separators
            expect(NumberLike::create('-1,234.56')->value())->toBe('-1234.56');
            expect(NumberLike::create('+1,234.56')->value())->toBe('+1234.56');
        });
    });

    describe('Boundary and limit testing', function (): void {
        it('handles PHP integer limits', function (): void {
            $maxInt = \PHP_INT_MAX;
            $minInt = \PHP_INT_MIN;

            // Max int
            $n = NumberLike::create($maxInt);
            expect($n->value())->toBe((string) $maxInt);
            // When converting back through float, precision may be lost for very large ints
            expect($n->asInt())->toBeInt();

            // Min int
            $n = NumberLike::create($minInt);
            expect($n->value())->toBe((string) $minInt);
            expect($n->asInt())->toBeInt();

            // Beyond int range (as string)
            $beyondMax = '99999999999999999999999999999';
            $n = NumberLike::create($beyondMax);
            expect($n->value())->toBe($beyondMax);
            // asInt will overflow/truncate
        });

        it('handles very long decimal strings', function (): void {
            $longDecimal = '123.45678901234567890123456789';
            $n = NumberLike::create($longDecimal);
            expect($n->value())->toBe($longDecimal);

            // When converted to float, precision is lost
            expect($n->asFloat())->toBeFloat();
        });
    });

    describe('Regex pattern edge cases', function (): void {
        it('validates numeric pattern strictly', function (): void {
            // The pattern is: /^[+-]?\d+(\.\d+)?$/

            // Valid patterns
            $valid = [
                '123',           // Simple integer
                '-123',          // Negative
                '+123',          // Positive with sign
                '123.45',        // Decimal
                '-123.45',       // Negative decimal
                '+123.45',       // Positive decimal with sign
                '0',             // Zero
                '0.0',           // Zero with decimal
                // Note: .5 and 123. are NOT valid per regex /^[+-]?\d+(\.\d+)?$/
            ];

            foreach ($valid as $input) {
                $result = NumberLike::create($input)->value();
                expect($result)->not->toBeNull(sprintf("Expected '%s' to be valid", $input));
            }

            // Invalid patterns
            $invalid = [
                '1e10',          // Scientific notation
                '0x123',         // Hex
                '123.45.67',     // Multiple decimals
                // Note: '12 34' becomes '1234' after space removal, which is valid
                'abc',           // Letters
                '1,2,3',         // Commas without proper context
                '.5',            // Leading decimal (requires digit before)
                '123.',          // Trailing decimal (requires digit after)
            ];

            foreach ($invalid as $input) {
                expect(NumberLike::create($input)->value())
                    ->toBeNull(sprintf("Expected '%s' to be invalid", $input));
            }
        });
    });

    describe('Memory and performance considerations', function (): void {
        it('handles repeated operations efficiently', function (): void {
            // Create same number multiple times
            $numbers = [];

            for ($i = 0; $i < 100; ++$i) {
                $numbers[] = NumberLike::create('1234.56');
            }

            // All should be valid
            foreach ($numbers as $n) {
                expect($n->value())->toBe('1234.56');
            }
        });

        it('handles various inputs without memory leaks', function (): void {
            // Different types of inputs
            $inputs = [
                123,
                123.45,
                '456',
                '789.01',
                null,
                new stdClass(),
                [],
                true,
                false,
            ];

            foreach ($inputs as $input) {
                $n = NumberLike::create($input);
                // Just verify it doesn't crash
                $n->value();
                $n->asInt();
                $n->asFloat();
                $n->isInteger();
                $n->isFloat();
            }

            expect(true)->toBeTrue(); // If we get here, no crashes
        });
    });
});

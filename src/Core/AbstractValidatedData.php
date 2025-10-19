<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Data\Core;

use Override;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\Creation\CreationContextFactory;
use Spatie\LaravelData\Support\Creation\ValidationStrategy;

/**
 * Base data class with enforced validation on every instantiation.
 *
 * This abstract class extends AbstractData to enforce validation rules on every object
 * creation, regardless of how the object is instantiated. By configuring the creation
 * context factory with ValidationStrategy::Always, this class ensures data integrity
 * by validating all properties against their defined validation rules.
 *
 * Use this class when you need:
 * - Guaranteed data validation on every object creation
 * - Type-safe data transfer objects with enforced business rules
 * - API resource transformation with mandatory validation
 * - Protection against invalid data propagation through the application
 *
 * For data objects without enforced validation, use AbstractData instead. The parent
 * class provides the same serialization and transformation capabilities without the
 * validation overhead.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @see AbstractData For data objects without enforced validation
 * @see AbstractValidatedDataTransferObject For validated DTOs without serialization features
 */
abstract class AbstractValidatedData extends AbstractData
{
    /**
     * Configure the creation context factory to enforce validation on every instantiation.
     *
     * Overrides the parent factory configuration to set ValidationStrategy::Always,
     * ensuring that all validation rules defined on the data object properties are
     * executed during object creation. This prevents invalid data from being instantiated
     * and provides immediate feedback when data does not meet defined constraints.
     *
     * @param  null|CreationContext   $creationContext Optional creation context for customizing
     *                                                 the object instantiation process. Allows
     *                                                 overriding default behavior for specific
     *                                                 creation scenarios while maintaining the
     *                                                 enforced validation strategy.
     * @return CreationContextFactory The configured factory instance with enforced validation strategy
     */
    #[Override()]
    public static function factory(?CreationContext $creationContext = null): CreationContextFactory
    {
        return parent::factory($creationContext)
            ->validationStrategy(ValidationStrategy::Always);
    }
}

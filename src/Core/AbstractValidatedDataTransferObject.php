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
 * Lightweight data transfer object with enforced validation on every instantiation.
 *
 * This abstract class extends AbstractDataTransferObject to enforce validation rules
 * on every object creation, combining the simplicity of DTOs with guaranteed data
 * integrity. By configuring the creation context factory with ValidationStrategy::Always,
 * this class ensures all properties are validated against their defined rules without
 * the overhead of serialization or API transformation features.
 *
 * Use this class when you need:
 * - Simple, validated data containers for internal application logic
 * - Type-safe data transfer with enforced business rules but without serialization
 * - Protection against invalid data in internal operations
 * - Guaranteed validation without API resource transformation overhead
 *
 * For validated data objects with serialization, use AbstractValidatedData instead.
 * For DTOs without enforced validation, use AbstractDataTransferObject.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @see AbstractValidatedData For validated data objects with serialization features
 * @see AbstractDataTransferObject For DTOs without enforced validation
 */
abstract class AbstractValidatedDataTransferObject extends AbstractDataTransferObject
{
    /**
     * Configure the creation context factory to enforce validation on every instantiation.
     *
     * Overrides the parent factory configuration to set ValidationStrategy::Always,
     * ensuring that all validation rules defined on the DTO properties are executed
     * during object creation. This prevents invalid data from being instantiated and
     * provides immediate feedback when data does not meet defined constraints.
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

<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Data\Core;

use Cline\Data\Pipes\CastPrimitivePropertiesDataPipe;
use Cline\Data\Pipes\ReplaceEmptyStringsWithNullDataPipe;
use Override;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataPipeline;

/**
 * Base data class that extends Spatie Laravel Data with custom data transformation pipelines.
 *
 * This abstract class provides a foundation for creating immutable data transfer objects
 * with automatic type casting and data normalization. It configures a default data pipeline
 * that handles primitive type casting and null normalization during object instantiation.
 *
 * Use this class when you need:
 * - Automatic serialization and deserialization capabilities
 * - API resource transformation with validation support
 * - Type-safe data transfer objects without enforced validation
 *
 * For data objects that require validation on every instantiation, use AbstractValidatedData instead.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @see AbstractValidatedData For data objects with enforced validation
 * @see AbstractDataTransferObject For simple DTOs without Laravel Data features
 */
abstract class AbstractData extends Data
{
    // use MarkupSerialization;

    /**
     * Configure the data transformation pipeline for object creation.
     *
     * Extends the parent pipeline with two custom data pipes that process incoming
     * data before hydration. These pipes ensure consistent data normalization across
     * all concrete implementations by casting primitive types and normalizing empty
     * strings to null values.
     *
     * The pipeline executes in the following order:
     * 1. CastPrimitivePropertiesDataPipe - Casts string values to their target primitive types
     * 2. ReplaceEmptyStringsWithNullDataPipe - Converts empty strings to null for nullable properties
     *
     * @return DataPipeline The configured data pipeline instance with custom transformation pipes
     */
    #[Override()]
    public static function pipeline(): DataPipeline
    {
        return parent::pipeline()
            ->firstThrough(CastPrimitivePropertiesDataPipe::class)
            ->firstThrough(ReplaceEmptyStringsWithNullDataPipe::class);
    }
}

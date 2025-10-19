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
use Spatie\LaravelData\DataPipeline;
use Spatie\LaravelData\Dto;

/**
 * Lightweight data transfer object without serialization or API transformation features.
 *
 * This abstract class extends Spatie's Dto class to provide simple, immutable data containers
 * with custom data transformation pipelines. Unlike AbstractData, this class does not include
 * serialization, API resource transformation, or collection handling capabilities, making it
 * ideal for internal data transfer where these features are unnecessary.
 *
 * Use this class when you need:
 * - Simple, immutable data containers for internal application logic
 * - Type-safe data transfer without serialization overhead
 * - Consistent data normalization without API resource features
 *
 * For data objects with serialization and API transformation, use AbstractData instead.
 * For DTOs that require validation on every instantiation, use AbstractValidatedDataTransferObject.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @see AbstractData For data objects with serialization and API transformation
 * @see AbstractValidatedDataTransferObject For DTOs with enforced validation
 */
abstract class AbstractDataTransferObject extends Dto
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

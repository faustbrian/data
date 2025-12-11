<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures;

use Cline\Data\Core\AbstractValidatedDataTransferObject;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;

/**
 * @author Brian Faust <brian@cline.sh>
 * @internal
 */
final class ConcreteValidatedDataTransferObject extends AbstractValidatedDataTransferObject
{
    public function __construct(
        #[Required()]
        public readonly string $productName,
        #[Required(), Min(0)]
        public readonly float $price,
        #[Required(), Min(1)]
        public readonly int $quantity,
        public readonly ?string $sku = null,
    ) {}
}

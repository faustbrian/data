<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures;

use Cline\Data\Core\AbstractDataTransferObject;

/**
 * @author Brian Faust <brian@cline.sh>
 * @internal
 */
final class ConcreteDataTransferObject extends AbstractDataTransferObject
{
    public function __construct(
        public readonly string $title,
        public readonly int $count,
        public readonly ?string $description = null,
        public readonly bool $enabled = false,
        public readonly ?float $price = null,
        public readonly ?array $metadata = null,
    ) {}
}

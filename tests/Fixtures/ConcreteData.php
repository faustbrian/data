<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures;

use Cline\Data\Core\AbstractData;

/**
 * @author Brian Faust <brian@cline.sh>
 * @internal
 */
final class ConcreteData extends AbstractData
{
    public function __construct(
        public readonly string $name,
        public readonly int $age,
        public readonly ?string $email = null,
        public readonly bool $active = false,
        public readonly ?float $score = null,
        public readonly ?array $tags = null,
    ) {}
}

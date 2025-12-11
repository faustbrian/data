<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures;

use Cline\Data\Core\AbstractValidatedData;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;

/**
 * @author Brian Faust <brian@cline.sh>
 * @internal
 */
final class ConcreteValidatedData extends AbstractValidatedData
{
    public function __construct(
        #[Required()]
        public readonly string $username,
        #[Required(), Email()]
        public readonly string $email,
        #[Required(), Max(120), Min(1)]
        public readonly int $age,
        public readonly ?string $bio = null,
    ) {}
}

<?php

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevere.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevere\Http\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Status
{
    /**
     * @var array<int>
     */
    public readonly array $other;

    public function __construct(
        public readonly int $primary = 200,
        int ...$other
    ) {
        $other = array_unique($other);
        $search = array_search($primary, $other, true);
        if ($search !== false) {
            unset($other[$search]);
        }
        $this->other = $other;
    }
}

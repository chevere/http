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

namespace Chevere\Http;

use Chevere\DataStructure\Interfaces\VectoredInterface;
use Chevere\DataStructure\Interfaces\VectorInterface;
use Chevere\DataStructure\Traits\VectorTrait;
use Chevere\DataStructure\Vector;

/**
 * @implements VectoredInterface<Header>
 */
final class Headers implements VectoredInterface
{
    use VectorTrait;

    public function __construct(Header ...$header)
    {
        $this->vector = new Vector(...$header);
    }

    /**
     * @return array<string>
     */
    public function toArray(): array
    {
        $return = [];
        foreach ($this->getIterator() as $header) {
            $return[] = $header->line;
        }

        return $return;
    }

    /**
     * @return VectorInterface<Headers>
     */
    public function vector(): VectorInterface
    {
        return $this->vector;
    }
}

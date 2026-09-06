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

use Chevere\DataStructure\Traits\VectorTrait;
use Chevere\DataStructure\Vector;
use Chevere\Http\Interfaces\HeadersInterface;

final class Headers implements HeadersInterface
{
    /**
     * @template-use VectorTrait<Header>
     */
    use VectorTrait;

    public function __construct(Header ...$header)
    {
        $this->vector = new Vector(...$header);
    }

    public function toLines(): array
    {
        $return = [];
        foreach ($this as $header) {
            $return[] = $header->line;
        }

        return $return;
    }

    public function toArray(): array
    {
        $return = [];
        foreach ($this as $header) {
            $return[$header->name] = $header->value;
        }

        return $return;
    }
}

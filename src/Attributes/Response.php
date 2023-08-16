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
use Chevere\Http\Header;
use Chevere\Http\Headers;
use Chevere\Http\Status;
use Iterator;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<string, Status|Header>
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class Response implements IteratorAggregate
{
    public readonly Headers $headers;

    public function __construct(
        public readonly Status $status = new Status(200),
        Header ...$header,
    ) {
        $this->headers = new Headers(...$header);
    }

    /**
     * @return Iterator<string, Status|Header>
     */
    public function getIterator(): Iterator
    {
        yield 'status' => $this->status;
        foreach ($this->headers as $header) {
            yield $header->name => $header;
        }
    }

    /**
     * @return array<string, Status|Header>
     */
    public function toArray(): array
    {
        return iterator_to_array($this->getIterator());
    }
}

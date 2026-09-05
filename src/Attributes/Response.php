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
use Chevere\Http\Interfaces\ResponseInterface;
use Chevere\Http\Interfaces\StatusInterface;
use Chevere\Http\Status;
use Iterator;

#[Attribute(Attribute::TARGET_CLASS)]
final class Response implements ResponseInterface
{
    public readonly Headers $headers;

    public function __construct(
        public readonly StatusInterface $status = new Status(200),
        Header ...$header,
    ) {
        $this->headers = new Headers(...$header);
    }

    /**
     * @return Iterator<string, StatusInterface|Header>
     */
    public function getIterator(): Iterator
    {
        yield 'status' => $this->status;
        foreach ($this->headers as $header) {
            yield $header->name => $header;
        }
    }

    public function count(): int
    {
        return count($this->status) + count($this->headers);
    }
}

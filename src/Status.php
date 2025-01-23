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

use Iterator;
use IteratorAggregate;
use RuntimeException;
use function Chevere\Message\message;

/**
 * @implements IteratorAggregate<int>
 */
class Status implements IteratorAggregate
{
    /**
     * Maps name => code
     * @var array<string|int, int>
     */
    public readonly array $other;

    /**
     * @param int $primary The primary status code
     * @param int ...$other Additional status codes `name: value,...`
     */
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

    /**
     * Provides read access to the `$other` status codes.
     */
    public function __get(string $name): int
    {
        return $this->other[$name]
            ?? throw new RuntimeException(
                (string) message(
                    'Property `{{ name }}` is not defined',
                    name: $name
                )
            );
    }

    /**
     * @return Iterator<int>
     */
    public function getIterator(): Iterator
    {
        yield $this->primary;
        foreach ($this->other as $status) {
            yield $status;
        }
    }

    /**
     * @return array<int>
     */
    public function toArray(): array
    {
        return iterator_to_array($this->getIterator());
    }
}

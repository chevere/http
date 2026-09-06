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

use Chevere\Http\Interfaces\StatusInterface;
use Iterator;
use OutOfBoundsException;
use OverflowException;

class Status implements StatusInterface
{
    /**
     * Maps additional codes
     *
     * @var array<string|int, int>
     */
    private array $codes;

    /**
     * @param int $code The main status code
     * @param int ...$additional Additional status codes
     */
    public function __construct(
        private int $code = 200,
        int ...$additional
    ) {
        $this->codes[0] = $code;
        $index = 1;
        foreach ($additional as $name => $value) {
            if (array_search($value, $this->codes, true) !== false) {
                throw new OverflowException(
                    sprintf('Status `%s` is already defined', $value)
                );
            }
            if (is_int($name)) {
                $name = $index;
            }
            $this->codes[$name] = $value;
            $index++;
        }
    }

    public function code(string|int $name): int
    {
        return array_key_exists($name, $this->codes)
            ? $this->codes[$name]
            : throw new OutOfBoundsException(
                sprintf('Status `%s` is not defined', $name)
            );
    }

    public function codes(): array
    {
        return $this->codes;
    }

    /**
     * @return Iterator<int>
     */
    public function getIterator(): Iterator
    {
        foreach ($this->codes as $status) {
            yield $status;
        }
    }

    public function count(): int
    {
        return count($this->codes);
    }
}

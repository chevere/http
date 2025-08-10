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
use Chevere\Parameter\Cast;
use Chevere\Parameter\Interfaces\CastInterface;
use Iterator;
use RuntimeException;
use function Chevere\Message\message;

class Status implements StatusInterface
{
    /**
     * Maps name => code
     * @var array<string|int, int|string>
     */
    public readonly array $codes;

    /**
     * @param int|string $success The success status code, e.g. `200` or `2xx`
     * @param int|string ...$code Additional status codes
     */
    public function __construct(
        public readonly int|string $success = 200,
        int|string ...$code
    ) {
        $code = array_unique($code);
        $search = array_search($success, $code, true);
        if ($search !== false) {
            unset($code[$search]);
        }
        $this->codes = $code;
    }

    public function success(): CastInterface
    {
        return new Cast($this->success);
    }

    public function code(string $name): CastInterface
    {
        $return = array_key_exists($name, $this->codes)
            ? $this->codes[$name]
            : throw new RuntimeException(
                (string) message(
                    'Status `{{ name }}` is not defined',
                    name: $name
                )
            );

        return new Cast($return);
    }

    public function codes(): array
    {
        return $this->codes;
    }

    /**
     * @return Iterator<int|string>
     */
    public function getIterator(): Iterator
    {
        yield $this->success;
        foreach ($this->codes as $status) {
            yield $status;
        }
    }

    public function toArray(): array
    {
        return iterator_to_array($this->getIterator());
    }
}

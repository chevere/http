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

use Chevere\Http\Interfaces\HeaderInterface;
use InvalidArgumentException;

class Header implements HeaderInterface
{
    private string $line;

    /**
     * @throws InvalidArgumentException if the header name or value is invalid according to RFC 7230
     */
    public function __construct(
        private string $name,
        private string $value
    ) {
        $this->assertHeader($name, $value);
        $this->line = $this->name . ': ' . $this->value;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->line;
    }

    /**
     * @see https://tools.ietf.org/html/rfc7230#section-3.2.4
     */
    private function assertHeader(string $name, string $value): void
    {
        if (preg_match("@^[!#$%&'*+.^_`|~0-9A-Za-z-]+$@D", $name) !== 1) {
            throw new InvalidArgumentException(
                sprintf('Header name `%s` is not valid according to RFC 7230', $name)
            );
        }
        if ($value !== '' && preg_match("@^[\x21-\x7E\x80-\xFF](?:[\t ]*[\x21-\x7E\x80-\xFF])*$@D", $value) !== 1) {
            throw new InvalidArgumentException(
                sprintf('Header value `%s` is not valid according to RFC 7230', $value)
            );
        }
    }
}

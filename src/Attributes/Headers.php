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
use function Chevere\Message\message;
use Chevere\Throwable\Errors\TypeError;

#[Attribute(Attribute::TARGET_CLASS)]
class Headers
{
    /**
     * @param array<string, string> $array
     */
    public function __construct(
        public readonly array $array = []
    ) {
        $position = 0;
        foreach ($array as $key => $value) {
            $index = (string) $position;
            if (! is_string($key)) {
                throw new TypeError(
                    message('Header key must be a string at index %index%')
                        ->withCode('%index%', $index)
                );
            }
            if (! is_string($value)) {
                throw new TypeError(
                    message('Header value must be a string for %key% at index %index%')
                        ->withCode('%key%', $key)
                        ->withCode('%index%', $index)
                );
            }
            $position++;
        }
    }
}

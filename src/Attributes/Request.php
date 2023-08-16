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

#[Attribute(Attribute::TARGET_CLASS)]
final class Request
{
    public readonly Headers $headers;

    public function __construct(
        Header ...$attribute,
    ) {
        $this->headers = new Headers(...$attribute);
    }
}

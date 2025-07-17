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

namespace Chevere\Tests\src;

use Chevere\Http\Attributes\Request;
use Chevere\Http\Controller;
use Chevere\Http\Header;
use Chevere\Parameter\Interfaces\IntParameterInterface;
use function Chevere\Parameter\int;

#[Request(
    new Header('Accept', 'application/json'),
)]
class JsonBodyController extends Controller
{
    public static function acceptBody(): IntParameterInterface
    {
        return int(min: 10, max: 100);
    }

    protected function main(): array
    {
        return [
            $this->bodyParsed()->toArray(),
            $this->body()->int(),
        ];
    }
}

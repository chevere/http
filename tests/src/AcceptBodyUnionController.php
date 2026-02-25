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
use Chevere\Parameter\Interfaces\UnionParameterInterface;
use function Chevere\Parameter\string;
use function Chevere\Parameter\unionNull;

#[Request(
    new Header('Content-Type', 'application/json')
)]
final class AcceptBodyUnionController extends Controller
{
    public function __invoke(): void
    {
    }

    public static function acceptBody(): UnionParameterInterface
    {
        return unionNull(string());
    }
}

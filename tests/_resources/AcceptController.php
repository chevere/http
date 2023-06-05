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

namespace Chevere\Tests\_resources;

use Chevere\Http\Attributes\Headers;
use Chevere\Http\Attributes\Statuses;
use Chevere\Http\Controller;
use function Chevere\Parameter\arrayp;
use function Chevere\Parameter\arrayString;
use function Chevere\Parameter\file;
use Chevere\Parameter\Interfaces\ArrayStringParameterInterface;
use Chevere\Parameter\Interfaces\ArrayTypeParameterInterface;
use function Chevere\Parameter\string;

#[Headers([
    'test' => 'header',
])]
#[Statuses(200, 404)]
final class AcceptController extends Controller
{
    public static function acceptQuery(): ArrayStringParameterInterface
    {
        return arrayString(
            foo: string('/^[a-z]+$/')
        );
    }

    public static function acceptBody(): ArrayTypeParameterInterface
    {
        return arrayp(
            bar: string('/^[1-9]+$/')
        );
    }

    public static function acceptFiles(): ArrayTypeParameterInterface
    {
        return arrayp(
            MyFile: file(
                type: string('/^text\/plain$/')
            )
        );
    }

    public function run(): array
    {
        return [];
    }
}

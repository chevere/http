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

use Chevere\Http\Attributes\Response;
use Chevere\Http\Controller;
use Chevere\Http\Header;
use Chevere\Http\Status;
use Chevere\Parameter\Interfaces\ArrayParameterInterface;
use Chevere\Parameter\Interfaces\ArrayStringParameterInterface;
use function Chevere\Parameter\arrayp;
use function Chevere\Parameter\arrayString;
use function Chevere\Parameter\file;
use function Chevere\Parameter\string;

#[Response(
    new Status(200, 400),
    new Header('Content-Disposition', 'attachment'),
    new Header('Content-Type', 'application/json')
)]
final class AcceptOptionalController extends Controller
{
    public static function acceptQuery(): ArrayStringParameterInterface
    {
        return arrayString()->withOptional(
            foo: string('/^[a-z]+$/')
        );
    }

    public static function acceptBody(): ArrayParameterInterface
    {
        return arrayp()->withOptional(
            bar: string('/^[1-9]+$/')
        );
    }

    public static function acceptFiles(): ArrayParameterInterface
    {
        return arrayp()->withOptional(
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

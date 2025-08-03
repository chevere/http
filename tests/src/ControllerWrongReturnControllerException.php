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

use Chevere\Http\Controller;
use Chevere\Http\Exceptions\ControllerException;
use Chevere\Parameter\Interfaces\IntParameterInterface;
use function Chevere\Parameter\int;

class ControllerWrongReturnControllerException extends Controller
{
    public function __construct()
    {
        throw new ControllerException(
            return: false,
        );
    }

    public static function return(): IntParameterInterface
    {
        return int(min: 1);
    }
}

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

use Chevere\Action\Traits\ActionNameTrait;
use Chevere\Http\Interfaces\ControllerInterface;
use Chevere\Http\Interfaces\ControllerNameInterface;

final class ControllerName implements ControllerNameInterface
{
    use ActionNameTrait;

    public static function symbol(): string
    {
        return 'HTTP Controller';
    }

    public static function interface(): string
    {
        return ControllerInterface::class;
    }
}

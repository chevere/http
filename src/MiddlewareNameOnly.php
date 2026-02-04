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
use Chevere\Http\Interfaces\MiddlewareNameInterface;
use Psr\Http\Server\MiddlewareInterface;

final class MiddlewareNameOnly implements MiddlewareNameInterface
{
    use ActionNameTrait;

    public function __construct(
        /**
         * @phpstan-ignore-next-line
         */
        private string $name,
    ) {
        $this->onConstruct();
        $this->arguments = [];
    }

    public static function interface(): string
    {
        return MiddlewareInterface::class;
    }
}

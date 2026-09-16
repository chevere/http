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

class ControllerThrowsControllerExceptionWithErrors extends Controller
{
    public function __construct(string ...$errors)
    {
        foreach ($errors as $pointer => $detail) {
            $this->addError((string) $pointer, $detail);
        }

        throw new ControllerException(
            errors: $this->errors(),
        );
    }

    public function __invoke(): void
    {
    }
}

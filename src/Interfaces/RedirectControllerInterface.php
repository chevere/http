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

namespace Chevere\Http\Interfaces;

use Psr\Http\Message\UriInterface;

/**
 * Describes the component in charge of defining a HTTP Redirect Controller.
 */
interface RedirectControllerInterface extends ControllerInterface
{
    public const STATUSES = [
        300,
        301,
        302,
        303,
        304,
        307,
        308,
    ];

    public function withUri(UriInterface $uri): static;

    public function withStatus(int $status): static;

    public function uri(): UriInterface;

    /**
     * @return int<300, 399>
     */
    public function status(): int;
}

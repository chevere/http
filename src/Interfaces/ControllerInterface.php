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

use Chevere\Controller\Interfaces\ControllerInterface as BaseControllerInterface;
use Chevere\Parameter\Interfaces\ArrayStringParameterInterface;
use Chevere\Parameter\Interfaces\ArrayTypeParameterInterface;

/**
 * Describes the component in charge of defining an Http Controller which adds methods for handling HTTP requests.
 */
interface ControllerInterface extends BaseControllerInterface
{
    /**
     * Defines the query accepted.
     */
    public static function acceptQuery(): ArrayStringParameterInterface;

    /**
     * Defines the body accepted.
     */
    public static function acceptBody(): ArrayTypeParameterInterface;

    /**
     * Defines the FILES accepted.
     */
    public static function acceptFiles(): ArrayTypeParameterInterface;

    /**
     * @param array<int|string, string> $query
     */
    public function withQuery(array $query): static;

    /**
     * @param array<int|string, mixed> $body
     */
    public function withBody(array $body): static;

    /**
     * @param array<int|string, array<string, int|string>> $files
     */
    public function withFiles(array $files): static;

    /**
     * @return array<int|string, string>
     */
    public function query(): array;

    /**
     * @return array<int|string, mixed>
     */
    public function body(): array;

    /**
     * @return array<int|string, array<string, int|string>>
     */
    public function files(): array;
}

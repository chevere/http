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

use Chevere\Controller\Controller as BaseController;
use Chevere\Http\Interfaces\ControllerInterface;
use Chevere\Parameter\Arguments;
use function Chevere\Parameter\arguments;
use function Chevere\Parameter\arrayp;
use function Chevere\Parameter\arrayString;
use Chevere\Parameter\Interfaces\ArgumentsInterface;
use Chevere\Parameter\Interfaces\ArrayStringParameterInterface;
use Chevere\Parameter\Interfaces\ArrayTypeParameterInterface;
use Chevere\Parameter\Interfaces\FileParameterInterface;

abstract class Controller extends BaseController implements ControllerInterface
{
    protected ?ArgumentsInterface $query = null;

    protected ?ArgumentsInterface $body = null;

    /**
     * @var array<ArgumentsInterface>
     */
    protected array $files = [];

    public static function acceptQuery(): ArrayStringParameterInterface
    {
        return arrayString();
    }

    public static function acceptBody(): ArrayTypeParameterInterface
    {
        return arrayp();
    }

    public static function acceptFiles(): ArrayTypeParameterInterface
    {
        return arrayp();
    }

    final public function withQuery(array $query): static
    {
        $new = clone $this;
        $new->query = arguments($new::acceptQuery()->parameters(), $query);

        return $new;
    }

    final public function withBody(array $body): static
    {
        $new = clone $this;
        $new->body = arguments($new::acceptBody()->parameters(), $body);

        return $new;
    }

    final public function withFiles(array $files): static
    {
        $new = clone $this;
        $array = [];
        /** @var FileParameterInterface $parameter */
        foreach ($new->acceptFiles()->parameters() as $key => $parameter) {
            $arguments = new Arguments(
                $parameter->parameters(),
                $files[$key]
            );
            $array[$key] = $arguments;
        }
        $new->files = $array;

        return $new;
    }

    final public function query(): ?ArgumentsInterface
    {
        return $this->query;
    }

    final public function body(): ?ArgumentsInterface
    {
        return $this->body;
    }

    final public function files(): array
    {
        return $this->files;
    }
}

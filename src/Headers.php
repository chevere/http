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

use Chevere\Common\Interfaces\ToArrayInterface;
use Chevere\DataStructure\Interfaces\MappedInterface;
use Chevere\DataStructure\Map;
use Chevere\DataStructure\Traits\MapTrait;

/**
 * @implements MappedInterface<Header>
 */
final class Headers implements MappedInterface, ToArrayInterface
{
    /**
     * @template-use MapTrait<Header>
     */
    use MapTrait;

    /**
     * @var Map<string>
     */
    private Map $export;

    public function __construct(Header ...$header)
    {
        $this->map = new Map();
        $this->export = new Map();
        foreach ($header as $header) {
            $this->map = $this->map->withPut($header->name, $header);
            $this->export = $this->export->withPut($header->name, $header->value);
        }
    }

    /**
     * @return array<string, Header>
     */
    public function toArray(): array
    {
        return $this->map->toArray();
    }

    /**
     * @return array<string, string>
     */
    public function toExport(): array
    {
        return $this->export->toArray();
    }
}

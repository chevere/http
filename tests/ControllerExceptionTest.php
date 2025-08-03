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

namespace Chevere\Tests;

use Chevere\Action\Exceptions\ActionException;
use Chevere\Http\Exceptions\ControllerException;
use Chevere\Tests\src\ControllerThrowsControllerException;
use Chevere\Tests\src\ControllerWrongReturnControllerException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use TypeError;

final class ControllerExceptionTest extends TestCase
{
    public function testConstructFromElsewhere(): void
    {
        try {
            $line = __LINE__ + 2;

            throw new ControllerException();
        } catch (ActionException $e) {
            $this->assertSame(
                'Chevere\Http\Exceptions\ControllerException must be thrown from a class implementing Chevere\Http\Interfaces\ControllerInterface',
                $e->getMessage()
            );
            $this->assertSame(InvalidArgumentException::class, $e->getPrevious()::class);
            $this->assertSame(__FILE__, $e->getFile());
            $this->assertSame($line, $e->getLine());
        }
    }

    public function testConstruct(): void
    {
        $this->expectException(ControllerException::class);
        new ControllerThrowsControllerException();
    }

    public function testWrongReturn(): void
    {
        try {
            new ControllerWrongReturnControllerException();
        } catch (ActionException $e) {
            $this->assertSame(
                'Argument `$return` value is not compatible with return type defined in Chevere\Tests\src\ControllerWrongReturnControllerException::return() method',
                $e->getMessage()
            );
            $this->assertSame(TypeError::class, $e->getPrevious()::class);
            $this->assertSame(
                __DIR__ . '/src/ControllerWrongReturnControllerException.php',
                $e->getFile()
            );
            $this->assertSame(25, $e->getLine());
        }
    }
}

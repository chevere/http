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
use Chevere\Tests\src\ControllerThrowsControllerExceptionDefault;
use Chevere\Tests\src\ControllerWrongReturnControllerException;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Throwable;
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

    public function testThrowsDefaults(): void
    {
        try {
            new ControllerThrowsControllerExceptionDefault();
        } catch (Throwable $e) {
            $this->assertInstanceOf(ControllerException::class, $e);
            $this->assertSame('', $e->getMessage());
            $this->assertSame(0, $e->getCode());
            $this->assertNull($e->getPrevious());
            $this->assertNull($e->return);
        }
    }

    public function testThrows(): void
    {
        try {
            new ControllerThrowsControllerException();
        } catch (Throwable $e) {
            $this->assertInstanceOf(ControllerException::class, $e);
            $this->assertSame('test', $e->getMessage());
            $this->assertSame(123, $e->getCode());
            $this->assertInstanceOf(Exception::class, $e->getPrevious());
            $this->assertSame('previous', $e->getPrevious()->getMessage());
            $this->assertSame(1.5, $e->return);
        }
    }

    public function testWrongReturn(): void
    {
        try {
            new ControllerWrongReturnControllerException();
        } catch (Throwable $e) {
            $this->assertInstanceOf(ActionException::class, $e);
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

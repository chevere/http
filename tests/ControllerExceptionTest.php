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
use Chevere\Tests\src\AcceptBodyController;
use Chevere\Tests\src\ControllerThrowsControllerException;
use Chevere\Tests\src\ControllerThrowsControllerExceptionAcceptReturn;
use Chevere\Tests\src\ControllerThrowsControllerExceptionDefault;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Throwable;

final class ControllerExceptionTest extends TestCase
{
    public function testConstructFromElsewhere(): void
    {
        try {
            $line = __LINE__ + 2;

            throw new ControllerException();
        } catch (ActionException $e) {
            $class = 'Chevere\Http\Exceptions\ControllerException';
            $interface = 'Chevere\Http\Interfaces\ControllerInterface';
            $this->assertSame(
                <<<PLAIN
                Exception `{$class}` must be thrown from a class implementing `{$interface}`
                PLAIN
                ,
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
            $this->assertSame(500, $e->getCode());
            $this->assertNull($e->getPrevious());
        }
    }

    public function testThrows(): void
    {
        try {
            new ControllerThrowsControllerException();
        } catch (Throwable $e) {
            $this->assertInstanceOf(ControllerException::class, $e);
            $this->assertSame('test', $e->getMessage());
            $this->assertSame(400, $e->getCode());
            $this->assertInstanceOf(Exception::class, $e->getPrevious());
            $this->assertSame('previous', $e->getPrevious()->getMessage());
        }
    }

    public function testWrongReturn(): void
    {
        try {
            new ControllerThrowsControllerExceptionAcceptReturn();
        } catch (ControllerException $e) {
            $this->assertInstanceOf(ControllerException::class, $e);
            $this->assertSame(0, $e->return());
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage(
                <<<PLAIN
                Argument value provided `0` is less than `1`
                PLAIN
            );
            $e->assertReturn();
        }
    }

    public function testInvalidStatusCode(): void
    {
        $this->expectException(ActionException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Status code `900` is not a valid HTTP status code
            PLAIN
        );
        new ControllerException('test', 900, null, null, AcceptBodyController::class);
    }
}

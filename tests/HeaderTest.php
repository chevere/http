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

use Chevere\Http\Header;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class HeaderTest extends TestCase
{
    #[DataProvider('providerValid')]
    public function testValid(string $name, string $value): void
    {
        $line = "{$name}: {$value}";
        $header = new Header($name, $value);
        $this->assertSame($name, $header->name());
        $this->assertSame($value, $header->value());
        $this->assertSame($line, $header->__toString());
    }

    public static function providerValid(): array
    {
        return [
            ['foo', ''],
            ['foo', 'bar'],
            ['Content-Type', 'text/html; charset=UTF-8'],
            ['Content-Type', 'multipart/form-data; boundary=something'],
            ['X-Foo', 'Canción'],
        ];
    }

    #[DataProvider('providerInvalidName')]
    public function testInvalidName(string $name): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf('Header name `%s` is not valid according to RFC 7230', $name)
        );
        new Header($name, '');
    }

    public static function providerInvalidName(): array
    {
        return [
            // empty / whitespace-only
            [''],
            [' '],
            ["\t"],
            [' foo '],
            ["\tfoo\t"],
            ['foo bar'],
            ["foo\tbar"],
            // RFC 7230 "delimiters" excluded from tchar
            ['foo@bar'],
            ['foo(bar'],
            ['foo)bar'],
            ['foo<bar'],
            ['foo>bar'],
            ['foo,bar'],
            ['foo;bar'],
            ['foo:bar'],
            ['foo\\bar'],
            ['foo"bar'],
            ['foo/bar'],
            ['foo[bar'],
            ['foo]bar'],
            ['foo?bar'],
            ['foo=bar'],
            ['foo{bar'],
            ['foo}bar'],
            // control characters
            ["foo\r\n"],
            ["foo\rbar"],
            ["foo\nbar"],
            ["foo\0bar"],
            ["foo\x1Fbar"],
            ["foo\x7Fbar"], // DEL, not in tchar range
            // non-ASCII / high bytes — token is ASCII-only, unlike field-value
            ["foo\x80bar"],
            ['Canción'], // "Canción" in UTF-8
            ["fo\xC3\xA9"], // "foé" in UTF-8
            // header injection / splitting attempt
            ["X-Foo\r\nX-Bar: evil"],
        ];
    }

    #[DataProvider('providerInvalidValue')]
    public function testInvalidValue(string $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf('Header value `%s` is not valid according to RFC 7230', $value)
        );
        new Header('foo', $value);
    }

    public static function providerInvalidValue(): array
    {
        return [
            // bare whitespace / empty-ish
            [' '],
            ["\t"],
            ['  '],
            // leading/trailing whitespace around otherwise-valid content
            [' value'],
            ['value '],
            [' value '],
            ["\tvalue"],
            ["value\t"],
            ["\tvalue\t"],
            // CR / LF injection
            ["\n"],
            ["\r"],
            ["\r\n"],
            ["\nvalue"],
            ["value\n"],
            ["\rvalue"],
            ["value\r"],
            ["val\nue"],
            ["val\rue"],
            ["val\r\nue"],
            ["value\r\nX-Injected: evil"],
            ["value\nX-Injected: evil"],
            // other C0 control characters (not SP/HTAB, not CR/LF)
            ["val\0ue"],
            ["val\x01ue"],
            ["val\x07ue"], // bell
            ["val\x1Fue"],
            ["\0"],
            // DEL (0x7F) — explicitly excluded from field-vchar range
            ["val\x7Fue"],
            ["value\x7F"],
            // vertical tab / form feed
            ["val\x0Bue"],
            ["val\x0Cue"],
        ];
    }
}

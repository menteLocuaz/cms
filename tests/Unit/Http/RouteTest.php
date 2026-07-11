<?php

declare(strict_types=1);

namespace App\Tests\Unit\Http;

use App\Http\Route;
use PHPUnit\Framework\TestCase;

final class RouteTest extends TestCase
{
    public function testParsesSimplePath(): void
    {
        $_SERVER['REQUEST_URI'] = '/foo/bar';
        $route = Route::current();

        $this->assertSame(['foo', 'bar'], $route->segments());
        $this->assertSame('foo', $route->first());
        $this->assertSame('bar', $route->segment(1));
        $this->assertFalse($route->isEmpty());
    }

    public function testEmptyPathProducesEmptyRoute(): void
    {
        $_SERVER['REQUEST_URI'] = '/';
        $route = Route::current();

        $this->assertTrue($route->isEmpty());
        $this->assertNull($route->first());
        $this->assertSame([], $route->segments());
    }

    public function testStripsQueryString(): void
    {
        $_SERVER['REQUEST_URI'] = '/foo/bar?baz=1&qux=2';
        $route = Route::current();

        $this->assertSame(['foo', 'bar'], $route->segments());
    }

    public function testUrlDecodesSegments(): void
    {
        $_SERVER['REQUEST_URI'] = '/pagina%20con%20espacios/otro';
        $route = Route::current();

        $this->assertSame('pagina con espacios', $route->first());
        $this->assertSame('otro', $route->segment(1));
    }

    public function testOutOfRangeSegmentReturnsNull(): void
    {
        $_SERVER['REQUEST_URI'] = '/foo';
        $route = Route::current();

        $this->assertNull($route->segment(5));
    }

    public function testFromFirstSegmentFactory(): void
    {
        $route = Route::fromFirstSegment('dashboard');

        $this->assertSame('dashboard', $route->first());
        $this->assertSame(['dashboard'], $route->segments());
        $this->assertFalse($route->isEmpty());
    }
}

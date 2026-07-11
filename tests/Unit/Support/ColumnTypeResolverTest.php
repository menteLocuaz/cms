<?php

declare(strict_types=1);

namespace App\Tests\Unit\Support;

use App\Support\ColumnTypeResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ColumnTypeResolverTest extends TestCase
{
    #[DataProvider('knownTypes')]
    public function testKnownTypesReturnExpectedSql(string $type, string $expected): void
    {
        $this->assertSame($expected, ColumnTypeResolver::toSqlDefinition($type));
    }

    public function testUnknownTypeFallsBackToText(): void
    {
        $this->assertSame('TEXT NULL DEFAULT NULL', ColumnTypeResolver::toSqlDefinition('unknown_type_xyz'));
    }

    public static function knownTypes(): array
    {
        return [
            'text'          => ['text', 'TEXT NULL DEFAULT NULL'],
            'password'      => ['password', 'TEXT NULL DEFAULT NULL'],
            'object'        => ['object', "TEXT NULL DEFAULT '{}'"],
            'json'          => ['json', "TEXT NULL DEFAULT '[]'"],
            'int'           => ['int', "INT NULL DEFAULT '0'"],
            'relations'     => ['relations', "INT NULL DEFAULT '0'"],
            'order'         => ['order', "INT NULL DEFAULT '0'"],
            'boolean'       => ['boolean', "INT NULL DEFAULT '1'"],
            'double'        => ['double', "DOUBLE NULL DEFAULT '0'"],
            'money'         => ['money', "DOUBLE NULL DEFAULT '0'"],
            'date'          => ['date', 'DATE NULL DEFAULT NULL'],
            'time'          => ['time', 'TIME NULL DEFAULT NULL'],
            'datetime'      => ['datetime', 'DATETIME NULL DEFAULT NULL'],
            'timestamp'     => ['timestamp', 'TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP'],
            'code'          => ['code', 'LONGTEXT NULL DEFAULT NULL'],
            'chatgpt'       => ['chatgpt', 'LONGTEXT NULL DEFAULT NULL'],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Mapea los tipos de columna del CMS a su definición DDL de PostgreSQL.
 */
final class ColumnTypeResolver
{
    public static function toSqlDefinition(string $type): string
    {
        return match ($type) {

            'text',
            'textarea',
            'image',
            'video',
            'file',
            'link',
            'select',
            'array',
            'color',
            'password',
            'email'
                => 'TEXT NULL DEFAULT NULL',

            'object'
                => "TEXT NULL DEFAULT '{}'",

            'json'
                => "TEXT NULL DEFAULT '[]'",

            'int',
            'relations',
            'order'
                => "INT NULL DEFAULT '0'",

            'boolean'
                => "INT NULL DEFAULT '1'",

            'double',
            'money'
                => "DOUBLE NULL DEFAULT '0'",

            'date'
                => 'DATE NULL DEFAULT NULL',

            'time'
                => 'TIME NULL DEFAULT NULL',

            'datetime'
                => 'DATETIME NULL DEFAULT NULL',

            'timestamp'
                => 'TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',

            'code',
            'chatgpt'
                => 'LONGTEXT NULL DEFAULT NULL',

            default
                => 'TEXT NULL DEFAULT NULL',
        };
    }
}

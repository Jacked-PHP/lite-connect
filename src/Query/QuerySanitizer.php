<?php

namespace JackedPhp\LiteConnect\Query;

class QuerySanitizer
{
    public static function sanitize(string $input): string
    {
        return sqlite_escape_string($input);
    }
}
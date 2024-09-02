<?php

namespace JackedPhp\LiteConnect;

use Exception;
use JackedPhp\LiteConnect\Connection\Connection;

class SQLiteFactory
{
    public static function make(array $config): Connection
    {
        if (!isset($config['database'])) {
            throw new Exception('Missing database config for sqlite3!');
        }

        return new Connection($config['database']);
    }
}

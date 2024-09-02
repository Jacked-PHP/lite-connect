<?php

namespace JackedPhp\LiteConnect\Migration;

use JackedPhp\LiteConnect\Connection\Connection;

class MigrationManager
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    /**
     * @param array<Migration> $migrations
     * @return void
     */
    public function runMigrations(array $migrations): void
    {
        $pdo = $this->connection->getPDO();

        foreach ($migrations as $migration) {
            $migration->up($pdo);
        }
    }
}
<?php

namespace Tests\Samples;

use JackedPhp\LiteConnect\Migration\Migration;
use PDO;

class CreateUsersTable implements Migration
{

    public function up(PDO $pdo): void
    {
        $pdo->exec('CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NULL,
            email TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )');
    }

    public function down(PDO $pdo): void
    {
        // no way back
    }
}
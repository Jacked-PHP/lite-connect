<?php

namespace JackedPhp\LiteConnect\Connection;

use PDO;
use RuntimeException;

class Connection
{
    private $pdo;

    public function __construct(string $dbPath = ':memory:')
    {
        $this->pdo = new PDO("sqlite:$dbPath");
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getPDO(): PDO
    {
        if ($this->pdo === null) {
            throw new RuntimeException('Connection is closed');
        }

        return $this->pdo;
    }

    public function close(): void
    {
        $this->pdo = null;
    }

    public function isConnected(): bool
    {
        return $this->pdo !== null;
    }
}
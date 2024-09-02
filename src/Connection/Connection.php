<?php

namespace JackedPhp\LiteConnect\Connection;

use PDO;

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
        return $this->pdo;
    }
}
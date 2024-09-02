<?php

namespace JackedPhp\LiteConnect\Migration;

use PDO;

interface Migration
{
    public function up(PDO $pdo): void;

    public function down(PDO $pdo): void;
}

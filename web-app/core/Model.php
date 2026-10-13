<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';

abstract class Model
{
    protected function getDB(): PDO
    {
        return Database::getConnection();
    }
}

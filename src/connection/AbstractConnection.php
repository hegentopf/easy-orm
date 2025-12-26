<?php

namespace Hegentopf\EasyOrm\connection;

use PDO;

abstract class AbstractConnection
{
    protected PDO $pdo;

    abstract protected function connect(): void;

    public function getPdo(): PDO
    {
        return $this->pdo;
    }
}
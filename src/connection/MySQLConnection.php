<?php

namespace Hegentopf\EasyOrm\connection;

use PDO;
use PDOException;

class MySQLConnection extends AbstractConnection
{
    private string $host;
    private int $port;
    private string $db;
    private string $user;
    private string $pass;

    public function __construct( string $db, string $user, string $pass, string $host = 'localhost', int $port = 3306 )
    {
        $this->host = $host;
        $this->port = $port;
        $this->db = $db;
        $this->user = $user;
        $this->pass = $pass;

        $this->connect();
    }

    protected function connect(): void
    {
        $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db};charset=utf8mb4";

        try {
            $this->pdo = new PDO( $dsn, $this->user, $this->pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ] );
        } catch ( PDOException $e ) {
            die( "Keine Verbindung möglich: " . $e->getMessage() );
        }
    }
}
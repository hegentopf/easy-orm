<?php

namespace Hegentopf\EasyOrm\connection;

class ConnectionManager
{
    const DEFAULT_CONNECTION_NAME = 'default';

    private static $connections = array();

    public static function setConnection( AbstractConnection $connection, string $name = ConnectionManager::DEFAULT_CONNECTION_NAME ): void
    {
        self::$connections[ $name ] = $connection;
    }

    public static function getConnection( string $name = ConnectionManager::DEFAULT_CONNECTION_NAME ): ?AbstractConnection
    {
        if ( false === isset( self::$connections[ $name ] ) ) {
            throw new \Exception( "Connection with name '{$name}' does not exist." );
        }
        return self::$connections[ $name ] ?? null;
    }
}
<?php

namespace Hegentopf\EasyOrm\modelCreator;

use Composer\InstalledVersions;
use Exception;
use Hegentopf\EasyOrm\connection\AbstractConnection;
use Hegentopf\EasyOrm\connection\ConnectionManager;
use Hegentopf\EasyOrm\Functions;
use Mustache\Engine;
use Mustache\Loader\FilesystemLoader;
use PDO;

class DbModelCreator
{
    private ?AbstractConnection $connection;

    protected ?string $namespace = 'dbModels';
    protected ?string $path = __DIR__ . '/../../src/dbModels';

    public function setNamespace( string $namespace ): static
    {
        $this->namespace = $namespace;
        return $this;
    }

    public function setPath( string $path ): static
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @throws Exception
     */
    protected function getConnection(): ?AbstractConnection
    {
        if ( $this->connection === null ) {
            throw new Exception( 'Connection not set. Please set the connection using setConnection() method.' );
        }
        return $this->connection;
    }

    /**
     * @throws Exception
     */
    public function __construct( $connectionName = ConnectionManager::DEFAULT_CONNECTION_NAME )
    {
        $this->connection = ConnectionManager::getConnection( $connectionName );

        $realPath = realpath( __DIR__ );
        if ( $realPath !== false && str_contains( $realPath, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR ) ) {
            $this->path = __DIR__ . '/../../../src/dbModels';
        }
    }

    /**
     * @throws Exception
     */
    public function createDbModel( $dbName, $tableName, $override = false ): void
    {
        $pdo = $this->getConnection()->getPdo();
        $stmt = $pdo->query( 'SHOW columns FROM `' . $tableName . '`' );
        $rows = $stmt->fetchAll( PDO::FETCH_ASSOC );

        $primaryKey = null;
        foreach ( $rows as $row ) {
            if ( $row[ 'Key' ] === 'PRI' ) {
                $primaryKey = $row[ 'Field' ];
                break;
            }
        }

        $modelName = Functions::snakeCaseToCamelCase( $tableName, true ) . 'Model';
        if ( ctype_digit( $modelName[ 0 ] ) ) {
            $modelName = '_' . $modelName;
        }

        $dir = rtrim( $this->path, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR . $dbName . DIRECTORY_SEPARATOR;
        if ( !is_dir( $dir ) ) mkdir( $dir, 0777, true );
        $filename = $dir . $modelName . '.php';

        if ( !$override && file_exists( $filename ) ) return;

        $needsDateTime = false;

        $columns = [];
        foreach ( $rows as $col ) {
            $field = $col[ 'Field' ];
            $type = $col[ 'Type' ];
            $isNullable = $col[ 'Null' ] === 'YES';
            $default = $col[ 'Default' ];

            $isInt = str_starts_with( $type, 'int' );
            $isFloat = str_starts_with( $type, 'float' )
                || str_starts_with( $type, 'double' )
                || str_starts_with( $type, 'decimal' );
            $isDate = str_contains( $type, 'date' );

            $isPrimary = $col[ 'Key' ] === 'PRI';
            $isUnique = $col[ 'Key' ] === 'UNI';
            $isIndex = $col[ 'Key' ] === 'MUL';
            $isAutoIncrement = str_contains( $col[ 'Extra' ], 'auto_increment' );

            if ( $isDate ) {
                $needsDateTime = true;
            }

            $columns[] = [
                'name' => $field,
                'camelName' => Functions::snakeCaseToCamelCase( $field, true ),
                'type' => $isInt ? 'integer' : ( $isFloat ? 'float' : ( $isDate ? 'datetime' : 'string' ) ),
                'varType' => $isInt ? 'int' : ( $isFloat ? 'float' : ( $isDate ? 'string|DateTime' : 'string' ) ),
                'start' => $isInt ? '0' : ( $isFloat ? '0.0' : ( $isDate ? '' : "''" ) ),
                'dbType' => $type,
                'nullable' => $isNullable,
                'default' => $default,
                'nullableSign' => $isNullable ? 'null|' : '',
                'hasStart' => false === $isDate,
                'isPrimary' => $isPrimary,
                'isUnique' => $isUnique,
                'isIndex' => $isIndex,
                'isAutoIncrement' => $isAutoIncrement,
                'setterAdditionalInfo' => $isDate ? 'DateTime returned as copy' : '',
                'getterAdditionalInfo' => $isDate ? 'Accepts a DateTime object or any string parsable by DateTime' : '',
            ];
        }

        $mustache = new Engine( [
                                    'loader' => new FilesystemLoader( __DIR__ . '/templates' )
                                ] );

        $template = $mustache->loadTemplate( 'DbModel' );

        $version = InstalledVersions::getVersion( 'hegentopf/easy-orm' ) ?: 'dev-master';

        if ( str_starts_with( $version, 'dev-' ) ) {
            $version = '0.0.0';
        }


        $fileContent = $template->render( [
                                              'namespace' => $this->namespace,
                                              'dbName' => $dbName,
                                              'modelName' => $modelName,
                                              'table' => $tableName,
                                              'primaryKey' => $primaryKey,
                                              'columns' => $columns,
                                              'needsDateTime' => $needsDateTime,
                                              'generatedAt' => date( 'Y-m-d H:i:s' ),
                                              'creatorVersion' => $version,
                                          ] );

        file_put_contents( $filename, $fileContent );
    }

    /**
     * @throws Exception
     */
    public function createAllDbModels( $override = false ): void
    {
        $pdo = $this->getConnection()->getPdo();

        $stmt = $pdo->query( 'SELECT DATABASE()' );
        $database = $stmt->fetchColumn();
        $dbName = str_replace( '.', '_', $database );

        $stmt = $pdo->query( 'SHOW TABLES' );
        $rows = $stmt->fetchAll( PDO::FETCH_NUM );

        foreach ( $rows as $row ) {
            $this->createDbModel( $dbName, $row[ 0 ], $override );
        }
    }
}

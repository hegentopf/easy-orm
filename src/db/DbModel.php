<?php

namespace Hegentopf\EasyOrm\db;

use DateTime;
use DateTimeImmutable;
use Exception;
use Hegentopf\EasyOrm\connection\AbstractConnection;
use Hegentopf\EasyOrm\connection\ConnectionManager;
use Hegentopf\EasyOrm\Functions;
use Hegentopf\EasyOrm\queryBuilder\DbExpression;
use Hegentopf\EasyOrm\queryBuilder\QueryBuilder;
use JsonSerializable;
use PDO;

abstract class DbModel implements JsonSerializable
{

    protected static ?QueryBuilder $querybuilder = null;
    protected static ?string $table = null;
    protected static ?string $primaryKey = null;

    protected static array $columnTypes = [];

    private ?string $connectionName;

    protected array $addedColumns = array();

    protected array $changedColumns = array();

    public function __construct( $connectionName = ConnectionManager::DEFAULT_CONNECTION_NAME )
    {
        $this->connectionName = $connectionName;
    }

    public function __call( $name, $value = null )
    {

        if ( str_starts_with( $name, 'get' ) ) {
            $feld = substr( $name, 3 );

            $snakeCase = Functions::camelCaseToSnakeCase( $feld );

            if ( property_exists( $this, $snakeCase ) ) {
                $value = $this->{$snakeCase} ?? null;

                if ( $value instanceof \DateTime ) {
                    return clone $value;
                }

                return $value;
            }

            return $this->addedColumns[ $snakeCase ] ?? null;
        }

        if ( str_starts_with( $name, 'set' ) ) {
            $feld = substr( $name, 3 );

            $snakeCase = Functions::camelCaseToSnakeCase( $feld );

            $val = $value[ 0 ];
            if ( property_exists( $this, $snakeCase ) ) {

                $this->{$snakeCase} = $this->castValue( $snakeCase, $val );

                $this->setChanged( $snakeCase );

                return $this;
            }
            $this->addedColumns[ $snakeCase ] = $val;

            return $this;
        }

        return null;
    }

    public static function getTable(): ?string
    {
        return static::$table;
    }

    public static function getDbModelPrimaryKey(): ?string
    {
        return static::$primaryKey;
    }

    /**
     * @throws Exception
     */
    private function getConnection(): AbstractConnection
    {
        return ConnectionManager::getConnection( $this->connectionName );
    }

    /**
     * @return PDO
     * @throws Exception
     */
    public function getPdo(): PDO
    {
        return $this->getConnection()->getPdo();
    }

    /**
     * @param string $connectionName
     * @return QueryBuilder<static>
     */
    public static function getQueryBuilder( string $connectionName = ConnectionManager::DEFAULT_CONNECTION_NAME ): QueryBuilder
    {
        $queryBuilder = new QueryBuilder( new static( $connectionName ) );

        $queryBuilder->setDbModelClass( get_called_class() );

        return $queryBuilder;
    }

    /**
     * @param $id
     * @return static
     * @throws Exception
     * @noinspection PhpUnused
     */
    public static function fetchById( $id ): static
    {
        return static::getQueryBuilder()->where( static::getDbModelPrimaryKey(), $id )->first();
    }


    /**
     * @param QueryBuilder $queryBuilder
     * @return static[]
     * @throws Exception
     */
    public function fetchByQueryBuilder( QueryBuilder $queryBuilder ): array
    {
        $sql = $queryBuilder->getSqlString();
        $preparedValues = $queryBuilder->getPreparedValues();

        $stmt = $this->getPdo()->prepare( $sql );
        $stmt->execute( $preparedValues );
        $rows = $stmt->fetchAll();

        $models = array();

        foreach ( $rows as $row ) {
            $model = new static( $this->connectionName );
            foreach ( $row as $property => $value ) {
                if ( property_exists( $model, $property ) ) {
                    if ( $value === null ) {
                        continue;
                    }

                    $model->{$property} = $this->castValue( $property, $value );
                    continue;
                }
                $model->addedColumns[ $property ] = $value;
            }
            $models[] = $model;
        }

        return $models;
    }

    /**
     * @throws Exception
     * @noinspection PhpUnused
     */
    public function save()
    {
        $objectVars = get_object_vars( $this );
        $primaryValue = $objectVars[ static::$primaryKey ];
        $changedColumns = $objectVars[ 'changedColumns' ];

        $dbCells = [];
        foreach ( $changedColumns as $col ) {
            $val = property_exists( $this, $col ) ? $this->{$col} : $this->addedColumns[ $col ] ?? null;
            if ( isset( static::$columnTypes[ $col ] ) ) {
                $val = $this->uncastValue( $col, $val );
            }
            $dbCells[ $col ] = $val;
        }

        $insert = false;
        if ( empty( $primaryValue ) ) {
            $insert = true;
        }

        $statement = '';
        if ( $insert ) {
            $statement .= 'INSERT INTO `' . static::$table . '` ';
            if ( false === empty( $dbCells ) ) {
                $statement .= ' (`' . implode( '`,`', array_keys( $dbCells ) ) . '`) ';
            }
            $statement .= ' VALUES (' . implode( ',', array_fill( 0, count( $dbCells ), '?' ) ) . ') ';
        } else {
            $statement .= 'UPDATE `' . static::$table . '` SET ';
            $sets = array();
            foreach ( array_keys( $dbCells ) as $spalte ) {
                $sets[] = '`' . $spalte . '`  = ?';
            }

            $statement .= ' ' . implode( ',', $sets );

            if ( empty ( $sets ) ) {
                $statement .= '`' . static::$primaryKey . '`  = `' . static::$primaryKey . '` ';
            }

            $statement .= ' WHERE `' . static::$primaryKey . '` = ?';

            $dbCells[ static::$primaryKey ] = $primaryValue;
        }

        $stmt = $this->getPdo()->prepare( $statement );
        $stmt->execute( array_values( $dbCells ) );

        if ( $insert ) {
            $this->{static::$primaryKey} = $this->getPdo()->lastInsertId();
        }

        return $this->{static::$primaryKey};
    }

    /**
     * @throws Exception
     */
    public function delete(): void
    {
        if ( empty( $this->{static::$primaryKey} ) ) {
            return;
        }

        $statement = 'DELETE FROM `' . static::$table . '` WHERE `' . static::$primaryKey . '` = ?';
        $stmt = $this->getPdo()->prepare( $statement );
        $stmt->execute( array($this->{static::$primaryKey}) );

        $this->{static::$primaryKey} = 0;
    }

    /**
     * @param $column
     * @return void
     */
    protected function setChanged( $column ): void
    {
        if ( false === in_array( $column, $this->changedColumns ) ) {
            $this->changedColumns[] = $column;
        }
    }

    protected function castValue( string $column, mixed $value )
    {
        $type = static::$columnTypes[ $column ] ?? 'string';

        if ( $value instanceof DbExpression ) {
            return $value;
        }

        return match ( $type ) {
            'datetime', 'date' => match ( true ) {
                $value instanceof DateTime => clone $value,
                default => new DateTime( $value ),
            },
            'float' => (float)$value,
            'int' => (int)$value,
            'bool' => (bool)$value,
            default => $value,
        };
    }

    protected function uncastValue( string $column, mixed $value )
    {
        $type = static::$columnTypes[ $column ] ?? 'string';

        return match ( $type ) {
            'datetime' => $value instanceof DateTime ? $value->format( 'Y-m-d H:i:s' ) : $value,
            'date' => $value instanceof DateTime ? $value->format( 'Y-m-d' ) : $value,
            'float' => (float)$value,
            'int' => (int)$value,
            'bool' => $value ? 1 : 0,
            default => $value,
        };
    }

    public static function __callStatic($name, $arguments)
    {
        if (!in_array($name, array_keys(static::$columnTypes), true)) {
            throw new \InvalidArgumentException("Column '$name' does not exist in model " . static::class);
        }
        return new DbColumn(static::class, $name);
    }

    public function jsonSerialize(): mixed
    {
        $objVars = get_object_vars( $this );

        $added = array();
        if ( isset( $objVars[ 'addedColumns' ] ) ) {
            $added = $objVars[ 'addedColumns' ];
        }
        unset( $objVars[ 'addedColumns' ] );
        unset( $objVars[ 'changedColumns' ] );

        $array = array();
        $array += $objVars;
        $array += $added;

        return $array;
    }

}

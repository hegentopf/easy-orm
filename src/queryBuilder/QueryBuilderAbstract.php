<?php

namespace Hegentopf\EasyOrm\queryBuilder;

use Hegentopf\EasyOrm\connection\ConnectionManager;
use Hegentopf\EasyOrm\db\DbModel;
use Hegentopf\EasyOrm\db\DbColumn;

/**
 * @template DbModelClass of DbModel
 */
abstract class QueryBuilderAbstract
{

    protected string $sqlString = '';

    protected array $preparedValues = array();

    protected bool $subquery = false;

    /** @var DbModelClass */
    protected DbModel $dbModel;

    abstract public function createSqlString();

    public function getSqlString(): string
    {

        $this->createSqlString();

        return $this->sqlString;
    }

    public function __construct( DbModel $dbModel = null )
    {

        if ( $dbModel === null ) {
            return;
        }
        $this->dbModel = $dbModel;
    }

    protected function addPreparedValue( $value ): void
    {

        $this->preparedValues[] = $value;
    }

    /**
     * @return array
     */
    public function getPreparedValues(): array
    {

        return $this->preparedValues;
    }

    protected function getPrimaryKey(): ?string
    {

        return $this->dbModel::getDbModelPrimaryKey();
    }

    protected function getTableName(): ?string
    {

        return $this->dbModel->getTable();
    }


    public function setSubquery( bool $true ): void
    {

        $this->subquery = $true;
    }

    /**
     * @param string|DbExpression|QueryBuilder $part
     * @return void
     */
    protected function addPreparedValuesQueryBuilderInstance( string|DbExpression|QueryBuilder $part ): void
    {

        if ( $part instanceof QueryBuilder ) {
            foreach ( $part->getPreparedValues() as $value ) {
                $this->addPreparedValue( $value );
            }
        }
    }


    /**
     * @param $columns
     * @return void
     */
    protected function checkForSubqueries( $columns ): void
    {

        if ( is_string( $columns ) ) {
            return;
        }

        if ( false === is_array( $columns ) ) {
            $this->checkForSubqueries( array($columns) );
        }


        foreach ( $columns as $column ) {
            if ( is_array( $column ) ) {
                $this->checkForSubqueries( $column );

                return;
            }

            if ( $column instanceof QueryBuilder ) {
                $column->setSubquery( true );
            }
        }
    }

    /**
     * @param string|DbColumn|DbExpression|QueryBuilder $dbSpalte
     * @return string
     */
    protected function getSpalteString( DbExpression|DbColumn|QueryBuilder|string $dbSpalte ): string
    {

        if ( $dbSpalte instanceof DbColumn || $dbSpalte instanceof DbExpression || $dbSpalte instanceof QueryBuilder ) {
            return (string)$dbSpalte;
        }

        return '`' . $this->getTableName() . '`.`' . $dbSpalte . '`';
    }

    /**
     * @param string|DbColumn|DbExpression|QueryBuilder $dbTable
     * @return string
     */
    protected function getTableString( DbExpression|DbColumn|QueryBuilder|string $dbTable ): string
    {

        if ( $dbTable instanceof DbExpression || $dbTable instanceof QueryBuilder ) {
            return (string)$dbTable;
        }

        return '`' . $dbTable . '`';
    }

}
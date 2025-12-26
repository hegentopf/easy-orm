<?php

namespace Hegentopf\EasyOrm\queryBuilder;

use Hegentopf\EasyOrm\db\DbColumn;

class Select extends QueryBuilderAbstract
{

    // list of columns requested
    private array $columns = array();
    private array $formattedColumns = array();
    private array $parameters = array();
    private string $columnsString;


    public function select( ...$string ): void
    {
        // Check for subqueries among provided parameters and merge into parameters list
        $this->checkForSubqueries( $string );
        $this->parameters = array_merge( $this->parameters, $string );
    }

    /**
     * Reads a parameter and maps it to a column entry with an alias if provided.
     *
     * Accepts strings, DbExpression, QueryBuilder, DbColumn or associative arrays like ['alias' => 'col'].
     *
     * @param mixed $parameter
     * @return void
     */
    private function readParameterToColumnWithAlias( mixed $parameter ): void
    {

        if ( is_string( $parameter ) || $parameter instanceof DbExpression || $parameter instanceof QueryBuilder ) {
            $this->columns[ (string)$parameter ] = $parameter;
            return;
        }

        if ( $parameter instanceof DbColumn ) {
            $key = (string)$parameter;
            $this->columns[ $key ] = $parameter;
            return;
        }

        // If parameter is iterable/array, map alias => column
        foreach ( $parameter as $alias => $column ) {
            if ( is_int( $alias ) ) {
                $alias = $column;
            }
            $this->columns[ $alias ] = $column;
        }
    }

    /**
     * Format the columns into SQL fragments and collect any prepared values from expressions/subqueries.
     *
     * @return void
     */
    private function formatColumns(): void
    {

        $this->formattedColumns = array();

        foreach ( $this->columns as $alias => $column ) {
            // get column representation (relies on parent/helper method)
            $dbColumn = $this->getSpalteString( $column );

            // when '*' is requested, format as `table`.*
            if ( $column === '*' ) {
                $dbColumn = '`' . $this->getTableName() . '`.*';
            }

            // If expression / subquery / DbColumn, collect its prepared values
            if ( $column instanceof DbExpression || $column instanceof QueryBuilder || $column instanceof DbColumn ) {
                $this->addPreparedValuesQueryBuilderInstance( $column );
            }

            // If alias equals the column string or column object has same name, don't add 'AS'
            if ( $alias === (string)$column || ( $column instanceof DbColumn && $alias === $column->getColumn() ) ) {
                $this->formattedColumns[] = $dbColumn;
                continue;
            }

            // Otherwise format as "<expr> as `alias`"
            if ( $alias !== $column ) {
                $this->formattedColumns[] = $dbColumn . ' as `' . $alias . '`';
            }
        }
    }

    // Build requested columns from parameters into internal $columns map
    private function buildRequestedColumns(): void
    {
        foreach ( $this->parameters as $value ) {
            $this->readParameterToColumnWithAlias( $value );
        }
    }

    /**
     * Create the final columns string from formatted columns.
     *
     * @return void
     */
    private function buildColumnsString(): void
    {
        $this->columnsString = implode( ', ', $this->formattedColumns );
    }


    public function createSqlString(): void
    {
        if ( false === empty( $this->columnsString ) ) {
            return;
        }

        $this->buildRequestedColumns();

        $this->ensurePrimaryKey();

        $this->moveStarToFront();

        $this->formatColumns();
        $this->buildColumnsString();

        if ( empty( $this->formattedColumns ) ) {
            $this->sqlString = 'SELECT `' . $this->getTableName() . '`.* ';
            return;
        }

        $this->sqlString = 'SELECT ' . $this->columnsString . ' ';
    }

    /**
     * Ensure the primary key column is always present for non-subqueries.
     *
     * @return void
     */
    private function ensurePrimaryKey(): void
    {
        if ( $this->subquery ) {
            return;
        }
        if ( false === empty( $this->columns ) ) {
            $primary = array($this->getPrimaryKey() => $this->getPrimaryKey());
            $this->columns = $primary + $this->columns;
        }
    }

    /**
     * If '*' is present in the columns, move it to the beginning so it appears first in the list.
     *
     * @return void
     */
    private function moveStarToFront(): void
    {

        if ( isset( $this->columns[ '*' ] ) ) {
            $firstElement = array('*' => $this->columns[ '*' ]);
            unset( $this->columns[ '*' ] );
            $this->columns = $firstElement + $this->columns;
        }
    }


}
<?php

namespace Hegentopf\EasyOrm\queryBuilder;

use Hegentopf\EasyOrm\db\DbColumn;

class Where extends QueryBuilderAbstract
{

    private array $whereArr;


    public function where( QueryBuilder|DbColumn|DbExpression|string $column, QueryBuilder|DbColumn|DbExpression|string $operator = null, QueryBuilder|DbColumn|DbExpression|string $value = null ): static
    {

        $this->checkForSubqueries( array($column, $operator, $value) );
        if ( $value === null ) {
            $value = $operator;
            $operator = '=';
        }

        $dbColumn = $this->getSpalteString( $column );

        if ( $column instanceof DbColumn ) {
            $dbColumn = $column;
        }

        if ( $value instanceof QueryBuilder ) {
            $this->addPreparedValuesQueryBuilderInstance( $value );
            $this->whereArr[] = $dbColumn . ' ' . $operator . ' ' . $value;

            return $this;
        }

        if ( $column instanceof DbExpression ) {
            $this->whereArr[] = $column;

            return $this;
        }

        if ( $value instanceof DbColumn ) {
            $this->whereArr[] = $dbColumn . ' ' . $operator . ' ' . $value;

            return $this;
        }

        $this->whereArr[] = $dbColumn . ' ' . $operator . ' ?';
        $this->addPreparedValue( $value );

        return $this;
    }

    public function whereNull( $column ): static
    {

        $this->checkForSubqueries( $column );
        $this->whereArr[] = $this->getSpalteString( $column ) . ' IS NULL';

        return $this;

    }

    public function whereNotNull( $column ): static
    {

        $this->checkForSubqueries( $column );
        $this->whereArr[] = $this->getSpalteString( $column ) . ' IS NOT NULL';

        return $this;

    }


    public function createSqlString(): void
    {

        if ( empty( $this->whereArr ) ) {
            return;
        }
        $this->sqlString = 'WHERE ' . implode( ' AND ', $this->whereArr ) . ' ';
    }

    public function whereIn( QueryBuilder|DbColumn|DbExpression|string $dbSpalte, array $array ): static
    {

        $this->checkForSubqueries( array($dbSpalte, $array) );

        if ( empty( $array ) ) {
            $this->whereArr[] = ' 1 = 0 ';

            return $this;
        }

        $this->whereArr[] = $this->getSpalteString( $dbSpalte ) . ' IN (' . implode( ',', array_fill( 0, count( $array ), '?' ) ) . ')';
        $this->preparedValues = array_merge( $this->preparedValues, $array );

        return $this;
    }

    public function whereNotIn( QueryBuilder|DbColumn|DbExpression|string $dbSpalte, array $array ): static
    {

        $this->checkForSubqueries( array($dbSpalte, $array) );

        if ( empty( $array ) ) {
            $this->whereArr[] = ' 1 = 1 ';

            return $this;
        }

        $this->whereArr[] = $this->getSpalteString( $dbSpalte ) . ' NOT IN (' . implode( ',', array_fill( 0, count( $array ), '?' ) ) . ')';
        $this->preparedValues = array_merge( $this->preparedValues, $array );

        return $this;
    }

}
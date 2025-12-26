<?php

namespace Hegentopf\EasyOrm\queryBuilder;

use Hegentopf\EasyOrm\db\DbColumn;

class Join extends QueryBuilderAbstract
{

    private array $join;

    public function join( string|QueryBuilder $table, DbColumn $matchLeft, DbColumn $matchRight ): void
    {

        $joinType = 'JOIN';
        $this->join[] = $joinType . ' ' . $this->getJoinString( $table, $matchLeft, $matchRight );
    }

    public function leftJoin( string|QueryBuilder $table, DbColumn $matchLeft, DbColumn $matchRight ): void
    {

        $joinType = 'LEFT JOIN';
        $this->join[] = $joinType . ' ' . $this->getJoinString( $table, $matchLeft, $matchRight );
    }

    public function rightJoin( string|QueryBuilder $table, DbColumn $matchLeft, DbColumn $matchRight ): void
    {

        $joinType = 'RIGHT JOIN';
        $this->join[] = $joinType . ' ' . $this->getJoinString( $table, $matchLeft, $matchRight );
    }

    public function innerJoin( string|QueryBuilder $table, DbColumn $matchLeft, DbColumn $matchRight ): void
    {

        $joinType = 'INNER JOIN';
        $this->join[] = $joinType . ' ' . $this->getJoinString( $table, $matchLeft, $matchRight );
    }

    public function createSqlString(): void
    {

        if ( empty( $this->join ) ) {
            return;
        }
        $this->sqlString = implode( '', $this->join );
    }

    /**
     * @param string|QueryBuilder $table
     * @param DbColumn $matchLeft
     * @param DbColumn $matchRight
     * @return string
     */
    public function getJoinString( string|QueryBuilder $table, DbColumn $matchLeft, DbColumn $matchRight ): string
    {

        $this->checkForSubqueries( array($table) );
        $this->addPreparedValuesQueryBuilderInstance( $table );


        return $this->getTableString( $table ) . ' ON `' . $matchLeft->getTable() . '`' . '.`' . $matchLeft->getColumn() . '` = `' . $matchRight->getTable() . '`.`' . $matchRight->getColumn() . '` ';
    }
}
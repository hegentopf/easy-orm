<?php

namespace Hegentopf\EasyOrm\queryBuilder;

use Hegentopf\EasyOrm\db\DbConstants;

class Limit extends QueryBuilderAbstract
{

    private int|null $limit = null;
    private int|null $offset = null;

    public function createSqlString(): void
    {

        $this->sqlString = '';
        $limit = $this->limit;
        if ( $this->limit === null && $this->offset !== null ) {
            $limit = DbConstants::MYSQL_MAX_LIMIT;
        }

        if ( $limit !== null ) {
            $this->sqlString = 'LIMIT ' . $limit;
        }
        if ( $this->offset !== null ) {
            $this->sqlString .= ' OFFSET ' . $this->offset;
        }

        $this->sqlString .= ' ';
    }

    public function limit( int $limit, int $offset = null ): void
    {
        $this->limit = $limit;
        $this->offset = $offset;
    }

    public function offset( int $int ): void
    {
        $this->offset = $int;
    }

    public function take( int $limit, int $offset = null ): void
    {
        $this->limit( $limit, $offset );
    }

    public function skip( int $int ): void
    {
        $this->offset( $int );
    }
}
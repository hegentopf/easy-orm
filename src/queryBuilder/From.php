<?php

namespace Hegentopf\EasyOrm\queryBuilder;

use Hegentopf\EasyOrm\db\DbModel;

class From extends QueryBuilderAbstract
{

    public function createSqlString(): void
    {

        $this->sqlString = 'FROM `' . $this->dbModel->getTable() . '` ';
    }

    public function __construct( DbModel $dbModel )
    {
        parent::__construct( $dbModel );
    }
}
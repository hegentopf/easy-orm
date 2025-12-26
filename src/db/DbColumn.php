<?php

namespace Hegentopf\EasyOrm\db;

class DbColumn
{

    private string|DbModel $dbModel;
    private string $column;

    public function __construct( string|DbModel $dbModel, string $spalte )
    {
        $this->dbModel = $dbModel;
        $this->column = $spalte;
    }

    public function __toString()
    {
        return '`' . $this->dbModel::getTable() . '`.' . $this->getColumnString();
    }

    public function getDbModel()
    {
        return $this->dbModel;
    }

    public function getTable(): string
    {
        return $this->dbModel::getTable();
    }

    /**
     * @return string
     */
    public function getColumn(): string
    {
        return $this->column;
    }

    /**
     * @return string
     */
    public function getColumnString(): string
    {
        if ( $this->column === '*' ) {
            return $this->column;
        }

        return '`' . $this->column . '`';
    }
}
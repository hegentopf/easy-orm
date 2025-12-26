<?php

namespace Hegentopf\EasyOrm\queryBuilder;

use Exception;
use Hegentopf\EasyOrm\db\DbModel;
use Hegentopf\EasyOrm\db\DbColumn;
use Stringable;

/**
 * @template DbModelClass of DbModel
 */
class QueryBuilder extends QueryBuilderAbstract implements Stringable
{
    protected ?string $dbModelClass = null;
    private Select $select;
    private From $from;
    private Join $join;
    private Where $where;
    private GroupBy $groupBy;
    private OrderBy $orderBy;
    private Limit $limit;

    /**
     * @param $dbModelClass
     */
    public function setDbModelClass( $dbModelClass ): void
    {

        $this->dbModelClass = $dbModelClass;
    }

    public function where( QueryBuilder|DbColumn|DbExpression|string $column, QueryBuilder|DbColumn|DbExpression|string $operator = null, QueryBuilder|DbColumn|DbExpression|string $value = null ): static
    {

        $this->where->where( $column, $operator, $value );

        return $this;
    }


    public function orderBy( QueryBuilder|DbColumn|DbExpression|string $dbSpalte, $order = OrderBy::ASC ): static
    {

        $this->orderBy->orderBy( $dbSpalte, $order );

        return $this;
    }

    /**
     * @return DbModel|DbModelClass|null
     * @throws Exception
     */
    public function first(): ?DbModel
    {

        $this->limit( 1 );

        $dbModels = $this->fetch();

        if ( empty( $dbModels ) ) {
            return null;
        }

        return $dbModels[ 0 ];

    }

    /**
     * @param string[]|DbExpression[]|QueryBuilder[]|DbColumn[]|DbModel[]|string|DbExpression|QueryBuilder|DbColumn|DbModel ...$columns
     * @return $this
     */
    public function select( ...$columns ): static
    {

        $this->select->select( ...$columns );

        return $this;
    }

    /**
     * @return DbModelClass[]
     * @throws Exception
     */
    public function get(): array
    {

        return $this->fetch();
    }

    /**
     * @return DbModelClass[]
     * @throws Exception
     */
    public function fetch(): array
    {

        return $this->dbModel->fetchByQueryBuilder( $this );

    }

    public function limit( $limit, $offset = null ): static
    {

        $this->limit->limit( $limit, $offset );

        return $this;
    }

    public function createSqlString(): void
    {

        $this->sqlString = $this->select->getSqlString();
        $this->sqlString .= $this->from->getSqlString();
        $this->sqlString .= $this->join->getSqlString();
        $this->sqlString .= $this->where->getSqlString();
        $this->sqlString .= $this->groupBy->getSqlString();
        $this->sqlString .= $this->orderBy->getSqlString();
        $this->sqlString .= $this->limit->getSqlString();
        $this->sqlString = trim( $this->sqlString );
    }

    public function getPreparedValues(): array
    {

        $this->preparedValues = array_merge( $this->preparedValues, $this->select->getPreparedValues() );
        $this->preparedValues = array_merge( $this->preparedValues, $this->from->getPreparedValues() );
        $this->preparedValues = array_merge( $this->preparedValues, $this->where->getPreparedValues() );
        $this->preparedValues = array_merge( $this->preparedValues, $this->limit->getPreparedValues() );

        return $this->preparedValues;
    }

    public function __construct( DbModel $dbModel )
    {

        parent::__construct( $dbModel );

        $this->select = new Select( $dbModel );
        $this->from = new From( $dbModel );
        $this->join = new Join( $dbModel );
        $this->where = new Where( $dbModel );
        $this->groupBy = new GroupBy( $dbModel );
        $this->orderBy = new OrderBy( $dbModel );
        $this->limit = new Limit();

    }

    /**
     * @param QueryBuilder|DbColumn|DbExpression|string $string
     * @param QueryBuilder[]|DbColumn[]|DbExpression[]|string[] $array
     * @return $this
     */
    public function whereIn( QueryBuilder|DbColumn|DbExpression|string $string, array $array ): static
    {

        $this->where->whereIn( $string, $array );

        return $this;
    }

    /**
     * @param QueryBuilder|DbColumn|DbExpression|string $string
     * @param QueryBuilder[]|DbColumn[]|DbExpression[]|string[] $array
     * @return $this
     */
    public function whereNotIn( QueryBuilder|DbColumn|DbExpression|string $string, array $array ): static
    {

        $this->where->whereNotIn( $string, $array );

        return $this;
    }

    public function whereNull( QueryBuilder|DbColumn|DbExpression|string $column ): static
    {

        $this->where->whereNull( $column );

        return $this;

    }

    public function whereNotNull( QueryBuilder|DbColumn|DbExpression|string $column ): static
    {

        $this->where->whereNotNull( $column );

        return $this;

    }

    public function groupBy( QueryBuilder|DbColumn|DbExpression|string $string ): static
    {

        $this->groupBy->groupBy( $string );

        return $this;
    }

    public function __toString(): string
    {

        if ( $this->subquery ) {
            return new DbExpression( '(' . $this->getSqlString() . ')' );
        }

        return new DbExpression( $this->getSqlString() );
    }

    public function setSubquery( bool $true ): void
    {
        parent::setSubquery( $true );

        $this->select->setSubquery( $true );
        $this->from->setSubquery( $true );
        $this->join->setSubquery( $true );
        $this->where->setSubquery( $true );
        $this->groupBy->setSubquery( $true );
        $this->orderBy->setSubquery( $true );
        $this->limit->setSubquery( $true );
    }

    public function join( string|QueryBuilder $table, DbColumn $matchLeft, DbColumn $matchRight ): static
    {

        $this->join->join( $table, $matchLeft, $matchRight );

        return $this;
    }

    public function leftJoin( string|QueryBuilder $table, DbColumn $matchLeft, DbColumn $matchRight ): static
    {

        $this->join->leftJoin( $table, $matchLeft, $matchRight );

        return $this;
    }

    public function rightJoin( string|QueryBuilder $table, DbColumn $matchLeft, DbColumn $matchRight ): static
    {

        $this->join->rightJoin( $table, $matchLeft, $matchRight );

        return $this;
    }

    public function innerJoin( string|QueryBuilder $table, DbColumn $matchLeft, DbColumn $matchRight ): static
    {

        $this->join->innerJoin( $table, $matchLeft, $matchRight );

        return $this;
    }

    public function take( int $limit, int $offset = null ): static
    {

        $this->limit->take( $limit, $offset );

        return $this;
    }

    public function skip( int $int ): static
    {

        $this->limit->skip( $int );

        return $this;
    }

    /**
     * returns the rows count of the actual query
     * @return int
     * @throws Exception
     */
    public function count(): int
    {
        $qbClone = clone $this;

        $qbClone->select( new DbExpression( 'COUNT(*) AS cnt' ) );

        $results = $this->dbModel->fetchByQueryBuilder( $qbClone );

        if ( !empty( $results ) ) {
            return (int)$results[ 0 ]->getCnt();
        }

        return 0;
    }

}
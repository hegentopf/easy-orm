<?php

namespace Hegentopf\Tests\queryBuilder;

use PHPUnit\Framework\TestCase;
use Hegentopf\EasyOrm\queryBuilder\Join;

class joinTest extends TestCase {

    private Join $join;

    public function setUp():void
    {

        $this->join = new join( new JoinTestModel() );
    }

    public function testJoin()
    {

        $join = $this->join;

        $join->join( JoinTestModel::getTable(), JoinTestModel::test_id(), DbTestModel::id() );

        $this->assertEquals( 'JOIN `joinTest` ON `joinTest`.`test_id` = `dbTest`.`id` ', $join->getSqlString() );
    }

    public function testSubQueryJoin()
    {

        $join = $this->join;

        $join->leftJoin( JoinTestModel::getQueryBuilder()->select( 'Name' ), JoinTestModel::test_id(), DbTestModel::id() );

        $this->assertEquals( 'LEFT JOIN (SELECT `joinTest`.`Name` FROM `joinTest`) ON `joinTest`.`test_id` = `dbTest`.`id` ', $join->getSqlString() );
    }

    public function testSubQueryWhereJoin()
    {

        $join = $this->join;

        $join->leftJoin( JoinTestModel::getQueryBuilder()->select( 'Name' )->whereIn( JoinTestModel::test_id(), [1,3,5]), JoinTestModel::test_id(), DbTestModel::id() );

        $this->assertEquals( 'LEFT JOIN (SELECT `joinTest`.`Name` FROM `joinTest` WHERE `joinTest`.`test_id` IN (?,?,?)) ON `joinTest`.`test_id` = `dbTest`.`id` ', $join->getSqlString() );
        $this->assertEquals( [1,3,5], $join->getPreparedValues());
    }
}

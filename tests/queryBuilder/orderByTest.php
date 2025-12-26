<?php

namespace Hegentopf\Tests\queryBuilder;

use PHPUnit\Framework\TestCase;
use Hegentopf\EasyOrm\queryBuilder\OrderBy;

class orderByTest extends TestCase {
    private OrderBy $orderBy;

    public function setUp():void
    {

        $this->orderBy = new orderBy( new DbTestModel() );
    }

    public function testOrderBy()
    {

        $orderBy = $this->orderBy;

        $orderBy->orderBy( 'feld' );

        $this->assertEquals( 'ORDER BY `dbTest`.`feld` ASC ', $orderBy->getSqlString() );
    }

    public function testOrderByEmpty()
    {

        $orderBy = $this->orderBy;

        $this->assertEquals( '', $orderBy->getSqlString() );
    }

    public function testOrderByDesc()
    {

        $orderBy = $this->orderBy;

        $orderBy->orderBy( 'feld', OrderBy::DESC );

        $this->assertEquals( 'ORDER BY `dbTest`.`feld` DESC ', $orderBy->getSqlString() );
    }

    public function testOrderByAsc()
    {

        $orderBy = $this->orderBy;

        $orderBy->orderBy( 'feld', OrderBy::ASC );

        $this->assertEquals( 'ORDER BY `dbTest`.`feld` ASC ', $orderBy->getSqlString() );
    }

    public function testOrderByMulti()
    {

        $orderBy = $this->orderBy;

        $orderBy->orderBy( 'feld' );
        $orderBy->orderBy( 'feld2', OrderBy::DESC );

        $this->assertEquals( 'ORDER BY `dbTest`.`feld` ASC, `dbTest`.`feld2` DESC ', $orderBy->getSqlString() );
    }
}

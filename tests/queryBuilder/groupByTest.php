<?php

namespace Hegentopf\Tests\queryBuilder;

use PHPUnit\Framework\TestCase;
use Hegentopf\EasyOrm\queryBuilder\GroupBy;

class groupByTest extends TestCase {

    private GroupBy $groupBy;

    public function setUp():void
    {

        $this->groupBy = new groupBy( new DbTestModel() );
    }

    public function testGroupBy()
    {

        $groupBy = $this->groupBy;

        $groupBy->groupBy( 'feld' );

        $this->assertEquals( 'GROUP BY `dbTest`.`feld` ', $groupBy->getSqlString() );
    }

    public function testGroupByEmpty()
    {

        $groupBy = $this->groupBy;

        $this->assertEquals( '', $groupBy->getSqlString() );
    }
    public function testGroupByMulti()
    {

        $groupBy = $this->groupBy;

        $groupBy->groupBy( 'feld' );
        $groupBy->groupBy( 'feld2' );

        $this->assertEquals( 'GROUP BY `dbTest`.`feld`, `dbTest`.`feld2` ', $groupBy->getSqlString() );
    }

    public function testGroupByMulti2()
    {

        $groupBy = $this->groupBy;

        $groupBy->groupBy( 'feld', 'feld2' );

        $this->assertEquals( 'GROUP BY `dbTest`.`feld`, `dbTest`.`feld2` ', $groupBy->getSqlString() );
    }
}

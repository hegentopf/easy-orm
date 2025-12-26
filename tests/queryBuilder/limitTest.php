<?php

namespace Hegentopf\Tests\queryBuilder;

use PHPUnit\Framework\TestCase;
use Hegentopf\EasyOrm\queryBuilder\Limit;

class limitTest extends TestCase {

    public function testLimit() {

            $limit = new limit();

            $limit->limit( 10 );

            $this->assertEquals( 'LIMIT 10 ', $limit->getSqlString() );
    }

    public function testLimitOffset()
    {

$limit = new limit();

        $limit->limit( 10, 20 );

        $this->assertEquals( 'LIMIT 10 OFFSET 20 ', $limit->getSqlString() );
    }

    public function testLimitOffset2()
    {

        $limit = new limit();

        $limit->limit( 10);
        $limit->offset( 20 );

        $this->assertEquals( 'LIMIT 10 OFFSET 20 ', $limit->getSqlString() );
    }

    public function testTakeSkip()
    {

        $limit = new limit();

        $limit->take( 10);
        $limit->skip( 20 );

        $this->assertEquals( 'LIMIT 10 OFFSET 20 ', $limit->getSqlString() );
    }

    public function testTakeSkip2()
    {

        $limit = new limit();

        $limit->skip( 30);

        $this->assertEquals( 'LIMIT 18446744073709551615 OFFSET 30 ', $limit->getSqlString() );
    }
}

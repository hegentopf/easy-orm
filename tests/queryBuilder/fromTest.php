<?php

namespace Hegentopf\Tests\queryBuilder;

use PHPUnit\Framework\TestCase;
use Hegentopf\EasyOrm\queryBuilder\From;

class fromTest extends TestCase {

    public function testFrom(  )
    {

            $from = new From(new DbTestModel());

            $this->assertEquals( 'FROM `dbTest` ', $from->getSqlString() );

    }
}

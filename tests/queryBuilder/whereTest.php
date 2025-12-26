<?php

namespace Hegentopf\Tests\queryBuilder;

use PHPUnit\Framework\TestCase;
use Hegentopf\EasyOrm\queryBuilder\Where;

class whereTest extends TestCase {

    private Where $where;

    public function setUp():void
    {
        $this->where = new where( new DbTestModel() );
    }
    public function testWhere()
    {

        $where = $this->where;

        $where->where( 'feld', 'inhalt' );

        $this->assertEquals( 'WHERE `dbTest`.`feld` = ? ', $where->getSqlString() );
    }

    public function testWhere2()
    {

        $where = $this->where;

        $where->where( 'feld', '>', 'inhalt' );

        $this->assertEquals( 'WHERE `dbTest`.`feld` > ? ', $where->getSqlString() );
    }

    public function testmultiWhere2()
    {

        $where = $this->where;

        $where->where( 'feld', '>', 'inhalt' );
        $where->where( 'feld2', 'like', '%test%' );
        $this->assertEquals( 'WHERE `dbTest`.`feld` > ? AND `dbTest`.`feld2` like ? ', $where->getSqlString() );
        $this->assertEquals( array( 'inhalt', '%test%' ), $where->getPreparedValues() );
    }

    public function testWhereNull()
    {

        $where = $this->where;

        $where->whereNull( 'feld' );
        $this->assertEquals( 'WHERE `dbTest`.`feld` IS NULL ', $where->getSqlString() );
    }


    public function testWhereNotNull()
    {

        $where = $this->where;

        $where->whereNotNull( 'feld' );
        $result = $where->getSqlString();
        $this->assertSame( 'WHERE `dbTest`.`feld` IS NOT NULL ', $result );
    }

    public function testWhereIn()
    {

        $where = $this->where;

        $where->whereIn( 'feld', array( 'test1', 'test2' ) );
        $this->assertEquals( 'WHERE `dbTest`.`feld` IN (?,?) ', $where->getSqlString() );
        $this->assertEquals( array( 'test1', 'test2' ), $where->getPreparedValues() );
    }

    public function testWhereNotIn()
    {

        $where = $this->where;

        $where->whereNotIn( 'feld', array( 'test1', 'test2' ) );
        $this->assertEquals( 'WHERE `dbTest`.`feld` NOT IN (?,?) ', $where->getSqlString() );
        $this->assertEquals( array( 'test1', 'test2' ), $where->getPreparedValues() );
    }

    public function testWhereNotInDbColumn()
    {

        $where = $this->where;

        $where->whereNotIn( DbTestModel::testSpalte(), array( 'test1', 'test2' ) );
        $this->assertEquals( 'WHERE `dbTest`.`testSpalte` NOT IN (?,?) ', $where->getSqlString() );
        $this->assertEquals( array( 'test1', 'test2' ), $where->getPreparedValues() );
    }

    public function testmultiNotIn()
    {

        $where = $this->where;

        $where->where( 'feld', '>', 'inhalt' );
        $where->whereNotIn( 'feld2', array( 'test1', 'test2' ) );
        $this->assertEquals( 'WHERE `dbTest`.`feld` > ? AND `dbTest`.`feld2` NOT IN (?,?) ', $where->getSqlString() );
        $this->assertEquals( array( 'inhalt', 'test1', 'test2' ), $where->getPreparedValues() );
    }

    public function testmultiNotInDbColumn()
    {

        $where = $this->where;

        $where->where( DbTestModel::testSpalte(), '>', 'inhalt' );
        $where->whereNotIn( DbTestModel::testSpalte(), array( 'test1', 'test2' ) );
        $this->assertEquals( 'WHERE `dbTest`.`testSpalte` > ? AND `dbTest`.`testSpalte` NOT IN (?,?) ', $where->getSqlString() );
        $this->assertEquals( array( 'inhalt', 'test1', 'test2' ), $where->getPreparedValues() );
    }


    public function testWhereDbExpression(){

        $where = $this->where;

        $where->where( 'feld', '>', new \Hegentopf\EasyOrm\queryBuilder\dbExpression( 'NOW()' ) );
        $this->assertEquals( 'WHERE `dbTest`.`feld` > ? ', $where->getSqlString() );
    }

    public function testNewDbColumnWhere()
    {

        $this->where->where( DbTestModel::testSpalte(), 'test');
        $result = $this->where->getSqlString();

        $this->assertEquals( 'WHERE `dbTest`.`testSpalte` = ? ', $result );

   }

    public function testNewDbColumnAliasWhere()
    {

        $this->where->where( 'test', '!=', DbTestModel::testSpalte() );
        $result = $this->where->getSqlString();

        $this->assertEquals( 'WHERE `dbTest`.`test` != `dbTest`.`testSpalte` ', $result );
    }

}

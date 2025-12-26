<?php

namespace Hegentopf\Tests\queryBuilder;

use PHPUnit\Framework\TestCase;
use Hegentopf\EasyOrm\queryBuilder;
use Hegentopf\EasyOrm\queryBuilder\Select;

class selectTest extends TestCase {

    private Select $select;

    public function setUp():void
    {

        $this->select = new select( new DbTestModel() );
    }

    public function testSelect()
    {

        $this->select->select();
        $result = $this->select->getSqlString();
        $this->assertSame( 'SELECT `dbTest`.* ', $result );
    }

    public function testSelectSingleString()
    {

        $this->select->select( 'test' );
        $result = $this->select->getSqlString();
        $this->assertSame( 'SELECT `dbTest`.`id`, `dbTest`.`test` ', $result );
    }

    public function testSelectMultiString()
    {

        $this->select->select( 'test1', 'test2' );
        $result = $this->select->getSqlString();
        $this->assertSame( 'SELECT `dbTest`.`id`, `dbTest`.`test1`, `dbTest`.`test2` ', $result );
    }

    public function testSelectArray()
    {

        $this->select->select( array( 'test1', 'test2' ) );
        $result = $this->select->getSqlString();
        $this->assertSame( 'SELECT `dbTest`.`id`, `dbTest`.`test1`, `dbTest`.`test2` ', $result );
    }

    public function testSelectArrayWithKey()
    {

        $this->select->select( array( 'test1' => 'test2' ) );
        $result = $this->select->getSqlString();
        $this->assertSame( 'SELECT `dbTest`.`id`, `dbTest`.`test2` as `test1` ', $result );
    }

    public function testSelectDbExpresseion()
    {

        $this->select->select( array( 'Testfeld' => new queryBuilder\dbExpression( '(SELECT Name from test where 1 = 1 group by Name))' ) ) );
        $result = $this->select->getSqlString();
        $this->assertSame( 'SELECT `dbTest`.`id`, (SELECT Name from test where 1 = 1 group by Name)) as `Testfeld` ', $result );
    }

    public function testMultiSelect()
    {

        $this->select->select( 'Vorname' );
        $this->select->select( new queryBuilder\dbExpression( '*' ) );
        $this->select->select( 'Nachname' );
        $this->select->select( array( 'asdf' => 'Salt' ) );
        $result = $this->select->getSqlString();

        $this->assertEquals( 'SELECT *, `dbTest`.`id`, `dbTest`.`Vorname`, `dbTest`.`Nachname`, `dbTest`.`Salt` as `asdf` ', $result );
    }

    public function testSelectAssoziativSubquery()
    {

        $subquery = DbTestModel::getQueryBuilder()->select( 'Name' );

        $this->select->select( array( 'test' => DbTestModel::getQueryBuilder()->select( 'Name' ) ) );
        $result = $this->select->getSqlString();

        $this->assertEquals( 'SELECT `dbTest`.`id`, (SELECT `dbTest`.`Name` FROM `dbTest`) as `test` ', $result );


    }

    public function testSelectSubquery()
    {

        $subquery = ( new DbTestModel() )::getQueryBuilder()->select( 'Name' );

        $this->select->select( $subquery );
        $result = $this->select->getSqlString();

        $this->assertEquals( 'SELECT `dbTest`.`id`, (SELECT `dbTest`.`Name` FROM `dbTest`) ', $result );
    }

    public function testNewDbColumnSelect()
    {

        $this->select->select( DbTestModel::testSpalte());
        $result = $this->select->getSqlString();

        $this->assertEquals( 'SELECT `dbTest`.`id`, `dbTest`.`testSpalte` ', $result );
    }

    public function testNewDbColumnAliasSelect()
    {

        $this->select->select( array('asdf' => DbTestModel::testSpalte()));
        $result = $this->select->getSqlString();

        $this->assertEquals( 'SELECT `dbTest`.`id`, `dbTest`.`testSpalte` as `asdf` ', $result );
    }

}

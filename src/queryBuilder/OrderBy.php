<?php

namespace Hegentopf\EasyOrm\queryBuilder;

use InvalidArgumentException;
use Hegentopf\EasyOrm\db\DbColumn;

class OrderBy extends QueryBuilderAbstract
{

    const ASC = 'ASC';
    const DESC = 'DESC';

    private array $spalten = array();
    /**
     * @var array|string
     */
    private string|array $formatierteSpalten = array();

    public function createSqlString(): void
    {

        if ( empty( $this->spalten ) ) {
            return;
        }
        $this->formatiereSpalten();
        $this->sqlString = 'ORDER BY ';
        $this->sqlString .= implode( ', ', $this->formatierteSpalten );
        $this->sqlString .= ' ';
    }

    public function orderBy( DbColumn|DbExpression|string $dbSpalte, $order = self::ASC ): void
    {


        if ( $order !== self::ASC && $order !== self::DESC ) {
            throw new InvalidArgumentException( 'Ungültiger Wert für $order. Erlaubt sind nur ASC und DESC.' );

        }


        $dbSpaltName = $dbSpalte;
        if ( $dbSpalte instanceof DbColumn ) {
            $dbSpaltName = $dbSpalte->getColumn();
        }

        $this->spalten[ $dbSpaltName ] = array('spalte' => $dbSpalte, 'order' => $order);
    }

    /**
     * @return void
     */
    public function formatiereSpalten(): void
    {

        $this->formatierteSpalten = array();
        foreach ( $this->spalten as $order ) {
            $spalte = $order[ 'spalte' ];
            $order = $order[ 'order' ];

            $this->formatierteSpalten [] = $this->getSpalteString( $spalte ) . ' ' . $order;
        }
    }


}
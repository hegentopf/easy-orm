<?php

namespace Hegentopf\EasyOrm\queryBuilder;

class GroupBy extends QueryBuilderAbstract
{

    private array $spalten = array();

    private array $formatierteSpalten = array();

    public function groupBy( ...$string ): void
    {

        foreach ( $string as $item ) {
            $this->spalten[] = $item;
        }

    }

    public function createSqlString(): void
    {

        if ( empty( $this->spalten ) ) {
            return;
        }

        $this->formatiereSpalten();

        $this->sqlString = 'GROUP BY ';
        $this->sqlString .= implode( ', ', $this->formatierteSpalten );
        $this->sqlString .= ' ';

    }

    private function formatiereSpalten(): void
    {

        $this->formatierteSpalten = array();
        foreach ( $this->spalten as $spalte ) {
            $this->formatierteSpalten [] = $this->getSpalteString( $spalte );
        }
    }
}
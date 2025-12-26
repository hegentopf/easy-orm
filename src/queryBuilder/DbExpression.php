<?php

namespace Hegentopf\EasyOrm\queryBuilder;

use Stringable;

class DbExpression implements Stringable
{

    private string $expression;

    public function __construct( string $expression )
    {

        $this->expression = $expression;
    }

    public function __toString(): string
    {

        return $this->expression;
    }
}
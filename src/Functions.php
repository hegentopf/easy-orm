<?php

namespace Hegentopf\EasyOrm;

class Functions
{
    public static function camelCaseToSnakeCase( $str ): string
    {

        return strtolower( preg_replace( '/([a-z])([A-Z])/', '$1_$2', $str ) );
    }

    public static function snakeCaseToCamelCase( $string, $pascalCase = false ): string
    {

        $str = str_replace( '_', '', ucwords( $string, '_' ) );

        if ( false === $pascalCase ) {
            $str = lcfirst( $str );
        }

        return $str;
    }
}
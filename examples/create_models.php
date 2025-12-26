<?php

use Hegentopf\EasyOrm\modelCreator\DbModelCreator;

require_once __DIR__ . '/db_setup.php';

$dbModelCreator = new DbModelCreator();
$dbModelCreator
    ->setNamespace( 'App\\dbModels' )
    ->setPath( __DIR__ . '/../src/dbModels' )
    ->createAllDbModels( true );

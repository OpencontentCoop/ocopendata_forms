<?php

$Module = array( 'name' => 'OpenDataForms' );

$ViewList = array();

$ViewList['demo'] = array(
    'functions' => array( 'use' ),
    'script' => 'demo.php',
    'params' => array(),
    'unordered_params' => array()
);

$ViewList['connector'] = array(
    'functions' => array( 'use' ),
    'script' => 'connector.php',
    'params' => array('Identifier', 'Service'),
    'unordered_params' => array()
);

$FunctionList = array();
$FunctionList['use'] = array();

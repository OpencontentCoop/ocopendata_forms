<?php

$Module = array( 'name' => 'OpenDataForms' );

$ViewList = array();

$ViewList['demo'] = array(
    'functions' => array( 'demo' ),
    'script' => 'demo.php',
    'params' => array(),
    'unordered_params' => array()
);

$ViewList['connector'] = array(
    'functions' => array( 'use' ),
    'script' => 'connector.php',
    'params' => array('Identifier', 'Service'),
    'unordered_params' => array(),
    'ui_context' => 'ajax',
);

$ViewList['ezoe'] = array(
    'functions' => array( 'use' ),
    'script' => 'ezoe.php',
    'params' => array('View', 'ObjectID', 'ObjectVersion', 'Param1', 'Param2', 'Param3', 'Param4'),
    'unordered_params' => array(),
    'ui_context' => 'ajax',
);


$FunctionList = array();
$FunctionList['use'] = array();
$FunctionList['demo'] = array();

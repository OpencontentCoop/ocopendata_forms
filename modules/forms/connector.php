<?php

$module = $Params['Module'];
$http = eZHTTPTool::instance();

$Identifier = $Params['Identifier'];
$Service = $Params['Service'];

$builder = new \Opencontent\Ocopendata\Forms\ConnectorBuilder();

try {
    $connector = $builder->build($Identifier);
    foreach($_GET as $key => $value){
        $connector->setParameter($key, $value);
    }
    $data = $connector->runService($Service);
} catch (Exception $e) {
    $data = array('error' => $e->getMessage());
}

if ($http->hasGetVariable('debug')) {
    echo '<pre>';
    print_r($data);
    eZDisplayDebug();
} else {
    header('Content-Type: application/json');
    echo json_encode($data);
}

eZExecution::cleanExit();

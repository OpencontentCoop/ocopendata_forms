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
    
    try {
        $data = $connector->runService($Service);
    } catch (Exception $e) {
        
        // Backward compatibility: l'interpretazione del $Service vuoto è stata aggiunta dopo la versione 1.1
        // I connettori custom potrebbero non essere aggiornati: vedi AbstractBaseConnector::runService riga 54
        if (empty($Service)){
            $data = array(
                'data' => $connector->runService('data'),
                'options' => $connector->runService('options'),
                'schema' => $connector->runService('schema'),
                'view' => $connector->runService('view'),
            );
        }else{
            throw $e;
        }
    }

} catch (Exception $e) {
    $data = array(
        'error' => $e->getMessage(),
//        'file' => $e->getFile(),
//        'line' => $e->getLine(),
//        'trace' => explode("\n", $e->getTraceAsString()),
//        'prev' => $e->getPrevious()
    );
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

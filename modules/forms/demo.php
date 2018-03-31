<?php

$module = $Params['Module'];
$tpl = eZTemplate::factory();
$http = eZHTTPTool::instance();

if (!empty($_POST)){
    echo '<pre>';
    print_r($_POST);
    eZDisplayDebug();
    eZExecution::cleanExit();
}

$Result = array();
$Result['path'] = array(
    array(
        'text' => "Forms demo",
        'url' => false
    )
);

$foundConnectors = array();
$notfoundConnectors = array();
$allDatatypes = eZDataType::allowedTypes();
foreach ($allDatatypes as $datatype){
    $fakeHelper = new \Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\ConnectorHelper('fake');
    $fakeClass = new eZContentClass(array(
        'identifier' => 'fake-identifier',
    ));
    $fakeAttribute = new eZContentClassAttribute(array(
        'identifier' => 'fake-identifier',
        'data_type_string' => $datatype
    ));
    $connector = \Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnectorFactory::load(
        $fakeAttribute,
        $fakeClass,
        $fakeHelper
    );
    if ($connector instanceof \Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnectorInterface){
        $class = get_class($connector);
        if ($class == 'Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector')
            $notfoundConnectors[$datatype] = $class;
        else
            $foundConnectors[$datatype] = $class;
    }
}

$tpl->setVariable('connector_by_datatype', $foundConnectors);
$tpl->setVariable('not_found_connector_by_datatype', $notfoundConnectors);

echo $tpl->fetch( 'design:forms/demo.tpl' );
eZDisplayDebug();
eZExecution::cleanExit();


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
$tpl->setVariable( 'ui_context', 'navigation' );

$Result = [];
$Result['path'] = array(
    array(
        'text' => "Forms demo",
        'url' => false
    )
);
$contentInfoArray['persistent_variable'] = [
    'show_path' => true,
];
if (is_array($tpl->variable('persistent_variable'))) {
    $contentInfoArray['persistent_variable'] = array_merge(
        $contentInfoArray['persistent_variable'],
        $tpl->variable('persistent_variable')
    );
}
$Result['content_info'] = $contentInfoArray;
$Result['content'] = $tpl->fetch( 'design:forms/demo.tpl' );
$Result['pagelayout'] = false;


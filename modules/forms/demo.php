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
$Result['content'] = $tpl->fetch( 'design:forms/demo.tpl' );


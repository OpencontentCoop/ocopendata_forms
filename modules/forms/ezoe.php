<?php

use Opencontent\Ocopendata\Forms\EzOnlineEditor\ModuleHelper;

$helper = new ModuleHelper($Params);
try {
    return $helper->getViewResult();
}catch (Exception $e){
    echo $e->getMessage();
    eZExecution::cleanExit();
}

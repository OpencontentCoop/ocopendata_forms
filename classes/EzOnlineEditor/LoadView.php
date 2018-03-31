<?php

namespace Opencontent\Ocopendata\Forms\EzOnlineEditor;


class LoadView extends AbstractView implements ViewInterface
{
    public function __construct($embedId, $dataMap, $imagePreGenerateSizes)
    {
        $embedId = 0;
        $embedObject = false;
        $http = \eZHTTPTool::instance();

        if ($embedId) {
            $embedType = 'ezobject';
            if (!is_numeric($embedId)) {
                list($embedType, $embedId) = explode('_', $embedId);
            }

            if (strcasecmp($embedType, 'eznode') === 0) {
                $embedObject = \eZContentObject::fetchByNodeID($embedId);
            } else {
                $embedObject = \eZContentObject::fetch($embedId);
            }
        }

        if (!$embedObject instanceof \eZContentObject || !$embedObject->canRead()) {
            $this->Result = 'false';

        } else {

            // Params for node to json encoder
            $params = array('loadImages' => true);
            $params['imagePreGenerateSizes'] = array('small', 'original');

            // look for datamap parameter ( what datamap attribute we should load )
            if ($dataMap) {
                $params['dataMap'] = array($dataMap);
            }

            // what image sizes we want returned with full data ( url++ )
            if ($http->hasPostVariable('imagePreGenerateSizes')) {
                $params['imagePreGenerateSizes'][] = $http->postVariable('imagePreGenerateSizes');
            } else if ($imagePreGenerateSizes) {
                $params['imagePreGenerateSizes'][] = $imagePreGenerateSizes;
            }

            $json = \ezjscAjaxContent::nodeEncode($embedObject, $params);

            $this->Result = $json;
        }
    }

    public function getResult()
    {
        echo $this->Result;
        \eZDB::checkTransactionCounter();
        \eZExecution::cleanExit();

        return true;
    }
}

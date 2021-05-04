<?php

namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

class BooleanField extends FieldConnector
{
    public function getData()
    {
        $data = parent::getData();
        if ($data === null){
            return (bool) $this->attribute->attribute( "data_int3" );
        }
        return (bool)$data;
    }

    public function getSchema()
    {
        $data = parent::getSchema();
        $data['type'] = "boolean";

        return $data;
    }

    public function getOptions()
    {
        return array(
            "helper" => $this->attribute->attribute('description'),
            'type' => 'checkbox',
            'rightLabel' => $this->attribute->attribute('name')
        );
    }

    public function setPayload($postData)
    {        
        return $postData === 'true';
    }
}

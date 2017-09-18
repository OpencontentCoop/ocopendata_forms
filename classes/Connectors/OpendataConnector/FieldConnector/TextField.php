<?php

namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

class TextField extends FieldConnector
{
    public function getOptions()
    {
        $options = array(
            "helper" => $this->attribute->attribute('description'),
            "type" => "textarea"
        );

        $rows = $this->attribute->attribute(\eZTextType::COLS_FIELD);
        if ($rows){
            $options['rows'] = (int)$rows;
        }

        return $options;
    }
}

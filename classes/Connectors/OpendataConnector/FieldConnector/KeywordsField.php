<?php

namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

class KeywordsField extends FieldConnector
{
    public function getData()
    {
        return implode(', ', (array)$this->getContent());
    }

    public function getOptions()
    {
        return array();
    }
}

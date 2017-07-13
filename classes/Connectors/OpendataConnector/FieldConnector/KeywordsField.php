<?php

namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

class KeywordsField extends FieldConnector
{
    public function getData($rawContent)
    {
        return implode(', ', $rawContent['content']);
    }

    public function getOptions()
    {
        return array();
    }
}

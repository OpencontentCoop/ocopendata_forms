<?php
namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

class PageField extends FieldConnector
{
    public function getOptions()
    {
        return array(
            'hidden' => true,
            'disabled' => true
        );
    }
}

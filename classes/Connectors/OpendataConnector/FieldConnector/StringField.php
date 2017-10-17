<?php

namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;
use eZStringType;

class StringField extends FieldConnector
{
    public function getOptions()
    {
        $options = array(
            "helper" => $this->attribute->attribute('description')
        );

        return $options;
    }

    public function getSchema()
    {
        $schema = parent::getSchema();

        $schema['default'] = $this->attribute->attribute(eZStringType::DEFAULT_STRING_FIELD);

        $maxLength = (int)$this->attribute->attribute(eZStringType::MAX_LEN_FIELD);
        if ($maxLength > 0) {
            $schema['maxLength'] = $maxLength;
        }

        return $schema;
    }
}

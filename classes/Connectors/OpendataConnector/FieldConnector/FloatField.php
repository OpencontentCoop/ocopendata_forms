<?php

namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;
use eZFloatType;

class FloatField extends FieldConnector
{
    public function getData()
    {
        return parent::getData() ? (float)parent::getData() : null;
    }

    public function getOptions()
    {
        $options = array(
            "type" => "number"
        );

        return $options;
    }

    public function getSchema()
    {
        $schema = parent::getSchema();
        $schema['default'] = $this->attribute->attribute(eZFloatType::DEFAULT_FIELD);

        $min = $this->attribute->attribute(eZFloatType::MIN_FIELD);
        $max = $this->attribute->attribute(eZFloatType::MAX_FIELD);

        if ($min) {
            $schema["minimum"] = $min;
        }

        if ($max) {
            $schema["maximum"] = $max;
        }

        return $schema;
    }
}

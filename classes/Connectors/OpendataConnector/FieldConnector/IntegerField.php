<?php

namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;
use eZIntegerType;

class IntegerField extends FieldConnector
{
    public function getData($rawContent)
    {
        return (int)$rawContent['content'];
    }

    public function getOptions()
    {
        $options = array(
            "type" => "integer"
        );

        return $options;
    }

    public function getSchema()
    {
        $schema = parent::getSchema();
        $schema['default'] = $this->attribute->attribute(eZIntegerType::DEFAULT_VALUE_FIELD);

        $inputState = $this->attribute->attribute( eZIntegerType::INPUT_STATE_FIELD );

        $min = $this->attribute->attribute(eZIntegerType::MIN_VALUE_FIELD);
        $max = $this->attribute->attribute(eZIntegerType::MAX_VALUE_FIELD);

        if ($inputState != eZIntegerType::NO_MIN_MAX_VALUE) {

            if ($inputState == eZIntegerType::HAS_MIN_VALUE || $inputState == eZIntegerType::HAS_MIN_MAX_VALUE) {
                $schema["minimum"] = $min;
            }

            if ($inputState == eZIntegerType::HAS_MAX_VALUE || $inputState == eZIntegerType::HAS_MIN_MAX_VALUE) {
                $schema["maximum"] = $max;
            }
        }

        return $schema;
    }
}

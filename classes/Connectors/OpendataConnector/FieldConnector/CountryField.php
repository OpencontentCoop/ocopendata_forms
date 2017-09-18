<?php

namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;


use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;
use eZCountryType;

class CountryField extends FieldConnector
{
    private $values;

    public function __construct($attribute, $class, $helper)
    {
        parent::__construct($attribute, $class, $helper);

        $countries = eZCountryType::fetchCountryList();
        $this->values = array();
        foreach ($countries as $country) {
            $this->values[$country['Alpha2']] = $country['Name'];
        }
    }

    public function getData()
    {
        return explode(',', (array)$this->getContent());
    }

    public function getSchema()
    {
        $schema = array(
            "enum" => array_keys($this->values),
            "title" => $this->attribute->attribute('name'),
            'required' => (bool)$this->attribute->attribute('is_required')
        );

        $classContent = $this->attribute->dataType()->classAttributeContent($this->attribute);
        $default = $classContent['default_countries'];
        if (!empty($default)){
            $schema['default'] = array_keys($default);
        }

        return $schema;
    }

    public function getOptions()
    {
        return array(
            "label" => $this->attribute->attribute('name'),
            "helper" => $this->attribute->attribute('description'),
            "optionLabels" => array_values($this->values),
            "hideNone" => false,
            "showMessages" => false,
            "type" => "select",
            "multiple" => (bool)$this->attribute->attribute(eZCountryType::MULTIPLE_CHOICE_FIELD),
        );
    }

    public function setPayload($postData)
    {
        return implode(',', (array)$postData);
    }
}

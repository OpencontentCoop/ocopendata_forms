<?php

namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

class SelectionField extends FieldConnector
{
    private $values;

    public function __construct($attribute, $class, $helper)
    {
        parent::__construct($attribute, $class, $helper);

        $classContent = $this->attribute->dataType()->classAttributeContent($this->attribute);
        $this->values = array();
        foreach ($classContent['options'] as $option) {
            $this->values[] = $option['name'];
        }
    }

    public function getData()
    {
        $rawContent = $this->getContent();
        if ($rawContent && !empty($rawContent['content']) && $rawContent['content'][0] != ''){
            return $rawContent['content'];
        }elseif($this->attribute->attribute('is_required')){
            return array($this->values[0]);
        }
        return null;
    }
    
    public function getSchema()
    {
        $schema = array(
            "enum" => $this->values,
            "title" => $this->attribute->attribute('name'),
            'required' => (bool)$this->attribute->attribute('is_required')
        );

        if ($schema['required']){
            $schema['default'] = current($this->values);
        }

        return $schema;
    }

    public function getOptions()
    {
        return array(
            "label" => $this->attribute->attribute('name'),
            "helper" => $this->attribute->attribute('description'),
            "hideNone" => (bool)$this->attribute->attribute('is_required'),
            "type" => "select",
            "multiple" => (bool)$this->attribute->attribute('data_int1'),
        );
    }
}

<?php

namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

class EzXmlField extends FieldConnector
{
    public function getSchema()
    {
        $schema = parent::getSchema();
        $schema['type'] = 'tinymce';
        return $schema;
    }

    public function getOptions()
    {
        $options = array(
            'type' => "tinymce",
            'hideInitValidationError' => true,
            'toolbar' => 'bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link',
            "helper" => $this->attribute->attribute('description'),
        );

        return $options;
    }
}

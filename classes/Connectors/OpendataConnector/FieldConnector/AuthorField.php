<?php
namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

class AuthorField extends FieldConnector
{
    public function getSchema()
    {
        return array(
            "type" => "array",
            "title" => $this->attribute->attribute('name'),
            "items" => array(
                "type" => "object",
                "properties" => array(
                    "name" => array(
                        "title" => \ezpI18n::tr( 'design/standard/content/datatype', 'Name' ),
                        "type" => "string"
                    ),
                    "email" => array(
                        "title" => \ezpI18n::tr( 'design/standard/content/datatype', 'Email' ),
                        "format" => "email"
                    )

                )
            ),
            'minItems' => (bool)$this->attribute->attribute('is_required') ? 1 : 0
        );
    }

    public function getOptions()
    {
        return array(
            "helper" => $this->attribute->attribute('description')
        );
    }
}

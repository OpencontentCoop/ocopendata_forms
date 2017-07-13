<?php

namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

class UserField extends FieldConnector
{
    public function getSchema()
    {
        return array(
            "type" => "object",
            "title" => $this->attribute->attribute('name'),
            "properties" => array(
                "login" => array(
                    "title" => \ezpI18n::tr( 'design/standard/content/datatype', 'Username' ),
                    "type" => "string",
                    "readonly" => $this->getHelper()->hasParameter('object'),
                ),
                "email" => array(
                    "title" => \ezpI18n::tr( 'design/standard/content/datatype', 'Email' ),
                    "format" => "email",
                    "readonly" => $this->getHelper()->hasParameter('object'),
                )
            ),
            'required' => (bool)$this->attribute->attribute('is_required')
        );
    }

    public function getOptions()
    {
        return array(
            "helper" => $this->attribute->attribute('description'),
            "fields" => array(
                "login" => array(
                    "autocomplete" => 'off',
                    "disabled" => $this->getHelper()->hasParameter('object'),
                ),
                "email" => array(
                    "autocomplete" => 'off',
                    "disabled" => $this->getHelper()->hasParameter('object'),
                )
            )

        );
    }

    public function setPayload($postData)
    {
        return $this->getHelper()->hasParameter('object') ? null : $postData;
    }
}

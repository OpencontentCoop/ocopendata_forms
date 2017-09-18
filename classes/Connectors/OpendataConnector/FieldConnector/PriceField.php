<?php

namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;
use eZPriceType;

class PriceField extends FieldConnector
{
    public function getData($rawContent)
    {
        $rawContent = $this->getContent();
        return isset($rawContent['content']['value']) ? number_format($rawContent['content']['value'], 2) : null;
    }

    public function getSchema()
    {
        $schema = parent::getSchema();
        $schema['default'] = 0;

        return $schema;
    }

    public function getOptions()
    {
        //@todo Currency type
        return array(
            "helper" => $this->attribute->attribute('description'),
            'type' => "currency",
            "centsSeparator" => ",",
            "prefix" => "",
            "suffix" => "€",
            "thousandsSeparator" => "."
        );
    }

    public function setPayload($postData)
    {
        return array(
            'value' => $postData,
            'vat_id' => $this->attribute->attribute( eZPriceType::INCLUDE_VAT_FIELD ),
            'is_vat_included' => $this->attribute->attribute( eZPriceType::VAT_ID_FIELD )
        );
    }

}

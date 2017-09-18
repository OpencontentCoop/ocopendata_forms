<?php

namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;
use eZDateType;

class DateField extends FieldConnector
{
    //@todo usare eZLocale per il formato delle date

    public function getData()
    {
        return date('d/m/Y', strtotime($this->getContent()));
    }

    public function getSchema()
    {
        $data = parent::getSchema();
        $default = $this->attribute->attribute( eZDateType::DEFAULT_FIELD );

        return array_merge_recursive($data, array(
            "format" => "date",
            "default" => $default == eZDateType::DEFAULT_CURRENT_DATE ? date('d/m/Y') : null
        ));
    }

    public function getOptions()
    {
        return array(
            'type' => 'date',
            "dateFormat" => "DD/MM/YYYY",
            "locale" => "it",
            "helper" => $this->attribute->attribute('description'),
        );
    }

    public function setPayload($postData)
    {
        $date = \DateTime::createFromFormat('d/m/Y', $postData);
        return $date instanceof \DateTime ? $date->format(\DateTime::ISO8601) : null;
    }
}

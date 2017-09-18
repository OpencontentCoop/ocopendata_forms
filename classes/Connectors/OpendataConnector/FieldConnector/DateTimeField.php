<?php

namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;
use eZDateTimeType;

class DateTimeField extends FieldConnector
{
    //@todo usare eZLocale per il formato delle date

    public function getData()
    {
        return $this->getContent() ? date('d/m/Y H:i', strtotime($this->getContent())) : null;
    }

    public function getSchema()
    {
        $data = parent::getSchema();
        $default = $this->attribute->attribute(eZDateTimeType::DEFAULT_FIELD);

        return array_merge_recursive($data, array(
            "format" => "datetime",
            "default" => $default == eZDateTimeType::DEFAULT_CURRENT_DATE ? date('d/m/Y H:i') : null
        ));
    }

    public function getOptions()
    {
        return array(
            'type' => 'datetime',
            "dateFormat" => "DD/MM/YYYY HH:mm",
            "picker" => array(
                "format" => "DD/MM/YYYY HH:mm",
                "useCurrent" => false,
                "locale" => "it",
            ),
            "locale" => "it",
            "helper" => $this->attribute->attribute('description'),
        );
    }

    public function setPayload($postData)
    {
        $date = \DateTime::createFromFormat('d/m/Y H:i', $postData);
        return $date instanceof \DateTime ? $date->format(\DateTime::ISO8601) : null;
    }
}

<?php
namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;
use eZTimeType;

class TimeField extends FieldConnector
{
    //@todo usare eZLocale per il formato delle date

    public function getData($rawContent)
    {
        return date('H:i', strtotime($rawContent));
    }

    public function getSchema()
    {
        $data = parent::getSchema();
        $default = $this->attribute->attribute(eZTimeType::DEFAULT_FIELD);

        return array_merge_recursive($data, array(
            "format" => "time",
            "default" => $default == eZTimeType::DEFAULT_CURRENT_DATE ? date('H:i') : null
        ));
    }

    public function getOptions()
    {
        return array(
            'type' => 'time',
            "dateFormat" => "HH:mm",
            "picker" => array(
                "format" => "HH:mm",
                "useCurrent" => false,
                "locale" => "it",
            ),
            "locale" => "it",
            "helper" => $this->attribute->attribute('description'),
        );
    }

    public function setPayload($postData)
    {
        $date = \DateTime::createFromFormat('H:i', $postData);
        return $date instanceof \DateTime ? $date->format(\DateTime::ISO8601) : null;
    }
}

<?php
namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

class GeoField extends FieldConnector
{
    public function getData()
    {
        $rawContent = $this->getContent();
        $content = $rawContent ? $rawContent['content'] : null;
        if ($content && $content['latitude'] == 0 && $content['longitude'] == 0 && $content['address'] == ''){
            return null;
        }

        return $content;
    }

    public function getSchema()
    {
        return array(
            "title" => $this->attribute->attribute('name'),
            'required' => (bool)$this->attribute->attribute('is_required'),
        );
    }

    public function getOptions()
    {
        return array(
            "helper" => $this->attribute->attribute('description'),
            "type" => 'openstreetmap',
        );
    }
}

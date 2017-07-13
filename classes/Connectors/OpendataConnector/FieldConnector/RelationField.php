<?php

namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

class RelationField extends FieldConnector
{
    public function getData($rawContent)
    {
        $data = array();
        foreach($rawContent['content'] as $item){

            $language = $this->getHelper()->getSetting('language');
            $itemName = $item['name'];
            $name = isset($itemName[$language]) ? $itemName[$language] : current($itemName);

            $data[] = array(
                'id' => $item['id'],
                'name' => $name,
                'class' => $item['classIdentifier'],
            );
        }

        return $data;
    }

    public function getSchema()
    {
        $schema = array(
            "title" => $this->attribute->attribute('name'),
            'required' => (bool)$this->attribute->attribute('is_required'),
            'type' => 'array',
            'minItems' => (bool)$this->attribute->attribute('is_required') ? 1 : 0,
            'maxItems' => 1
        );

        return $schema;
    }

    public function getOptions()
    {
        $options = array(
            "helper" => $this->attribute->attribute('description'),
            'type' => 'relationbrowse',
            'browse' => array(
                "selectionType" => 'single'
            ),
        );

        return $options;
    }

    public function setPayload($postData)
    {
        $postData = (array)$postData;
        foreach($postData as $item){
            if(is_array($item) && isset($item['id'])){
                return array((int)$item['id']);
            }
        }
        return null;
    }
}

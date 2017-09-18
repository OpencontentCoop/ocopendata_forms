<?php

namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

class RelationField extends FieldConnector
{
    public function getData()
    {
        $rawContent = $this->getContent();
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

        return empty($data) ? null : $data;
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
        $classContent = $this->attribute->dataType()->classAttributeContent($this->attribute);
        $defaultPlacement = $classContent['default_selection_node'] ? $classContent['default_selection_node'] : null;

        $options = array(
            "helper" => $this->attribute->attribute('description'),
            'type' => 'relationbrowse',
            'browse' => array(
                "selectionType" => 'single',
                "addCloseButton" => true
            ),
        );

        if ($defaultPlacement){
            $options['browse']["subtree"] = $defaultPlacement;
        }

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

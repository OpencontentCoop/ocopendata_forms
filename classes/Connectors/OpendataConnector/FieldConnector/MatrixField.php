<?php
namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

class MatrixField extends FieldConnector
{
    public function getSchema()
    {
        $schema = array(
            "type" => "array",
            "title" => $this->attribute->attribute('name'),
            "items" => array(
                "type" => "object",
                "properties" => array()
            ),
            'minItems' => (bool)$this->attribute->attribute('is_required') ? 1 : 0
        );

        /** @var \eZMatrixDefinition $definition */
        $definition = $this->attribute->attribute('content');
        $columns = $definition->attribute('columns');
        foreach ($columns as $column) {
            $schema["items"]["properties"][$column['identifier']] = array(
                "title" => $column['name'],
                "type" => "string"
            );
        }

        return $schema;
    }

    public function getOptions()
    {
        return array(
            "helper" => $this->attribute->attribute('description'),
            "type" => "table"
        );
    }

    public function setPayload($postData)
    {
        $definition = $this->attribute->attribute('content');
        $columns = $definition->attribute('columns');
        $fixedPostData = array();
        if(is_array($postData)){
            foreach ($postData as $item) {
                foreach ($columns as $column) {
                    if (!isset($item[$column['identifier']])){
                        $item[$column['identifier']] = '';
                    }
                }
                $fixedPostData[] = $item;
            }
        }
        return $fixedPostData;
    }
}

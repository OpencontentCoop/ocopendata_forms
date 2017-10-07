<?php

namespace Opencontent\Ocopendata\Forms\Connectors;

use eZContentObject;
use eZContentOperationCollection;
use eZOperationHandler;

class DeleteObjectConnector extends AbstractBaseConnector
{
    private $object;

    public function runService($serviceIdentifier)
    {
        if ($this->getHelper()->hasParameter('object')){
        	$this->object = eZContentObject::fetch((int)$this->getHelper()->getParameter('object'));
        }

        if (!$this->object instanceof eZContentObject){
        	throw new Exception("Object not found", 1);
        	
        }

        if (!$this->object->canRemove()){
        	throw new Exception("Current user can not remove object #" . $this->getHelper()->getParameter('object'), 1);
        	
        }

        if ($serviceIdentifier == 'data') {
            return $this->getData();

        } elseif ($serviceIdentifier == 'schema') {
            return $this->getSchema();

        } elseif ($serviceIdentifier == 'options') {
            return $this->getOptions();

        } elseif ($serviceIdentifier == 'view') {
            return $this->getView();

        } elseif ($serviceIdentifier == 'action') {
            return $this->submit();
        }

        throw new \Exception("Connector service $serviceIdentifier not handled");
    }

    protected function getData()
    {
        return null;
    }

    protected function getSchema()
    {
        return array(
            "title" => "Sei sicuro di voler eliminare il contenuto " . $this->object->attribute('name') . '?',            
            "type" => "object",
            "properties" => array(
            	"trash" => array(                    
                    "type" => "boolean"
                ),
            )
        );
    }

    protected function getOptions()
    {
        return array(
            "form" => array(
                "attributes" => array(
                    "action" => $this->getHelper()->getServiceUrl('action', $this->getHelper()->getParameters()),
                    "method" => "post"
                ),                
                "buttons" => array(
                    "submit" => array()
                ),                
            ),
            "fields" => array(
                "trash" => array(                    
                    "type" => "checkbox",
                    "rightLabel" => 'Sposta nel cestino'
                ),
            ),
        );
    }

    protected function getView()
    {
        return array(
            "parent" => "bootstrap-edit",
            "locale" => "it_IT"
        );
    }

    protected function submit()
    {
        $moveToTrash = $_POST['trash'] == 'true';
        $deleteIDArray = array($this->object->mainNodeID());
        if (eZOperationHandler::operationIsAvailable('content_delete')) {
	        eZOperationHandler::execute('content',
	            'delete',
	            array(
	                'node_id_list' => $deleteIDArray,
	                'move_to_trash' => $moveToTrash
	            ),
	            null, true);
	    } else {
	        eZContentOperationCollection::deleteObject($deleteIDArray, $moveToTrash);
	    }

	    return true;
    }

    protected function upload()
    {
    	throw new Exception("Method not allowed", 1);
    	
    }
   
}

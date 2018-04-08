<?php

namespace Opencontent\Ocopendata\Forms\Connectors;

use eZContentObject;
use eZContentOperationCollection;
use eZOperationHandler;
use Exception;
use eZUser;

class DeleteObjectConnector extends AbstractBaseConnector
{
    /**
     * @var eZContentObject
     */
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
        
        } elseif ($serviceIdentifier == '') {
            return $this->getAll();

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
            "title" => "Sei sicuro di voler eliminare il contenuto (" . $this->object->attribute('class_name') . ') ' . $this->object->attribute('name') . '?',            
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
        $isUser = in_array($this->object->attribute('contentclass_id'), eZUser::contentClassIDs());
        $isUserLabel = $isUser ? ' (non è possibile usare il cestino per contenuti di tipo ' . $this->object->attribute('class_name') . ')' : '';

        $locationDescription = '';
        $locations = $this->object->assignedNodes();
        $countLocations = count($locations);
        if ($countLocations > 1){
            $locationDescription = "Verranno eliminate tutte le $countLocations collocazioni del contenuto: <ol>";
            $locationsNames = array();
            foreach ($locations as $location) {
                $locationsNames[] = '<li><a class="help-block" target="_blank" href="' . $location->attribute('url_alias') . '">' . $location->attribute('url_alias') . '</a></li>';
            }
            $locationDescription .= implode('', $locationsNames) . '</ol>';
        } 

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
                    "helper" => $locationDescription,
                    "type" => "checkbox",
                    "rightLabel" => 'Sposta nel cestino' . $isUserLabel,
                    'disabled' => $isUser
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
        $deleteIDArray = array();
        foreach ($this->object->assignedNodes() as $node) {
            $deleteIDArray[] = $node->attribute('node_id');
        }
        if (!empty($deleteIDArray)){
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
        }

	    return true;
    }

    protected function upload()
    {
    	throw new Exception("Method not allowed", 1);
    	
    }
   
}

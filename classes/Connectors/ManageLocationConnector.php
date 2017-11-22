<?php

namespace Opencontent\Ocopendata\Forms\Connectors;

use eZContentObject;
use eZContentObjectTreeNode;
use eZContentOperationCollection;
use eZOperationHandler;
use Exception;

class ManageLocationConnector extends AbstractBaseConnector
{
	/**
     * @var eZContentObjectTreeNode
     */
    private $destinationNode;

    /**
     * @var eZContentObjectTreeNode
     */
    private $sourceNode;

    private $defaultPlacement;

    private $classList;

    public function runService($serviceIdentifier)
    {
        if ($this->getHelper()->hasParameter('destination')){
        	$destinationNodeId = $this->getHelper()->getParameter('destination');
        	$this->destinationNode = eZContentObjectTreeNode::fetch($destinationNodeId);
        	if (!$this->destinationNode instanceof eZContentObjectTreeNode){
        		throw new Exception("Destination node $destinationNodeId not found", 1);        		
        	}

        	$canCreate = $this->destinationNode->checkAccess( 'create' ) || ($this->destinationNode->canAddLocation() && $this->destinationNode->canRead());
        	if (!$canCreate){
        		throw new Exception("User can not manage locations in node $destinationNodeId", 1);        		
        	}

        	if ($this->getHelper()->hasParameter('source-subtree')){
        		$this->defaultPlacement = $this->getHelper()->getParameter('source-subtree');
        	}

        	if ($this->getHelper()->hasParameter('source-classes')){
        		$this->classList = $this->getHelper()->getParameter('source-classes');
        	}
        }elseif ($this->getHelper()->hasParameter('source')){
        	$sourceNodeId = $this->getHelper()->getParameter('source');
        	$this->sourceNode = eZContentObjectTreeNode::fetch($sourceNodeId);
        	if (!$this->sourceNode instanceof eZContentObjectTreeNode){
        		throw new Exception("Source node $sourceNodeId not found", 1);        		
        	}

        	if (!$this->sourceNode->object()->checkAccess('edit') && !eZUser::currentUser()->attribute('has_manage_locations')){
        		throw new Exception("User can not manage locations in node $sourceNodeId", 1);  
        	}

        	if ($this->getHelper()->hasParameter('destination-subtree')){
        		$this->defaultPlacement = $this->getHelper()->getParameter('destination-subtree');
        	}

        	if ($this->getHelper()->hasParameter('destination-classes')){
        		$this->classList = $this->getHelper()->getParameter('destination-classes');
        	}
        }else{
        	throw new Exception("Missing parameters", 1);  
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
        $data = array();
        if ($this->sourceNode){        	
        	$assignedNodes = $this->sourceNode->object()->assignedNodes();
        	foreach ($assignedNodes as $node) {
        		$data['node-'.$node->attribute('node_id')] = true;
        	}        	
        }      
        return $data;
    }

    protected function getSchema()
    {
        if ($this->destinationNode){    	
        	return array( 	            
	            "title" => 'Gestisci collocazioni',
	            "type" => "object",
	            'properties' => array(
	            	'source-nodes' => array(
	            		"title" => 'Seleziona gli elementi da aggiungere a ' . $this->destinationNode->attribute('name'),
	            		'required' => true,
	            		'minItems' => 1,
	            		'type' => 'array'
	            	)
	            )
	        );
        }elseif ($this->sourceNode){
        	$properties = array();
        	$assignedNodes = $this->sourceNode->object()->assignedNodes();
        	foreach ($assignedNodes as $node) {
        		$properties['node-'.$node->attribute('node_id')] = array(
        			'type' => "boolean"
        		);
        	}
        	$properties['destination-nodes'] = array(
        		"title" => 'Aggiungi collocazioni per l\'elemento ' . $this->sourceNode->attribute('name'),
        		'type' => 'array'
        	);
        	return array( 	            
	            "title" => 'Gestisci collocazioni',
	            "type" => "object",
	            'properties' => $properties
	        );
        }        
    }

    protected function getOptions()
    {
        if ($this->destinationNode){        	
        	$fields = array( 
        		'source-nodes' => array(
            		"type" => 'locationbrowse',
            		"browse" => array(
	            		"subtree" => $this->defaultPlacement,
		                "classes" => (array)$this->classList,
		                "selectionType" => 'multiple',
		                "addCloseButton" => false,
		                "addCreateButton" => false,
		                "openInSearchMode" => true
		            )
            	)
	        );
        }elseif ($this->sourceNode){
        	$fields = array();
        	$assignedNodes = $this->sourceNode->object()->assignedNodes();
        	foreach ($assignedNodes as $node) {
        		$label = $node->attribute('parent')->attribute('name');
        		$isMain = $node->attribute('is_main');
        		if ($isMain){
        			$label .= ' (principale)';
        		}
        		$fields['node-'.$node->attribute('node_id')] = array(
	        		'type' => 'checkbox',
	            	'rightLabel' => $label,
	            	'disabled' => $isMain
	            );
        	}
			$fields['destination-nodes'] = array(
        		"type" => 'locationbrowse',
        		"browse" => array(
            		"subtree" => $this->defaultPlacement,
	                "classes" => (array)$this->classList,
	                "selectionType" => 'multiple',
	                "addCloseButton" => false,
	                "addCreateButton" => false
	            )
	        );
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
            "fields" => $fields
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
        $data = array(
	        'remove_list' => array(),
	        'add_list' => array(),
	    );
        if ($this->destinationNode){   
        	if (isset($_POST['source-nodes'])){
                foreach ($_POST['source-nodes'] as $item) {
                    $node = eZContentObjectTreeNode::fetch($item['node_id']);
                    if ($node instanceof eZContentObjectTreeNode){
                        if (eZOperationHandler::operationIsAvailable('content_addlocation')) {
                            $operationResult = eZOperationHandler::execute('content',
                                'addlocation', array(
                                    'node_id' => $node->attribute('node_id'),
                                    'object_id' => $node->attribute('contentobject_id'),
                                    'select_node_id_array' => array($this->destinationNode->attribute('node_id'))
                                ),
                                null,
                                true);
                        } else {
                            eZContentOperationCollection::addAssignment($node->attribute('node_id'), $node->attribute('contentobject_id'), array($this->destinationNode->attribute('node_id')));
                        }
                        $data['add_list'][] = $item['node_id'];
                    }
                }
            }
    	
    	}elseif ($this->sourceNode){
    		$removeList = array();
    		$assignedNodes = $this->sourceNode->object()->assignedNodes();	    		
    		foreach ($assignedNodes as $node) {        		
        		$isMain = $node->attribute('is_main');
        		if (!$isMain 
        			&& isset($_POST['node-'.$node->attribute('node_id')]) && $_POST['node-'.$node->attribute('node_id')] === 'false'
        			&& $node->canRemove()
        			&& $node->canRemoveLocation()
        			&& $node->childrenCount(false) == 0
        		){
        			$removeList[] = $node->attribute('node_id');
        		}
        	}
        	if (!empty($removeList)){
	        	if (eZOperationHandler::operationIsAvailable('content_removelocation')){
		            $operationResult = eZOperationHandler::execute( 'content',
		                                                            'removelocation', array('node_list' => $removeList),
		                                                            null,
		                                                            true );
		        }else{
		            eZContentOperationCollection::removeNodes($removeList);
		        }
		        $data['remove_list'] = $removeList;
		    }

		    if (isset($_POST['destination-nodes'])){
				$addList = array();
				foreach ($_POST['destination-nodes'] as $item) {
					$addList[] = $item['node_id'];
				}
				if (!empty($addList)){
					if (eZOperationHandler::operationIsAvailable('content_addlocation')) {
			            $operationResult = eZOperationHandler::execute('content',
			                'addlocation', array(
			                    'node_id' => $this->sourceNode->attribute('node_id'),
			                    'object_id' => $this->sourceNode->attribute('contentobject_id'),
			                    'select_node_id_array' => $addList
			                ),
			                null,
			                true);
			        } else {
			            eZContentOperationCollection::addAssignment($this->sourceNode->attribute('node_id'), $this->sourceNode->attribute('contentobject_id'), $addList);
			        }
					$data['add_list'] = $addList;
				}
		    }
    	}

    	return $data;
    }

    protected function upload()
    {
    	throw new Exception("Method upload not allowed", 1);    	
    }
}
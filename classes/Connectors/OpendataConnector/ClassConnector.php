<?php

namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector;

use eZContentClass;
use Opencontent\Opendata\Api\ContentRepository;
use Opencontent\Opendata\Api\EnvironmentLoader;
use Opencontent\Opendata\Rest\Client\PayloadBuilder;
use eZHTTPTool;

class ClassConnector implements ClassConnectorInterface
{
    const SELECT_PARENT_FIELD_IDENTIFIER = '_parent';

    /**
     * @var eZContentClass
     */
    protected $class;

    /**
     * @var FieldConnectorInterface[]
     */
    protected $fieldConnectors;

    /**
     * @var array
     */
    protected $fieldCategories;

    /**
     * @var ConnectorHelper
     */
    protected $helper;

    public function __construct(eZContentClass $class, $helper)
    {
        $this->class = $class;
        $this->helper = $helper;
    }


    public function getData($rawContent)
    {
        $content = array();
        if ($rawContent) {
            foreach ($this->getFieldConnectors() as $identifier => $connector) {
                $content[$identifier] = $connector->getData($rawContent[$identifier]);
            }
        }

        return $content;
    }


    public function getSchema()
    {
        $data = array(
            'title' => $this->class->attribute('name'),
            "description" => $this->class->attribute('description'),
            "type" => "object",
            "properties" => array()
        );

        foreach ($this->getFieldConnectors() as $identifier => $fieldConnector) {
            $data["properties"][$identifier] = $fieldConnector->getSchema();
        }

        if (!$this->getHelper()->hasParameter('parent')) {
            $data["properties"][self::SELECT_PARENT_FIELD_IDENTIFIER] = array(
                "title" => "Parent Node",
                'required' => true,
                'type' => 'array',
                'minItems' => 1
            );
        }

        return $data;
    }

    public function getOptions()
    {
        $data = array(
            "helper" => $this->class->attribute('description'),
            'hideInitValidationError' => true,
            "fields" => array()
        );

        foreach ($this->getFieldConnectors() as $identifier => $fieldConnector) {
            $data["fields"][$identifier] = $fieldConnector->getOptions();
            if (empty($data["fields"][$identifier])){
                unset($data["fields"][$identifier]);
            }
        }

        if (!$this->getHelper()->hasParameter('parent')) {
            $data["fields"][self::SELECT_PARENT_FIELD_IDENTIFIER] = array(
                "helper" => "Select a parent location",
                'type' => 'locationbrowse'
            );
        }

        return $data;
    }

    public function getView()
    {
        $view = array(
            "parent" => "bootstrap-edit",
            "locale" => "it_IT"
        );

        return $view;
    }

    public function getHelper()
    {
        return $this->helper;
    }

    public function submit()
    {
        $payload = new PayloadBuilder();
        $http = eZHTTPTool::instance();

        $isUpdate = false;
        if ($this->getHelper()->hasParameter('object')) {
            $payload->setId((int)$this->getHelper()->getParameter('object'));
            $isUpdate = true;
        }

        $payload->setClassIdentifier($this->class->attribute('identifier'));
        $payload->setLanguages(array($this->getHelper()->getSetting('language')));

        if ($this->getHelper()->hasParameter('parent')) {
            $parent = $this->getHelper()->getParameter('parent');
            if (is_numeric($parent)){
                $payload->setParentNode((int)$parent);
            }elseif(is_array($parent)){
                $parent = array_map('intval', $parent);
                $payload->setParentNodes($parent);
            }
        }elseif($http->hasPostVariable(self::SELECT_PARENT_FIELD_IDENTIFIER)){
            $parentData = $http->postVariable(self::SELECT_PARENT_FIELD_IDENTIFIER);
            foreach($parentData as $item){
                $payload->setParentNode((int)$item['node_id']);
            }
        }

        foreach ($this->getFieldConnectors() as $identifier => $connector) {

            $postData = $http->hasPostVariable($identifier) ? $http->postVariable($identifier) : null;
            if ($postData) {
                $data = $connector->setPayload($postData);
                if ($data !== null) {
                    $payload->setData(
                        $this->getHelper()->getSetting('language'),
                        $identifier,
                        $data
                    );
                }
            }
        }

        $contentRepository = new ContentRepository();
        $contentRepository->setEnvironment(EnvironmentLoader::loadPreset('content'));


        if ($isUpdate){
            $result = $contentRepository->update($payload->getArrayCopy());
        }else{
            $result = $contentRepository->create($payload->getArrayCopy());
        }

        foreach ($this->getFieldConnectors() as $identifier => $connector) {
            if ($connector instanceof UploadFieldConnector){
                $connector->cleanup();
            }
        }
        return $result;
    }

    public function upload()
    {
        if ($this->getHelper()->hasParameter('attribute')){
            $identifier = $this->getHelper()->getParameter('attribute');
            $connectors = $this->getFieldConnectors();
            if (isset($connectors[$identifier])){
                $connector = $connectors[$identifier];
                if ($connector instanceof UploadFieldConnector){
                    return $connector->handleUpload();
                }
            }
        }

        return false;
    }

    protected function getFieldConnectors()
    {
        if ($this->fieldConnectors === null) {
            /** @var \eZContentClassAttribute[] $classDataMap */
            $classDataMap = $this->class->dataMap();
            $defaultCategory = \eZINI::instance('content.ini')->variable('ClassAttributeSettings', 'DefaultCategory');
            foreach ($classDataMap as $identifier => $attribute) {

                $category = $attribute->attribute('category');
                if (empty($category)){
                    $category = $defaultCategory;
                }

                $add = true;

                if ((bool)$this->getHelper()->getSetting('OnlyRequired')) {
                    $add = (bool)$attribute->attribute('is_required');
                }

                if ($add == true && $this->getHelper()->hasSetting('ShowCategories')){
                    $add = in_array($category, (array)$this->getHelper()->getSetting('Categories'));
                }

                if ($add == true && $this->getHelper()->hasSetting('HideCategories')){
                    $add = !in_array($category, (array)$this->getHelper()->getSetting('HideCategories'));
                }

                if ($add) {
                    $this->fieldConnectors[$identifier] = FieldConnectorFactory::load(
                        $attribute,
                        $this->class,
                        $this->getHelper()
                    );
                }
            }
        }

        return $this->fieldConnectors;
    }

    protected function getFieldCategories()
    {
        if ($this->fieldCategories === null){

            $this->fieldCategories = array();

            if ($this->getHelper()->hasSetting('SplitAttributeCategories')){
                $defaultCategory = \eZINI::instance('content.ini')->variable('ClassAttributeSettings', 'DefaultCategory');
                $categoryNames = \eZINI::instance('content.ini')->variable('ClassAttributeSettings', 'CategoryList');

                foreach ($this->getFieldConnectors() as $identifier => $fieldConnector) {
                    $category = $fieldConnector->getAttribute()->attribute('category');
                    if (empty($category)){
                        $category = $defaultCategory;
                    }
                    if (!isset($this->fieldCategories[$category])){
                        $this->fieldCategories[$category] = array(
                            'name' => $categoryNames[$category],
                            'identifiers' => array()
                        );
                    }
                    $this->fieldCategories[$category]['identifiers'] = $identifier;
                }
            }
        }

        return $this->fieldCategories;
    }


}

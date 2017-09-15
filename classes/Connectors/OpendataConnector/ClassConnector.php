<?php

namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector;

use eZContentClass;
use Opencontent\Opendata\Api\ContentRepository;
use Opencontent\Opendata\Api\EnvironmentLoader;
use Opencontent\Opendata\Rest\Client\PayloadBuilder;
use eZHTTPTool;

class ClassConnector implements ClassConnectorInterface
{
    const SELECT_PARENT_FIELD_IDENTIFIER = 'add-assignments';

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

    protected $content;

    protected $submitData;

    public function __construct(eZContentClass $class, $helper)
    {
        $this->class = $class;
        $this->helper = $helper;
    }


    public function getData()
    {
        $content = array();
        $rawContent = $this->getContent();
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
                'type' => 'array'
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
        $baseView = 'create';
        if ($this->getHelper()->hasParameter('object') || $this->getHelper()->hasParameter('from')){
            $baseView = 'edit';
        }
        $view = array(
            "parent" => "bootstrap-{$baseView}",
            "locale" => "it_IT"
        );

        if ($this->getHelper()->hasSetting('SplitAttributeCategories')) {
            $view['layout'] = $this->getSplitAttributeCategoriesLayout();
        }

        return $view;
    }

    public function getHelper()
    {
        return $this->helper;
    }

    public function submit()
    {
        $payload = $this->getPayloadFromArray($this->getSubmitData());

        $result = $this->doSubmit($payload);

        return $result;
    }

    protected function doSubmit(PayloadBuilder $payload)
    {
        $contentRepository = new ContentRepository();
        $contentRepository->setEnvironment(EnvironmentLoader::loadPreset('content'));

        if ($this->isUpdate()){
            $result = $contentRepository->update($payload->getArrayCopy());
        }else{
            $result = $contentRepository->create($payload->getArrayCopy());
        }

        $this->cleanup();

        return $result;
    }

    protected function cleanup()
    {
        foreach ($this->getFieldConnectors() as $identifier => $connector) {
            if ($connector instanceof UploadFieldConnector){
                $connector->cleanup();
            }
        }
    }

    protected function isUpdate()
    {
        return $this->getHelper()->hasParameter('object');
    }

    protected function getPayloadFromPostData()
    {
        return $this->getPayloadFromArray($_POST);
    }

    protected function getPayloadFromArray(array $data)
    {
        $payload = new PayloadBuilder();

        if ($this->getHelper()->hasParameter('object')) {
            $payload->setId((int)$this->getHelper()->getParameter('object'));
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
        }elseif(isset($data[self::SELECT_PARENT_FIELD_IDENTIFIER])){
            $parentData = $data[self::SELECT_PARENT_FIELD_IDENTIFIER];
            foreach($parentData as $item){
                $payload->setParentNode((int)$item['node_id']);
            }
        }

        foreach ($this->getFieldConnectors() as $identifier => $connector) {
            $postData = isset($data[$identifier]) ? $data[$identifier] : null;
            if ($postData) {
                $payloadData = $connector->setPayload($postData);
                if ($payloadData !== null) {
                    $payload->setData(
                        $this->getHelper()->getSetting('language'),
                        $identifier,
                        $payloadData
                    );
                }
            }
        }

        return $payload;
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

    public function getFieldConnectors()
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
                    $this->fieldCategories[$category]['identifiers'][] = $identifier;
                }
            }
        }

        return $this->fieldCategories;
    }

    protected function getSplitAttributeCategoriesLayout()
    {
        $categories = $this->getFieldCategories();
        $bindings = array();
        $tabs = '<ul class="nav nav-tabs">';
        $panels = '<div class="tab-content">';
        $i = 0;

        foreach ($categories as $identifier => $category) {
            $tabClass = $i == 0 ? ' class="active"' : '';
            $panelClass = $i == 0 ? ' active' : '';
            $tabs .= '<li' . $tabClass . '><a data-toggle="tab" href="#attribute-group-' . $identifier . '">' . $category['name'] . '</a></li>';
            $panels .= '<div class="clearfix tab-pane' . $panelClass . '" id="attribute-group-' . $identifier . '"></div>';
            foreach($category['identifiers'] as $field){
                $bindings[$field] = 'attribute-group-' . $identifier;
            }
            $i++;
        }
        $tabs .= '</ul>';
        $panels .= '</div>';

        if (!$this->getHelper()->hasParameter('parent')) {
            $panels .= '<div class="clearfix" id="attribute-group-' . self::SELECT_PARENT_FIELD_IDENTIFIER . '"></div>';
            $bindings[self::SELECT_PARENT_FIELD_IDENTIFIER] = 'attribute-group-' . self::SELECT_PARENT_FIELD_IDENTIFIER;
        }

        if (count($categories) == 1){
            $tabs = '';
        }

        return array(
            'template' => '<div><legend class="alpaca-container-label">{{options.label}}</legend>' . $tabs . $panels . '</div>',
            'bindings' => $bindings
        );
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getSubmitData()
    {
        return $this->submitData;
    }

    /**
     * @param mixed $submitData
     */
    public function setSubmitData($submitData)
    {
        $this->submitData = $submitData;
    }

}

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

    protected $copyFieldsFromPrevVersion = array();

    public function __construct(eZContentClass $class, $helper)
    {
        $this->class = $class;
        $this->helper = $helper;
    }

    protected function copyFieldFromPrevVersion($identifier)
    {
        $this->copyFieldsFromPrevVersion[$identifier] = $identifier;
    }

    public function getData()
    {
        $content = array();
        foreach ($this->getFieldConnectors() as $identifier => $connector) {
            $content[$identifier] = $connector->getData();
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
            if ($this->isDisplay()) {
                $connectorData = $fieldConnector->getData();
                if (empty( $connectorData )) {
                    unset( $data["properties"][$identifier] );
                } else {
                    unset( $data["properties"][$identifier]["required"] );
                }
            }
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
            if (empty( $data["fields"][$identifier] )) {
                unset( $data["fields"][$identifier] );
            }
            if ($this->isDisplay()) {
                unset( $data["fields"][$identifier]["helper"] );
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
        if ($this->getHelper()->hasParameter('object') || $this->getHelper()->hasParameter('from')) {
            $baseView = 'edit';
        }

        if ($this->getHelper()->hasParameter('view')) {
            $baseView = $this->getHelper()->getParameter('view');
        }

        $view = array(
            "parent" => $this->getAlpacaBaseDesign() . "-" . $baseView,
            "locale" => $this->getAlpacaLocale()
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

        if ($this->isUpdate()) {
            $result = $contentRepository->update($payload->getArrayCopy());
        } else {
            $result = $contentRepository->create($payload->getArrayCopy());
        }

        $this->cleanup();

        return $result;
    }

    protected function cleanup()
    {
        foreach ($this->getFieldConnectors() as $identifier => $connector) {
            if ($connector instanceof UploadFieldConnector) {
                $connector->cleanup();
            }
        }
    }

    protected function isCreate()
    {
        return $this->getHelper()->hasParameter('object') === false;
    }

    protected function isUpdate()
    {
        return $this->getHelper()->hasParameter('object');
    }

    protected function isDisplay()
    {
        return $this->getHelper()->hasParameter('view') && $this->getHelper()->getParameter('view') == 'display';
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
            if (is_numeric($parent)) {
                $payload->setParentNode((int)$parent);
            } elseif (is_array($parent)) {
                $parent = array_map('intval', $parent);
                $payload->setParentNodes($parent);
            }
        } elseif (isset( $data[self::SELECT_PARENT_FIELD_IDENTIFIER] )) {
            $parentData = $data[self::SELECT_PARENT_FIELD_IDENTIFIER];
            foreach ($parentData as $item) {
                $payload->setParentNode((int)$item['node_id']);
            }
        }

        foreach ($this->getFieldConnectors() as $identifier => $connector) {
            $postData = isset( $data[$identifier] ) ? $data[$identifier] : null;
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

        $payload->setOption('update_null_field', true);
        $payload->setOption('copy_prev_version_fields', $this->copyFieldsFromPrevVersion);

        return $payload;
    }

    public function upload()
    {
        if ($this->getHelper()->hasParameter('attribute')) {
            $id = $this->getHelper()->getParameter('attribute');
            $connectors = $this->getFieldConnectors();
            foreach($connectors as $connector) {
                if ($connector->getAttribute()->attribute('id') == $id) {
                    if ($connector instanceof UploadFieldConnector) {
                        return $connector->handleUpload(
                            $this->getHelper()->getSetting('upload_param_name_prefix')
                        );
                    }
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
                if (empty( $category )) {
                    $category = $defaultCategory;
                }

                $add = true;

                if ((bool)$this->getHelper()->getSetting('OnlyRequired')) {
                    $add = (bool)$attribute->attribute('is_required');
                }

                if ($add == true && $this->getHelper()->hasSetting('ShowCategories')) {
                    $add = in_array($category, (array)$this->getHelper()->getSetting('Categories'));
                }

                if ($add == true && $this->getHelper()->hasSetting('HideCategories')) {
                    $add = !in_array($category, (array)$this->getHelper()->getSetting('HideCategories'));
                }

                if ($add) {
                    $this->fieldConnectors[$identifier] = FieldConnectorFactory::load(
                        $attribute,
                        $this->class,
                        $this->getHelper()
                    );
                }else{
                    $this->copyFieldFromPrevVersion($identifier);
                }
            }
        }

        return $this->fieldConnectors;
    }

    public function getFieldConnector($identifier)
    {
        $this->getFieldConnectors();

        return isset( $this->fieldConnectors[$identifier] ) ? $this->fieldConnectors[$identifier] : null;
    }

    protected function getFieldCategories()
    {
        if ($this->fieldCategories === null) {

            $this->fieldCategories = array();

            if ($this->getHelper()->hasSetting('SplitAttributeCategories')) {
                $defaultCategory = \eZINI::instance('content.ini')->variable('ClassAttributeSettings', 'DefaultCategory');
                $categoryNames = \eZINI::instance('content.ini')->variable('ClassAttributeSettings', 'CategoryList');

                foreach ($this->getFieldConnectors() as $identifier => $fieldConnector) {
                    $category = $fieldConnector->getAttribute()->attribute('category');
                    if (empty( $category )) {
                        $category = $defaultCategory;
                    }
                    if (!isset( $this->fieldCategories[$category] )) {
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
            foreach ($category['identifiers'] as $field) {
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

        if (count($categories) == 1) {
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
        if (is_array($this->content)) {
            foreach ($this->getFieldConnectors() as $identifier => $connector) {
                if (isset( $this->content[$identifier] )) {
                    $connector->setContent($this->content[$identifier]);
                }
            }
        }
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

    protected function getAlpacaLocale()
    {
        $localeMap = array(
            'eng-GB' => false,
            'chi-CN' => 'zh_CN',
            'cze-CZ' => 'cs_CZ',
            'cro-HR' => 'hr_HR',
            'dut-NL' => 'nl_BE',
            'fin-FI' => 'fi_FI',
            'fre-FR' => 'fr_FR',
            //'ger-DE' => 'de_AT',
            'ger-DE' => 'de_DE',
            'ell-GR' => 'el_GR',
            'ita-IT' => 'it_IT',
            'jpn-JP' => 'ja_JP',
            'nor-NO' => 'nb_NO',
            'pol-PL' => 'pl_PL',
            'por-BR' => 'pt_BR',
            'esl-ES' => 'es_ES',
            'swe-SE' => 'sv_SE',
        );

        $currentLanguage = $this->getHelper()->getSetting('language');

        return isset($localeMap[$currentLanguage]) ? $localeMap[$currentLanguage] : "it_IT";
    }

    protected function getAlpacaBaseDesign()
    {
        return "bootstrap";
    }

}

<?php

namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector;

use eZContentClass;
use eZContentClassAttribute;

class FieldConnector implements FieldConnectorInterface
{
    /**
     * @var eZContentClass
     */
    protected $class;

    /**
     * @var eZContentClassAttribute
     */
    protected $attribute;

    /**
     * @var ConnectorHelper
     */
    protected $helper;

    protected $content;

    /**
     * FieldConnector constructor.
     *
     * @param eZContentClassAttribute $attribute
     * @param eZContentClass $class
     * @param ConnectorHelper $helper
     */
    public function __construct($attribute, $class, $helper)
    {
        $this->attribute = $attribute;
        $this->class = $class;
        $this->helper = $helper;
    }

    protected function getIdentifier()
    {
        return $this->attribute->attribute('identifier');
    }

    public function getData()
    {
        $rawContent = $this->getContent();
        return $rawContent ? $rawContent['content'] : null;
    }

    public function getSchema()
    {
        return array(
            "type" => "string",
            "title" => $this->attribute->attribute('name'),
            'required' => (bool)$this->attribute->attribute('is_required')
        );
    }

    public function getOptions()
    {
        return array(
            "helper" => $this->attribute->attribute('description'),
            'disabled' => true,
            'readonly' => true
        );
    }

    public function setHelper($helper)
    {
        $this->helper = $helper;
    }

    public function getHelper()
    {
        return $this->helper;
    }

    protected function getServiceUrl($serviceIdentifier, $extraParameters = array())
    {
        $actionParameters = $this->getHelper()->getParameters();
        $actionParameters['attribute'] = $this->attribute->attribute('identifier');

        return $this->getHelper()->getServiceUrl(
            $serviceIdentifier,
            array_merge(
                $extraParameters,
                $actionParameters
            )
        );
    }

    public function handleUpload()
    {
        return false;
    }

    public function setPayload($postData)
    {
        return $postData;
    }

    /**
     * @return eZContentClass
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return eZContentClassAttribute
     */
    public function getAttribute()
    {
        return $this->attribute;
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
}

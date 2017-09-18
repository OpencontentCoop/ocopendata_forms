<?php

namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector;

use eZContentClass;
use eZContentClassAttribute;

interface FieldConnectorInterface
{
    /**
     * FieldConnector constructor.
     *
     * @param eZContentClassAttribute $attribute
     * @param eZContentClass $class
     * @param ConnectorHelper $helper
     */
    public function __construct($attribute, $class, $helper);

    public function getData();

    public function getSchema();

    public function getOptions();

    public function setHelper($helper);

    public function getHelper();

    public function setPayload($postData);

    /**
     * @return eZContentClass
     */
    public function getClass();

    /**
     * @return eZContentClassAttribute
     */
    public function getAttribute();

    public function setContent($content);

    public function getContent();
}

<?php

namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector;
use eZContentClass;

interface ClassConnectorInterface
{
    public function __construct(eZContentClass $class, $helper);

    public function getData();

    public function getSchema();

    public function getOptions();

    public function getView();

    public function getHelper();

    public function submit();

    public function upload();

    public function getFieldConnectors();

    public function getFieldConnector($identifier);

    public function setContent($content);

    public function getContent();

    public function getSubmitData();

    public function setSubmitData($submitData);
}

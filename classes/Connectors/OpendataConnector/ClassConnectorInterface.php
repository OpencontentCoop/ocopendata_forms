<?php

namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector;
use eZContentClass;

interface ClassConnectorInterface
{
    public function __construct(eZContentClass $class, $helper);

    public function getData($content);

    public function getSchema();

    public function getOptions();

    public function getView();

    public function getHelper();

    public function submit();

    public function upload();
}

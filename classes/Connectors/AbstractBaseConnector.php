<?php

namespace Opencontent\Ocopendata\Forms\Connectors;

use Opencontent\Ocopendata\Forms\ConnectorInterface;
use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\ConnectorHelper;


abstract class AbstractBaseConnector implements ConnectorInterface
{
    const BASE_URI = 'forms/connector/';

    protected $identifier;

    protected $settings;

    protected $helper;

    public function __construct($identifier)
    {
        $this->identifier = $identifier;
        $this->helper = new ConnectorHelper($this->getIdentifier());
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function runService($serviceIdentifier)
    {
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

        } elseif ($serviceIdentifier == 'upload') {
            return $this->upload();

        } elseif ($serviceIdentifier == '') {
            return $this->getAll();

        }

        throw new \Exception("Connector service $serviceIdentifier not handled");
    }

    protected function getAll()
    {
        return array(
            'data' => $this->getData(),
            'options' => $this->getOptions(),
            'schema' => $this->getSchema(),
            'view' => $this->getView(),
        );
    }

    /**
     * @return ConnectorHelper
     */
    public function getHelper()
    {
        return $this->helper;
    }

    public function setSettings($settings)
    {
        $this->settings = $settings;
        $this->helper->setSettings($this->settings);
        if (isset($this->settings['DefaultParameters'])){
            foreach((array)$this->settings['DefaultParameters'] as $key => $value){
                if (is_string($key)){
                    $this->helper->setParameter($key, $value);
                }
            }
        }
    }

    public function getSettings()
    {
        return $this->settings;
    }

    public function setSetting($key, $value)
    {
        $this->settings[$key] = $value;
        $this->helper->setSettings($this->settings);
    }

    public function getSetting($key)
    {
        return isset($this->settings[$key]) ? $this->settings[$key] : null;
    }

    public function hasSetting($key)
    {
        return isset($this->settings[$key]);
    }

    public function setParameter($key, $value)
    {
        $this->helper->setParameter($key, $value);
    }

    public function getParameter($key)
    {
        return $this->helper->getParameter($key);
    }

    public function hasParameter($key)
    {
        return $this->helper->hasParameter($key);
    }

    abstract protected function getData();

    abstract protected function getSchema();

    abstract protected function getOptions();

    abstract protected function getView();

    abstract protected function submit();

    abstract protected function upload();
}

<?php

namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector;


class ConnectorHelper
{
    const BASE_URI = 'forms/connector/';

    private $identifier;

    private $settings = array();

    private $parameters = array();

    public function __construct($connectorIdentifier)
    {
        $this->identifier = $connectorIdentifier;
    }

    public function __clone()
    {
        $this->parameters = array();
    }

    public function getServiceUrl($serviceIdentifier, array $params = array())
    {
        $suffix = '';
        if (!empty($params)){
            $suffix .= '?' . http_build_query($params);
        }
        $url = self::BASE_URI . "{$this->identifier}/{$serviceIdentifier}";
        \eZURI::transformURI($url, false, 'full');

        $url .= $suffix;
        return $url;
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @return mixed
     */
    public function getSetting($key)
    {
        return isset($this->settings[$key]) ? $this->settings[$key] : null;
    }

    /**
     * @return mixed
     */
    public function hasSetting($key)
    {
        return isset($this->settings[$key]);
    }

    /**
     * @param array $settings
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;
    }

    /**
     * @param $key
     * @param $value
     */
    public function setSetting($key, $value)
    {
        $this->settings[$key] = $value;
    }

    /**
     * @return mixed
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param $key
     * @param $value
     */
    public function setParameter($key, $value)
    {
        $this->parameters[$key] = $value;
    }

    /**
     * @return mixed
     */
    public function getParameter($key)
    {
        return isset($this->parameters[$key]) ? $this->parameters[$key] : null;
    }

    /**
     * @return mixed
     */
    public function hasParameter($key)
    {
        return isset($this->parameters[$key]);
    }


}

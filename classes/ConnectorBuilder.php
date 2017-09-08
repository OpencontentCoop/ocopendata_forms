<?php

namespace Opencontent\Ocopendata\Forms;

class ConnectorBuilder
{
    private static $connectorInstances = array();

    private $ini;

    private $availableConnectors;

    public function __construct()
    {
        $this->ini = \eZINI::instance('ocopendata_forms.ini');
        if ($this->ini->hasVariable('ConnectorSettings', 'AvailableConnectors')){
            $this->availableConnectors = (array)$this->ini->variable('ConnectorSettings', 'AvailableConnectors');
        }
    }

    /**
     * @param string $identifier
     * @return ConnectorInterface;
     * @throws Exception
     */
    public function build($identifier)
    {
        if (in_array($identifier, $this->availableConnectors)){
            return $this->getConnectorInstance($identifier);
        }

        throw new Exception("Connector $identifier not found");
    }

    private function getConnectorInstance($identifier)
    {
        if (!isset(self::$connectorInstances[$identifier])) {
            $connector = null;
            if ($this->ini->hasGroup("{$identifier}_ConnectorSettings")) {
                $settings = $this->ini->group("{$identifier}_ConnectorSettings");
                if (isset( $settings['PHPClass'] )) {
                    $phpClass = $settings['PHPClass'];
                    if (class_exists($phpClass)) {
                        $connector = new $phpClass($identifier);
                        if ($connector instanceof ConnectorInterface) {
                            unset( $settings['PHPClass'] );
                            $connector->setSettings($settings);
                        } else {
                            throw new \Exception("Connector $phpClass must implement Opencontent\Ocopendata\Forms\ConnectorInterface");
                        }
                    }
                }
            }
            if (!$connector instanceof ConnectorInterface) {
                throw new \Exception("Connector $identifier misconfigured");
            }

            self::$connectorInstances[$identifier] = $connector;
        }

        return self::$connectorInstances[$identifier];
    }
}

<?php

namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector;

use eZContentClass;
use eZINI;

class ClassConnectorFactory
{
    /**
     * @param eZContentClass $class
     *
     * @return ClassConnector|ClassConnectorInterface
     * @throws \Exception
     */
    public static function load(eZContentClass $class, $helper)
    {
        $settings = eZINI::instance('ocopendata_connectors.ini')->group('ClassSettings');

        if (isset( $settings['ClassConnectors'][$class->attribute('identifier')] )) {
            $customClassConnector = $settings['ClassConnectors'][$class->attribute('identifier')];
            $connector = new $customClassConnector($class, $helper);

        }else{
            $defaultClassConnector = $settings['DefaultClassConnector'];
            $connector = new $defaultClassConnector($class, $helper);
        }

        if ($connector instanceof ClassConnectorInterface){
            return $connector;
        }

        throw new \Exception("Class connector not found");
    }
}

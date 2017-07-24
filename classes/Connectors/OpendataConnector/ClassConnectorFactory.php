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
            $classConnectorName = $settings['ClassConnectors'][$class->attribute('identifier')];
        }else{
            $classConnectorName = $settings['DefaultClassConnector'];
        }

        return self::instance($classConnectorName, $class, $helper);

    }

    /**
     * @param string $classConnectorName
     * @param eZContentClass $class
     * @param $helper
     *
     * @return mixed
     * @throws \Exception
     */
    public static function instance($classConnectorName, eZContentClass $class, $helper)
    {
        if (!class_exists($classConnectorName)){
            throw new \Exception("Class connector $classConnectorName not found");
        }

        $connector = new $classConnectorName($class, $helper);
        if ($connector instanceof ClassConnectorInterface){
            return $connector;
        }

        throw new \Exception("Class connector misconfigured");
    }
}

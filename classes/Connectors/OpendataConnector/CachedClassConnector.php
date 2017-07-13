<?php

namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector;

use eZClusterFileHandler;
use eZDir;
use eZSys;

/*
 * @todo trovare il modo di gestire i Parameters
 */

class CachedClassConnector extends ClassConnector
{
    public function getSchema()
    {
        $connector = $this;
        return $this->getCacheManager( $this->class->attribute('identifier') . '_schema' )->processCache(
            function($file) use($connector){
                $content = include( $file );

                return $content;
            },
            function() use($connector){
                return array(
                    'content' => $connector->generateSchema(),
                    'scope' => 'ocopendata-cache',
                    'datatype' => 'php',
                    'store' => true
                );
            }
        );
    }

    public function getOptions()
    {
        $connector = $this;
        return $this->getCacheManager( $this->class->attribute('identifier') . '_options' )->processCache(
            function($file) use($connector){
                $content = include( $file );

                return $content;
            },
            function() use($connector){

                return array(
                    'content' => $connector->generateOptions(),
                    'scope' => 'ocopendata-cache',
                    'datatype' => 'php',
                    'store' => true
                );
            }
        );
    }

    public function getView()
    {
        $connector = $this;
        return $this->getCacheManager( $this->class->attribute('identifier') . '_options' )->processCache(
            function($file) use($connector){
                $content = include( $file );

                return $content;
            },
            function() use($connector){

                return array(
                    'content' => $connector->generateView(),
                    'scope' => 'ocopendata-cache',
                    'datatype' => 'php',
                    'store' => true
                );
            }
        );
    }

    protected function generateSchema()
    {
        return parent::getSchema();
    }

    protected function generateOptions()
    {
        return parent::getOptions();
    }

    protected function generateView()
    {
        return parent::getView();
    }

    protected function getCacheManager( $identifier )
    {
        $cacheFile = $identifier . '.cache';
        $cacheFilePath = eZDir::path(
            array( eZSys::cacheDirectory(), 'ocopendata', 'class', 'form', $this->getHelper()->getSetting('language'), $cacheFile )
        );

        return eZClusterFileHandler::instance( $cacheFilePath );
    }
}

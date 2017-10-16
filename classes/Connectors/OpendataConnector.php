<?php

namespace Opencontent\Ocopendata\Forms\Connectors;

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\ClassConnectorInterface;
use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\ClassConnectorFactory;
use eZContentClass;
use eZContentObject;
use Opencontent\Opendata\Api\EnvironmentLoader;
use Opencontent\Opendata\Api\ContentRepository;

class OpendataConnector extends AbstractBaseConnector
{
    /**
     * @var eZContentClass
     */
    protected $class;

    /**
     * @var eZContentObject
     */
    protected $object;

    /**
     * @var ClassConnectorInterface
     */
    protected $classConnector;

    protected $language;

    private static $isLoaded;

    private static $availableParameters = array(
        'class',
        'object',
        'attribute',
        'preview',
        'delete',
        'parent',
        'from',
        'view',
    );

    protected function load()
    {
        if (!self::$isLoaded){

            $this->language = \eZLocale::currentLocaleCode();

            $this->getHelper()->setSetting('language', $this->language);

            if ($this->hasParameter('class')) {
                $classIdentifier = $this->getParameter('class');
                $this->class = eZContentClass::fetchByIdentifier($classIdentifier);
                if (!$this->class instanceof eZContentClass) {
                    throw new \Exception("Class $classIdentifier not found");
                }
            } elseif ($this->hasParameter('object')) {
                $this->object = eZContentObject::fetch((int)$this->getParameter('object'));
                if ($this->object instanceof eZContentObject) {
                    $this->class = $this->object->contentClass();
                    $parents = $this->object->assignedNodes(false);
                    $parentsIdList = array_column($parents, 'parent_node_id');
                    $this->getHelper()->setParameter('parent', $parentsIdList);
                }
            }

            if ($this->hasParameter('from')) {
                $this->object = eZContentObject::fetch((int)$this->getParameter('from'));
                if ($this->object instanceof eZContentObject) {
                    $this->class = $this->object->contentClass();
                }
            }

            if ($this->object instanceof eZContentObject) {
                if (!$this->object->canRead()) {
                    throw new \Exception("User can not read object #" . $this->object->attribute('id'));
                }
                if (!$this->object->canEdit() && $this->getHelper()->getParameter('view') != 'display') {
                    throw new \Exception("User can not edit object #" . $this->object->attribute('id'));
                }
            }

            if (!$this->class instanceof eZContentClass) {
                throw new \Exception("Missing class/object parameter");
            }

            if ($this->getHelper()->hasSetting('ClassConnector')){
                $this->classConnector = ClassConnectorFactory::instance($this->getHelper()->getSetting('ClassConnector'), $this->class, $this->getHelper());
            }else{
                $this->classConnector = ClassConnectorFactory::load($this->class, $this->getHelper());
            }

            if ($this->object instanceof eZContentObject) {
                $contentRepository = new ContentRepository();
                $currentEnvironment = EnvironmentLoader::loadPreset('full');
                $contentRepository->setEnvironment($currentEnvironment);

                $data = (array)$contentRepository->read( $this->object->attribute('id') );
                $locale = \eZLocale::currentLocaleCode();
                if (isset($data['data'][$locale])){
                    $this->classConnector->setContent($data['data'][$locale]);
                }
            }

            self::$isLoaded = true;
        }
    }

    public function setParameter($key, $value)
    {
        if (in_array($key, self::$availableParameters)){
            parent::setParameter($key, $value);
        }
    }

    public function runService($serviceIdentifier)
    {
        $this->load();
        if ($serviceIdentifier == 'debug') {
            return $this->getDebug();
        }
        return parent::runService($serviceIdentifier);
    }

    protected function getDebug()
    {
        $data = array(
            'connector' => get_called_class(),
            'class' => get_class($this->classConnector),
            'attributes' => array()
        );
        foreach($this->classConnector->getFieldConnectors() as $identifier => $connector){
            $data['attributes'][$identifier] = get_class($connector);
        }

        return $data;
    }

    protected function getData()
    {
        return $this->classConnector->getData();
    }

    protected function getSchema()
    {
        return $this->classConnector->getSchema();
    }

    protected function getOptions()
    {
        return array_merge_recursive(
            array(
                "form" => array(
                    "attributes" => array(
                        "class" => 'opendata-connector',
                        "action" => $this->getHelper()->getServiceUrl('action', $this->getHelper()->getParameters()),
                        "method" => "post",
                        "enctype" => "multipart/form-data"
                    )
                ),
            ),
            $this->classConnector->getOptions()
        );
    }

    protected function getView()
    {
        return $this->classConnector->getView();
    }

    protected function submit()
    {
        $this->classConnector->setSubmitData($_POST);
        return $this->classConnector->submit();
    }

    protected function upload()
    {
        return $this->classConnector->upload();
    }
}

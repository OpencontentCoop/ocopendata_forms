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
    private $class;

    /**
     * @var eZContentObject
     */
    private $object;

    /**
     * @var ClassConnectorInterface
     */
    private $classConnector;

    private $language;

    private static $isLoaded;

    private static $availableParameters = array(
        'class',
        'object',
        'attribute',
        'preview',
        'delete',
        'parent',
        'from',
    );

    private function load()
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
                    if (!$this->object->canRead()) {
                        throw new \Exception("User can not read object #" . $this->getParameter('object'));
                    }
                    $this->class = $this->object->contentClass();
                    $parents = $this->object->assignedNodes(false);
                    $parentsIdList = array_column($parents, 'node_id');
                    $this->getHelper()->setParameter('parent', $parentsIdList);
                }
            } elseif ($this->hasParameter('from')) {
                $this->object = eZContentObject::fetch((int)$this->getParameter('from'));
                if ($this->object instanceof eZContentObject) {
                    if (!$this->object->canRead()) {
                        throw new \Exception("User can not read object #" . $this->getParameter('from'));
                    }
                    $this->class = $this->object->contentClass();
                }
            }

            if (!$this->class instanceof eZContentClass) {
                throw new \Exception("Missing class/object parameter");
            }

            $this->classConnector = ClassConnectorFactory::load($this->class, $this->getHelper());

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
        return parent::runService($serviceIdentifier);
    }

    protected function getData()
    {
        $content = null;
        if ($this->object instanceof eZContentObject) {
            $contentRepository = new ContentRepository();
            $currentEnvironment = EnvironmentLoader::loadPreset('full');
            $contentRepository->setEnvironment($currentEnvironment);

            $data = (array)$contentRepository->read( $this->object->attribute('id') );
            $locale = \eZLocale::currentLocaleCode();
            if (isset($data['data'][$locale])){
                $content = $data['data'][$locale];
            }
        }

        return $this->classConnector->getData($content);
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
        return $this->classConnector->submit();
    }

    protected function upload()
    {
        return $this->classConnector->upload();
    }
}

<?php

namespace Opencontent\Ocopendata\Forms\EzOnlineEditor;


class ModuleHelper
{
    private $params;

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array|mixed
     * @throws \Exception
     */
    public function getViewResult()
    {
        $view = null;

        switch ($this->params['View']) {
            case 'tags':
                {
                    $tagName = isset($this->params['Param1']) ? strtolower(trim($this->params['Param1'])) : '';
                    $customTagName = isset($this->params['Param2']) ? trim($this->params['Param2']) : '';
                    $view = new TagsView($tagName, $customTagName);
                }
                break;

            case 'upload':
                {
                    $contentType = isset($this->params['Param1']) ? $this->params['Param1'] : 'objects';
                    $forcedUpload = isset($this->params['Param2']) ? $this->params['Param2'] : false;
                    $view = new UploadView($contentType, $forcedUpload);
                }
                break;

            case 'relations':
                {
                    $contentType = isset($this->params['Param1']) ? $this->params['Param1'] : 'objects';
                    $embedId = isset($this->params['Param2']) ? $this->params['Param2'] : false;
                    $embedInline = isset($this->params['Param3']) ? $this->params['Param3'] === 'true' : false;
                    $embedSize = isset($this->params['Param4']) ? $this->params['Param4'] : '';
                    $view = new RelationsView($contentType, $embedId, $embedInline, $embedSize);
                }
                break;

            case 'embed_view':
                {
                    $embedId = isset($this->params['ObjectID']) ? $this->params['ObjectID'] : false;
                    $view = new EmbedView($embedId);
                }
                break;

            case 'dialog':
                {
                    $dialog = isset($this->params['Param1']) ? $this->params['Param1'] : '';
                    $view = new DialogView($dialog);
                }
                break;

            case 'load':
                {
                    $embedId = isset($this->params['ObjectID']) ? $this->params['ObjectID'] : false;
                    $dataMap = isset($this->params['ObjectVersion']) ? $this->params['ObjectVersion'] : false;
                    $imagePreGenerateSizes = isset($this->params['Param1']) ? $this->params['Param1'] : false;
                    $view = new LoadView($embedId, $dataMap, $imagePreGenerateSizes);
                }
        }

        if ($view instanceof ViewInterface) {
            return $view->getResult();
        }
        throw new \Exception('View not found');
    }
}

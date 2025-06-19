<?php

namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\UploadFieldConnector;
use eZContentObjectAttribute;
use eZBinaryFile;
use eZFSFileHandler;

class FileField extends UploadFieldConnector
{
    public function getData()
    {
        if ($rawContent = $this->getContent()) {
            $file = false;
            $attribute = eZContentObjectAttribute::fetch($rawContent['id'], $rawContent['version']);
            if ($attribute instanceof eZContentObjectAttribute) {
                /** @var \eZBinaryFile $attributeContent */
                $file = $attribute->content();
            }
            if ($file instanceof eZBinaryFile) {
                return array(
                    'id' => $rawContent['id'],
                    'name' => $file->attribute('original_filename'),
                    'size' => $file->attribute('filesize'),
                    'url' => $rawContent['content']['url'],
                    'thumbnailUrl' => $this->getIconImageBase64Data($file->attribute('filepath')),
                    'deleteUrl' => $this->getServiceUrl('upload', array('delete' => $file->attribute('original_filename'))),
                    'deleteType' => "GET"
                );
            }
        }

        return null;
    }

    public function getSchema()
    {
        return array(
            "title" => $this->attribute->attribute('name'),
            "type" => "string",
            'required' => (bool)$this->attribute->attribute('is_required')
        );
    }

    public function getOptions()
    {
        return array(
            "helper" => $this->attribute->attribute('description'),
            "type" => "upload",
            "upload" => array(
                "url" => $this->getServiceUrl('upload'),
                "autoUpload" => true,
                "showSubmitButton" => false,
                "disableImagePreview" => true,
                "maxFileSize" => 25000000, //@todo,
                "maxNumberOfFiles" => 1,
            ),
            "showUploadPreview" => false,
            "maxNumberOfFiles" => 1,
            "label" => $this->attribute->attribute('name'),
            "multiple" => false
        );
    }

    protected function getUploadParamNameSuffix()
    {
        return '_files';
    }

    public function setPayload($files)
    {
        if (count($files)){
            $file = array_pop($files);
            if (!is_numeric($file['id'])){
                $filePath = $this->getUploadDir() .  $file['name'];
                $fileHandler = new eZFSFileHandler($filePath);
                if ($fileHandler->exists()) {

                    $fileContent = base64_encode($fileHandler->fetchContents());

                    return array(
                        'file' => $fileContent,
                        'filename' =>  $file['name'],
                    );
                }
            }
        }

        return null;
    }

}

<?php
namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\UploadFieldConnector;
use eZImageAliasHandler;
use eZContentObjectAttribute;
use eZURI;
use eZFSFileHandler;

class ImageField extends UploadFieldConnector
{
    public function getData($rawContent)
    {
        $thumbnail = false;
        $original = false;
        $attribute = eZContentObjectAttribute::fetch($rawContent['id'], $rawContent['version']);
        if ($attribute instanceof eZContentObjectAttribute) {
            /** @var \eZImageAliasHandler $attributeContent */
            $attributeContent = $attribute->content();
            if ($attributeContent instanceof eZImageAliasHandler) {
                $thumbnail = $attributeContent->attribute('small');
                $original = $attributeContent->attribute('original');

            }
        }
        if ($original) {

            $url = $original['url'];
            eZURI::transformURI($url, true, 'full');

            $thumbnailUrl = false;
            if ($thumbnail) {
                $thumbnailUrl = $thumbnail['url'];
                eZURI::transformURI($thumbnailUrl, true, 'full');
            }

            return array(
                'image' => array(
                    'id' => $rawContent['id'],
                    'name' => $original['original_filename'],
                    'size' => $original['filesize'],
                    'url' => $url,
                    'thumbnailUrl' => $thumbnailUrl,
                    'deleteUrl' => $this->getServiceUrl('upload', array('delete' => $original['original_filename'])),
                    'deleteType' => "GET"
                ),
                'alt' => $original['alternative_text']
            );
        }

        return null;
    }

    public function getSchema()
    {
        return array(
            "type" => "object",
            "title" => $this->attribute->attribute('name'),
            "properties" => array(
                "image" => array(
                    "type" => "string", //@todo sarebbe object bug di Alpaca.Fields.UploadField
                    'required' => (bool)$this->attribute->attribute('is_required')
                ),
                "alt" => array(
                    "type" => "string"
                )
            ),
        );
    }

    public function getOptions()
    {
        return array(
            "helper" => $this->attribute->attribute('description'),
            "fields" => array(
                "image" => array(
                    "type" => "upload",
                    "upload" => array(
                        "url" => $this->getServiceUrl('upload'),
                        "autoUpload" => true,
                        "showSubmitButton" => false,
                        "maxFileSize" => 25000000, //@todo,
                        "maxNumberOfFiles" => 1,
                    ),
                    "showUploadPreview" => true,
                    "maxNumberOfFiles" => 1,
                    "label" => \ezpI18n::tr('design/standard/content/datatype', 'New image file for upload'),
                    "multiple" => false
                ),
                "alt" => array(
                    "type" => "text",
                    "label" => \ezpI18n::tr('design/standard/content/datatype', 'Alternative image text')
                )
            )
        );
    }

    protected function getUploadParamName()
    {
        return $this->getIdentifier() . '_image_files';
    }

    public function setPayload($postData)
    {
        $images = isset($postData['image']) ? $postData['image'] : array();
        if (count($images)){
            $alt = isset($postData['alt']) ? $postData['alt'] : '';
            $image = array_pop($images);
            if (!is_numeric($image['id'])){
                $filePath = $this->getUploadDir() .  $image['name'];
                $fileHandler = new eZFSFileHandler($filePath);
                if ($fileHandler->exists()) {

                    $fileContent = base64_encode($fileHandler->fetchContents());

                    return array(
                        'file' => $fileContent,
                        'filename' =>  $image['name'],
                        'alt' => $alt
                    );
                }
            }
        }

        return null;
    }
}

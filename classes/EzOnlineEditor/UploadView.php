<?php

namespace Opencontent\Ocopendata\Forms\EzOnlineEditor;


class UploadView extends AbstractView implements ViewInterface
{
    public function __construct($contentType, $forcedUpload)
    {
        $user = \eZUser::currentUser();
        if ($user instanceOf \eZUser) {
            $result = $user->hasAccessTo('ezoe', 'relations');
        } else {
            $result = array('accessWord' => 'no');
        }

        if ($result['accessWord'] === 'no') {
            throw new \Exception(
                \ezpI18n::tr('design/standard/error/kernel',
                    'Your current user does not have the proper privileges to access this page.')
            );
        }

        $http = \eZHTTPTool::instance();
        $imageIni = \eZINI::instance('image.ini');
        $params = array('dataMap' => array('image'));

        // is this a upload?
        // forcedUpload is needed since hasPostVariable returns false if post size exceeds
        // allowed size set in max_post_size in php.ini
        if ($http->hasPostVariable('uploadButton') || $forcedUpload) {
            $upload = new \eZContentUpload();

            $location = false;
            if ($http->hasPostVariable('location')) {
                $location = $http->postVariable('location');
                if ($location === 'auto' || trim($location) === '') {
                    $location = false;
                }
            }

            $objectName = '';
            if ($http->hasPostVariable('objectName')) {
                $objectName = trim($http->postVariable('objectName'));
            }

            try {
                $uploadedOk = $upload->handleUpload(
                    $result,
                    'fileName',
                    $location,
                    false,
                    $objectName,
                    \eZLocale::currentLocaleCode(),
                    false
                );
                if (!$uploadedOk) {
                    throw new \RuntimeException("Upload failed");
                }

                /** @var \eZContentObject $uploadedObject */
                $uploadedObject = $uploadedOk['contentobject'];

                /** @var \eZContentObjectVersion $uploadVersion */
                $uploadVersion = $uploadedObject->currentVersion();
                $newObjectID = (int)$uploadedObject->attribute('id');

                /**
                 * @var int $key
                 * @var \eZContentObjectAttribute $attr
                 */
                foreach ($uploadVersion->dataMap() as $key => $attr) {
                    //post pattern: ContentObjectAttribute_attribute-identifier
                    $base = 'ContentObjectAttribute_' . $key;
                    $postVar = trim($http->postVariable($base, ''));
                    if ($postVar !== '') {
                        switch ($attr->attribute('data_type_string')) {
                            case 'ezstring':
                                $classAttr = $attr->attribute('contentclass_attribute');
                                /** @var \eZStringType $dataType */
                                $dataType = $classAttr->attribute('data_type');
                                if ($dataType->validateStringHTTPInput($postVar, $attr,
                                        $classAttr) !== \eZInputValidator::STATE_ACCEPTED) {
                                    throw new \InvalidArgumentException($attr->validationError());
                                }
                            case 'eztext':
                            case 'ezkeyword':
                                $attr->fromString($postVar);
                                $attr->store();
                                break;
                            case 'ezfloat':
                                $floatValue = (float)str_replace(',', '.', $postVar);
                                $classAttr = $attr->attribute('contentclass_attribute');
                                /** @var \eZFloatType $dataType */
                                $dataType = $classAttr->attribute('data_type');
                                if ($dataType->validateFloatHTTPInput($floatValue, $attr,
                                        $classAttr) !== \eZInputValidator::STATE_ACCEPTED) {
                                    throw new \InvalidArgumentException($attr->validationError());
                                }
                                $attr->setAttribute('data_float', $floatValue);
                                $attr->store();
                                break;
                            case 'ezinteger':
                                $classAttr = $attr->attribute('contentclass_attribute');
                                /** @var \eZIntegerType $dataType */
                                $dataType = $classAttr->attribute('data_type');
                                if ($dataType->validateIntegerHTTPInput($postVar, $attr,
                                        $classAttr) !== \eZInputValidator::STATE_ACCEPTED) {
                                    throw new \InvalidArgumentException($attr->validationError());
                                }
                            case 'ezboolean':
                                $attr->setAttribute('data_int', (int)$postVar);
                                $attr->store();
                                break;
                            case 'ezimage':
                                // validation has been done by eZContentUpload
                                $content = $attr->attribute('content');
                                $content->setAttribute('alternative_text', $postVar);
                                $content->store($attr);
                                break;
                            case 'ezxmltext':
                                $parser = new \eZOEInputParser();
                                $document = $parser->process($postVar);
                                $xmlString = \eZXMLTextType::domString($document);
                                $attr->setAttribute('data_text', $xmlString);
                                $attr->store();
                                break;
                        }
                    }
                }

                \eZOperationHandler::execute(
                    'content', 'publish',
                    array(
                        'object_id' => $newObjectID,
                        'version' => $uploadVersion->attribute('version')
                    )
                );
                $newObject = \eZContentObject::fetch($newObjectID);
                $newObjectName = $newObject->attribute('name');
                $newObjectNodeID = (int)$newObject->attribute('main_node_id');

                /* @todo
                $object->addContentObjectRelation(
                    $newObjectID,
                    $uploadVersion->attribute('version'),
                    0,
                    \eZContentObject::RELATION_EMBED
                );
                */

                echo '<html><head><title>HiddenUploadFrame</title><script type="text/javascript">';
                echo 'window.parent.eZOEPopupUtils.selectByEmbedId( ' . $newObjectID . ', ' . $newObjectNodeID . ', ' . json_encode($newObjectName) . ' );';
                echo '</script></head><body></body></html>';
            } catch (\InvalidArgumentException $e) {
                $uploadedObject->purge();
                echo '<html><head><title>HiddenUploadFrame</title><script type="text/javascript">';
                echo 'window.parent.document.getElementById("upload_in_progress").style.display = "none";';
                echo '</script></head><body><div style="position:absolute; top: 0px; left: 0px;background-color: white; width: 100%;">';
                echo '<p style="margin: 0; padding: 3px; color: red">' . htmlspecialchars($e->getMessage()) . '</p>';
                echo '</div></body></html>';
            } catch (\RuntimeException $e) {
                echo '<html><head><title>HiddenUploadFrame</title><script type="text/javascript">';
                echo 'window.parent.document.getElementById("upload_in_progress").style.display = "none";';
                echo '</script></head><body><div style="position:absolute; top: 0px; left: 0px;background-color: white; width: 100%;">';
                foreach ($result['errors'] as $err) {
                    echo '<p style="margin: 0; padding: 3px; color: red">' . htmlspecialchars($err['description']) . '</p>';
                }
                echo '</div></body></html>';
            }
            \eZDB::checkTransactionCounter();
            \eZExecution::cleanExit();
        }


        $siteIni = \eZINI::instance('site.ini');
        $contentIni = \eZINI::instance('content.ini');

        $groups = $contentIni->variable('RelationGroupSettings', 'Groups');
        $defaultGroup = $contentIni->variable('RelationGroupSettings', 'DefaultGroup');
        $imageDatatypeArray = $siteIni->variable('ImageDataTypeSettings', 'AvailableImageDataTypes');

        $classGroupMap = array();
        $groupClassLists = array();
        $groupedRelatedObjects = array();
        /* @todo
        $relatedObjects = $object->relatedContentObjectArray($objectVersion);
        */
        $relatedObjects = array();
        // $hasContentTypeGroup   = false;
        // $contentTypeGroupName  = $contentType . 's';

        foreach ($groups as $groupName) {
            $groupedRelatedObjects[$groupName] = array();
            $setting = ucfirst($groupName) . 'ClassList';
            $groupClassLists[$groupName] = $contentIni->variable('RelationGroupSettings', $setting);
            foreach ($groupClassLists[$groupName] as $classIdentifier) {
                $classGroupMap[$classIdentifier] = $groupName;
                // if ( $contentTypeGroupName  === $groupName ) $hasContentTypeGroup = true;
            }
        }

        $groupedRelatedObjects[$defaultGroup] = array();

        /* @todo
        foreach ($relatedObjects as $relatedObjectKey => $relatedObject) {
            $srcString = '';
            $imageAttribute = false;
            $relID = $relatedObject->attribute('id');
            $classIdentifier = $relatedObject->attribute('class_identifier');
            $groupName = isset($classGroupMap[$classIdentifier]) ? $classGroupMap[$classIdentifier] : $defaultGroup;

            // if ( $hasContentTypeGroup === true && $contentTypeGroupName !== $groupName ) continue;

            if ($groupName === 'images') {
                $objectAttributes = $relatedObject->contentObjectAttributes();
                foreach ($objectAttributes as $objectAttribute) {
                    $classAttribute = $objectAttribute->contentClassAttribute();
                    $dataTypeString = $classAttribute->attribute('data_type_string');
                    if (in_array($dataTypeString, $imageDatatypeArray) && $objectAttribute->hasContent()) {
                        $content = $objectAttribute->content();
                        if ($content == null) {
                            continue;
                        }

                        if ($content->hasAttribute('small')) {
                            $srcString = $content->imageAlias('small');
                            $imageAttribute = $classAttribute->attribute('identifier');
                            break;
                        } else {
                            eZDebug::writeError("Image alias does not exist: small, missing from image.ini?",
                                __METHOD__);
                        }
                    }
                }
            }
            $item = array(
                'object' => $relatedObjects[$relatedObjectKey],
                'id' => 'eZObject_' . $relID,
                'image_alias' => $srcString,
                'image_attribute' => $imageAttribute,
                'selected' => false
            );
            $groupedRelatedObjects[$groupName][] = $item;
        }
        */

        $tpl = \eZTemplate::factory();
        $tpl->setVariable('object', array());
        $tpl->setVariable('object_id', 0);
        $tpl->setVariable('object_version', 0);
        $tpl->setVariable('related_contentobjects', $relatedObjects);
        $tpl->setVariable('grouped_related_contentobjects', $groupedRelatedObjects);
        $tpl->setVariable('content_type', $contentType);

        $contentTypeCase = ucfirst($contentType);
        if ($contentIni->hasVariable('RelationGroupSettings', $contentTypeCase . 'ClassList')) {
            $tpl->setVariable('class_filter_array',
                $contentIni->variable('RelationGroupSettings', $contentTypeCase . 'ClassList'));
        } else {
            $tpl->setVariable('class_filter_array', array());
        }

        $tpl->setVariable('content_type_name', rtrim($contentTypeCase, 's'));

        $tpl->setVariable('persistent_variable', array());

        $this->Result = array();
        $content = $tpl->fetch('design:ezoe/upload_' . $contentType . '.tpl');
        $content = str_replace('ezoe/', 'forms/ezoe/', $content);
        $this->Result['content'] = $content;
        $this->Result['pagelayout'] = 'design:ezoe/popup_pagelayout.tpl';
        $this->Result['persistent_variable'] = $tpl->variable('persistent_variable');
    }
}

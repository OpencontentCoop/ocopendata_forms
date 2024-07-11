<?php

namespace Opencontent\Ocopendata\Forms\EzOnlineEditor;

use DOMElement;
use DOMNode;
use eZContentObject;
use eZContentObjectTreeNode;
use eZDebug;
use eZINI;
use eZOEXMLInput;
use eZTemplate;
use eZTemplateDesignResource;
use eZURI;
use eZURL;

class EzOnlineEditorXMLInput extends eZOEXMLInput
{
    function inputTagXML(&$tag, $currentSectionLevel, $tdSectionLevel = null)
    {
        $output = '';
        $tagName = $tag instanceof DOMNode ? $tag->nodeName : '';
        $childTagText = '';

        // render children tags
        if ($tag->hasChildNodes()) {
            $tagChildren = $tag->childNodes;
            foreach ($tagChildren as $childTag) {
                $childTagText .= $this->inputTagXML($childTag, $currentSectionLevel, $tdSectionLevel);
            }
        }
        switch ($tagName) {
            case '#text' :
                {
                    $tagContent = $tag->textContent;
                    if (!strlen($tagContent)) {
                        break;
                    }

                    $tagContent = htmlspecialchars($tagContent);
                    $tagContent = str_replace('&amp;nbsp;', '&nbsp;', $tagContent);

                    if ($this->allowMultipleSpaces) {
                        $tagContent = str_replace('  ', ' &nbsp;', $tagContent);
                    } else {
                        $tagContent = preg_replace("/ {2,}/", ' ', $tagContent);
                    }

                    if ($tagContent[0] === ' ' && !$tag->previousSibling)//- Fixed "first space in paragraph" issue (ezdhtml rev.12246)
                    {
                        $tagContent[0] = ';';
                        $tagContent = '&nbsp' . $tagContent;
                    }

                    if ($this->allowNumericEntities) {
                        $tagContent = preg_replace('/&amp;#([0-9]+);/', '&#\1;', $tagContent);
                    }

                    $output .= $tagContent;
                }
                break;

            case 'embed' :
            case 'embed-inline' :
                {
                    $view = $tag->getAttribute('view');
                    $size = $tag->getAttribute('size');
                    $alignment = $tag->getAttribute('align');
                    $objectID = $tag->getAttribute('object_id');
                    $nodeID = $tag->getAttribute('node_id');
                    $showPath = $tag->getAttribute('show_path');
                    $htmlID = $tag->getAttributeNS('http://ez.no/namespaces/ezpublish3/xhtml/', 'id');
                    $className = $tag->getAttribute('class');
                    $idString = '';
                    $tplSuffix = '';

                    if (!$size) {
                        $contentIni = eZINI::instance('content.ini');
                        $size = $contentIni->variable('ImageSettings', 'DefaultEmbedAlias');
                    }

                    if (!$view) {
                        $view = $tagName;
                    }

                    $objectAttr = '';
                    $objectAttr .= ' alt="' . $size . '"';
                    $objectAttr .= ' view="' . $view . '"';

                    if ($htmlID != '') {
                        $objectAttr .= ' html_id="' . $htmlID . '"';
                    }
                    if ($showPath === 'true') {
                        $objectAttr .= ' show_path="true"';
                    }

                    if ($tagName === 'embed-inline') {
                        $objectAttr .= ' inline="true"';
                    } else {
                        $objectAttr .= ' inline="false"';
                    }

                    $customAttributePart = self::getCustomAttrPart($tag, $styleString);
                    $object = false;

                    if (is_numeric($objectID)) {
                        $object = eZContentObject::fetch($objectID);
                        $idString = 'eZObject_' . $objectID;
                    } elseif (is_numeric($nodeID)) {
                        $node = eZContentObjectTreeNode::fetch($nodeID);
                        $object = $node instanceof eZContentObjectTreeNode ? $node->object() : false;
                        $idString = 'eZNode_' . $nodeID;
                        $tplSuffix = '_node';
                    }

                    if ($object instanceof eZContentObject) {
                        $objectName = $object->attribute('name');
                        $classIdentifier = $object->attribute('class_identifier');
                        if (!$object->attribute('can_read') ||
                            !$object->attribute('can_view_embed')) {
                            $tplSuffix = '_denied';
                        } else {
                            if ($object->attribute('status') == eZContentObject::STATUS_ARCHIVED) {
                                $className .= ' ezoeItemObjectInTrash';
                                if (self::$showEmbedValidationErrors) {
                                    $oeini = eZINI::instance('ezoe.ini');
                                    if ($oeini->variable('EditorSettings', 'ValidateEmbedObjects') === 'enabled') {
                                        $className .= ' ezoeItemValidationError';
                                    }
                                }
                            }
                        }
                    } else {
                        $objectName = 'Unknown';
                        $classIdentifier = false;
                        $tplSuffix = '_deleted';
                        $className .= ' ezoeItemObjectDeleted';
                        if (self::$showEmbedValidationErrors) {
                            $className .= ' ezoeItemValidationError';
                        }
                    }

                    $embedContentType = self::embedTagContentType($classIdentifier);
                    if ($embedContentType === 'images') {
                        $ini = eZINI::instance();
                        $URL = self::getServerURL();
                        $objectAttributes = $object->contentObjectAttributes();
                        $imageDatatypeArray = $ini->variable('ImageDataTypeSettings', 'AvailableImageDataTypes');
                        $imageWidth = 32;
                        $imageHeight = 32;
                        foreach ($objectAttributes as $objectAttribute) {
                            $classAttribute = $objectAttribute->contentClassAttribute();
                            $dataTypeString = $classAttribute->attribute('data_type_string');
                            if (in_array($dataTypeString, $imageDatatypeArray) && $objectAttribute->hasContent()) {
                                /** @var mixed $content */
                                $content = $objectAttribute->content();
                                if ($content == null) {
                                    continue;
                                }

                                if ($content->hasAttribute($size)) {
                                    $imageAlias = $content->imageAlias($size);
                                    eZURI::transformURI($imageAlias['url'], true);
                                    $srcString = $imageAlias['url'];
                                    $imageWidth = $imageAlias['width'];
                                    $imageHeight = $imageAlias['height'];
                                    break;
                                } else {
                                    eZDebug::writeError(
                                        "Image alias does not exist: $size, missing from image.ini?",
                                        __METHOD__
                                    );
                                }
                            }
                        }

                        if (!isset($srcString)) {
                            $srcString = self::getDesignFile('images/tango/mail-attachment32.png');
                        }

                        if ($alignment === 'center') {
                            $objectAttr .= ' align="middle"';
                            $className .= ' ezoeAlignmiddle'; // align="middle" is not taken into account by browsers on img
                        } else {
                            if ($alignment) {
                                $objectAttr .= ' align="' . $alignment . '"';
                            }
                        }

                        if ($className != '') {
                            $objectAttr .= ' class="' . $className . '"';
                        }

                        $output .= '<img id="' . $idString . '" title="' . $objectName . '" src="' .
                            htmlspecialchars($srcString) . '" width="' . $imageWidth . '" height="' . $imageHeight .
                            '" ' . $objectAttr . $customAttributePart . $styleString . ' />';
                    } else {
                        if (self::embedTagIsCompatibilityMode()) {
                            $srcString = self::getDesignFile('images/tango/mail-attachment32.png');
                            if ($alignment === 'center') {
                                $objectAttr .= ' align="middle"';
                            } else {
                                if ($alignment) {
                                    $objectAttr .= ' align="' . $alignment . '"';
                                }
                            }

                            if ($className != '') {
                                $objectAttr .= ' class="' . $className . '"';
                            }

                            $output .= '<img id="' . $idString . '" title="' . $objectName . '" src="' .
                                $srcString . '" width="32" height="32" ' . $objectAttr .
                                $customAttributePart . $styleString . ' />';
                        } else {
                            if ($alignment) {
                                $objectAttr .= ' align="' . $alignment . '"';
                            }

                            if ($className) {
                                $objectAttr .= ' class="ezoeItemNonEditable ' . $className . ' ezoeItemContentType' .
                                    ucfirst($embedContentType) . '"';
                            } else {
                                $objectAttr .= ' class="ezoeItemNonEditable ezoeItemContentType' .
                                    ucfirst($embedContentType) . '"';
                            }

                            if ($tagName === 'embed-inline') {
                                $htmlTagName = 'span';
                            } else {
                                $htmlTagName = 'div';
                            }

                            $objectParam = ['size' => $size, 'align' => $alignment, 'show_path' => $showPath];
                            if ($htmlID) {
                                $objectParam['id'] = $htmlID;
                            }

                            $res = eZTemplateDesignResource::instance();
                            $res->setKeys([['classification', $className]]);

                            if (isset($node)) {
                                $templateOutput = self::fetchTemplate(
                                    'design:content/datatype/view/ezxmltags/' . $tagName . $tplSuffix . '.tpl',
                                    [
                                        'view' => $view,
                                        'object' => $object,
                                        'link_parameters' => [],
                                        'classification' => $className,
                                        'object_parameters' => $objectParam,
                                        'node' => $node,
                                    ]
                                );
                            } else {
                                $templateOutput = self::fetchTemplate(
                                    'design:content/datatype/view/ezxmltags/' . $tagName . $tplSuffix . '.tpl',
                                    [
                                        'view' => $view,
                                        'object' => $object,
                                        'link_parameters' => [],
                                        'classification' => $className,
                                        'object_parameters' => $objectParam,
                                    ]
                                );
                            }

                            $output .= '<' . $htmlTagName . ' id="' . $idString . '" title="' . $objectName . '"' .
                                $objectAttr . $customAttributePart . $styleString . '>' . $templateOutput .
                                '</' . $htmlTagName . '>';
                        }
                    }
                }
                break;

            case 'custom' :
                {
                    $name = $tag->getAttribute('name');
                    $align = $tag->getAttribute('align');
                    $customAttributePart = self::getCustomAttrPart($tag, $styleString);
                    $inline = self::customTagIsInline($name);
                    if ($align) {
                        $customAttributePart .= ' align="' . $align . '"';
                    }

                    if (isset(self::$nativeCustomTags[$name])) {
                        if (!$childTagText) {
                            $childTagText = '&nbsp;';
                        }
                        $output .= '<' . self::$nativeCustomTags[$name] . $customAttributePart . $styleString .
                            '>' . $childTagText . '</' . self::$nativeCustomTags[$name] . '>';
                    } else {
                        if ($inline === true) {
                            if (!$childTagText) {
                                $childTagText = '&nbsp;';
                            }
                            $tagName = $name === 'underline' ? 'u' : 'span';
                            $output .= '<' . $tagName . ' class="ezoeItemCustomTag ' . $name . '" type="custom"' .
                                $customAttributePart . $styleString . '>' . $childTagText . '</' . $tagName . '>';
                        } else {
                            if ($inline) {
                                $imageUrl = self::getCustomAttribute($tag, 'image_url');
                                if ($imageUrl === null || !$imageUrl) {
                                    $imageUrl = self::getDesignFile($inline);
                                    $customAttributePart .= ' width="22" height="22"';
                                }
                                $output .= '<img src="' . $imageUrl . '" class="ezoeItemCustomTag ' . $name .
                                    '" type="custom"' . $customAttributePart . $styleString . ' />';
                            } else {
                                if ($tag->textContent === '' && !$tag->hasChildNodes()) {
                                    // for empty custom tag, just put a paragraph with the name
                                    // of the custom tag in to handle it in the rich text editor
                                    $output .= '<div class="ezoeItemCustomTag ' . $name . '" type="custom"' .
                                        $customAttributePart . $styleString . '><p>' . $name . '</p></div>';
                                } else {
                                    $customTagContent = $this->inputSectionXML(
                                        $tag,
                                        $currentSectionLevel,
                                        $tdSectionLevel
                                    );
                                    /*foreach ( $tag->childNodes as $tagChild )
                                    {
                                        $customTagContent .= $this->inputTdXML( $tagChild,
                                                                                $currentSectionLevel,
                                                                                $tdSectionLevel );
                                    }*/
                                    $output .= '<div class="ezoeItemCustomTag ' . $name . '" type="custom"' .
                                        $customAttributePart . $styleString . '>' . $customTagContent . '</div>';
                                }
                            }
                        }
                    }
                }
                break;

            case 'literal' :
                {
                    $literalText = '';
                    foreach ($tagChildren as $childTag) {
                        $literalText .= $childTag->textContent;
                    }
                    $className = $tag->getAttribute('class');

                    $customAttributePart = self::getCustomAttrPart($tag, $styleString);

                    $literalText = htmlspecialchars($literalText);
                    $literalText = str_replace("\n", '<br />', $literalText);

                    if ($className != '') {
                        $customAttributePart .= ' class="' . $className . '"';
                    }

                    $output .= '<pre' . $customAttributePart . $styleString . '>' . $literalText . '</pre>';
                }
                break;

            case 'ul' :
            case 'ol' :
                {
                    $listContent = '';

                    $customAttributePart = self::getCustomAttrPart($tag, $styleString);

                    // find all list elements
                    foreach ($tag->childNodes as $listItemNode) {
                        if (!$listItemNode instanceof DOMElement) {
                            continue;// ignore whitespace
                        }

                        $LIcustomAttributePart = self::getCustomAttrPart($listItemNode, $listItemStyleString);

                        $noParagraphs = self::childTagCount($listItemNode) <= 1;
                        $listItemContent = '';
                        foreach ($listItemNode->childNodes as $itemChildNode) {
                            $listSectionLevel = $currentSectionLevel;
                            if ($itemChildNode instanceof DOMNode
                                && ($itemChildNode->nodeName === 'section'
                                    || $itemChildNode->nodeName === 'paragraph')) {
                                $listItemContent .= $this->inputListXML(
                                    $itemChildNode,
                                    $currentSectionLevel,
                                    $listSectionLevel,
                                    $noParagraphs
                                );
                            } else {
                                $listItemContent .= $this->inputTagXML(
                                    $itemChildNode,
                                    $currentSectionLevel,
                                    $tdSectionLevel
                                );
                            }
                        }

                        $LIclassName = $listItemNode->getAttribute('class');

                        if ($LIclassName) {
                            $LIcustomAttributePart .= ' class="' . $LIclassName . '"';
                        }

                        $listContent .= '<li' . $LIcustomAttributePart . $listItemStyleString . '>' .
                            $listItemContent . '</li>';
                    }
                    $className = $tag->getAttribute('class');
                    if ($className != '') {
                        $customAttributePart .= ' class="' . $className . '"';
                    }

                    $output .= '<' . $tagName . $customAttributePart . $styleString . '>' . $listContent . '</' .
                        $tagName . '>';
                }
                break;

            case 'table' :
                {
                    $tableRows = '';
                    $border = $tag->getAttribute('border');
                    $width = $tag->getAttribute('width');
                    $align = $tag->getAttribute('align');
                    $tableClassName = $tag->getAttribute('class');

                    $customAttributePart = self::getCustomAttrPart($tag, $styleString);

                    // find all table rows
                    foreach ($tag->childNodes as $tableRow) {
                        if (!$tableRow instanceof DOMElement) {
                            continue; // ignore whitespace
                        }
                        $TRcustomAttributePart = self::getCustomAttrPart($tableRow, $tableRowStyleString);
                        $TRclassName = $tableRow->getAttribute('class');

                        $tableData = '';
                        foreach ($tableRow->childNodes as $tableCell) {
                            if (!$tableCell instanceof DOMElement) {
                                continue; // ignore whitespace
                            }

                            $TDcustomAttributePart = self::getCustomAttrPart($tableCell, $tableCellStyleString);

                            $className = $tableCell->getAttribute('class');
                            $cellAlign = $tableCell->getAttribute('align');

                            $colspan = $tableCell->getAttributeNS(
                                'http://ez.no/namespaces/ezpublish3/xhtml/',
                                'colspan'
                            );
                            $rowspan = $tableCell->getAttributeNS(
                                'http://ez.no/namespaces/ezpublish3/xhtml/',
                                'rowspan'
                            );
                            $cellWidth = $tableCell->getAttributeNS(
                                'http://ez.no/namespaces/ezpublish3/xhtml/',
                                'width'
                            );
                            if ($className != '') {
                                $TDcustomAttributePart .= ' class="' . $className . '"';
                            }
                            if ($cellWidth != '') {
                                $TDcustomAttributePart .= ' width="' . $cellWidth . '"';
                            }
                            if ($colspan && $colspan !== '1') {
                                $TDcustomAttributePart .= ' colspan="' . $colspan . '"';
                            }
                            if ($rowspan && $rowspan !== '1') {
                                $TDcustomAttributePart .= ' rowspan="' . $rowspan . '"';
                            }
                            if ($cellAlign) {
                                $TDcustomAttributePart .= ' align="' . $cellAlign . '"';
                            }
                            $cellContent = '';
                            $tdSectionLevel = $currentSectionLevel;
                            foreach ($tableCell->childNodes as $tableCellChildNode) {
                                $cellContent .= $this->inputTdXML(
                                    $tableCellChildNode,
                                    $currentSectionLevel,
                                    $tdSectionLevel - $currentSectionLevel
                                );
                            }
                            if ($cellContent === '') {
                                // tinymce has some issues with empty content in some browsers
                                if (self::browserSupportsDHTMLType() != 'Trident') {
                                    $cellContent = '<p><br data-mce-bogus="1"/></p>';
                                }
                            }
                            if ($tableCell->nodeName === 'th') {
                                $tableData .= '<th' . $TDcustomAttributePart . $tableCellStyleString . '>' .
                                    $cellContent . '</th>';
                            } else {
                                $tableData .= '<td' . $TDcustomAttributePart . $tableCellStyleString . '>' .
                                    $cellContent . '</td>';
                            }
                        }
                        if ($TRclassName) {
                            $TRcustomAttributePart .= ' class="' . $TRclassName . '"';
                        }

                        $tableRows .= '<tr' . $TRcustomAttributePart . $tableRowStyleString . '>' .
                            $tableData . '</tr>';
                    }
                    //if ( self::browserSupportsDHTMLType() === 'Trident' )
                    //{
                    $customAttributePart .= ' width="' . $width . '"';
                    /*}
                    else
                    {
                        // if this is reenabled merge it with $styleString
                        $customAttributePart .= ' style="width:' . $width . ';"';
                    }*/

                    if ($border !== '' && is_string($border)) {
                        if ($border === '0%') {
                            $border = '0';
                        }// Strip % if 0 to make sure TinyMCE shows a dotted border

                        $customAttributePart .= ' border="' . $border . '"';
                    }

                    if ($align) {
                        $customAttributePart .= ' align="' . $align . '"';
                    }

                    if ($tableClassName) {
                        $customAttributePart .= ' class="' . $tableClassName . '"';
                    }

                    $output .= '<table' . $customAttributePart . $styleString . '><tbody>' . $tableRows .
                        '</tbody></table>';
                }
                break;

            // normal content tags
            case 'emphasize' :
                {
                    $customAttributePart = self::getCustomAttrPart($tag, $styleString);

                    $className = $tag->getAttribute('class');
                    if ($className) {
                        $customAttributePart .= ' class="' . $className . '"';
                    }
                    $output .= '<em' . $customAttributePart . $styleString . '>' . $childTagText . '</em>';
                }
                break;

            case 'strong' :
                {
                    $customAttributePart = self::getCustomAttrPart($tag, $styleString);

                    $className = $tag->getAttribute('class');
                    if ($className) {
                        $customAttributePart .= ' class="' . $className . '"';
                    }
                    $output .= '<strong' . $customAttributePart . $styleString . '>' . $childTagText . '</strong>';
                }
                break;

            case 'line' :
                {
                    $output .= $childTagText . '<br />';
                }
                break;

            case 'anchor' :
                {
                    $name = $tag->getAttribute('name');

                    $customAttributePart = self::getCustomAttrPart($tag, $styleString);

                    $output .= '<a name="' . $name . '" class="mceItemAnchor"' . $customAttributePart .
                        $styleString . '></a>';
                }
                break;

            case 'link' :
                {
                    $customAttributePart = self::getCustomAttrPart($tag, $styleString);

                    $linkID = $tag->getAttribute('url_id');
                    $target = $tag->getAttribute('target');
                    $className = $tag->getAttribute('class');
                    $viewName = $tag->getAttribute('view');
                    $objectID = $tag->getAttribute('object_id');
                    $nodeID = $tag->getAttribute('node_id');
                    $anchorName = $tag->getAttribute('anchor_name');
                    $showPath = $tag->getAttribute('show_path');
                    $htmlID = $tag->getAttributeNS('http://ez.no/namespaces/ezpublish3/xhtml/', 'id');
                    $htmlTitle = $tag->getAttributeNS('http://ez.no/namespaces/ezpublish3/xhtml/', 'title');
                    $attributes = [];

                    if ($objectID != null) {
                        $href = 'ezobject://' . $objectID;
                    } elseif ($nodeID != null) {
                        if ($showPath === 'true') {
                            $node = eZContentObjectTreeNode::fetch($nodeID);
                            $href = $node ?
                                'eznode://' . $node->attribute('path_identification_string') :
                                'eznode://' . $nodeID;
                        } else {
                            $href = 'eznode://' . $nodeID;
                        }
                    } elseif ($linkID != null) {
                        $href = eZURL::url($linkID);
                    } else {
                        $href = $tag->getAttribute('href');
                    }

                    if ($anchorName != null) {
                        $href .= '#' . $anchorName;
                    }

                    if ($className != '') {
                        $attributes[] = 'class="' . $className . '"';
                    }

                    if ($viewName != '') {
                        $attributes[] = 'view="' . $viewName . '"';
                    }

                    $attributes[] = 'href="' . $href . '"';
                    // Also set mce_href for use by OE to make sure href attribute is not messed up by IE 6 / 7
                    $attributes[] = 'data-mce-href="' . $href . '"';
                    if ($target != '') {
                        $attributes[] = 'target="' . $target . '"';
                    }
                    if ($htmlTitle != '') {
                        $attributes[] = 'title="' . $htmlTitle . '"';
                    }
                    if ($htmlID != '') {
                        $attributes[] = 'id="' . $htmlID . '"';
                    }

                    $attributeText = '';
                    if (!empty($attributes)) {
                        $attributeText = ' ' . implode(' ', $attributes);
                    }
                    $output .= '<a' . $attributeText . $customAttributePart . $styleString . '>' .
                        $childTagText . '</a>';
                }
                break;
            case 'tr' :
            case 'td' :
            case 'th' :
            case 'li' :
            case 'paragraph' :
                {
                }
                break;
            default :
                {
                }
                break;
        }
        return $output;
    }

    protected static function fetchTemplate($template, $parameters = [])
    {
        $template = 'design:forms/embed_view.tpl';
        $tpl = eZTemplate::factory();
        $existingPramas = [];

        foreach ($parameters as $name => $value) {
            if ($tpl->hasVariable($name)) {
                $existingPramas[$name] = $tpl->variable($name);
            }

            $tpl->setVariable($name, $value);
        }

        $result = $tpl->fetch($template);

        foreach ($parameters as $name => $value) {
            if (isset($existingPramas[$name])) {
                $tpl->setVariable($name, $existingPramas[$name]);
            } else {
                $tpl->unsetVariable($name);
            }
        }

        return $result;
    }
}
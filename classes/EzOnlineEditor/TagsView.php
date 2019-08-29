<?php

namespace Opencontent\Ocopendata\Forms\EzOnlineEditor;


class TagsView extends AbstractView implements ViewInterface
{
    public function __construct($tagName, $customTagName)
    {
        if ($customTagName === 'undefined') {
            $customTagName = '';
        }

        $templateName = '';

        // pick template based on tag, tags that have same
        // set of attributes usually share template.
        switch ($tagName) {
            case 'strong':
            case 'emphasize':
            case 'literal':
            case 'li':
            case 'ol':
            case 'ul':
            case 'tr':
            case 'paragraph':
                $templateName = 'tag_general.tpl';
                break;
            case 'header':
                $templateName = 'tag_header.tpl';
                break;
            case 'custom':
                $templateName = 'tag_custom.tpl';
                break;
            case 'link':
                $templateName = 'tag_link.tpl';
                break;
            case 'anchor':
                $templateName = 'tag_anchor.tpl';
                break;
            case 'table':
                $templateName = 'tag_table.tpl';
                break;
            case 'th':
            case 'td':
                $templateName = 'tag_table_cell.tpl';
                break;
            //case 'embed': this view is not used for embed tags, look in relations.php
        }


        if (!$templateName) {
            throw new \Exception(
                \ezpI18n::tr('design/standard/ezoe', 'Invalid parameter: %parameter = %value', null,
                    array('%parameter' => 'TagName', '%value' => $tagName))
            );
        }


        // class list with description
        $classList = array();
        $customInlineList = array();
        $contentIni = \eZINI::instance('content.ini');

        if ($tagName === 'custom') {
            // custom tags dosn't have a class, so we use custom tag name as class internally
            // in the editor to be able to have different styles on differnt custom tags.
            if ($contentIni->hasVariable('CustomTagSettings', 'CustomTagsDescription')) {
                $customTagDescription = $contentIni->variable('CustomTagSettings', 'CustomTagsDescription');
            } else {
                $customTagDescription = array();
            }

            if ($contentIni->hasVariable('CustomTagSettings', 'IsInline')) {
                $customInlineList = $contentIni->variable('CustomTagSettings', 'IsInline');
            }

            foreach ($contentIni->variable('CustomTagSettings', 'AvailableCustomTags') as $tag) {
                if (isset($customTagDescription[$tag])) {
                    $classList[$tag] = $customTagDescription[$tag];
                } else {
                    $classList[$tag] = $tag;
                }
            }
        } else {
            // class data for normal tags
            if ($contentIni->hasVariable($tagName, 'ClassDescription')) {
                $classListDescription = $contentIni->variable($tagName, 'ClassDescription');
            } else {
                $classListDescription = array();
            }

            $classList['-0-'] = 'None';
            if ($contentIni->hasVariable($tagName, 'AvailableClasses')) {
                foreach ($contentIni->variable($tagName, 'AvailableClasses') as $class) {
                    if (isset($classListDescription[$class])) {
                        $classList[$class] = $classListDescription[$class];
                    } else {
                        $classList[$class] = $class;
                    }
                }
            }
        }

        $tpl = \eZTemplate::factory();

        $tpl->setVariable('tag_name', $tagName);
        $tpl->setVariable('custom_tag_name', $customTagName);

        $tpl->setVariable('custom_inline_tags', $customInlineList);

        $tpl->setVariable('class_list', $classList);

        $ezoeIni = \eZINI::instance('ezoe.ini');
        $tpl->setVariable('custom_attribute_style_map',
            json_encode($ezoeIni->variable('EditorSettings', 'CustomAttributeStyleMap')));

        // use persistent_variable like content/view does, sending parameters
        // to pagelayout as a hash.
        $tpl->setVariable('persistent_variable', array());

        $xmlTagAliasList = $ezoeIni->variable('EditorSettings', 'XmlTagNameAlias');
        if (isset($xmlTagAliasList[$tagName])) {
            $tpl->setVariable('tag_name_alias', $xmlTagAliasList[$tagName]);
        } else {
            $tpl->setVariable('tag_name_alias', $tagName);
        }


        if ($tagName === 'td' || $tagName === 'th') {
            // generate javascript data for td / th classes
            $tagName2 = $tagName === 'td' ? 'th' : 'td';
            $cellClassList = array($tagName => $classList, $tagName2 => array('-0-' => 'None'));

            if ($contentIni->hasVariable($tagName2, 'ClassDescription')) {
                $classListDescription = $contentIni->variable($tagName2, 'ClassDescription');
            } else {
                $classListDescription = array();
            }

            if ($contentIni->hasVariable($tagName2, 'AvailableClasses')) {
                foreach ($contentIni->variable($tagName2, 'AvailableClasses') as $class) {
                    if (isset($classListDescription[$class])) {
                        $cellClassList[$tagName2][$class] = $classListDescription[$class];
                    } else {
                        $cellClassList[$tagName2][$class] = $class;
                    }
                }
            }
            $tpl->setVariable('cell_class_list', json_encode($cellClassList));
        }

        // run template and return result
        $this->Result = array();
        $this->Result['content'] = $tpl->fetch('design:ezoe/' . $templateName);
        $this->Result['pagelayout'] = 'design:ezoe/popup_pagelayout.tpl';
        $this->Result['persistent_variable'] = $tpl->variable('persistent_variable');
    }

}

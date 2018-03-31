<?php

namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;
use eZINI;
use eZOEXMLInput;
use ezjscPacker;
use eZPublishSDK;
use eZLocale;
use eZURI;
use eZURLOperator;
use eZTemplate;

class EzXmlField extends FieldConnector
{
    private $eZPublishVersion;

    public function __construct($attribute, $class, $helper)
    {
        parent::__construct($attribute, $class, $helper);
        $this->eZPublishVersion = eZPublishSDK::majorVersion() + eZPublishSDK::minorVersion() * 0.1;
    }

    public function getData()
    {
        if ($this->isDisplay()){
            return '<div class="clearfix float-break">' . parent::getData() . '</div>';
        }
        $rawContent = $this->getContent();
        if ($rawContent) {
            /** @var \eZContentObjectAttribute $attribute */
            $attribute = \eZContentObjectAttribute::fetch($rawContent['id'], $rawContent['version']);
            /** @var \eZXMLText $content */
            $content = $attribute->content();

            $handler = new eZOEXMLInput(
                \eZXMLTextType::rawXMLText($attribute),
                true,
                $attribute
            );

            return html_entity_decode($handler->attribute('input_xml'));
        }

        return null;
    }

    protected function isDisplay()
    {
        return $this->getHelper()->hasParameter('view') && $this->getHelper()->getParameter('view') == 'display';
    }

    public function getSchema()
    {
        $schema = parent::getSchema();

        return $schema;
    }

    public function getOptions()
    {
        $options = array(
            'type' => "ezoe",
            'hideInitValidationError' => true,
            'oesettings' => $this->getSettings()
        );

        return $options;
    }

    public function setPayload($postData)
    {
        return $postData;
    }

    private function getSettings()
    {
        $oeini = eZINI::instance('ezoe.ini');
        $designini = eZINI::instance('design.ini');

        $pluginList = $oeini->variable('EditorSettings', 'Plugins');

        $skin = $oeini->variable('EditorSettings', 'Skin');
        $skinVariant = '';
        if ($oeini->hasVariable('EditorSettings', 'SkinVariant')) {
            $skinVariant = $oeini->variable('EditorSettings', 'SkinVariant');
        }

        $editorCssList = array("skins/{$skin}/ui.css");
        if (!empty($skinVariant)) {
            $editorCssList[] = "skins/{$skin}/ui_{$skinVariant}.css";
        }

        $contentCssList = array();
        foreach ((array)$designini->variable('StylesheetSettings', 'EditorCSSFileList') as $css) {
            $contentCssList[] = str_replace('<skin>', $skin, $css);
        }

        $directionality = 'ltr';
        if ($oeini->hasVariable('EditorSettings', 'Directionality')) {
            $directionality = $oeini->variable('EditorSettings', 'Directionality');
        }

        $toolbarAlignment = 'left';
        if ($oeini->hasVariable('EditorSettings', 'ToolbarAlign')) {
            $toolbarAlignment = $oeini->variable('EditorSettings', 'ToolbarAlign');
        }

        $locale = eZLocale::instance();
        $httpLocaleCodeParts = explode('-', $locale->attribute('http_locale_code'));
        $spellLanguages = '+' . $locale->attribute('intl_language_name') . '=' . $httpLocaleCodeParts[0];

        $layoutSettings = $this->getEditorLayoutSettings();
        $buttons = array();
        foreach ($layoutSettings['buttons'] as $button) {
            if ($button != 'disable' && $button != 'help' && $button != 'fullscreen') {
                $buttons[] = $button;
            }
        }
        $buttonsString = implode(',', $buttons);
        $buttonsString = rtrim($buttonsString, ',|');

        return array(
            'mode' => 'none',
            'theme' => 'ez',
            'width' => '100%',
            'language' => '-' . eZINI::instance()->variable('RegionalSettings', 'Locale'),
            'skin' => $skin,
            'skin_variant' => $skinVariant,
            'plugins' => '-' . implode(',-', $pluginList),
            'directionality' => $directionality,
            'theme_advanced_buttons1' => $buttonsString,
            'theme_advanced_buttons2' => '',
            'theme_advanced_buttons3' => '',
            'theme_advanced_blockformats' => "p,pre,h1,h2,h3,h4,h5,h6",
            'theme_advanced_path_location' => false,
            'theme_advanced_statusbar_location' => $layoutSettings['path_location'],
            'theme_advanced_toolbar_location' => $layoutSettings['toolbar_location'],
            'theme_advanced_toolbar_align' => $toolbarAlignment,
            'theme_advanced_toolbar_floating' => true,
            'theme_advanced_resize_horizontal' => false,
            'theme_advanced_resizing' => true,
            'valid_elements' => "-strong/b[class|customattributes],-em/i[class|customattributes],span[id|type|class|title|customattributes|align|style|view|inline|alt],sub[class|type|customattributes|align],sup[class|type|customattributes|align],u[class|type|customattributes|align],pre[class|title|customattributes],ol[class|customattributes],ul[class|customattributes],li[class|customattributes],a[href|name|target|view|title|class|id|customattributes],p[class|customattributes|align|style],img[id|type|class|title|customattributes|align|style|view|inline|alt|src],table[class|border|width|id|title|customattributes|ezborder|bordercolor|align|style],tr[class|customattributes],th[class|width|rowspan|colspan|customattributes|align|style],td[class|width|rowspan|colspan|customattributes|align|style],div[id|type|class|title|customattributes|align|style|view|inline|alt],h1[class|customattributes|align|style],h2[class|customattributes|align|style],h3[class|customattributes|align|style],h4[class|customattributes|align|style],h5[class|customattributes|align|style],h6[class|customattributes|align|style],br",
            'valid_child_elements' => "a[%itrans_na],table[tr],tr[td|th],ol/ul[li],h1/h2/h3/h4/h5/h6/pre/strong/b/p/em/i/u/span/sub/sup/li[%itrans|#text]div/pre/td/th[%btrans|%itrans|#text]",
            'entities' => '160,nbsp',
            'fix_list_elements' => true,
            'fix_table_elements' => true,
            'convert_urls' => false,
            'inline_styles' => false,
            'tab_focus' => ':prev,:next',
            'theme_ez_xml_alias_list' => eZOEXMLInput::getXmlTagAliasList(),
            'theme_ez_editor_css' => implode(',', ezjscPacker::buildStylesheetFiles($editorCssList, 3)),
            'theme_ez_content_css' => implode(',', ezjscPacker::buildStylesheetFiles($contentCssList, 3)),
            'theme_ez_statusbar_open_dialog' => $oeini->variable('EditorSettings', 'TagPathOpenDialog') == 'enabled',
            'popup_css' => $this->ezdesign("stylesheets/skins/{$skin}/dialog.css"),
            'gecko_spellcheck' => true,
            'object_resizing' => false,
            'table_inline_editing' => true,
            'save_enablewhendirty' => true,
            'ez_root_url' => $this->ezroot('/'),
            'ez_extension_url' => $this->ezurl('/forms/ezoe/'),  // custom endpoint perché se c'è oggetto e attributo a disposizione
            'ez_js_url' => $this->ezroot('/extension/ezoe/design/standard/javascript/'),
            'ez_tinymce_url' => $this->ezdesign('javascript/tiny_mce.js'),
            'ez_contentobject_id' => 0,
            'ez_contentobject_version' => 0,
            'ez_form_token' => '@$ezxFormToken@',
            'spellchecker_rpc_url' => $this->ezurl('/ezoe/spellcheck_rpc'),
            'spellchecker_languages' => $spellLanguages,
            'paste_text_linebreaktype' => 'br',
        );
    }

    private function ezurl($path)
    {
        eZURI::transformURI($path);

        return $path;
    }

    private function ezroot($path)
    {
        eZURI::transformURI($path, true);

        return $path;
    }

    private function ezdesign($path)
    {
        return eZURLOperator::eZDesign(eZTemplate::factory(), $path, 'ezdesign');
    }

    private function getEditorLayoutSettings()
    {
        $oeini = eZINI::instance('ezoe.ini');
        $xmlini = eZINI::instance('ezxml.ini');

        // get global layout settings
        $editorLayoutSettings = eZOEXMLInput::getEditorGlobalLayoutSettings();

        // get custom layout features, works only in eZ Publish 4.1 and higher
        $contentClassAttribute = $this->attribute;
        $buttonPreset = $contentClassAttribute->attribute('data_text2');
        $buttonPresets = $xmlini->hasVariable('TagSettings', 'TagPresets') ? $xmlini->variable('TagSettings',
            'TagPresets') : array();

        if ($buttonPreset && isset($buttonPresets[$buttonPreset])) {
            if ($oeini->hasSection('EditorLayout_' . $buttonPreset)) {
                if ($oeini->hasVariable('EditorLayout_' . $buttonPreset, 'Buttons')) {
                    $editorLayoutSettings['buttons'] = $oeini->variable('EditorLayout_' . $buttonPreset, 'Buttons');
                }

                if ($oeini->hasVariable('EditorLayout_' . $buttonPreset, 'ToolbarLocation')) {
                    $editorLayoutSettings['toolbar_location'] = $oeini->variable('EditorLayout_' . $buttonPreset,
                        'ToolbarLocation');
                }

                if ($oeini->hasVariable('EditorLayout_' . $buttonPreset, 'PathLocation')) {
                    $editorLayoutSettings['path_location'] = $oeini->variable('EditorLayout_' . $buttonPreset,
                        'PathLocation');
                }
            }
        }

        $contentini = eZINI::instance('content.ini');
        $tags = $contentini->variable('CustomTagSettings', 'AvailableCustomTags');
        $hideButtons = array();
        $showButtons = array();

        // filter out custom tag icons if the custom tag is not enabled
        if (!in_array('underline', $tags)) {
            $hideButtons[] = 'underline';
        }

        if (!in_array('sub', $tags)) {
            $hideButtons[] = 'sub';
        }

        if (!in_array('sup', $tags)) {
            $hideButtons[] = 'sup';
        }

        if (!in_array('pagebreak', $tags)) {
            $hideButtons[] = 'pagebreak';
        }

        $user = \eZUser::currentUser();
        if ($user instanceOf \eZUser) {
            $result = $user->hasAccessTo('ezoe', 'relations');
        } else {
            $result = array('accessWord' => 'no');
        }

        if ($result['accessWord'] === 'no') {
            $hideButtons[] = 'image';
            $hideButtons[] = 'object';
            $hideButtons[] = 'file';
            $hideButtons[] = 'media';
        }

        // filter out align buttons on eZ Publish 4.0.x
        if ($this->eZPublishVersion < 4.1) {
            $hideButtons[] = 'justifyleft';
            $hideButtons[] = 'justifycenter';
            $hideButtons[] = 'justifyright';
            $hideButtons[] = 'justifyfull';
        }

        foreach ($editorLayoutSettings['buttons'] as $buttonString) {
            if (strpos($buttonString, ',') !== false) {
                foreach (explode(',', $buttonString) as $button) {
                    if (!in_array($button, $hideButtons)) {
                        $showButtons[] = trim($button);
                    }
                }
            } else if (!in_array($buttonString, $hideButtons)) {
                $showButtons[] = trim($buttonString);
            }
        }

        $editorLayoutSettings['buttons'] = $showButtons;

        return $editorLayoutSettings;
    }
}

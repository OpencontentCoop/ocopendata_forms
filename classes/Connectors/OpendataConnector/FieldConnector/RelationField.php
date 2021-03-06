<?php

namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

class RelationField extends FieldConnector
{
    const MODE_LIST_BROWSE = 0;

    const MODE_LIST_DROP_DOWN = 1;

    protected $selectionType;

    protected $defaultPlacement;

    public function __construct($attribute, $class, $helper)
    {
        parent::__construct($attribute, $class, $helper);

        $classContent = $this->attribute->dataType()->classAttributeContent($this->attribute);
        $this->selectionType = (int)$classContent['selection_type'];
        $this->defaultPlacement = $classContent['default_selection_node'] ? $classContent['default_selection_node'] : null;
    }

    public function getData()
    {
        $data = array();
        if ($rawContent = $this->getContent()) {
            foreach ($rawContent['content'] as $item) {

                $language = $this->getHelper()->getSetting('language');
                $itemName = $item['name'];
                $name = isset( $itemName[$language] ) ? $itemName[$language] : current($itemName);

                if ($this->selectionType == self::MODE_LIST_BROWSE) {
                    $data[] = array(
                        'id' => $item['id'],
                        'name' => $name,
                        'class' => $item['classIdentifier'],
                    );
                } else {
                    $data[] = (string)$item['id'];
                }
            }
        }

        return empty( $data ) ? null : $data;
    }

    public function getSchema()
    {
        $schema = array(
            "title" => $this->attribute->attribute('name'),
            'required' => (bool)$this->attribute->attribute('is_required'),
            'relation_mode' => $this->selectionType
        );
        if ($this->selectionType == self::MODE_LIST_BROWSE) {
            $schema['type'] = 'array';
            $schema['minItems'] = (bool)$this->attribute->attribute('is_required') ? 1 : 0;
            $schema['maxItems'] = 1;
        } else {
            $schema['enum'] = array();
        }

        return $schema;
    }

    public function getOptions()
    {
        $options = array(
            "helper" => $this->attribute->attribute('description'),
        );

        if ($this->selectionType == self::MODE_LIST_BROWSE) {
            $options["type"] = 'relationbrowse';
            $options["browse"] = array(
                "selectionType" => 'single',
                "addCloseButton" => true,
                "subtree" => $this->defaultPlacement ? $this->defaultPlacement : null,
                "language" => \eZLocale::currentLocaleCode(),
                "i18n" => self::i18n()
            );
        } else {
            $options["type"] = "select";
            $options["showMessages"] = false;
            $options["hideNone"] = false;
            $options["dataSource"] = $this->getDataSourceUrl();
            $options["multiple"] = false;
        }


        return $options;
    }

    public function setPayload($postData)
    {
        $postData = (array)$postData;
        foreach ($postData as $item) {
            if (is_array($item) && isset( $item['id'] )) {
                return array((int)$item['id']);
            } elseif (is_numeric($item)) {
                return array((int)$item);
            }
        }

        return null;
    }

    private function getDataSourceUrl($fields = '[metadata.id=>metadata.name]')
    {
        $query = "select-fields $fields";
        if ($this->defaultPlacement) {
            $query .= " subtree [" . $this->defaultPlacement . "] and raw[meta_main_node_id_si] != $this->defaultPlacement";
        }

        $query .= " sort [name=>asc] limit 300";

        $searchUri = "/opendata/api/content/search/";
        \eZURI::transformURI($searchUri);

        return  $searchUri . '?q=' . $query;
    }

    public static function i18n()
    {
        return [
            'clickToClose' => \ezpI18n::tr('opendata_forms', "Click to close"),
            'clickToOpenSearch' => \ezpI18n::tr('opendata_forms', "Click to open search engine"),
            'search' => \ezpI18n::tr('opendata_forms', "Search"),
            'clickToBrowse' => \ezpI18n::tr('opendata_forms', "Click to browse contents"),
            'browse' => \ezpI18n::tr('opendata_forms', "Browse"),
            'createNew' => \ezpI18n::tr('opendata_forms', "Create new"),
            'create' => \ezpI18n::tr('opendata_forms', "Create"),
            'allContents' => \ezpI18n::tr('opendata_forms', "All contents"),
            'clickToBrowseParent' => \ezpI18n::tr('opendata_forms', "Click to view parent"),
            'noContents' => \ezpI18n::tr('opendata_forms', "No contents"),
            'back' => \ezpI18n::tr('opendata_forms', "Back"),
            'goToPreviousPage' => \ezpI18n::tr('opendata_forms', "Go to previous"),
            'goToNextPage' => \ezpI18n::tr('opendata_forms', "Go to next"),
            'clickToBrowseChildren' => \ezpI18n::tr('opendata_forms', "Click to view children"),
            'clickToPreview' => \ezpI18n::tr('opendata_forms', "Click to preview"),
            'preview' => \ezpI18n::tr('opendata_forms', "Preview"),
            'closePreview' => \ezpI18n::tr('opendata_forms', "Close preview"),
            'addItem' => \ezpI18n::tr('opendata_forms', "Add"),
            'selectedItems' => \ezpI18n::tr('opendata_forms', "Selected items"),
            'removeFromSelection' => \ezpI18n::tr('opendata_forms', "Remove from selection"),
            'addItemToSelection' => \ezpI18n::tr('opendata_forms', "Add to selection"),
            'store' => \ezpI18n::tr('opendata_forms', "Store"),
        ];
    }
}

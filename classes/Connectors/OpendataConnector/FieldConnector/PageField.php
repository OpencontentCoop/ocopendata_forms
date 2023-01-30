<?php

namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;
use ezpI18n;
use eZINI;

class PageField extends FieldConnector
{
    /**
     * @var array
     */
    private $layouts;

    /**
     * @var array
     */
    private $blocks;

    public function getData()
    {
        if (!$this->canHandle()) {
            return parent::getData();
        }

        $page = parent::getData();
        $data = [];
        $blockIni = eZINI::instance('block.ini');
        foreach ($page as $index => $zone) {
            $data[$index] = $zone;
            if (is_array($zone) && isset($zone['blocks'])) {
                foreach ($zone['blocks'] as $i => $block) {
                    $type = $block['type'];
                    $data[$index]['blocks'][$i][$type . '_view'] = $data[$index]['blocks'][$i]['view'];
                    unset($data[$index]['blocks'][$i]['view']);
                    $data[$index]['blocks'][$i][$type . '_custom_attributes'] = $data[$index]['blocks'][$i]['custom_attributes'];
                    unset($data[$index]['blocks'][$i]['custom_attributes']);
                    $data[$index]['blocks'][$i][$type . '_valid_items'] = $data[$index]['blocks'][$i]['valid_items'];
                    unset($data[$index]['blocks'][$i]['valid_items']);
                    $useBrowseMode = $blockIni->variable($type, 'UseBrowseMode');
                    foreach ($data[$index]['blocks'][$i][$type . '_custom_attributes'] as $customAttribute => $value) {
                        if (isset($useBrowseMode[$customAttribute]) && $useBrowseMode[$customAttribute]) {
                            $name = '';
                            if ($node = \eZContentObjectTreeNode::fetch((int)$value)) {
                                $name = $node->getName();
                            }
                            $data[$index]['blocks'][$i][$type . '_custom_attributes'][$customAttribute] = [
                                [
                                    'node_id' => (int)$value,
                                    'name' => $name,
                                ],
                            ];
                        }
                    }
                }
            }
        }

        return $data;
    }


    public function getSchema()
    {
        if (!$this->canHandle()) {
            return parent::getSchema();
        }

        $schema = [
            'title' => $this->attribute->attribute('name'),
            'required' => (bool)$this->attribute->attribute('is_required'),
            'type' => 'object',
            'properties' => [
                'zone_layout' => [
                    'enum' => array_column($this->getLayouts(), 'type'),
//                    'title' => 'Layout',
                    'default' => $this->getLayouts()[0]['type'],
                    'required' => true,
                ],
            ],
        ];

        $blockIni = eZINI::instance('block.ini');
        $perBlockProperties = $blockDependencies = [];
        foreach ($this->getBlocks() as $type => $block) {
            $viewNames = $blockIni->variable($type, 'ViewName');
            $viewsProperties = [
                'title' => ezpI18n::tr('design/standard/datatype/ezpage', 'Block type:'),
                'enum' => array_keys($viewNames),
                'required' => true,
            ];
            $perBlockProperties[$type . '_view'] = $viewsProperties;
            $blockDependencies[$type . '_view'] = ['type'];

            $customAttributesProperties = [];
            if ($blockIni->hasVariable($type, 'CustomAttributes')) {
                $customAttributes = $blockIni->variable($type, 'CustomAttributes');
                $customAttributeTypes = $blockIni->variable($type, 'CustomAttributeTypes');
                $customAttributeNames = $blockIni->variable($type, 'CustomAttributeNames');
                $useBrowseMode = $blockIni->variable($type, 'UseBrowseMode');
//echo '<pre>';
//print_r([
//    $type,
//    $customAttributes,
//    $customAttributeNames,
//    $customAttributeTypes,
//    $useBrowseMode
//]);
//echo '</pre>';
                foreach ($customAttributes as $customAttribute) {
                    if (isset($useBrowseMode[$customAttribute]) && $useBrowseMode[$customAttribute]) {
                        $customAttributesProperties[$customAttribute] = [
                            'title' => ezpI18n::tr('design/standard/block/edit', 'Choose source'),
                            'type' => 'array',
                            'maxItems' => 1,
                        ];
                    } elseif (isset($customAttributeTypes[$customAttribute])) {
                        switch ($customAttributeTypes[$customAttribute]) {
                            case 'string':
                            case 'text':
                            case 'tag_tree_select': //@todo
                                {
                                    $customAttributesProperties[$customAttribute] = [
                                        'title' => $customAttributeNames[$customAttribute] ?? $customAttribute,
                                        'type' => 'string',
                                    ];
                                }
                                break;
                            case 'checkbox':
                                {
                                    $customAttributesProperties[$customAttribute] = [
                                        'title' => $customAttributeNames[$customAttribute] ?? $customAttribute,
                                        'type' => 'boolean',
                                    ];
                                }
                                break;
                            case 'class_select':
                            case 'state_select':
                            case 'topic_select':
                            case 'select':
                                {
                                    $enum = [];
                                    $customAttributesProperties[$customAttribute] = [
                                        'title' => $customAttributeNames[$customAttribute] ?? $customAttribute,
                                        'enum' => $enum,
                                    ];
                                }
                                break;
                        }
                    } else {
                        $customAttributesProperties[$customAttribute] = [
                            'title' => $customAttributeNames[$customAttribute] ?? $customAttribute,
                            'type' => 'string',
                        ];
                    }
                }
                $perBlockProperties[$type . '_custom_attributes'] = [
                    'type' => 'object',
                    'properties' => $customAttributesProperties,
                ];
                $blockDependencies[$type . '_custom_attributes'] = ['type'];
            }
//echo '<pre>';
//print_r($customAttributesProperties);
//echo '</pre>';
        }
//die();

        foreach ($this->getLayouts() as $layout) {
            foreach ($layout['zones'] as $zone) {
                $schema['properties'][$zone['id']] = [
//                    "title" => $zone['name'],
                    'type' => 'object',
                    'properties' => [
                        'zone_id' => [
                            'type' => 'string',
                        ],
                        'blocks' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => array_merge([
                                    'block_id' => [
                                        'type' => 'string',
                                    ],
                                    'name' => [
                                        'title' => ezpI18n::tr('design/standard/block/edit', 'Name:'),
                                        'type' => 'string',
                                    ],
                                    'type' => [
                                        'type' => 'string',
                                        'title' => ezpI18n::tr('design/standard/block/view', 'Block:'),
                                        'enum' => array_keys($this->getBlocks()),
                                        'required' => true,
                                    ],
                                ], $perBlockProperties),
                                'dependencies' => $blockDependencies,
                            ],
                        ],
                    ],
                ];
            }
        }
        if ($this->getHelper()->hasParameter('debug')) {
            echo '<pre>';
            print_r($schema);
            print_r($this->getOptions());
            print_r($this->getData());
            \eZExecution::cleanExit();
        }
        return $schema;
    }

    public function getOptions()
    {
        if (!$this->canHandle()) {
            return [
                'hidden' => true,
                'disabled' => true,
            ];
        }

        $blockIni = eZINI::instance('block.ini');
        $perBlockFields = [];
        foreach ($this->getBlocks() as $type => $block) {
            $viewNames = $blockIni->variable($type, 'ViewName');
            $viewsFields = [
                'optionLabels' => array_values($viewNames),
                'dependencies' => [
                    'type' => [$type],
                ],
            ];
            $perBlockFields[$type . '_view'] = $viewsFields;

            $customAttributesFields = [];
            $customAttributes = $blockIni->variable($type, 'CustomAttributes');
            $customAttributeTypes = $blockIni->variable($type, 'CustomAttributeTypes');
            $customAttributeNames = $blockIni->variable($type, 'CustomAttributeNames');
            $useBrowseMode = $blockIni->variable($type, 'UseBrowseMode');
            foreach ($customAttributes as $customAttribute) {
                if (isset($useBrowseMode[$customAttribute]) && $useBrowseMode[$customAttribute]) {
                    $customAttributesFields[$customAttribute] = [
                        'type' => 'locationbrowse',
                    ];
                } else {
                }
            }
            $customAttributes = [
                'fields' => $customAttributesFields,
                'dependencies' => [
                    'type' => [$type],
                ],
            ];

            $perBlockFields[$type . '_custom_attributes'] = $customAttributes;
        }

        $options = [
            'helper' => $this->attribute->attribute('description'),
            'fields' => [
                'zone_layout' => [
                    'optionLabels' => array_column($this->getLayouts(), 'name'),
                ],
            ],
        ];

        foreach ($this->getLayouts() as $layout) {
            foreach ($layout['zones'] as $zone) {
                $options['fields'][$zone['id']] = [
                    'fields' => [
                        'zone_id' => [
                            'hidden' => true,
                        ],
                        'blocks' => [
                            'toolbarSticky' => true,
//                            'type' => 'table',
                            'items' => [
                                'fields' => array_merge([
                                    'block_id' => [
                                        'hidden' => true,
                                    ],
                                    'type' => [
                                        'optionLabels' => array_column($this->getBlocks(), 'Name'),
                                    ],
                                ], $perBlockFields),
                            ],
                        ],
                    ],
                ];
            }
        }

        return $options;
    }

    public function setPayload($postData)
    {
        $rawContent = $this->getContent();
        print_r($rawContent);
        die();
        return $postData;
    }

    private function canHandle()
    {
        return count($this->getLayouts()) == 1;
    }

    private function getLayouts()
    {
        if ($this->layouts === null) {
            $this->layouts = [];
            $zones = \eZFunctionHandler::execute('ezflow', 'allowed_zones', []);
            foreach ($zones as $zone) {
                if (in_array($this->class->Identifier, $zone['classes'])) {
                    $this->layouts[] = $zone;
                }
            }
        }

        return $this->layouts;
    }

    private function getBlocks()
    {
        if ($this->blocks === null) {
            $this->blocks = [];
            $blockIni = eZINI::instance('block.ini');
            $types = $blockIni->variable('General', 'AllowedTypes');
            foreach ($types as $type) {
                $this->blocks[$type] = $blockIni->group($type);
                $this->blocks[$type]['Type'] = $type;
            }
        }

        return $this->blocks;
    }
}

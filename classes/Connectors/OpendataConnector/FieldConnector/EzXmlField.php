<?php

namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

class EzXmlField extends FieldConnector
{
    public function getSchema()
    {
        $schema = parent::getSchema();
        //$schema['type'] = 'tinymce';
        return $schema;
    }

    public function getOptions()
    {
        $options = array(
            /*'type' => "tinymce",
            'hideInitValidationError' => true,
            'toolbar' => 'bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link',


            "toolbar": [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']]
            ],
            */
            "type" => "summernote",
            "summernote" => array(
                "toolbar" => array (
                    array('style', array('bold', 'italic', 'underline', 'clear')),
                    array('font', array('strikethrough', 'superscript', 'subscript')),
                    array('para', array('ul', 'ol', 'paragraph')),
                    array('insert', array('link', 'table', 'hr')),
                    array('insert', array('undo', 'redo'))
                )
            ),
            "helper" => $this->attribute->attribute('description'),
        );

        return $options;
    }
}

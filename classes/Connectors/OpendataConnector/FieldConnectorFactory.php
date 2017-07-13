<?php

namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector;

use eZContentClass;
use eZContentClassAttribute;
use eZINI;

class FieldConnectorFactory
{
    /**
     * @param eZContentClassAttribute $attribute
     * @param eZContentClass $class
     * @param $helper
     *
     * @return FieldConnectorInterface
     */
    public static function load($attribute, $class, $helper)
    {
        $settings = eZINI::instance('ocopendata_connectors.ini')->group('FieldSettings');

        $customFieldConnector = null;

        $classIdentifier = $class->attribute('identifier');
        $identifier = $attribute->attribute('identifier');
        $dataType = $attribute->attribute('data_type_string');

        if (isset( $settings['FieldConnectors'][$classIdentifier . '/' . $identifier] )) {
            $customFieldConnector = $settings['FieldConnectors'][$classIdentifier . '/' . $identifier];

        } elseif (isset( $settings['FieldConnectors'][$identifier] )) {
            $customFieldConnector = $settings['FieldConnectors'][$identifier];

        } elseif (isset( $settings['FieldConnectors'][$dataType] )) {
            $customFieldConnector = $settings['FieldConnectors'][$dataType];

        } else {
            $defaults = self::getDefaultFieldConnectors();
            if (isset( $defaults[$dataType] )) {
                $customFieldConnector = $defaults[$dataType];
            }
        }

        if ($customFieldConnector) {
            return new $customFieldConnector($attribute, $class, $helper);
        } else {
            return new FieldConnector($attribute, $class, $helper);
        }

    }

    private static function getDefaultFieldConnectors()
    {
        return array(
            'ezselection' => '\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\SelectionField',
            'ezprice' => '\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\PriceField',
            'ezkeyword' => '\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\KeywordsField',
            'eztags' => '\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\TagsField',
            'ezgmaplocation' => '\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\GeoField',
            'ezdate' => '\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\DateField',
            'ezdatetime' => '\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\DateTimeField',
            'eztime' => '\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\TimeField',
            'ezmatrix' => '\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\MatrixField',
            'ezxmltext' => '\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\EzXmlField',
            'ezauthor' => '\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\AuthorField',
            'ezobjectrelation' => '\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\RelationField',
            'ezobjectrelationlist' => '\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\RelationsField',
            'ezbinaryfile' => '\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\FileField',
            //'ezmedia' => '\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\FileField', //@todo
            'ezimage' => '\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\ImageField',
            'ezpage' => '\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\PageField',
            'ezboolean' => '\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\BooleanField',
            'ezuser' => '\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\UserField',
            'ezfloat' => '\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\FloatField',
            'ezinteger' => '\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\IntegerField',
            'ezstring' => '\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\StringField',
            'ezsrrating' => '\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\RatingField',
            'ezemail' => '\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\EmailField',
            'ezcountry' => '\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\CountryField',
            'ezurl' => '\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\UrlField',
            'eztext' => '\Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\TextField',
        );
    }
}

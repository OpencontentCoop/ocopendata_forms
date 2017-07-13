<?php

namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

class RatingField extends BooleanField
{
    public function setPayload($postData)
    {
        return (bool)$postData ? '1' : '0'; //@todo non va perché ezsrRatingType non ha fromString!
    }

    public function getOptions()
    {
        return array(
            "helper" => $this->attribute->attribute('description'),
            'type' => 'checkbox',
            'rightLabel' => \ezpI18n::tr( 'extension/ezstarrating/datatype', 'Disabled' )
        );
    }
}

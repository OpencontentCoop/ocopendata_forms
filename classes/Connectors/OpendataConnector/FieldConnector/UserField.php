<?php

namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector;
use eZUser;
use eZContentObject;
use Exception;

class UserField extends FieldConnector
{
    public function getSchema()
    {
        return array(
            "type" => "object",
            "title" => $this->attribute->attribute('name'),
            "properties" => array(
                "login" => array(
                    "title" => \ezpI18n::tr( 'design/standard/content/datatype', 'Username' ),
                    "type" => "string",
                    "readonly" => $this->getHelper()->hasParameter('object'),
                ),
                "email" => array(
                    "title" => \ezpI18n::tr( 'design/standard/content/datatype', 'Email' ),
                    "format" => "email"
                )
            ),
            'required' => (bool)$this->attribute->attribute('is_required')
        );
    }

    public function getOptions()
    {
        return array(
            "helper" => $this->attribute->attribute('description'),
            "fields" => array(
                "login" => array(
                    "autocomplete" => 'off',
                    "disabled" => $this->getHelper()->hasParameter('object'),
                ),
                "email" => array(
                    "autocomplete" => 'off'
                )
            )

        );
    }

    public function setPayload($postData)
    {
        // workaround per permettere modifica email
        if ($this->getHelper()->hasParameter('object') && isset($postData['email'])){
            $user = eZUser::fetch((int)$this->getHelper()->getParameter('object'));
            if ($user instanceof eZUser && $user->attribute('email') !== $postData['email']){
                $alreadyExists = eZUser::fetchByEmail($postData['email']);
                if ($alreadyExists instanceof eZUser){
                    throw new Exception("Indirizzo email già in uso", 1);
                }else{                    
                    $user->setAttribute('email', $postData['email']);                    
                    $userObject = eZContentObject::fetch($user->id());
                    if ($userObject instanceof eZContentObject){
                        foreach ($userObject->attribute( 'contentobject_attributes' ) as $contentObjectAttribute){
                            if ($contentObjectAttribute->attribute( 'data_type_string' ) === 'ezuser'){
                                $contentObjectAttribute->setAttribute('data_text', $this->serializeDraft( $user ));
                                $user->store();
                                $contentObjectAttribute->store();
                            }
                        }
                    }
                }
            }
        }
        return $this->getHelper()->hasParameter('object') ? null : $postData;
    }

    private function serializeDraft(eZUser $user)
    {
        return json_encode(
            array(
                 'login' => $user->attribute( 'login' ),
                 'password_hash' => $user->attribute( 'password_hash' ),
                 'email' => $user->attribute( 'email' ),
                 'password_hash_type' => $user->attribute( 'password_hash_type' )
            )
        );
    }
}

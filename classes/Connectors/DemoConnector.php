<?php

namespace Opencontent\Ocopendata\Forms\Connectors;


class DemoConnector extends AbstractBaseConnector
{
    protected function getData()
    {
        if ($this->hasParameter('demo')) {
            return array(
                "name" => "Diego Maradona",
                "feedback" => "Very impressive.",
                "ranking" => "excellent"
            );
        }

        return array();
    }

    protected function getSchema()
    {
        return array(
            "title" => "User Feedback",
            "description" => "What do you think about Alpaca?",
            "type" => "object",
            "properties" => array(
                "name" => array(
                    "type" => "string",
                    "title" => "Name",
                    "required" => true
                ),
                "feedback" => array(
                    "type" => "string",
                    "title" => "Feedback"
                ),
                "ranking" => array(
                    "type" => "string",
                    "title" => "Ranking",
                    "enum" => array('excellent', 'ok', 'so so'),
                    "required" => true
                )
            )
        );
    }

    protected function getOptions()
    {
        return array(
            "form" => array(
                "attributes" => array(
                    "action" => $this->getHelper()->getServiceUrl('action'),
                    "method" => "post"
                ),
                "buttons" => array(
                    "submit" => array()
                )
            ),
            "helper" => "Tell us what you think about Alpaca!",
            "fields" => array(
                "name" => array(
                    "size" => 20,
                    "helper" => "Please enter your name."
                ),
                "feedback" => array(
                    "type" => "textarea",
                    "name" => "your_feedback",
                    "rows" => 5,
                    "cols" => 40,
                    "helper" => "Please enter your feedback."
                ),
                "ranking" => array(
                    "type" => "select",
                    "helper" => "Select your ranking.",
                    "optionLabels" => array(
                        "Awesome!",
                        "It's Ok",
                        "Hmm..."
                    )
                )
            )
        );
    }

    protected function getView()
    {
        return array(
            "parent" => "bootstrap-edit",
            "locale" => "it_IT"
        );
    }

    protected function submit()
    {
        return $_POST;
    }

    protected function upload()
    {
        return $_POST;
    }

}

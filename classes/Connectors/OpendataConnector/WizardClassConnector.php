<?php

namespace Opencontent\Ocopendata\Forms\Connectors\OpendataConnector;


class WizardClassConnector extends ClassConnector
{
    public function getView()
    {
        $view = parent::getView();

        if ($this->getHelper()->hasSetting('Wizard')){

            $stepsData = array();
            foreach($this->getHelper()->getSetting('Wizard') as $identifier){
                $stepsData[] = array(
                    "title" => $this->getHelper()->getSetting('Wizard_' . $identifier . '_title'),
                    "description" => $this->getHelper()->getSetting('Wizard_' . $identifier . '_description'),
                    "fields" => explode(',', $this->getHelper()->getSetting('Wizard_' . $identifier . '_fields')),
                );
            }

            $steps = array();
            $bindings = array();
            foreach($stepsData as $i => $step){
                $index = $i + 1;
                $fields = $step['fields'];
                $bindings = array_merge(
                    $bindings,
                    array_fill_keys($fields, $index)

                );
                unset($step['fields']);
                $steps[] = $step;
            }

            $view["wizard"] = array(
                "bindings" => $bindings,
                "steps" => $steps
            );
        }

        return $view;
    }
}

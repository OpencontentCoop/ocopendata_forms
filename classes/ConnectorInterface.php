<?php

namespace Opencontent\Ocopendata\Forms;


interface ConnectorInterface
{
    public function getIdentifier();

    public function setSettings($settings);

    public function getSettings();

    public function setSetting($key, $value);

    public function getSetting($key);

    public function hasSetting($key);

    public function setParameter($key, $value);

    public function getParameter($key);

    public function hasParameter($key);

    public function runService($serviceIdentifier);
}

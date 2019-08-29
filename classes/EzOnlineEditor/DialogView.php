<?php

namespace Opencontent\Ocopendata\Forms\EzOnlineEditor;


class DialogView extends AbstractView implements ViewInterface
{
    public function __construct($dialog)
    {
        if ($dialog === '') {
            throw new \Exception(
                \ezpI18n::tr('design/standard/ezoe', 'Invalid or missing parameter: %parameter', null,
                    array('%parameter' => 'Dialog'))
            );
        }

        $ezoeInfo = \eZExtension::extensionInfo('ezoe');

        $tpl = \eZTemplate::factory();
        $tpl->setVariable('object', array());
        $tpl->setVariable('object_id', 0);
        $tpl->setVariable('object_version', 0);

        $tpl->setVariable('ezoe_name', $ezoeInfo['name']);
        $tpl->setVariable('ezoe_version', $ezoeInfo['version']);
        $tpl->setVariable('ezoe_copyright', $ezoeInfo['copyright']);
        $tpl->setVariable('ezoe_license', $ezoeInfo['license']);
        $tpl->setVariable('ezoe_info_url', $ezoeInfo['info_url']);

        // use persistent_variable like content/view does, sending parameters
        // to pagelayout as a hash.
        $tpl->setVariable('persistent_variable', array());

        // run template and return result
        $this->Result = array();
        $this->Result['content'] = $tpl->fetch('design:ezoe/' . $dialog . '.tpl');
        $this->Result['pagelayout'] = 'design:ezoe/popup_pagelayout.tpl';
        $this->Result['persistent_variable'] = $tpl->variable('persistent_variable');
    }
}

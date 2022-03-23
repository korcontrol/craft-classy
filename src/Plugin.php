<?php

namespace korcontrol\classy;

use Craft;

class Plugin extends \craft\base\Plugin
{
    public function init()
    {
        parent::init();

        if (Craft::$app->request->getIsSiteRequest()) {
            Craft::$app->view->registerTwigExtension(new Extension());
        }
    }
}

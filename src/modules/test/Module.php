<?php

namespace markhuot\craftpest\modules\test;

use yii\base\Module as BaseModule;

class Module extends BaseModule
{
    function init()
    {
        $this->controllerNamespace = 'markhuot\craftpest\tests\module\test\controllers';
        
        // echo debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        // die;
        if (\Craft::$app->request->isConsoleRequest) {
            $this->controllerNamespace = 'markhuot\craftpest\tests\module\test\console';
        }
    }
}
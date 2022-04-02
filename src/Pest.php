<?php

namespace markhuot\craftpest;

use craft\base\Plugin;
use markhuot\craftpest\services\Http;

/**
 * @property Http $http
 * @method static self getInstance()
 */
class Pest extends Plugin {

    function init()
    {
        $this->controllerNamespace = 'markhuot\\craftpest\\console';

        parent::init();
    }

}

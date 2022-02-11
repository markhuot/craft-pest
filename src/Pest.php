<?php

namespace markhuot\craftpest;

use craft\base\Plugin;
use craft\elements\Entry;
use craft\events\DefineBehaviorsEvent;
use markhuot\craftpest\services\Http;
use yii\base\Event;

/**
 * @property Http $http
 * @method static self getInstance()
 */
class Pest extends Plugin {

    function init() {
        // Set the controllerNamespace based on whether this is a console or web request
        $this->controllerNamespace = 'markhuot\\craftpest\\console';

        parent::init();
    }

}

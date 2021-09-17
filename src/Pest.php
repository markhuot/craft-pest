<?php

namespace markhuot\craftpest;

use craft\base\Plugin;

class Pest extends Plugin {

    function init() {
        // Set the controllerNamespace based on whether this is a console or web request
        $this->controllerNamespace = 'markhuot\\craftpest\\console';

        parent::init();
    }

}

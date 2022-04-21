<?php

namespace markhuot\craftpest;

use craft\base\Plugin;
use craft\elements\Entry;
use craft\elements\GlobalSet;
use craft\events\DefineBehaviorsEvent;
use markhuot\craftpest\behaviors\Macroable;
use markhuot\craftpest\services\Http;
use yii\base\Event;

/**
 * @property Http $http
 * @method static self getInstance()
 */
class Pest extends Plugin {

    function init()
    {
        $this->controllerNamespace = 'markhuot\\craftpest\\console';

        parent::init();

        $macroableClasses = [
            Entry::class,
            GlobalSet::class,
        ];

        foreach ($macroableClasses as $class) {
            Event::on($class, $class::EVENT_DEFINE_BEHAVIORS, function (DefineBehaviorsEvent $event) {
                $event->behaviors[] = Macroable::class;
            });
        }
    }

}

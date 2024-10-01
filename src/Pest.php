<?php

namespace markhuot\craftpestplugin;

use craft\base\Field;
use craft\base\Plugin;
use craft\elements\db\ElementQuery;
use craft\elements\Entry;
use craft\events\DefineBehaviorsEvent;
use craft\events\PluginEvent;
use craft\services\Fields;
use craft\services\Plugins;
use markhuot\craftpest\actions\CopyInitialStubs;
use markhuot\craftpest\actions\RenderCompiledClasses;
use markhuot\craftpest\behaviors\ExpectableBehavior;
use markhuot\craftpest\behaviors\FieldTypeHintBehavior;
use markhuot\craftpest\behaviors\TestableElementBehavior;
use markhuot\craftpest\behaviors\TestableElementQueryBehavior;
use yii\base\Event;
use function markhuot\craftpest\helpers\base\service;

/**
 * @method static self getInstance()
 */
class Pest extends Plugin {

    function init()
    {
        parent::init();

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if (is_a($event->plugin, Pest::class)) {
                    service(CopyInitialStubs::class)();
                }
            }
        );
    }
}

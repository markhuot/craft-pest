<?php

namespace markhuot\craftpest;

use craft\base\Field;
use craft\elements\db\ElementQuery;
use craft\elements\Entry;
use craft\events\DefineBehaviorsEvent;
use craft\events\PluginEvent;
use craft\services\Fields;
use craft\services\Plugins;
use markhuot\craftpest\actions\RenderCompiledClasses;
use markhuot\craftpest\behaviors\ExpectableBehavior;
use markhuot\craftpest\behaviors\FieldTypeHintBehavior;
use markhuot\craftpest\behaviors\TestableElementBehavior;
use markhuot\craftpest\behaviors\TestableElementQueryBehavior;
use markhuot\craftpest\console\IdeController;
use markhuot\craftpest\console\PestController;
use yii\base\BootstrapInterface;
use yii\base\Event;

/**
 * @method static self getInstance()
 */
class Pest implements BootstrapInterface {

    function bootstrap($app)
    {
        \Craft::setAlias('@markhuot/craftpest', __DIR__);

        if (\Craft::$app->request->isConsoleRequest) {
            \Craft::$app->controllerMap['pest'] = PestController::class;
        }

        Event::on(
            Entry::class,
            Entry::EVENT_DEFINE_BEHAVIORS,
            function (DefineBehaviorsEvent $event) {
                $event->behaviors[] = ExpectableBehavior::class;
                $event->behaviors[] = TestableElementBehavior::class;
            }
        );

        Event::on(
            ElementQuery::class,
            ElementQuery::EVENT_DEFINE_BEHAVIORS,
            function (DefineBehaviorsEvent $event) {
                $event->behaviors[] = TestableElementQueryBehavior::class;
            }
        );

        Event::on(
            Field::class,
            Field::EVENT_DEFINE_BEHAVIORS,
            function (DefineBehaviorsEvent $event) {
                $event->behaviors[] = FieldTypeHintBehavior::class;
            }
        );

        Event::on(
            Fields::class,
            Fields::EVENT_AFTER_SAVE_FIELD,
            function () {
                (new RenderCompiledClasses)->handle();
            }
        );
    }
}

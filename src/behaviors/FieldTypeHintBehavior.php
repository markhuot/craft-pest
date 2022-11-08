<?php

namespace markhuot\craftpest\behaviors;

use craft\fields\Assets;
use craft\fields\Categories;
use craft\fields\Color;
use craft\fields\Date;
use craft\fields\Lightswitch;
use craft\fields\PlainText;
use yii\base\Behavior;

class FieldTypeHintBehavior extends Behavior
{
    function getTypeHintFactory()
    {
        switch (get_class($this->owner)) {
            case Lightswitch::class:
                return 'boolean $value';

            case PlainText::class:
            case Color::class:
                return 'string $value';

            case Categories::class:
                return 'int[]|\craft\elements\Category[] $value';

            case Assets::class:
                return 'int[]|\craft\elements\Asset[] $value';

            case Date::class:
                return 'int|string|\DateTime $value';
        }

        return 'mixed $value';
    }
}

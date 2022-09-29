<?php

namespace markhuot\craftpest\factories;

use craft\helpers\StringHelper;
use markhuot\craftpest\test\QueryRecorder;
use yii\db\Connection;
use yii\db\Query;

/**
 * @method void context(string $context)
 */
class Field extends Factory
{
    protected $type;

    function type(string $type)
    {
        $this->type = $type;

        return $this;
    }

    function group(string $groupName)
    {
        $this->attributes['groupId'] = function () use ($groupName) {
            foreach (\Craft::$app->fields->getAllGroups() as $group) {
                if ($group->name === $groupName) {
                    return $group->id;
                }
            }

            return self::NULL;
        };

        return $this;
    }

    /**
     * Get the element to be generated
     *
     * @return \craft\base\Field
     */
    function newElement()
    {
        $fieldClass = $this->type;

        return new $fieldClass;
    }

    /**
     * The faker definition
     *
     * @return array
     */
    function definition(int $index = 0) {
        $name = $this->faker->words(2, true);
        $handle = StringHelper::toCamelCase($name);
        $firstFieldGroupId = \Craft::$app->fields->getAllGroups()[0]->id;

        return [
            'name' => $name,
            'handle' => $handle,
            'groupId' => $firstFieldGroupId,
        ];
    }

    /**
     * Persist the entry to local
     */
    function store($field): bool
    {
        $result = \Craft::$app->fields->saveField($field);
        QueryRecorder::record($field);

        return $result;
    }


    // $config = craft\helpers\App::dbConfig();
    //            return Craft::createObject($config);

}

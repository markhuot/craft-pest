<?php

namespace markhuot\craftpest\factories;

use craft\helpers\StringHelper;

/**
 * @method self name(string $name)
 * @method self handle(string $name)
 * @method void context(string $context)
 * @deprecated
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
        $firstFieldGroupId = \Craft::$app->fields->getAllGroups()[0]->id;

        return [
            'name' => $name,
            'groupId' => $firstFieldGroupId,
        ];
    }

    function inferences(array $definition = [])
    {
        if (empty($definition['handle']) && !empty($definition['name'])) {
            $definition['handle'] = StringHelper::toCamelCase($definition['name']);
        }

        return $definition;
    }

    /**
     * Persist the entry to local
     */
    function store($element): bool
    {
        return \Craft::$app->fields->saveField($element);
    }

}

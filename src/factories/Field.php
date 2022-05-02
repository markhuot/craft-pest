<?php

namespace markhuot\craftpest\factories;

use craft\helpers\StringHelper;

class Field extends Factory
{
    protected $type;

    function type(string $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the element to be generated
     *
     * @return \craft\records\Field
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

        return [
            'name' => $name,
            'handle' => $handle,
        ];
    }

    /**
     * Persist the entry to local
     *
     * @return \craft\records\Field
     */
    function store($element)
    {
        \Craft::$app->fields->saveField($element);
    }

}

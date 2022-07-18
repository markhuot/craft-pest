<?php

namespace markhuot\craftpest\factories;

use craft\models\MatrixBlockType;

class BlockType extends Factory
{
    use Fieldable;

    function definition(int $index = 0)
    {
        $name = $this->faker->words(2, true);

        return [
            'name' => $name,
            'handle' => \craft\helpers\StringHelper::toCamelCase($name),
        ];
    }

    function newElement()
    {
        return new MatrixBlockType();
    }

    function store($blockType)
    {
        throw new \Exception('Block types can not be saved on their own. They must be saved via their parent Matrix Field.');
    }
}

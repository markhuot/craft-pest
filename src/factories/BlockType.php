<?php

namespace markhuot\craftpest\factories;

use craft\helpers\StringHelper;
use craft\models\MatrixBlockType;

/**
 * @method self name(string $name)
 * @method self handle(string $name)
 * @deprecated
 */
class BlockType extends Factory
{
    use Fieldable;

    function definition(int $index = 0)
    {
        $name = $this->faker->words(2, true);

        return [
            'name' => $name,
        ];
    }

    function inferences(array $definition = [])
    {
        if (empty($definition['handle']) && !empty($definition['name'])) {
            $definition['handle'] = StringHelper::toCamelCase($definition['name']);
        }

        return $definition;
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

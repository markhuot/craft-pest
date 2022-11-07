<?php

namespace markhuot\craftpest\factories;

use craft\fields\Matrix;
use craft\models\MatrixBlockType;

/**
 * @mixin
 */
trait AddsMatrixBlocks
{
    function handlesMagicAddsMatrixBlocksCall($key, $args)
    {
        return true;
    }

    function callMagicAddsMatrixBlocksCall($key, $args)
    {
        preg_match('/^add(.+)Block$/', $key, $matches);
        if (empty($matches[1])) {
            throw new \Exception('Could not determine a field name from the method name [' . $key . ']');
        }
        $fieldName = lcfirst($matches[1]);
        return $this->addBlockTo($fieldName, ...$args);
    }

    function addBlockTo(Matrix|string $fieldOrHandle, ...$args)
    {
        if (is_string($fieldOrHandle)) {
            /** @var Matrix $field */
            $field = \Craft::$app->fields->getFieldByHandle($fieldOrHandle);
        }
        else if (is_a($fieldOrHandle, Matrix::class)) {
            $field = $fieldOrHandle;
        }

        if (empty($field)) {
            throw new \Exception('Could not determine a field to add to from key [' . $fieldOrHandle . ']');
        }

        if (!empty($args[0]) && is_string($args[0])) {
            $blockType = collect($field->getBlockTypes())->where('handle', '=', $args[0])->first();
            array_shift($args);
        }
        else if (!empty($args[0]) && is_a($args[0], MatrixBlockType::class)) {
            $blockType = $args[0];
            array_shift($args);
        }
        else {
            $blockType = $field->getBlockTypes()[0];
        }


        if (!empty($args[0]) && is_array($args[0])) {
            $fieldData = $args[0];
        }
        else {
            $fieldData = $args;
        }

        $this->set($field->handle, Block::factory()
            ->type($blockType)
            ->set($fieldData)
        );

        return $this;
    }
}

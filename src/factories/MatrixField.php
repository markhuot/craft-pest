<?php

namespace markhuot\craftpest\factories;

use craft\models\MatrixBlockType;
use function markhuot\craftpest\helpers\base\version_greater_than_or_equal_to;

class MatrixField extends Field
{
    protected $blockTypes = [];

    function blockTypes(...$blockTypes)
    {
        if (is_array($blockTypes[0])) {
            $this->blockTypes = array_merge($this->blockTypes, $blockTypes[0]);
        }
        else {
            $this->blockTypes = array_merge($this->blockTypes, $blockTypes);
        }

        return $this;
    }

    function addBlockType($blockType)
    {
        $this->blockTypes[] = $blockType;

        return $this;
    }

    /**
     * Get the element to be generated
     */
    function newElement()
    {
        return new \craft\fields\Matrix;
    }

    function store($element): bool
    {
        // Push the block types in to the field
        $element->blockTypes = collect($this->blockTypes)
            ->map
            ->make()
            ->flatten()
            ->each(function ($blockType, $index) use ($element) {
                $blockType->fieldId = $element->id;
                $blockType->sortOrder = $index;
            })
            ->toArray();
            
        // Store the field, which also saves the block types
        $result = parent::store($element);
        
        // Add the fields in to the block types
        collect($this->blockTypes)
            ->zip($element->blockTypes)
            ->each(function ($props) {
                /** @var MatrixBlockType $blockType */
                [$factory, $blockType] = $props;
                $factory->storeFields($blockType->fieldLayout, $blockType);

                if (version_greater_than_or_equal_to(\Craft::$app->version, '3')) {
                    $blockType->fieldLayoutId = $blockType->fieldLayout->id;
                    \Craft::$app->matrix->saveBlockType($blockType);
                }
            });

        return $result;
    }
}

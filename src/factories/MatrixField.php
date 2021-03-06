<?php

namespace markhuot\craftpest\factories;

use craft\fields\Matrix;

class MatrixField extends Field
{
    //use Fieldable;

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

    /**
     * Get the element to be generated
     *
     * @return \craft\records\Field
     */
    function newElement()
    {
        return new \craft\fields\Matrix;
    }

    function store($element)
    {
        // Push the block types in to the field
        $element->blockTypes = collect($this->blockTypes)
            ->map
            ->make()
            ->flatten()
            ->each(function ($blockType, $index) use ($element) {
                $blockType->fieldId = $element->id;
                $blockType->sortOrder = $index;
            });
            
        // Store the field, which also saves the block types
        parent::store($element);
        
        // Add the fields in to the block types
        collect($this->blockTypes)
            ->zip($element->blockTypes)
            ->each(function ($props) {
                [$factory, $blockType] = $props;
                $factory->storeFields($blockType->fieldLayout, $blockType);
            });
    }
}
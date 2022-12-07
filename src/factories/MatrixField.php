<?php

namespace markhuot\craftpest\factories;

use craft\fields\Matrix;
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

    /**
     * @param Matrix $element
     */
    function store($element): bool
    {
        // Push the block types in to the field
        $element->setBlockTypes(
            collect($this->blockTypes)
            ->map
            ->make()
            ->flatten()
            ->each(function ($blockType, $index) use ($element) {
                $blockType->fieldId = $element->id;
                $blockType->sortOrder = $index;
            })
            ->toArray()
        );
            
        // Store the field, which also saves the block types
        $result = parent::store($element);

        // If we have an error, stop here because it will be impossible to save
        // block types on an unsaved/errored matrix field
        if ($result === false) {
            return $result;
        }
        
        // Add the fields in to the block types
        collect($this->blockTypes)
            ->zip($element->getBlockTypes())
            ->each(function ($props) {
                /** @var MatrixBlockType $blockType */
                [$factory, $blockType] = $props;
                $factory->storeFields($blockType->fieldLayout, $blockType);

                if (version_greater_than_or_equal_to(\Craft::$app->version, '3')) {
                    $blockType->fieldLayoutId = $blockType->fieldLayout->id;
                    \Craft::$app->matrix->saveBlockType($blockType);
                }
            });

        // In Craft 3.7 the Matrix Field model stores a reference to the `_blockTypes` of the
        // matrix. Inside that reference the block type stores a reference to its `fieldLayoutId`.
        //
        // The reference to the Matrix Field is cached in to \Craft::$app->fields->_fields when the
        // field is created and it's cached without a valid `fieldLayoutId`.
        //
        // The following grabs the global \Craft::$app->fields->field reference to this matrix field
        // and updates the block types by pulling them fresh from the database. This ensures everything
        // is up to date and there are no null fieldLayoutId values.
        /** @var Matrix $cachedMatrixField */
        $cachedMatrixField = \Craft::$app->fields->getFieldById($element->id);
        $cachedMatrixField->setBlockTypes(\Craft::$app->matrix->getBlockTypesByFieldId($element->id));

        return $result;
    }
}

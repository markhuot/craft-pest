<?php

namespace markhuot\craftpest\factories;

class Category extends Element {

    /** @var string */
    protected $groupHandle;

    function group($handle) {
        $this->groupHandle = $handle;

        return $this;
    }

    function newElement() {
        return new \craft\elements\Category();
    }

    function definition(int $index = 0)
    {
        /** @var \craft\elements\Category $group */
        $group = \Craft::$app->categories->getGroupByHandle($this->groupHandle);

        return array_merge(parent::definition($index), [
            'groupId' => $group->id,
        ]);
    }
    
    /**
     * @inheritdoc
     */
    protected function setAttributes($attributes, $element)
    {
        /** @var \craft\elements\Category $element */
        // Set `groupId` early on the element so that it does break when trying to retrieve the field layout
        if (isset($attributes['groupId'])) {
            $element->groupId = $attributes['groupId'];
            unset($attributes['groupId']);
        }

        return parent::setAttributes($attributes, $element);
    }
}

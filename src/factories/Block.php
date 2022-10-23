<?php

namespace markhuot\craftpest\factories;

class Block extends Element
{
    /** @var string|null */
    protected $type;
    protected bool $enabled = true;

    function type($type)
    {
        $this->type = $type;

        return $this;
    }

    function enabled(bool $enabled=true)
    {
        $this->enabled = $enabled;

        return $this;
    }

    function definition(int $index = 0)
    {
        return [];
    }

    function newElement()
    {
        return [];
    }

    /**
     * @param array $attributes
     * @param array $element
     */
    protected function setAttributes($attributes, $element) // @phpstan-ignore-line since Element::setAttribute types $element as an Element, but here it's an array
    {
        $element['type'] = $this->type;
        $element['enabled'] = $this->enabled;

        foreach ($attributes as $key => $value) {
            $element['fields'][$key] = $value;
        }

        return $element;
    }

    function store($element)
    {
        // no-op, blocks can't be stored directly, they are returned
        // as arrays for their parent element/field to store.
    }
}

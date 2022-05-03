<?php

namespace markhuot\craftpest\factories;

use craft\models\FieldLayout;

trait Fieldable
{
    protected $fields = [];

    function fields(array $fields)
    {
        $this->fields = $fields;

        return $this;
    }

    function storeFields(FieldLayout $fieldLayout, $context=null)
    {
        if (!empty($this->fields)) {
            $fields = collect($this->fields)
                ->map(function ($f) use ($context) {
                    if (is_a($f, Field::class)) {
                        $f->context($context ? 'matrixBlockType:' . $context->uid : 'global');
                        return $f->create();
                    }

                    return $f;
                })
                ->flatten(1)
                ->toArray();

            $fieldLayout->setFields($fields);
            \Craft::$app->fields->saveLayout($fieldLayout);
        }
    }
}
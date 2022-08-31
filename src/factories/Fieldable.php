<?php

namespace markhuot\craftpest\factories;

use craft\fieldlayoutelements\CustomField;
use craft\models\FieldLayout;
use function markhuot\craftpest\helpers\base\version_greater_than_or_equal_to;

trait Fieldable
{
    protected $fields = [];

    function fields(array $fields)
    {
        $this->fields = array_merge($this->fields, $fields);

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
                ->map(function (\craft\base\Field $f) {
                    if (version_greater_than_or_equal_to(\Craft::$app->version, '4')) {
                        \Craft::$app->fields->saveField($f);
                        return new CustomField($f);
                    }

                    return $f;
                })
                ->flatten(1)
                ->toArray();

            if (version_greater_than_or_equal_to(\Craft::$app->version, '3')) {
                $fieldLayout->setFields($fields);
            }
            else if (version_greater_than_or_equal_to(\Craft::$app->version, '4')) {
                $fieldLayout->getTabs()[0]->setElements($fields);
            }

            \Craft::$app->fields->saveLayout($fieldLayout);
        }
    }
}

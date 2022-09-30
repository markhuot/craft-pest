<?php

namespace markhuot\craftpest\factories;

use craft\fieldlayoutelements\CustomField;
use craft\models\FieldLayout;
use craft\models\FieldLayoutTab;
use function markhuot\craftpest\helpers\base\array_wrap;
use function markhuot\craftpest\helpers\base\version_greater_than_or_equal_to;

trait Fieldable
{
    protected $fields = [];

    function addField($field)
    {
        $this->fields[] = $field;

        return $this;
    }

    /**
     * @param array|\craft\base\Field $fields
     */
    function fields(...$fields)
    {
        if (is_array($fields[0])) {
            $fields = $fields[0];
        }

        $this->fields = array_merge($this->fields, array_wrap($fields));

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

            if (version_greater_than_or_equal_to(\Craft::$app->version, '4')) {
                if (empty($fieldLayout->getTabs()[0])) {       // @phpstan-ignore-line
                    $fieldLayoutTab = new FieldLayoutTab();    // @phpstan-ignore-line
                    $fieldLayoutTab->name = 'Content';         // @phpstan-ignore-line
                    $fieldLayoutTab->sortOrder = 1;            // @phpstan-ignore-line
                    $fieldLayout->setTabs([$fieldLayoutTab]);  // @phpstan-ignore-line
                }

                $fieldLayout->getTabs()[0]->setElements($fields);
            }
            else if (version_greater_than_or_equal_to(\Craft::$app->version, '3')) {
                $fieldLayout->setFields($fields);              // @phpstan-ignore-line
            }

            \Craft::$app->fields->saveLayout($fieldLayout);
        }
    }
}

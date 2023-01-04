<?php

namespace markhuot\craftpest\factories;

use craft\fieldlayoutelements\CustomField;
use craft\models\FieldLayout;
use craft\models\FieldLayoutTab;
use markhuot\craftpest\exceptions\ModelStoreException;
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
        if (empty($this->fields)) {
            return;
        }

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

        if (empty($fieldLayout->getTabs()[0])) {
            $fieldLayoutTab = new FieldLayoutTab();
            $fieldLayoutTab->name = 'Content';
            $fieldLayoutTab->sortOrder = 1;
            $fieldLayout->setTabs([$fieldLayoutTab]);
        }

        if (version_greater_than_or_equal_to(\Craft::$app->version, '4')) {
            $fieldLayout->getTabs()[0]->setElements(           // @phpstan-ignore-line
                collect($fields)->map(function ($field) {      // @phpstan-ignore-line
                    \Craft::$app->fields->saveField($field);   // @phpstan-ignore-line
                    $fieldElement = new CustomField($field);   // @phpstan-ignore-line
                    if ($field->required) {                    // @phpstan-ignore-line
                        $fieldElement->required = true;        // @phpstan-ignore-line
                    }                                          // @phpstan-ignore-line
                    return $fieldElement;                      // @phpstan-ignore-line
                })->toArray()                                  // @phpstan-ignore-line
            );                                                 // @phpstan-ignore-line
        }
        else if (version_greater_than_or_equal_to(\Craft::$app->version, '3')) {
            $fieldLayout->getTabs()[0]->setFields($fields);    // @phpstan-ignore-line
            $fieldLayout->setFields($fields);                  // @phpstan-ignore-line
        }

        if (!\Craft::$app->fields->saveLayout($fieldLayout)) {
            throw new ModelStoreException($fieldLayout);
        }
    }
}

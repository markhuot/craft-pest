<?php

namespace markhuot\craftpest\test;

use craft\base\ElementInterface;
use craft\base\FieldInterface;
use craft\base\Model;
use craft\db\Table;
use craft\models\Section;
use craft\services\Elements;
use craft\services\Fields;
use craft\services\Sections;
use craft\services\Volumes;
use yii\base\InvalidConfigException;

class QueryRecorder
{
    private static array $records = [];

    public function __construct(
        protected Fields $fieldService,
        protected Elements $elementsService,
        protected Sections $sectionsService
    ) {}

    public static function record(Model $model): void
    {
        QueryRecorder::$records[] = $model;
    }


    public function rollback(): void
    {
        echo "rollback called" . PHP_EOL;

        if (count(QueryRecorder::$records) === 0) {
            return;
        }

        // Try to delete Entries first
        foreach (QueryRecorder::$records as $model) {
           if (is_a($model, ElementInterface::class)) {
               try {
                   $this->elementsService->deleteElement($model, true);
                   echo 'deleting: ' . get_class($model) . ':'. $model->id . PHP_EOL;
               } catch (InvalidConfigException) {

               }

           }
        }

        // and Fields and Sections later
        foreach (QueryRecorder::$records as $model) {
            if (is_a($model, FieldInterface::class)) {
                $this->fieldService->deleteField($model);
                echo 'deleting: ' . get_class($model) . ':'. $model->handle . PHP_EOL;
                continue;
            }
            if (is_a($model, Section::class)) {
                $this->sectionsService->deleteSectionById($model->id);
                echo 'deleting: ' . get_class($model) . ':'. $model->handle . PHP_EOL;
            }
        }

        \Craft::$app->gc->hardDelete([Table::SECTIONS, Table::FIELDLAYOUTS]);
    }
}

<?php

namespace markhuot\craftpest\test;

use craft\base\ElementInterface;
use craft\base\FieldInterface;
use craft\base\Model;
use craft\models\Section;
use craft\services\Elements;
use craft\services\Fields;
use craft\services\Sections;
use craft\services\Volumes;

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
        if (count(QueryRecorder::$records) === 0) {
            return;
        }

        foreach (QueryRecorder::$records as $model) {
           if (is_a($model, FieldInterface::class)) {
               $this->fieldService->deleteField($model);
               continue;
           }
           if (is_a($model, ElementInterface::class)) {
                $this->elementsService->deleteElement($model, true);
               continue;
           }
            if (is_a($model, Section::class)) {
                $this->sectionsService->deleteSection($model);
            }
        }
    }
}

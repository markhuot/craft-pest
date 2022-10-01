<?php

namespace markhuot\craftpest\exceptions;

use yii\base\Model;

class ModelStoreException extends \Exception
{
    function __construct(Model $model)
    {
        $message = implode(" ", $model->getErrorSummary(false));
        parent::__construct($message);
    }
}

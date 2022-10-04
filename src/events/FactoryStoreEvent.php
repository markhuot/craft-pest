<?php

namespace markhuot\craftpest\events;

use craft\events\CancelableEvent;
use markhuot\craftpest\factories\Factory;

class FactoryStoreEvent extends CancelableEvent
{
    /** @var Factory */
    public $sender;

    /** @var mixed */
    public $model;
}

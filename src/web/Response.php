<?php

namespace markhuot\craftpest\web;

use markhuot\craftpest\behaviors\TestableResponseBehavior;

/**
 * @mixin TestableResponseBehavior
 */
class Response extends \craft\web\Response
{
    public function behaviors(): array
    {
        return [TestableResponseBehavior::class];
    }

    /**
     * Make prepare() publicly accessible
     */
    public function prepare(): void
    {
        parent::prepare();
    }
}

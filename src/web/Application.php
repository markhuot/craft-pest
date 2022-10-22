<?php

namespace markhuot\craftpest\web;

class Application extends \craft\web\Application
{
    public function bootstrap(): void
    {
        $this->request->setIsConsoleRequest(false);

        parent::bootstrap();
    }
}

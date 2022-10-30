<?php

namespace markhuot\craftpest\web;

class Application extends \craft\web\Application
{
    public function bootstrap(): void
    {
        $this->request->setIsConsoleRequest(false);

        if (\Craft::$app->config->getGeneral()->devMode) {
            $this->request->headers->add('X-Debug', 'enable');
        }

        parent::bootstrap();
    }
}

<?php

namespace markhuot\craftpest\traits;

use markhuot\craftpest\base\Benchmark as BaseBenchmark;
use yii\debug\Module;

trait Benchmark
{
    /**
     * You can start a benchmark at any time. It does not have to come first in your
     * test.
     */
    function beginBenchmark()
    {
        \Craft::getLogger()->flush();

        // It would be nice to conditionally enable the debug bar when this is called
        // but theres a lot of setup in \craft\web\Application::bootstrapSebug() that
        // we don't want to take ownership of right now.
        // \Craft::$app->db->enableLogging = true;
        // \Craft::$app->db->enableProfiling = true;
        // \Craft::createObject(['class' => 'yiisoft\\debug\\Module']);

        // Because we can't dynamically load the sebug bar we'll require DEV_MODE be
        // enabled by the user if they get here.
        if (!\Craft::$app->config->getGeneral()->devMode) {
            throw new \Exception('You must enable devMode to use benchmarking.');
        }

        return $this;
    }

    /**
     * Ending a benchmark returns a testable Benchmark class
     */
    function endBenchmark()
    {
        $messages = \Craft::getLogger()->messages;
        
        return new BaseBenchmark($messages);
    }

    function tearDownBenchmark()
    {
        $this->endBenchmark();
    }
}
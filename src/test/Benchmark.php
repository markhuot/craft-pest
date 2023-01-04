<?php

namespace markhuot\craftpest\test;

use markhuot\craftpest\web\BenchmarkResult;

trait Benchmark
{
    /**
     * Benchmarks are started on your test case by calling `->beginBenchmark()`. You are
     * free to start as many benchmarks as needed, however, note that starting a new
     * benchmark will clear out any existing benchmarks already in progress.
     * 
     * > **Warning**
     * > In order to use a benchmark you must enable Craft's `devMode` (which
     * will enable the Yii Debug Bar).
     */
    function beginBenchmark()
    {
        // It would be nice to conditionally enable the debug bar when this is called
        // but theres a lot of setup in \craft\web\Application::bootstrapDebug() that
        // we don't want to take ownership of right now.
        // \Craft::$app->db->enableLogging = true;
        // \Craft::$app->db->enableProfiling = true;
        // \Craft::createObject(['class' => 'yiisoft\\debug\\Module']);
        
        // Because we can't dynamically load the debug bar we'll require DEV_MODE be
        // enabled by the user if they get here.
        if (!\Craft::$app->config->getGeneral()->devMode) {
            throw new \Exception('You must enable devMode to use benchmarking.');
        }

        // Normally each request bootstraps its own logTarget with a unique tag each
        // time. However, because we're running multiple requests through a single
        // logTarget we need to manually update thentag (triggering independant log)
        // files to be written.
        \craft\debug\Module::getInstance()->logTarget->tag = uniqid();

        return $this;
    }

    /**
     * Ending a benchmark returns a testable Benchmark class. You can end a benchmark
     * by calling `->endBenchmark()` on the test case or on a response. Either of the
     * following will work,
     * 
     * ```php
     * it('ends on the test case', function () {
     *   $this->beginBenchmark();
     *   $this->get('/');
     *   $benchmark = $this->endBenchmark();
     * });
     * ```
     * 
     * ```php
     * it('ends on the response', function () {
     *   $this->beginBenchmark()
     *      ->get('/')
     *      ->endBenchmark();
     * });
     * ```
     * 
     * > **Note**
     * > Unlike the traditional Craft request/response lifecycle you are
     * free to make multiple requests in a single benchmark.
     */
    function endBenchmark()
    {
        \craft\debug\Module::getInstance()?->logTarget->export();
        
        return new BenchmarkResult();
    }

    function tearDownBenchmark()
    {
        $this->endBenchmark();
    }
}

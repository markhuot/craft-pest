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
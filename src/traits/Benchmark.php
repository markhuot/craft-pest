<?php

namespace markhuot\craftpest\traits;

use markhuot\craftpest\base\Benchmark as BaseBenchmark;

trait Benchmark
{
    function beginBenchmark()
    {
        \Craft::getLogger()->flush();
    }

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
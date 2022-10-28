<?php

namespace markhuot\craftpest\traits;

use markhuot\craftpest\base\Benchmark as BaseBenchmark;
use yii\debug\Module;

trait Benchmark
{
    function beginBenchmark()
    {
        \Craft::getLogger()->flush();

        return $this;
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
<?php

namespace markhuot\craftpest\traits;

trait Benchmarkable
{
    function endBenchmark()
    {
        return test()->endBenchmark();
    }
}
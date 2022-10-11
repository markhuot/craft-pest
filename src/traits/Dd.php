<?php

namespace markhuot\craftpest\traits;

use Symfony\Component\VarDumper\VarDumper;

trait Dd
{
    /**
     * Does a dump on the class
     */
    public function dd(): void
    {
        VarDumper::dump($this);
        exit(1);
    }
}
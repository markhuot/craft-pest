<?php

namespace markhuot\craftpest\traits;

use Symfony\Component\VarDumper\VarDumper;

trait Dd
{
    /**
     * Does a dump on the class
     */
    public function dd($var=null): void
    {
        VarDumper::dump($var ?? $this);
        exit(1);
    }
}
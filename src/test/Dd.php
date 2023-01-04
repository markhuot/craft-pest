<?php

namespace markhuot\craftpest\test;

use Symfony\Component\VarDumper\VarDumper;

trait Dd
{
    /**
     * Does a dump on the class
     */
    public function dd($var=null): void
    {
        if (is_callable($var)) {
            $var = $var($this);
        }
        else {
            $var = $var ?? $this;
        }

        VarDumper::dump($var);
        exit(1);
    }
}

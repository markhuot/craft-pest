<?php

it('serves', function (){
    expect($this->serve())
        ->exitCode->toBe(0);
});
<?php

use \markhuot\craftpest\factories\Entry;

it('creates entries')
    ->factory(Entry::class)
    ->fooBar()
    ->create();

(new Entry)->colorField('foo')->categoryField([1,2,3])->assetsField([1,2,3]);

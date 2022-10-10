<?php

it ('test', function () {
    $this->get('/form')
        ->fill('firstFormTextField', 'test')
        ->submit()
        ->querySelector('#form-data')
        ->expect()
        ->innerHTML->toBe(json_encode(['firstFormTextField' => 'test']));
});
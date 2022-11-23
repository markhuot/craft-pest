<?php

it('renders the page with a form')
    ->get('/page-with-basic-form')
    ->assertOk()
    ->form();


it('is unhappy when no form found')
    ->expectExceptionMessage("Unable to select form.")
    ->get('/response-test')
    ->assertOk()
    ->form();


it('can fill a field and collect existing fields', function () {
    $formResponse = $this->get('/page-with-basic-form')
        ->assertOk();
    
    $formResponse->getRequest()
        ->assertMethod('get');

    $submitResponse = $formResponse
        ->fill('second', 'updated value')
        ->submit()
        ->assertOk();

    $submitResponse->getRequest()
        ->assertMethod('post')
        ->assertBody([
            'first' => 'prefilled',
            'second' => 'updated value',
            'third' => 'foo',
        ]);
});


it('can deal with many forms on one page')
    ->get('/page-with-multiple-forms')
    ->assertOk()
    ->form('#form2');


it('can fill fields with array style names')
    ->get('/page-with-multiple-forms')
    ->assertOk()
    ->form('#form3')
    ->fill('row[two]', 'updated')
    ->submit()
    ->getRequest()
    ->assertBody([
        'row' => [
            'one' => 'one',
            'two' => 'updated',
            'three' => 'three'
        ]
    ]);


it('does not see disabled fields', function () {
    $this->get('/page-with-multiple-forms')
        ->assertOk()
        ->form('#form4')
        ->submit()
        ->getRequest()
        ->expect()

        // row[one] exists but is disabled
        ->bodyParams->not->toHaveKey('row.one')
        ->bodyParams->toHaveKey('row.two');
});


it('works with select fields', function () {
    $form = $this->get('/page-with-multiple-forms')
        ->assertOk()
        ->form('#form5');

    $initalState = $form->getFields();
    $selectByName = $form->select('country', 'Ukraine')->getFields();
    $selectByValue = $form->select('country', 'DE')->getFields();

    expect($initalState)->toBe(['country' => '']);
    expect($selectByName)->toBe(['country' => 'UA']);
    expect($selectByValue)->toBe(['country' => 'DE']);
});

it('works with select fields on single form pages', function () {
    $form = $this->get('/page-with-basic-form')
        ->assertOk()
        ->select('third', 'baz')
        ->submit()
        ->getRequest()
        ->expect()
        ->bodyParams->toMatchArray(['third' => 'baz']);
});



it('can create virtual fields', function() {
    $this->get('/page-with-basic-form')
        ->assertOk()
        ->form()
        ->addField('does-not-exist', 'theValue')
        ->submit()
        ->getRequest()
        ->expect()
        ->bodyParams->toMatchArray([
            'first' => 'prefilled',
            'does-not-exist' => 'theValue'
        ]);
});

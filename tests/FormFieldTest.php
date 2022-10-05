<?php

it('renders the page with a form')
    ->get('/page-with-basic-form')
    ->assertOk()
    ->form();


it('is unhappy when no form found', function () {
    $this->expectExceptionMessage("Unable to select form.");
    $this->get('/response-test')
        ->assertOk()
        ->form();
});


it('can fill a field and collect existing fields', function () {
    $fields = $this->get('/page-with-basic-form')
        ->assertOk()
        ->fill('second', 'updated value')
        ->getFields();

    expect($fields)->toBe([
        'first' => 'prefilled',
        'second' => 'updated value'
    ]);
});


it('can deal with many forms on one page')->get('/page-with-multiple-forms')
    ->assertOk()
    ->form('#form2');


it('can fill fields with array style names', function () {
    $fields = $this->get('/page-with-multiple-forms')
        ->assertOk()
        ->form('#form3')
        ->fill('row[two]', 'updated')
        ->getFields();

    expect($fields)->toBe([
        'row' => [
            'one' => 'one',
            'two' => 'updated',
            'three' => 'three'
        ]
    ]);
});


it('does not see disabled fields', function () {
    $fields = $this->get('/page-with-multiple-forms')
        ->assertOk()
        ->form('#form4')
        ->getFields();

    // row[one] exists but is disabled
    expect($fields)->toBe([
        'row' => [
            'two' => 'two'
        ]
    ]);
});


it('works with select fields', function () {
    $form = $this->get('/page-with-multiple-forms')
        ->assertOk()
        ->form('#form5');

    $initalState = $form->getFields();
    $selectedState = $form->select('country', 'UA')->getFields();

    expect($initalState)->toBe(['country' => '']);
    expect($selectedState)->toBe(['country' => 'UA']);
});

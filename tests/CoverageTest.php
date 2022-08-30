<?php

use function markhuot\craftpest\helpers\http\get;

beforeEach(function () {
    \Craft::setAlias('@templates', __DIR__ . '/templates');
    \Craft::$app->view->setTemplateMode('site');
    //dd(\Craft::$app->view->twig->isStrictVariables());
});

get('/')->assertOk();
get('/loop-test')->assertOk();
// get('/?foo=1')->assertOk();

// it('covers loops', function () {
//     // $section = \Craft::$app->sections->getSectionByHandle('news');
//     // if ($section) {
//     //     \Craft::$app->sections->deleteSection($section);
//     // }
//
//     \markhuot\craftpest\factories\Section::factory()
//         ->name('News')
//         ->handle('news')
//         ->create();
//
//     \markhuot\craftpest\factories\Entry::factory()
//         ->section('news')
//         ->create();
//
//     get('/loop-test')->assertOk();
// })->skip();

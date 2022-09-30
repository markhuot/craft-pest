<?php

use function markhuot\craftpest\helpers\model\entry;
use function markhuot\craftpest\helpers\model\user;

it ('creates entries with no props')
    ->expect(fn () => entry('posts')->create())
    ->errors->toBeEmpty()
    ->enabled->toBeTrue()
    ->expiryDate->toBeNull()
    ->author->toBeNull();

it ('disables posts')
    ->expect(fn () => entry('posts')->enabled(false)->create())
    ->enabled->toBeFalse()
    ->status->toBe(\craft\elements\Entry::STATUS_DISABLED);

it ('sets post date')
    ->expect(fn () => entry('posts')
        ->postDate('2122-12-13 12:01:01')
        ->create()
    )
    ->postDate->format('F j, Y g:i A')->toBe('December 13, 2122 12:01 PM')
    ->status->toBe(\craft\elements\Entry::STATUS_PENDING);

it ('throws on bad post date', function () {
    $this->expectException(Exception::class);

    entry('posts')->postDate('foo bar')->create();
});

it('sets expiry date')
    ->expect(fn () => entry('posts')
        ->expiryDate('2022-12-13 12:01:01')
        ->create()
    )
    ->expiryDate->format('F j, Y g:i A')->toBe('December 13, 2022 12:01 PM');

it('sets author by user object', function () {
    $user = user()->create();

    entry('posts')->author($user)->create()->expect()
        ->author->id->toBe($user->id);
});

it('sets author by id', function () {
    $user = user()->create();

    entry('posts')->author($user->id)->create()->expect()
        ->author->id->toBe($user->id);
});

it('sets author by username', function () {
    $user = user()->create();

    entry('posts')->author($user->username)->create()->expect()
        ->author->id->toBe($user->id);
});

it('sets entry parent')
    ->skip()
    ->expect(fn () => entry('posts')
        ->parent(entry('posts')->create()->id)
        ->create()
    )
    ->parent->not->toBeNull();

<?php

namespace markhuot\craftpest\factories;

use craft\base\ElementInterface;
use craft\models\EntryType;
use craft\models\Section;
use Faker\Factory as Faker;
use Illuminate\Support\Collection;

class User extends Element {

    function newElement() {
        return new \craft\elements\User;
    }

    function definition(int $index = 0)
    {
        $email = $this->faker->email;

        return [
            'email' => $email,
            'username' => $email,
        ];
    }

}

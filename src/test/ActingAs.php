<?php

namespace markhuot\craftpest\test;

use craft\web\User;
use markhuot\craftpest\factories\User as UserFactory;

trait ActingAs
{

    function actingAs(UserFactory|User|string|callable $userOrName = null): self
    {
        if (is_string($userOrName)) {
            $user = \Craft::$app->getUsers()->getUserByUsernameOrEmail($userOrName);
        }
        else if (is_a($userOrName, User::class)) {
            $user = $userOrName;
        }
        else if (is_a($userOrName, UserFactory::class))
        {
            $user = $userOrName->create();
        }
        else if (is_callable($userOrName)) {
            $user = $userOrName();
        }


        if (empty($user)) {
            throw new \Exception('Unknown user `' . $userOrName . '`');
        }

        \Craft::$app->getUser()->setIdentity($user);

        return $this;
    }

    function tearDownActingAs()
    {
        \Craft::$app->getUser()->logout(false);
    }

}

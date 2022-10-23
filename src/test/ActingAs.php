<?php

namespace markhuot\craftpest\test;

use craft\elements\User;

trait ActingAs
{

    /**
     * @param User|string $userOrName
     */
    function actingAs($userOrName = null): self
    {
        if (is_string($userOrName)) {
            $user = \Craft::$app->getUsers()->getUserByUsernameOrEmail($userOrName);
        }
        else if (is_a($userOrName, User::class)) {
            $user = $userOrName;
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

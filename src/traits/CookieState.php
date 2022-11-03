<?php

namespace markhuot\craftpest\traits;

use yii\web\Cookie;
use yii\web\CookieCollection;

trait CookieState
{
    protected CookieCollection $cookies;

    function setUpCookieState()
    {
        $this->clearCookieCollection();
    }

    function tearDownCookieState()
    {
        $this->clearCookieCollection();
    }

    function clearCookieCollection()
    {
        $this->cookies = new CookieCollection([], ['readOnly' => false]);
    }

    function storeCookieCollection($cookies)
    {
        if (empty($cookies)) {
            return $this;
        }

        /** @var Cookie $cookie */
        foreach ($cookies as $cookie) {
            $this->cookies->add($cookie);
        }
        
        // We have to manually clear our expired cookies because this is normally handled
        // by the browser for us
        foreach ($this->cookies as $cookie) {
            if ($cookie->expire !== 0 && $cookie->expire < time()) {
                $this->cookies->remove($cookie, false);
            }
        }

        return $this;
    }

    function getCookieCollection()
    {
        return $this->cookies;
    }
}
<?php

namespace markhuot\craftpest\test;

use yii\web\Cookie;
use yii\web\CookieCollection;

/**
 * # Cookies
 * 
 * Craft-pest simulates a browser's cookie storage throughout a single test. That
 * means that cookies are retained through multiple requests in a single test.
 * 
 * For example, the following may set a logged in cookie on the first request.
 * That cookie will then be retained through the subsequent requests.
 * 
 * ```php
 * it ('logs a user in and navigates their dashboard', function () {
 *   $this->get('/login')
 *     ->fill('username', 'michael')
 *     ->fill('password', '***')
 *     ->submit()
 *     ->assertOk();
 * 
 *   $this->get('/dashboard')->assertOk();
 *   $this->get('/dashboard/secret-page')->assertOk();
 *   $this->get('/dashboard/dangerous-page')->assertOk();
 * });
 * ```
 * 
 * > **Note**
 * > This is a verbose and slow way to manage login. It's better to use the
 * > `->actingAs()` method on a test to log a user in.
 */
trait CookieState
{
    protected CookieCollection $cookies;

    /**
     * When a test starts we need to ensure the cookie collection is typed
     * and empty.
     * 
     * @internal
     */
    function setUpCookieState()
    {
        $this->clearCookieCollection();
    }

    /**
     * When a test ends we want to clean up, to be safe. Honestly the `setUp`
     * method should handle this for us, but in case a developer is doing ther
     * own `tearDown` we want the cookie collection to be blanked out so they
     * have a consistent experience.
     * 
     * @internal
     */
    function tearDownCookieState()
    {
        $this->clearCookieCollection();
    }

    /**
     * If you need to clear the stored cookies mid-test you can call
     * `test()->clearCookieCollection()`.
     */
    function clearCookieCollection()
    {
        $this->cookies = new CookieCollection([], ['readOnly' => false]);

        return $this;
    }

    /**
     * Stores cookies from the passed cookie collection in the test state
     * so they can be re-sent to subsequent requests in the same test.
     * 
     * > *Warning* This is automatically called after every response so that cookies
     * may be retained through a test. Expired cookies that come back from
     * a test are automatically pruned, mimicing the functionality of
     * a browser.
     */
    function storeCookieCollection(?CookieCollection $cookies)
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

    /**
     * Get the stored cookie collection
     */
    function getCookieCollection()
    {
        return $this->cookies;
    }
}

<?php

namespace markhuot\craftpest\traits;

use markhuot\craftpest\http\RequestBuilder;
use markhuot\craftpest\web\TestableResponse;

/**
 * # Requests
 * 
 * You can simulate requests to the Craft application via several helper methods
 * on the `TestCase` as well as via the standalone `Http` helpers. The most
 * common helper is `$this->get('uri')` or `get('uri')`. This will make a request
 * to Craft at the given `'uri'` and return a [`TestableResponse`](assertions/response.md).
 * 
 * You can kick off a request in a classic test,
 * 
 * ```php
 * it ('gets something', function () {
 *   $this->get('/')->assertOk();
 * });
 * ```
 * 
 * Using Pest's higher order proxies you can do the same thing without a closure,
 * 
 * ```php
 * it('gets something')
 *   ->get('/')
 *   ->assertOk();
 * ```
 * 
 * And, lastly, you can skip the description all together and use a descriptionless
 * test.
 * 
 * ```php
 * use function markhuot\craftpest\helpers\Http\get;
 * 
 * get('/')->assertOk();
 * ```
 * 
 * All of these are functionally identical. You are free to select the syntax that reads
 * the most naturally for your test and provides the right context for the test. For
 * more information on the test context see, [Getting Started](getting-started.md).
 */
trait RequestBuilders
{
    /**
     * Makes a `GET` request to Craft.
     */
    function get(string $uri): TestableResponse
    {
        return (new RequestBuilder('get', $uri))
            ->send();
    }

    /**
     * Makes a `POST` request to Craft.
     * 
     * ```php
     * $this->post('/comments', [
     *   'author' => '...',
     *   'body' => '...',
     * ])->assertOk();
     * ```
     * 
     * Because _many_ `POST` requests need to send the CSRF token along with the
     * request it is handled automatically within the `->post()` method. If
     * you would prefer to handle this yourself you may use the raw `->http()` method
     * insetad. The above `/comments` example is functionally similar to,
     * 
     * ```php
     * $this->http('post', '/comments')
     *   ->withCsrfToken()
     *   ->setBody(['author' => '...', 'body' => '...'])
     *   ->send()
     *   ->assertOk();
     * ```
     */
    function post(string $uri, array $body=[]): TestableResponse
    {
        return (new RequestBuilder('post', $uri))
            ->withCsrfToken()
            ->setBody($body)
            ->send();
    }

    /**
     * Maes a `POST` request to Craft with the `action` param filled in to the
     * passed value.
     */
    function action(string $action, array $body=[]): TestableResponse
    {
        return (new RequestBuilder('post', ''))
            ->withCsrfToken()
            ->setBody([
                'action' => $action,
                ...$body,
            ])
            ->send();
    }

    /**
     * Generate a raw HTTP request without any conventions applied.
     */
    function http(string $method, string $uri): RequestBuilder
    {
        return new RequestBuilder($method, $uri);
    }
}
<?php

namespace markhuot\craftpest\behaviors;

use markhuot\craftpest\dom\NodeList;
use markhuot\craftpest\web\TestableResponse;
use Symfony\Component\DomCrawler\Crawler;
use yii\base\Behavior;

/**
 * A testable response is returned whenever you perform a HTTP request
 * with Pest. It is an extension of Craft's native Response with a
 * number of convience methods added for testing. For example, most
 * tests will perform a `get()` and want to check that the response did
 * not return an error. You may use `->assertOk()` to check that the
 * status code was 200.
 * 
 * @property \craft\web\Response $owner
 */
class TestableResponseBehavior extends Behavior {

    public TestableResponse $response;

    public function attach($owner)
    {
        parent::attach($owner);

        if (is_a($owner, TestableResponse::class)) {
            $this->response = $owner;
        }
    }


    /**
     * If the response returns HTML you can `querySelector()` to inspect the
     * HTML for specific content. The `querySelector()` method takes a
     * CSS selector to look for (just like in Javascript).
     * 
     * The return from `querySelector()` is always a `NodeList` containing zero
     * or more nodes. You can interact with the `NodeList` regardless of the return
     * and you will get back a scalar value or a collection of values.
     * 
     * ```php
     * $response->querySelector('h1')->text; // returns the string contents of the h1 element
     * $response->querySelector('li')->text; // returns a collection containing the text of all list items
     * ```
     */
    function querySelector(string $selector) {
        $html = $this->response->content;
        $crawler = new Crawler($html);
        return new NodeList($crawler->filter($selector));
    }

    /**
     * Runs the same `querySelector()` against the response's HTML but instead
     * of returning a `NodeList` it returns an expectation against the `NodeList`.
     * This allows you to use Pest's expectation API against the found nodes.
     * 
     * ```php
     * $response->expectSelector('h1')->text->toBe('Hello World!');
     * ```
     */
    function expectSelector(string $selector) {
       return $this->querySelector($selector)->expect();
    }

    /**
     * Starts an expectation on the response. This allows you to use the expectation
     * API on Craft's response properties.
     *
     * ```php
     * $response->expect()->statusCode->toBe(200);
     * ```
     */
    public function expect() {
        return test()->expect($this);
    }

    /**
     * Checks that the response contains the given cookie. When not passed a value
     * the assertion only checks the presence of the cookie. When passed a value the
     * value will be checked for strict equality.
     *
     * ```php
     * $response->assertCookie('cookieName'); // checks presence, with any value
     * $response->assertCookie('cookieName', 'cookie value'); // checks that the values match
     * ```
     */
    function assertCookie(string $name, string $value=null) {
        if ($value === null) {
            test()->assertContains($name, array_keys($this->response->cookies->toArray()));
        }
        else {
            test()->assertSame($this->response->cookies->getValue($name), $value);
        }


        return $this->response;
    }

    /**
     * Checks that the given cookie has an expiration in the past. Cookies are sent in headers and if left
     * unset a cookie will persist from request to request. Therefore, the only way to "remove" a cookie
     * is to set its expiration to a date in the past (negative number). This is common when logging people out.
     *
     * ```php
     * $response->assertCookieExpired('cookieName');
     * ```
     */
    function assertCookieExpired(string $name) {
        // First check that the cookie exists
        $this->assertCookie($name);

        // Then check the expiration of it
        $cookie = $this->response->cookies->get($name);
        if ($cookie->expire >= 0) {
            test()->fail('Cookie `' . $name . '` does not have an expiration in the past.');
        }

        return $this->response;
    }

    /**
     * Checks that the given cookie has an expiration in the future.
     *
     * ```php
     * $response->assertCookieNotExpired('cookieName');
     * ```
     */
    function assertCookieNotExpired(string $name) {
        // First check that the cookie exists
        $this->assertCookie($name);

        // Then check the expiration of it
        $cookie = $this->response->cookies->get($name);
        if ($cookie->expire < 0) {
            test()->fail('Cookie `' . $name . '` does not have an expiration in the future.');
        }

        return $this->response;
    }

    /**
     * Checks that the given cookie is not present in the response
     *
     * ```php
     * $response->assertCookieMissing('cookieName');
     * ```
     */
    function assertCookieMissing(string $name) {
        // First check that the cookie exists
        test()->assertNotContains($name, array_keys($this->response->cookies->toArray()));

        return $this->response;
    }

    /**
     * Checks that the response has a 201 Created status code
     *
     * ```php
     * $response->assertCreated();
     * ```
     */
    function assertCreated() {
        return $this->assertStatus(201);
    }

    /**
     * Checks that the given string does not appear in thr response.
     *
     * ```php
     * $response->assertDontSee('text that should not be in the response');
     * ```
     */
    function assertDontSee(string $text) {
        test()->assertStringNotContainsString($text, $this->response->content);

        return $this->response;
    }

    /**
     * Checks that the given string does not appear in the response after first stripping all non-text elements (like HTML) from the response.
     * For example, if the response contains `foo <em>bar</em>` you could check against the text `foo bar` because the `<em>` will be stripped.
     *
     * ```php
     * $response->assertDontSeeText('foo bar');
     * ```
     */
    function assertDontSeeText(string $text) {
        test()->assertStringNotContainsString($text, preg_replace('/\s+/', ' ', strip_tags($this->response->data)));
        return $this->response;
    }

    /**
     * Checks that the response contains a file download, optionally checking that the filename of the download
     * matches the given filename.
     *
     * ```php
     * $response->assertDownload(); // checks that any download is returned
     * $response->assertDownload('file.jpg'); // checks that a download with the name `file.jpg` is returned
     * ```
     */
    function assertDownload(string $filename=null) {
        $contentDisposition = explode(';', $this->response->headers->get('content-disposition'));

        if (trim($contentDisposition[0]) !== 'attachment') {
            test()->fail(
                'Response does not offer a file download.'.PHP_EOL.
                'Disposition ['.trim($contentDisposition[0]).'] found in header, [attachment] expected.'
            );
        }

        if (! is_null($filename)) {
            if (isset($contentDisposition[1]) &&
                trim(explode('=', $contentDisposition[1])[0]) !== 'filename') {
                test()->fail(
                    'Unsupported Content-Disposition header provided.'.PHP_EOL.
                    'Disposition ['.trim(explode('=', $contentDisposition[1])[0]).'] found in header, [filename] expected.'
                );
            }

            $message = "Expected file [{$filename}] is not present in Content-Disposition header.";

            if (! isset($contentDisposition[1])) {
                test()->fail($message);
            } else {
                test()->assertSame(
                    $filename,
                    isset(explode('=', $contentDisposition[1])[1])
                        ? trim(explode('=', $contentDisposition[1])[1], " \"'")
                        : '',
                    $message
                );

                return $this;
            }
        } else {
            test()->assertTrue(true);

            return $this;
        }
    }

    /**
     * Checks that the given JSON exactly matches the returned JSON using PHPUnit's "canonicalizing" logic to
     * validate the objects.
     *
     * ```php
     * $response->assertExactJson(['foo' => 'bar']);
     * ```
     */
    function assertExactJson(array $json) {
        test()->assertEqualsCanonicalizing($json, json_decode($this->response->content, true));

        return $this->response;
    }

    /**
     * Checks that the response has a 403 Forbidden status code
     *
     * ```php
     * $response->assertForbidden();
     * ```
     */
    function assertForbidden() {
        return $this->assertStatus(403);
    }

    /**
     * Checks that the given header is present in the response and, if provided, that the value of the
     * header matches the given value.
     *
     * ```php
     * $response->assertHeader('x-foo'); // checks for presence of header, with any value
     * $response->assertHeader('x-foo', 'bar'); // checks for header with matching value
     * ```
     */
    function assertHeader(string $name, string $expected=null) {
        if ($expected === null) {
            test()->assertContains($name, array_keys($this->response->headers->toArray()));
        }
        else {
            $value = $this->response->headers->get($name);
            if ($expected === $value) {
                test()->assertTrue(true);
            }
            else {
                test()->fail('Response header `' . $name . '` with value `' . $value . '` does not match `' . $expected . '`');
            }
        }

        return $this->response;
    }

    /**
     * Checks that the response headers do not contain the given header.
     *
     * ```php
     * $response->assertHeaderMissing('x-foo');
     * ```
     */
    function assertHeaderMissing(string $name) {
        test()->assertNotContains($name, array_keys($this->response->headers->toArray()));

        return $this->response;
    }

    function assertJson() {
        // TODO
        return $this->response;
    }

    function assertJsonCount() {
        // TODO
        return $this->response;
    }

    function assertJsonFragment() {
        // TODO
        return $this->response;
    }

    function assertJsonMissing() {
        // TODO
        return $this->response;
    }

    function assertJsonMissingExact() {
        // TODO
        return $this->response;
    }

    function assertJsonMissingValidationErrors() {
        // TODO
        return $this->response;
    }

    function assertJsonPath() {
        // TODO
        return $this->response;
    }

    function assertJsonStructure() {
        // TODO
        return $this->response;
    }

    function assertJsonValidationErrors() {
        // TODO
        return $this->response;
    }

    /**
     * Checks that the location header matches the given location
     *
     * ```php
     * $response->assertLocation('/foo/bar');
     * ```
     */
    function assertLocation(string $location) {
        test()->assertSame($location, $this->response->getHeaders()->get('Location'));

        return $this->response;
    }

    /**
     * Check that the response has the given status code and no content.
     *
     * ```php
     * $response->assertNoContent();
     * ```
     */
    function assertNoContent($status=204) {
        $this->assertStatus($status);

        test()->assertEmpty($this->response->content, 'Response content is not empty.');

        return $this->response;
    }

    /**
     * Check that the response returns a 404 Not Found status code
     *
     * ```php
     * $response->assertNotFound();
     * ```
     */
    function assertNotFound() {
        return $this->assertStatus(404);
    }

    /**
     * Check that the response returns a 200 OK status code
     *
     * ```php
     * $response->assertOk();
     * ```
     */
    function assertOk() {
        return $this->assertStatus(200);
    }

    function assertPlainCookie() {
        // TODO
        return $this->response;
    }

    /**
     * Check that the response returns a 300 status code
     *
     * ```php
     * $response->assertRedirect();
     * ```
     */
    function assertRedirect() {
        test()->assertGreaterThanOrEqual(300, $this->response->getStatusCode());
        test()->assertLessThan(400, $this->response->getStatusCode());

        return $this->response;
    }

    /**
     * A sugar method that checks the status code as well as the location of the redirect.
     *
     * ```php
     * $response->assertRedirectTo('/foo/bar');
     * ```
     */
    function assertRedirectTo(string $location) {
        $this->assertRedirect();
        $this->assertLocation($location);;

        return $this->response;
    }

    /**
     * Checks that the response contains the given text
     *
     * ```php
     * $response->assertSee('foo bar');
     * ```
     */
    function assertSee(string $text) {
        test()->assertStringContainsString($text, $this->response->content);

        return $this->response;
    }

    function assertSeeInOrder(array $text) {
        // TODO
        return $this->response;
    }

    function assertSeeText(string $text) {
        return $this->assertSeeTextInOrder($text);
    }

    function assertSeeTextInOrder(string $text) {
        test()->assertStringContainsString($text, preg_replace('/\s+/', ' ', strip_tags($this->response->data)));

        return $this->response;
    }

    function assertSessionHas() {
        // TODO
        return $this->response;
    }

    function assertSessionHasInput() {
        // TODO
        return $this->response;
    }

    function assertSessionHasAll() {
        // TODO
        return $this->response;
    }

    function assertSessionHasErrors() {
        // TODO
        return $this->response;
    }

    function assertSessionHasErrorsIn() {
        // TODO
        return $this->response;
    }

    function assertSessionHasNoErrors() {
        // TODO
        return $this->response;
    }

    function assertSessionDoesntHaveErrors() {
        // TODO
        return $this->response;
    }

    function assertSessionMissing() {
        // TODO
        return $this->response;
    }

    function assertStatus($code) {
        test()->assertSame($code, $this->response->getStatusCode());
        return $this->response;
    }

    function assertSuccessful() {
        test()->assertGreaterThanOrEqual(200, $this->response->getStatusCode());
        test()->assertLessThan(300, $this->response->getStatusCode());

        return $this->response;
    }

    function assertUnauthorized() {
        return $this->assertStatus(401);
    }

    function assertValid() {
        // TODO
        return $this->response;
    }

    function assertInvalid() {
        // TODO
        return $this->response;
    }

    function assertViewHas() {
        // TODO
        return $this->response;
    }

    function assertViewHasAll() {
        // TODO
        return $this->response;
    }

    function assertViewIs() {
        // TODO
        return $this->response;
    }

    function assertViewMissing() {
        // TODO
        return $this->response;
    }

}

<?php

namespace markhuot\craftpest\behaviors;

use markhuot\craftpest\dom\Form;
use markhuot\craftpest\dom\NodeList;
use markhuot\craftpest\http\RequestBuilder;
use markhuot\craftpest\http\requests\WebRequest;
use markhuot\craftpest\web\TestableResponse;
use Symfony\Component\DomCrawler\Crawler;
use yii\base\Behavior;

/**
 * # Response Assertions
 *
 * A testable response is returned whenever you perform a HTTP request
 * with Pest. It is an extension of Craft's native Response with a
 * number of convience methods added for testing. For example, most
 * tests will perform a `get()` and want to check that the response did
 * not return an error. You may use `->assertOk()` to check that the
 * status code was 200.
 * 
 * @property \craft\web\Response $owner
 * @method self fill(string $key, string $value)
 * @method self tick(string $key)
 * @method self untick(string $key)
 * @method self select(string $key, string|array $value)
 * @method self submit(?string $key)
 */
class TestableResponseBehavior extends Behavior
{
    /**
     * The request that 
     */
    public WebRequest $request;

    /**
     * The response we're testing against
     */
    public TestableResponse $response;

    /**
     * The methods of the Form class that we're proxying and what each
     * method should return.
     */
    const FORM_METHODS = [
        'fill' => 'self',
        'tick' => 'self',
        'untick' => 'self',
        'select' => 'self',
        'click' => '',
        'submit' => '',
    ];

    /**
     * The first form on the page. Automatically grabbed when a form
     * is interacted with the first time
     */
    protected $form;

    public function attach($owner)
    {
        parent::attach($owner);

        if (is_a($owner, TestableResponse::class)) {
            $this->response = $owner;
        }
    }

    function setRequest(WebRequest $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Get the requesr that triggered this reaponse.
     */
    function getRequest(): WebRequest
    {
        return $this->request;
    }

    /**
     * We're proxying some methods from the underlying Form
     * class.
     * 
     * @internal
     */
    function hasMethod($method)
    {
        if (in_array($method, array_keys(static::FORM_METHODS))) {
            return true;
        }

        return parent::hasMethod($method);
    }

    /**
     * If this is a form method, proxy the call to the form
     */
    function __call($method, $args)
    {
        if (in_array($method, array_keys(static::FORM_METHODS))) {
            $result = $this->form()->{$method}(...$args);

            return static::FORM_METHODS[$method] === 'self' ? $this : $result;
        }

        throw new \Exception('Unknown method ' . $method . ' called.');
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
    function querySelector(string $selector)
    {
        $html = $this->response->content;
        $crawler = new Crawler($html);
        return new NodeList($crawler->filter($selector));
    }

    /**
     * The entry point for interactions with forms. This returns a testable
     * implementaion of the [Symfony DomCrawler's Form](#) class.
     * 
     * If a response only has one form you may call `->form()` without any parameters
     * to get the only form in the response. If the response contains more than
     * one form then you must pass in a selector matching a specific form.
     * 
     * To submit the form use `->submit()` or `->click('.button-selector')`.
     */
    public function form(string|null $selector=null): Form
    {
        if ($selector === null) {
            if ($this->form) {
                return $this->form;
            }

            return $this->form = new Form($this->querySelector('form'));
        }

        return new Form($this->querySelector($selector));
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
        $header = $this->response->getHeaders()->get('Location');
        $headerParts = parse_url($header);

        // If $location is passed in as an absolute path like /foo we want to
        // make sure that UrlHelper::url() generates a full schema+host URL and
        // the current logic (as of Craft 4.2) only does that if the path is
        // relative. Stripping off the leading slash will allow this while also
        // keeping any full URLs like `http://foo.com` in tact.
        // @TODO this will break protocol relative URLs though because they
        // start with a double slash like `//foo.com`
        $locationUri = ltrim($location, '/');
        $locationUrl = \craft\helpers\UrlHelper::url($locationUri);
        $locationParts = parse_url($locationUrl);

        test()->assertSame($locationParts, $headerParts);

        return $this->response;
    }

    /**
     * Check that the given message/key is present in the flashed data.
     * 
     * ```php
     * $response->assertFlash('The title is required');
     * $response->assertFlash('Field is required', 'title');
     * ```
     */
    function assertFlash(?string $message = null, ?string $key = null)
    {
        $flash = \Craft::$app->getSession()->getAllFlashes();

        if ($key) {
            expect($flash)->toMatchArray([$key => $message]);
        }

        else if ($message) {
            expect($flash)->toContain($message);
        }

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
        test()->assertContains('location', array_keys($this->response->headers->toArray()), 'The response does not contain a location header.');

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
        $this->assertLocation($location);

        return $this->response;
    }

    /**
     * For a 300 class response with a `Location` header, trigger a new
     * request for the redirected page.
     * 
     * ```php
     * $response->assertRedirect()->followRedirect()->assertOk();
     * ```
     */
    function followRedirect()
    {
        $this->assertRedirect();

        return (new RequestBuilder('get', $this->response->headers->get('location')))->send();
    }

    /**
     * For a 300 class response with a `Location` header, trigger a new
     * request for the redirected page. If the redirected page also contains
     * a redirect, follow the resulting redirects until you reach a non-300
     * response code.
     * 
     * 
     * ```php
     * $response->assertRedirect()->followRedirects()->assertOk();
     * ```
     */
    function followRedirects()
    {
        $this->assertRedirect();
        $response = $this->response;

        while ($response->isRedirection) {
            $response = (new RequestBuilder('get', $response->headers->get('location')))->send();
        }

        return $response;
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

    protected function seeInOrder(string $haystack, array $needles)
    {
        $lastPos = false;
        foreach ($needles as $needle) {
            $lastPos = strpos($haystack, $needle, $lastPos);
            if ($lastPos === false) {
                test()->fail('The text `' . $needle . '` was not found in order');
                return;
            }
        }
        expect(true)->toBe(true);
    }

    /**
     * Checks that the response contains the given text, in successive order
     *
     * ```php
     * $response->assertSee(['first', 'second', 'third']);
     * ```
     */
    function assertSeeInOrder(array $texts) {
        $this->seeInOrder($this->response->content, $texts);

        return $this->response;
    }

    /**
     * Checks that the response contains the given text stripping tags. This would
     * pass against source code of `<b>foo</b> bar`
     *
     * ```php
     * $response->assertSeeText('foo bar');
     * ```
     */
    function assertSeeText(string $text) {
        return $this->assertSeeTextInOrder([$text]);
    }

    /**
     * Checks that the response contains the given text, in successive order
     * while stripping tags.
     *
     * ```php
     * $response->assertSeeTextInOrder(['first', 'second', 'third']);
     * ```
     */
    function assertSeeTextInOrder(array $texts) {
        $this->seeInOrder(preg_replace('/\s+/', ' ', strip_tags($this->response->content)), $texts);

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

    /**
     * Asserts the given status code matches the response status code.
     * 
     * ```php
     * $response->assertStatus(404);
     * ```
     */
    function assertStatus($code) {
        test()->assertSame($code, $this->response->getStatusCode());
        return $this->response;
    }

    /**
     * Asserts a successfull (200-class) response code.
     */
    function assertSuccessful() {
        test()->assertGreaterThanOrEqual(200, $this->response->getStatusCode());
        test()->assertLessThan(300, $this->response->getStatusCode());

        return $this->response;
    }

    /**
     * Assert the given title matches the title of the page.
     * 
     * ```php
     * $response->assertTitle('The Title');
     * ```
     */
    function assertTitle(string $title)
    {
        $actualTitle = $this->querySelector('title')->text;
        test()->assertSame($title, $actualTitle, 'The given title did not match `' . $actualTitle . '`');

        return $this->response;
    }

    /**
     * Asserts that the response's status code is 401
     */
    function assertUnauthorized() {
        return $this->assertStatus(401);
    }
}

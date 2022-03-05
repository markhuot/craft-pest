<?php

namespace markhuot\craftpest\test;

use markhuot\craftpest\dom\NodeList;
use Symfony\Component\DomCrawler\Crawler;

class Response extends \craft\web\Response
{
    public function send()
    {
        // This page intentionally left blank so we can inspect the response body without it
        // being prematurely written to the screen
    }

    public function expect() {
        return $this->expect($this);
    }

    public function querySelector($selector) {
        $html = $this->data;
        $crawler = new Crawler($html);
        return new NodeList($crawler->filter($selector));
    }

    public function __isset($key) {
        return $this->querySelector($key)->count() > 0;
    }

    public function __get($key) {
        return $this->querySelector($key);
    }

    function assertCookie() {
        // TODO
        return $this;
    }

    function assertCookieExpired() {
        // TODO
        return $this;
    }

    function assertCookieNotExpired() {
        // TODO
        return $this;
    }

    function assertCookieMissing() {
        // TODO
        return $this;
    }

    function assertCreated() {
        return $this->assertStatus(201);
    }

    function assertDontSee(string $text) {
        test()->assertStringNotContainsString($text, $this->data);
        return $this;
    }

    function assertDontSeeText() {
        // TODO
        return $this;
    }

    function assertDownload() {
        // TODO
        return $this;
    }

    function assertExactJson(array $json) {
        test()->assertExact($json, $this->data);
        return $this;
    }

    function assertForbidden() {
        return $this->assertStatus(403);
    }

    function assertHeader($name, $expected=null) {
        $value = $this->headers->get($name);
        if ($expected === null) {
            test()->assertNotNull($value);
        }
        else {
            test()->assertSame($expected, $value);
        }
        return $this;
    }

    function assertHeaderMissing($name) {
        test()->assertNull($this->headers->get($name));
        return $this;
    }

    function assertJson() {
        // TODO
        return $this;
    }

    function assertJsonCount() {
        // TODO
        return $this;
    }

    function assertJsonFragment() {
        // TODO
        return $this;
    }

    function assertJsonMissing() {
        // TODO
        return $this;
    }

    function assertJsonMissingExact() {
        // TODO
        return $this;
    }

    function assertJsonMissingValidationErrors() {
        // TODO
        return $this;
    }

    function assertJsonPath() {
        // TODO
        return $this;
    }

    function assertJsonStructure() {
        // TODO
        return $this;
    }

    function assertJsonValidationErrors() {
        // TODO
        return $this;
    }

    function assertLocation() {
        // TODO
        return $this;
    }

    /**
     * Assert that the response has the given status code and no content.
     *
     * @param  int  $status
     * @return $this
     */
    function assertNoContent($status = 204) {
        $this->assertStatus($status);

        test()->assertEmpty($this->data, 'Response content is not empty.');

        return $this;
    }

    function assertNotFound() {
        return $this->assertStatus(404);
    }

    function assertOk() {
        return $this->assertStatus(200);
    }

    function assertPlainCookie() {
        // TODO
        return $this;
    }

    function assertRedirect() {
        test()->toBeGreaterThanOrEqual(300)
            ->toBeLessThanOrEqual(399);
        return $this;
    }

    // function assertRedirectToSignedRoute() {
    // }

    function assertSee($text) {
        test()->assertStringContainsString($text, $this->data);
        return $this;
    }

    function assertSeeInOrder() {
        // TODO
        return $this;
    }

    function assertSeeText(string $text) {
        return $this->assertSeeTextInOrder($text);
    }

    function assertSeeTextInOrder() {
        // TODO
        return $this;
    }

    function assertSessionHas() {
        // TODO
        return $this;
    }

    function assertSessionHasInput() {
        // TODO
        return $this;
    }

    function assertSessionHasAll() {
        // TODO
        return $this;
    }

    function assertSessionHasErrors() {
        // TODO
        return $this;
    }

    function assertSessionHasErrorsIn() {
        // TODO
        return $this;
    }

    function assertSessionHasNoErrors() {
        // TODO
        return $this;
    }

    function assertSessionDoesntHaveErrors() {
        // TODO
        return $this;
    }

    function assertSessionMissing() {
        // TODO
        return $this;
    }

    function assertStatus($code) {
        test()->assertSame($code, $this->getStatusCode());
        return $this;
    }

    function assertSuccessful() {
        test()->assertGreaterThanOrEqual(200, $this->getStatusCode());
        test()->assertLessThan(300, $this->getStatusCode());

        return $this;
    }

    function assertUnauthorized() {
        return $this->assertStatus(401);
    }

    function assertValid() {
        // TODO
        return $this;
    }

    function assertInvalid() {
        // TODO
        return $this;
    }

    function assertViewHas() {
        // TODO
        return $this;
    }

    function assertViewHasAll() {
        // TODO
        return $this;
    }

    function assertViewIs() {
        // TODO
        return $this;
    }

    function assertViewMissing() {
        // TODO
        return $this;
    }
}

<?php

namespace markhuot\craftpest\behaviors;

use markhuot\craftpest\dom\NodeList;
use markhuot\craftpest\test\Response;
use Symfony\Component\DomCrawler\Crawler;
use yii\base\Behavior;

/**
 * @property \craft\web\Response | TestableResponseBehavior $owner
 */
class TestableResponseBehavior extends Behavior {

    public function querySelector($selector) {
        $html = $this->owner->data;
        $crawler = new Crawler($html);
        return new NodeList($crawler->filter($selector));
    }

    // public function __isset($key) {
    //     if (parent::__isset($key)) {
    //         return true;
    //     }
    //
    //     return $this->querySelector($key)->count() > 0;
    // }

    // public function __get($key) {
    //     if ($value = parent::__get($key)) {
    //         return $value;
    //     }
    //
    //     return $this->querySelector($key);
    // }

    function assertCookie() {
        // TODO
        return $this->owner;
    }

    function assertCookieExpired() {
        // TODO
        return $this->owner;
    }

    function assertCookieNotExpired() {
        // TODO
        return $this->owner;
    }

    function assertCookieMissing() {
        // TODO
        return $this->owner;
    }

    function assertCreated() {
        return $this->assertStatus(201);
    }

    function assertDontSee(string $text) {
        test()->assertStringNotContainsString($text, $this->owner->data);
        return $this->owner;
    }

    function assertDontSeeText(string $text) {
        test()->assertStringNotContainsString($text, preg_replace('/\s+/', ' ', strip_tags($this->owner->data)));
        return $this->owner;
    }

    function assertDownload() {
        // TODO
        return $this->owner;
    }

    function assertExactJson(array $json) {
        test()->assertExact($json, $this->owner->data);
        return $this->owner;
    }

    function assertForbidden() {
        return $this->assertStatus(403);
    }

    function assertHeader($name, $expected=null) {
        $value = $this->owner->headers->get($name);
        if ($expected === null) {
            test()->assertNotNull($value);
        }
        else {
            test()->assertSame($expected, $value);
        }
        return $this->owner;
    }

    function assertHeaderMissing($name) {
        test()->assertNull($this->owner->headers->get($name));
        return $this->owner;
    }

    function assertJson() {
        // TODO
        return $this->owner;
    }

    function assertJsonCount() {
        // TODO
        return $this->owner;
    }

    function assertJsonFragment() {
        // TODO
        return $this->owner;
    }

    function assertJsonMissing() {
        // TODO
        return $this->owner;
    }

    function assertJsonMissingExact() {
        // TODO
        return $this->owner;
    }

    function assertJsonMissingValidationErrors() {
        // TODO
        return $this->owner;
    }

    function assertJsonPath() {
        // TODO
        return $this->owner;
    }

    function assertJsonStructure() {
        // TODO
        return $this->owner;
    }

    function assertJsonValidationErrors() {
        // TODO
        return $this->owner;
    }

    function assertLocation() {
        // TODO
        return $this->owner;
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

        return $this->owner;
    }

    function assertNotFound() {
        return $this->assertStatus(404);
    }

    function assertOk() {
        return $this->assertStatus(200);
    }

    function assertPlainCookie() {
        // TODO
        return $this->owner;
    }

    function assertRedirect() {
        test()->toBeGreaterThanOrEqual(300)
            ->toBeLessThanOrEqual(399);

        return $this->owner;
    }

    // function assertRedirectToSignedRoute() {
    // }

    function assertSee($text) {
        test()->assertStringContainsString($text, $this->owner->data);
        return $this->owner;
    }

    function assertSeeInOrder() {
        // TODO
        return $this->owner;
    }

    function assertSeeText(string $text) {
        return $this->assertSeeTextInOrder($text);
    }

    function assertSeeTextInOrder(string $text) {
        test()->assertStringContainsString($text, preg_replace('/\s+/', ' ', strip_tags($this->owner->data)));
        return $this->owner;
    }

    function assertSessionHas() {
        // TODO
        return $this->owner;
    }

    function assertSessionHasInput() {
        // TODO
        return $this->owner;
    }

    function assertSessionHasAll() {
        // TODO
        return $this->owner;
    }

    function assertSessionHasErrors() {
        // TODO
        return $this->owner;
    }

    function assertSessionHasErrorsIn() {
        // TODO
        return $this->owner;
    }

    function assertSessionHasNoErrors() {
        // TODO
        return $this->owner;
    }

    function assertSessionDoesntHaveErrors() {
        // TODO
        return $this->owner;
    }

    function assertSessionMissing() {
        // TODO
        return $this->owner;
    }

    function assertStatus($code) {
        test()->assertSame($code, $this->owner->getStatusCode());
        return $this->owner;
    }

    function assertSuccessful() {
        test()->assertGreaterThanOrEqual(200, $this->owner->getStatusCode());
        test()->assertLessThan(300, $this->owner->getStatusCode());

        return $this->owner;
    }

    function assertUnauthorized() {
        return $this->assertStatus(401);
    }

    function assertValid() {
        // TODO
        return $this->owner;
    }

    function assertInvalid() {
        // TODO
        return $this->owner;
    }

    function assertViewHas() {
        // TODO
        return $this->owner;
    }

    function assertViewHasAll() {
        // TODO
        return $this->owner;
    }

    function assertViewIs() {
        // TODO
        return $this->owner;
    }

    function assertViewMissing() {
        // TODO
        return $this->owner;
    }

}

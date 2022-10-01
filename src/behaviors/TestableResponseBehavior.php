<?php

namespace markhuot\craftpest\behaviors;

use markhuot\craftpest\dom\NodeList;
use markhuot\craftpest\web\TestableResponse;
use Symfony\Component\DomCrawler\Crawler;
use yii\base\Behavior;

/**
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


    function querySelector(string $selector) {
        $html = $this->response->content;
        $crawler = new Crawler($html);
        return new NodeList($crawler->filter($selector));
    }

    function expectSelector(string $selector) {
       return $this->querySelector($selector)->expect();
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
        return $this->response;
    }

    function assertCookieExpired() {
        // TODO
        return $this->response;
    }

    function assertCookieNotExpired() {
        // TODO
        return $this->response;
    }

    function assertCookieMissing() {
        // TODO
        return $this->response;
    }

    function assertCreated() {
        return $this->assertStatus(201);
    }

    function assertDontSee(string $text) {
        test()->assertStringNotContainsString($text, $this->response->content);
        return $this->response;
    }

    function assertDontSeeText(string $text) {
        test()->assertStringNotContainsString($text, preg_replace('/\s+/', ' ', strip_tags($this->response->data)));
        return $this->response;
    }

    function assertDownload() {
        // TODO
        return $this->response;
    }

    function assertExactJson(array $json) {
        test()->assertExact($json, $this->response->content);
        return $this->response;
    }

    function assertForbidden() {
        return $this->assertStatus(403);
    }

    function assertHeader($name, $expected = null) {
        $value = $this->response->headers->get($name);
        if ($expected === null) {
            test()->assertNotNull($value);
        }
        else {
            test()->assertSame($expected, $value);
        }
        return $this->response;
    }

    function assertHeaderMissing($name) {
        test()->assertNull($this->response->headers->get($name));
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

    function assertLocation(string $location) {
        test()->assertSame($location, $this->response->getHeaders()->get('Location'));
        return $this->response;
    }

    /**
     * Assert that the response has the given status code and no content.
     *
     * @param  int  $status
     * @return $this|\craft\web\Response
     */
    function assertNoContent($status = 204) {
        $this->assertStatus($status);

        test()->assertEmpty($this->response->content, 'Response content is not empty.');

        return $this->response;
    }

    function assertNotFound() {
        return $this->assertStatus(404);
    }

    function assertOk() {
        return $this->assertStatus(200);
    }

    function assertPlainCookie() {
        // TODO
        return $this->response;
    }

    function assertRedirect() {

        test()->assertGreaterThanOrEqual(300, $this->response->getStatusCode());
        test()->assertLessThan(400, $this->response->getStatusCode());

        return $this->response;
    }

    function assertRedirectTo(string $location) {

        $this->assertRedirect();
        $this->assertLocation($location);;

        return $this->response;
    }

    // function assertRedirectToSignedRoute() {
    // }

    function assertSee($text) {
        test()->assertStringContainsString($text, $this->response->content);
        return $this->response;
    }

    function assertSeeInOrder() {
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

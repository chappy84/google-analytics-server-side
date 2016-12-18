<?php
/**
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * Google Analytics Server Side is free software; you can redistribute it and/or
 * modify it under the terms of the BSD 3-clause "New" or "Revised" License
 *
 * N/B: This code is nether written or endorsed by Google or any of it's
 *      employees. "Google" and "Google Analytics" are trademarks of
 *      Google Inc. and it's respective subsidiaries.
 *
 * @copyright   Copyright (c) 2011-2016 Tom Chapman (http://tom-chapman.uk/)
 * @license     BSD 3-clause "New" or "Revised" License
 * @link        http://github.com/chappy84/google-analytics-server-side
 */

namespace GassTests\Gass\Validate;

use Gass\Validate\Url;

class UrlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Gass\Validate\Url
     */
    private $urlValidator;

    public function setUp()
    {
        parent::setUp();
        $this->urlValidator = new Url;
    }

    /**
     * @dataProvider dataProviderTestIsValidValidUrls
     */
    public function testIsValidValidUrls($url)
    {
        $urlValidator = new Url;
        $this->assertTrue($urlValidator->isValid($url));
        $this->assertAttributeEmpty('messages', $urlValidator);
    }

    public function dataProviderTestIsValidValidUrls()
    {
        return array(
            array('http://www.example.com/'),
            array('ftp://foo.bar'),
            array('udp://baz.qux'),
            array('http://www.example.com/with/a/path'),
            array('http://www.example.com/?just=query'),
            array('http://www.example.com/path?and=a&query'),
            array('http://www.example.com/path#andHash'),
            array('http://www.example.com/?query=and#hash'),
            array('http://www.example.com/path?and=a&query#andHash'),
        );
    }

    /**
     * @dataProvider dataProviderTestIsValidInValidUrls
     */
    public function testIsValidInValidUrls($url, $message)
    {
        $urlValidator = new Url;
        $this->assertFalse($urlValidator->isValid($url));
        $this->assertAttributeEquals(array($message), 'messages', $urlValidator);
    }

    public function dataProviderTestIsValidInValidUrls()
    {
        return array(
            array('no.scheme/but/a/path', '"no.scheme/but/a/path" is an invalid URL'),
            array('ftp:///no/host', '"ftp:///no/host" is an invalid URL'),
            array('string', '"string" is an invalid URL'),
            array(null, 'The provided URL must be a string.'),
            array(true, 'The provided URL must be a string.'),
            array(12345, 'The provided URL must be a string.'),
            array(12345.678, 'The provided URL must be a string.'),
            array(new \stdClass, 'The provided URL must be a string.'),
            array(array('http://www.example.com/'), 'The provided URL must be a string.'),
        );
    }
}

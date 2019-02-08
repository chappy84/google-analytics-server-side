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
 * @copyright   Copyright (c) 2011-2019 Tom Chapman (http://tom-chapman.uk/)
 * @license     BSD 3-clause "New" or "Revised" License
 * @link        http://github.com/chappy84/google-analytics-server-side
 */

namespace GassTests\Gass\Http;

use Gass\Http\Curl;
use GassTests\TestAbstract;

class CurlTest extends TestAbstract
{
    public $testUrl = 'http://www.example.com/';

    protected function setUp()
    {
        parent::setUp();
        if (!extension_loaded('curl')) {
            $this->markTestSkipped('The cURL extension is not available.');
        }
    }

    public function testConstruct()
    {
        $options = array(
            'foo' => 'bar',
            'baz' => 'qux',
        );
        $curl = new Curl($options);
        $this->assertArrayAttributeHasSubset($options, 'options', $curl);
    }

    public function testRequestValid()
    {
        $curl = new Curl;
        $curl->request($this->testUrl);
        $this->assertAttributeInternalType('resource', 'curl', $curl);
        $this->assertAttributeNotEmpty('response', $curl);
    }

    public function testRequestValidAddsUserAgentWhenSet()
    {
        $userAgent = 'FooBarBaz';
        $curl = new Curl;
        $curl->setUserAgent($userAgent)
            ->request($this->testUrl);
        $this->assertArrayAttributeHasSubset(array(CURLOPT_USERAGENT => $userAgent), 'options', $curl);
    }

    public function testRequestValidAddsAcceptLanguageHeader()
    {
        $acceptLanguage = 'en-gb';
        $curl = new Curl;
        $curl->setAcceptLanguage($acceptLanguage)
            ->request($this->testUrl);
        $this->assertArrayAttributeHasSubset(
            array(CURLOPT_HTTPHEADER => array('Accepts-Language: ' . $acceptLanguage)),
            'options',
            $curl
        );
    }

    public function testRequestValidAddsXForwardedFor()
    {
        $remoteAddress = '127.0.0.1';
        $curl = new Curl;
        $curl->setRemoteAddress($remoteAddress)
            ->request($this->testUrl);
        $this->assertArrayAttributeHasSubset(
            array(CURLOPT_HTTPHEADER => array('X-Forwarded-For: ' . $remoteAddress)),
            'options',
            $curl
        );
    }

    public function testRequestValidCombinesNewOptionsWithExistingOptions()
    {
        $userAgent = 'FooBarBaz';
        $acceptLanguage = 'en-gb';
        $remoteAddress = '127.0.0.1';
        $originalOptions = array(
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        );
        $otherOptions = array(
            CURLOPT_HTTPHEADER => array(
                'X-Random-Header: Random-Value',
            ),
        );
        $curl = new Curl($originalOptions);
        $curl->setUserAgent($userAgent)
            ->setAcceptLanguage($acceptLanguage)
            ->setRemoteAddress($remoteAddress)
            ->request($this->testUrl, $otherOptions);
        $this->assertAttributeEquals(
            array(
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_FOLLOWLOCATION => 1,
                CURLOPT_HTTPHEADER => array_merge(
                    $otherOptions[CURLOPT_HTTPHEADER],
                    array(
                        'Accepts-Language: ' . $acceptLanguage,
                        'X-Forwarded-For: ' . $remoteAddress,
                    )
                ),
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $this->testUrl,
                CURLOPT_USERAGENT => $userAgent,
                CURLOPT_HTTP_VERSION => $originalOptions[CURLOPT_HTTP_VERSION],
            ),
            'options',
            $curl
        );
    }

    public function testRequestUnexpectedValueException()
    {
        $curl = new Curl;
        $this->setExpectedException(
            'Gass\Exception\UnexpectedValueException',
            'One of the extra curl options specified is invalid. Error: '
        );
        $curl->request($this->testUrl, array('foo' => 'bar'));
    }

    public function testRequestRuntimeException()
    {
        $curl = new Curl;
        $this->setExpectedException(
            'Gass\Exception\RuntimeException',
            'Source could not be retrieved. Error: '
        );
        $curl->request('unknownurl');
    }

    /**
     * @depends testRequestValid
     */
    public function testGetInfoValidWithoutArgument()
    {
        $curl = new Curl;
        $curl->request($this->testUrl);
        $this->assertArraySubset(
            array('url' => $this->testUrl, 'http_code' => 200),
            $curl->getInfo()
        );
    }

    /**
     * @depends testRequestValid
     */
    public function testGetInfoValidWithArgument()
    {
        $curl = new Curl;
        $curl->request($this->testUrl);
        $this->assertEquals($this->testUrl, $curl->getInfo(CURLINFO_EFFECTIVE_URL));
        $this->assertEquals(200, $curl->getInfo(CURLINFO_HTTP_CODE));
    }

    public function testGetInfoDomainException()
    {
        $curl = new Curl;
        $this->setExpectedException(
            'Gass\Exception\DomainException',
            'A cURL request has not been made yet.'
        );
        $curl->getInfo();
    }

    public function testSetUrl()
    {
        $curl = new Curl;
        $curl->setUrl($this->testUrl);
        $this->assertArrayAttributeHasSubset(array(CURLOPT_URL => $this->testUrl), 'options', $curl);
    }

    private function assertArrayAttributeHasSubset(array $subset, $property, $class)
    {
        $rp = new \ReflectionProperty(get_class($class), $property);
        $rp->setAccessible(true);
        $this->assertArraySubset($subset, $rp->getValue($class));
    }
}

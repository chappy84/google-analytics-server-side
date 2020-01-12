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
 * @copyright   Copyright (c) 2011-2020 Tom Chapman (http://tom-chapman.uk/)
 * @license     BSD 3-clause "New" or "Revised" License
 * @link        http://github.com/chappy84/google-analytics-server-side
 */

namespace GassTests\Gass\Http;

use Gass\Http\Stream;
use GassTests\TestAbstract;

class StreamTest extends TestAbstract
{
    private $testUrl = 'http://www.example.com/';

    public function testSetOption()
    {
        $option1Name = 'foo';
        $option1Value = 'bar';
        $option2Name = 'baz';
        $option2Value = 'qux';

        $stream = new Stream();
        $this->assertSame($stream, $stream->setOption($option1Name, $option1Value));
        $this->assertSame($stream, $stream->setOption($option2Name, $option2Value));
        $this->assertAttributeEquals(
            array(
                'context' => array(
                    'http' => array(
                        'method' => 'GET',
                        $option1Name => $option1Value,
                        $option2Name => $option2Value,
                    ),
                ),
            ),
            'options',
            $stream
        );
    }

    /**
     * @depends testSetOption
     */
    public function testGetOptionExistingOption()
    {
        $option1Name = 'foo';
        $option1Value = 'bar';
        $option2Name = 'baz';
        $option2Value = 'qux';

        $stream = new Stream();
        $stream->setOption($option1Name, $option1Value);
        $stream->setOption($option2Name, $option2Value);

        $this->assertEquals($option1Value, $stream->getOption($option1Name));
        $this->assertEquals($option2Value, $stream->getOption($option2Name));
    }

    /**
     * @depends testSetOption
     */
    public function testGetOptionUnknownOption()
    {
        $stream = new Stream();
        $this->assertNull($stream->getOption('foo'));
    }

    /**
     * @depends testSetOption
     */
    public function testGetOptions()
    {
        $option1Name = 'foo';
        $option1Value = 'bar';
        $option2Name = 'baz';
        $option2Value = 'qux';

        $stream = new Stream();
        $stream->setOption($option1Name, $option1Value);
        $stream->setOption($option2Name, $option2Value);
        $this->assertEquals(
            array(
                'method' => 'GET',
                $option1Name => $option1Value,
                $option2Name => $option2Value,
            ),
            $stream->getOptions()
        );
    }

    public function testSetUrl()
    {
        $url = 'foo';
        $stream = new Stream();
        $stream->setUrl($url);
        $this->assertAttributeEquals(
            array(
                'url' => $url,
                'context' => array(
                    'http' => array('method' => 'GET'),
                ),
            ),
            'options',
            $stream
        );
    }

    public function testRequestValid()
    {
        $stream = new Stream;
        $this->assertSame($stream, $stream->request($this->testUrl));
        $this->assertAttributeNotEmpty('response', $stream);
        $this->assertAttributeInternalType('string', 'response', $stream);
        $this->assertAttributeNotEmpty('responseHeaders', $stream);
        $this->assertAttributeInternalType('array', 'responseHeaders', $stream);
    }

    /**
     * @depends testGetOptionExistingOption
     * @depends testGetOptionUnknownOption
     */
    public function testRequestValidWithUserAgent()
    {
        $userAgent = 'fooBarBaz';
        $stream = new Stream;
        $stream->setUserAgent($userAgent);
        $this->assertSame($stream, $stream->request($this->testUrl));
        $this->assertAttributeNotEmpty('response', $stream);
        $headerOption = $stream->getOption('header');
        $this->assertNotEmpty($headerOption);
        $this->assertRegExp('/(^|\r\n)User-Agent: ' . $userAgent . '(\r\n|$)/', $headerOption);
    }

    /**
     * @depends testGetOptionExistingOption
     * @depends testGetOptionUnknownOption
     */
    public function testRequestValidWithAcceptLanguage()
    {
        $acceptLanguage = 'en-gb';
        $stream = new Stream;
        $stream->setAcceptLanguage($acceptLanguage);
        $this->assertSame($stream, $stream->request($this->testUrl));
        $this->assertAttributeNotEmpty('response', $stream);
        $headerOption = $stream->getOption('header');
        $this->assertNotEmpty($headerOption);
        $this->assertRegExp('/(^|\r\n)Accepts-Language: ' . $acceptLanguage . '(\r\n|$)/', $headerOption);
    }

    /**
     * @depends testGetOptionExistingOption
     * @depends testGetOptionUnknownOption
     */
    public function testRequestValidWithXForwardedFor()
    {
        $remoteAddress = '127.0.0.1';
        $stream = new Stream;
        $stream->setRemoteAddress($remoteAddress);
        $this->assertSame($stream, $stream->request($this->testUrl));
        $this->assertAttributeNotEmpty('response', $stream);
        $headerOption = $stream->getOption('header');
        $this->assertNotEmpty($headerOption);
        $this->assertRegExp('/(^|\r\n)X-Forwarded-For: ' . $remoteAddress . '(\r\n|$)/', $headerOption);
    }

    public function testRequestExceptionRuntime()
    {
        $url = 'definitely not a valid url';
        $stream = new Stream;
        $this->setExpectedException(
            'Gass\Exception\RuntimeException',
            'Source could not be retrieved. Error: ' .
                'file_get_contents(' . $url . '): failed to open stream: No such file or directory'
        );
        $stream->request($url);
    }

    /**
     * @depends testGetOptionExistingOption
     * @depends testGetOptionUnknownOption
     */
    public function testRequestValidWithMultipleHeadersAndOptions()
    {
        $userAgent = 'fooBarBaz';
        $acceptLanguage = 'en-gb';
        $remoteAddress = '127.0.0.1';
        $timeout = 5;
        $followLocation = 1;
        $randomHeader1 = 'X-Foo: bar';
        $randomHeader2 = 'X-Baz: qux';

        $stream = new Stream;
        $stream->setOption('header', $randomHeader1 . "\n" . $randomHeader2);
        $stream->setOption('follow_location', $followLocation);
        $stream->setUserAgent($userAgent);
        $stream->setAcceptLanguage($acceptLanguage);
        $stream->setRemoteAddress($remoteAddress);

        $this->assertSame($stream, $stream->request($this->testUrl, array('timeout' => $timeout)));
        $this->assertAttributeNotEmpty('response', $stream);
        $headerOption = $stream->getOption('header');
        $this->assertNotEmpty($headerOption);
        $headersToTest = array(
            $randomHeader1,
            $randomHeader2,
            'User-Agent: ' . $userAgent,
            'Accepts-Language: ' . $acceptLanguage,
            'X-Forwarded-For: ' . $remoteAddress,
        );
        foreach ($headersToTest as $headerToTest) {
            $this->assertRegExp('/(^|\r\n)' . $headerToTest . '(\r\n|$)/', $headerOption);
        }
        $this->assertEquals($followLocation, $stream->getOption('follow_location'));
        $this->assertEquals($timeout, $stream->getOption('timeout'));
    }

    public function testGetInfoValidNoArgs()
    {
        $stream = new Stream;
        $stream->request($this->testUrl);
        $this->assertArraySubset(
            array(
                'Http-Code' => '200',
                'Content-Length' => strlen($stream->getResponse()),
            ),
            $stream->getInfo()
        );
    }

    public function testGetInfoValidWithArgs()
    {
        $stream = new Stream;
        $stream->request($this->testUrl);
        $this->assertEquals('200', $stream->getInfo('Http-Code'));
    }

    public function testGetInfoExceptionDomainNoRequestMade()
    {
        $stream = new Stream();
        $this->setExpectedException(
            'Gass\Exception\DomainException',
            'A Http Request has not been made yet.'
        );
        $stream->getInfo();
    }

    public function testParseHeadersInvalidDataType()
    {
        $stream = new Stream;
        $stream->setOption('header', new \stdClass);
        $this->setExpectedException(
            'Gass\Exception\InvalidArgumentException',
            'Headers must be provided in either string or numerically indexed array format.'
        );
        $stream->request($this->testUrl);
    }

    public function testParseHeadersSetCookie()
    {
        $setCookieHeader1 = 'Set-Cookie: foo=bar';
        $setCookieHeader2 = 'Set-Cookie: baz=qux';
        $stream = new Stream;
        $stream->setOption('header', $setCookieHeader1 . "\n" . $setCookieHeader2);
        $stream->request($this->testUrl);
        $this->assertRegExp('/(^|\r\n)' . $setCookieHeader1 . '(\r\n|$)/', $stream->getOption('header'));
        $this->assertRegExp('/(^|\r\n)' . $setCookieHeader2 . '(\r\n|$)/', $stream->getOption('header'));
    }
}

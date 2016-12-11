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

namespace GassTests\Gass\Http;

class BaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Gass\Http\Base
     */
    private $baseHttp;

    public function setUp()
    {
        parent::setUp();
        $this->baseHttp = $this->getMockForAbstractClass('Gass\Http\Base');
    }

    public function testSetRemoteAddressValid()
    {
        $validRemoteAddress = '192.168.0.1';
        $this->assertSame($this->baseHttp, $this->baseHttp->setRemoteAddress($validRemoteAddress));
        $this->assertEquals($validRemoteAddress, $this->baseHttp->getRemoteAddress());
    }

    /**
     * @dataProvider dataProviderTestSetRemoteAddressExceptions
     */
    public function testSetRemoteAddressExceptions($remoteAddress)
    {
        $this->setExpectedException(
            'Gass\Exception\InvalidArgumentException',
            'Remote Address validation errors: '
        );
        $this->baseHttp->setRemoteAddress($remoteAddress);
    }

    public function dataProviderTestSetRemoteAddressExceptions()
    {
        return array(
            array('abc.def.ghi.jkl'),
            array('500.500.500.500'),
            array('255.255'),
            array('192'),
            array(array('255.255.255.0')),
        );
    }

    public function testSetUserAgent()
    {
        $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_4) AppleWebKit/536.11 ' .
            '(KHTML, like Gecko) Chrome/20.0.1132.47 Safari/536.11';
        $this->assertSame($this->baseHttp, $this->baseHttp->setUserAgent($userAgent));
        $this->assertEquals($userAgent, $this->baseHttp->getUserAgent());
    }

    /**
     * @dataProvider dataProviderTestSetAcceptLanguageValid
     */
    public function testSetAcceptLanguageValid($languageCode)
    {
        $this->assertSame($this->baseHttp, $this->baseHttp->setAcceptLanguage($languageCode));
        $this->assertEquals($languageCode, $this->baseHttp->getAcceptLanguage());
    }

    public function dataProviderTestSetAcceptLanguageValid()
    {
        return array(
            array('en-gb'),
            array('fil-ph'),
            array('en'),
            array('fil'),
        );
    }

    /**
     * @dataProvider dataProviderTestSetAcceptLanguageExceptionInvalidArgument
     */
    public function testSetAcceptLanguageExceptionInvalidArgument($acceptLanguage)
    {
        $this->setExpectedException(
            'Gass\Exception\InvalidArgumentException',
            'Accept Language validation errors: '
        );
        $this->baseHttp->setAcceptLanguage($acceptLanguage);
    }

    public function dataProviderTestSetAcceptLanguageExceptionInvalidArgument()
    {
        return array(
            array('abcd'),
            array('AbCDefg'),
            array('ab-cde'),
            array('abcd-ef'),
            array(array('en-gb')),
        );
    }

    public function testSetResponse()
    {
        $response = 'Test Response String';
        $this->assertSame($this->baseHttp, $this->baseHttp->setResponse($response));
        $this->assertEquals($response, $this->baseHttp->getResponse());
    }

    public function testRequestNoArguments()
    {
        $this->assertSame($this->baseHttp, $this->baseHttp->request());
    }

    public function testRequestWithUrl()
    {
        $url = 'http://www.example.com/';
        $this->baseHttp->expects($this->once())
            ->method('setUrl')
            ->with($this->equalTo($url))
            ->willReturnSelf();
        $this->assertSame($this->baseHttp, $this->baseHttp->request($url));
    }

    public function testRequestWithOptions()
    {
        $options = array(
            'foo' => 'bar',
            'baz' => 'qux',
        );
        $this->assertSame($this->baseHttp, $this->baseHttp->request(null, $options));
        $this->assertAttributeEquals($options, 'options', $this->baseHttp);
    }

    /**
     * No assertions as purpose of functions is to throw exceptions on specific codes
     */
    public function testCheckResponseNoExceptionValidCode()
    {
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 200);
    }

    public function testCheckResponseCodeExceptionInvalidArgument()
    {
        $this->setExpectedException('Gass\Exception\InvalidArgumentException', 'HTTP Status Code must be numeric.');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 'TestCode');
    }

    /**
     * @dataProvider dataProviderTestCheckResponseCodeExceptions
     */
    public function testCheckResponseCodeExceptions($code, $message)
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', $message);
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, $code);
    }

    public function dataProviderTestCheckResponseCodeExceptions()
    {
        return array(
            array('204', 'No Content'),
            array('205', 'Reset Content'),
            array('206', 'Partial Content'),
            array('207', 'Multi-Status'),
            array('400', 'Bad Request'),
            array('401', 'Unauthorised Request'),
            array('402', 'Payment Required'),
            array('403', 'Forbidden'),
            array('404', 'Not Found'),
            array('405', 'Method Not Allowed'),
            array('406', 'Not Acceptable'),
            array('407', 'Proxy Authentication Required'),
            array('408', 'Request Timeout'),
            array('409', 'Conflict'),
            array('410', 'Gone'),
            array('411', 'Length Required'),
            array('412', 'Precondition Failed'),
            array('413', 'Request Entity Too Large'),
            array('414', 'Request-URI Too Long'),
            array('415', 'Unsupported Media Type'),
            array('416', 'Request Range Not Satisfiable'),
            array('417', 'Expectation Failed'),
            array('418', 'I\'m a Teapot'),
            array('422', 'Unprocessable Entity (WebDAV)'),
            array('423', 'Locked (WebDAV)'),
            array('424', 'Failed Dependancy (WebDAV)'),
            array('425', 'Unordered Collection'),
            array('426', 'Upgrade Required'),
            array('444', 'No Response'),
            array('449', 'Retry With'),
            array('450', 'Blocked by Windows Parental Controls'),
            array('499', 'Client Closed Request'),
            array('500', 'Internal Server Error'),
            array('501', 'Not Implemented'),
            array('502', 'Bad Gateway'),
            array('503', 'Service Unavailable'),
            array('504', 'Gateway Timeout'),
            array('505', 'HTTP Version Not Supported'),
            array('506', 'Variant Also Negotiates'),
            array('507', 'Insufficient Storage (WebDAV)'),
            array('509', 'Bandwidth Limit Exceeded'),
            array('510', 'Not Exceeded'),
        );
    }
}

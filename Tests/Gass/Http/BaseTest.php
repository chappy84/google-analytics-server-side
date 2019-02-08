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

use GassTests\TestAbstract;
use Mockery as m;

class BaseTest extends TestAbstract
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSetRemoteAddressValid()
    {
        $baseHttp = $this->getBaseHttp();
        $validRemoteAddress = 'testString';
        $ipValidator = m::mock('overload:Gass\Validate\IpAddress');
        $ipValidator->shouldReceive('isValid')
            ->with($validRemoteAddress)
            ->once()
            ->andReturn(true);
        $this->assertSame($baseHttp, $baseHttp->setRemoteAddress($validRemoteAddress));
        $this->assertAttributeEquals($validRemoteAddress, 'remoteAddress', $baseHttp);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSetRemoteAddressExceptionInvalidArgument()
    {
        $baseHttp = $this->getBaseHttp();
        $validRemoteAddress = 'testString';
        $testValidationMessages = array('Test Message 1', 'Test Message 2');

        $ipValidator = m::mock('overload:Gass\Validate\IpAddress');
        $ipValidator->shouldReceive('isValid')
            ->with($validRemoteAddress)
            ->once()
            ->andReturn(false);
        $ipValidator->shouldReceive('getMessages')
            ->withNoArgs()
            ->once()
            ->andReturn($testValidationMessages);

        $this->setExpectedException(
            'Gass\Exception\InvalidArgumentException',
            'Remote Address validation errors: ' . implode(', ', $testValidationMessages)
        );
        $baseHttp->setRemoteAddress($validRemoteAddress);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @depends testSetRemoteAddressValid
     */
    public function testGetRemoteAddress()
    {
        $baseHttp = $this->getBaseHttp();
        $validRemoteAddress = 'testString';
        $ipValidator = m::mock('overload:Gass\Validate\IpAddress');
        $ipValidator->shouldReceive('isValid')
            ->with($validRemoteAddress)
            ->once()
            ->andReturn(true);
        $this->assertSame($baseHttp, $baseHttp->setRemoteAddress($validRemoteAddress));
        $this->assertEquals($validRemoteAddress, $baseHttp->getRemoteAddress());
    }

    public function testSetUserAgent()
    {
        $baseHttp = $this->getBaseHttp();
        $userAgent = 'TestUserAgent';
        $this->assertSame($baseHttp, $baseHttp->setUserAgent($userAgent));
        $this->assertAttributeEquals($userAgent, 'userAgent', $baseHttp);
    }

    /**
     * @depends testSetUserAgent
     */
    public function testGetUserAgent()
    {
        $baseHttp = $this->getBaseHttp();
        $userAgent = 'TestUserAgent';
        $this->assertSame($baseHttp, $baseHttp->setUserAgent($userAgent));
        $this->assertEquals($userAgent, $baseHttp->getUserAgent());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSetAcceptLanguageValid()
    {
        $baseHttp = $this->getBaseHttp();
        $languageCode = 'testString';
        $ipValidator = m::mock('overload:Gass\Validate\LanguageCode');
        $ipValidator->shouldReceive('isValid')
            ->with($languageCode)
            ->once()
            ->andReturn(true);
        $this->assertSame($baseHttp, $baseHttp->setAcceptLanguage($languageCode));
        $this->assertAttributeEquals($languageCode, 'acceptLanguage', $baseHttp);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSetAcceptLanguageExceptionInvalidArgument()
    {
        $baseHttp = $this->getBaseHttp();
        $languageCode = 'testString';
        $testValidationMessages = array('Test Message 1', 'Test Message 2');

        $ipValidator = m::mock('overload:Gass\Validate\LanguageCode');
        $ipValidator->shouldReceive('isValid')
            ->with($languageCode)
            ->once()
            ->andReturn(false);
        $ipValidator->shouldReceive('getMessages')
            ->withNoArgs()
            ->once()
            ->andReturn($testValidationMessages);

        $this->setExpectedException(
            'Gass\Exception\InvalidArgumentException',
            'Accept Language validation errors: ' . implode(', ', $testValidationMessages)
        );
        $baseHttp->setAcceptLanguage($languageCode);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @depends testSetAcceptLanguageValid
     */
    public function testGetAcceptLanguageValid()
    {
        $baseHttp = $this->getBaseHttp();
        $languageCode = 'testString';
        $ipValidator = m::mock('overload:Gass\Validate\LanguageCode');
        $ipValidator->shouldReceive('isValid')
            ->with($languageCode)
            ->once()
            ->andReturn(true);
        $this->assertSame($baseHttp, $baseHttp->setAcceptLanguage($languageCode));
        $this->assertEquals($languageCode, $baseHttp->getAcceptLanguage());
    }

    public function testSetResponse()
    {
        $baseHttp = $this->getBaseHttp();
        $response = 'Test Response String';
        $this->assertSame($baseHttp, $baseHttp->setResponse($response));
        $this->assertAttributeEquals($response, 'response', $baseHttp);
    }

    /**
     * @depends testSetResponse
     */
    public function testGetResponse()
    {
        $baseHttp = $this->getBaseHttp();
        $response = 'Test Response String';
        $this->assertSame($baseHttp, $baseHttp->setResponse($response));
        $this->assertEquals($response, $baseHttp->getResponse());
    }

    public function testRequestNoArguments()
    {
        $baseHttp = $this->getBaseHttp();
        $this->assertSame($baseHttp, $baseHttp->request());
    }

    public function testRequestWithUrl()
    {
        $baseHttp = $this->getBaseHttp();
        $url = 'http://www.example.com/';
        $baseHttp->shouldReceive('setUrl')
            ->once()
            ->with($url)
            ->andReturnSelf();
        $this->assertSame($baseHttp, $baseHttp->request($url));
    }

    public function testRequestWithOptions()
    {
        $baseHttp = $this->getBaseHttp();
        $options = array(
            'foo' => 'bar',
            'baz' => 'qux',
        );
        $this->assertSame($baseHttp, $baseHttp->request(null, $options));
        $this->assertAttributeEquals($options, 'options', $baseHttp);
    }

    /**
     * No assertions as purpose of functions is to throw exceptions on specific codes
     */
    public function testCheckResponseNoExceptionValidCode()
    {
        $baseHttp = $this->getBaseHttp();
        $reflectionMethod = new \ReflectionMethod($baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($baseHttp, 200);
    }

    public function testCheckResponseCodeExceptionInvalidArgument()
    {
        $baseHttp = $this->getBaseHttp();
        $this->setExpectedException('Gass\Exception\InvalidArgumentException', 'HTTP Status Code must be numeric.');
        $reflectionMethod = new \ReflectionMethod($baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($baseHttp, 'TestCode');
    }

    /**
     * @dataProvider dataProviderTestCheckResponseCodeExceptions
     */
    public function testCheckResponseCodeExceptions($code, $message)
    {
        $baseHttp = $this->getBaseHttp();
        $this->setExpectedException('Gass\Exception\RuntimeException', $message);
        $reflectionMethod = new \ReflectionMethod($baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($baseHttp, $code);
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

    private function getBaseHttp()
    {
        return m::mock('Gass\Http\Base[]');
    }
}

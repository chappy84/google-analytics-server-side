<?php
/**
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * Google Analytics Server Side is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or any later
 * version.
 *
 * The GNU General Public License can be found at:
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * N/B: This code is nether written or endorsed by Google or any of it's
 *      employees. "Google" and "Google Analytics" are trademarks of
 *      Google Inc. and it's respective subsidiaries.
 *
 * @copyright   Copyright (c) 2011-2013 Tom Chapman (http://tom-chapman.co.uk/)
 * @license     http://www.gnu.org/copyleft/gpl.html  GPL
 * @author      Tom Chapman
 * @link        http://github.com/chappy84/google-analytics-server-side
 * @category    GoogleAnalyticsServerSide
 * @package     GoogleAnalyticsServerSide
 * @subpackage  Http
 */
namespace GassTests\Gass\Http;

class BaseTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Gass\Http\Base
     * @access private
     */
    private $baseHttp;

    public function setUp()
    {
        parent::setUp();
        $this->baseHttp = $this->getMockForAbstractClass('Gass\Http\Base');
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testSetRemoteAddressValid()
    {
        $validRemoteAddress = '192.168.0.1';
        $this->assertInstanceOf('Gass\Http\Base', $this->baseHttp->setRemoteAddress($validRemoteAddress));
        $this->assertEquals($validRemoteAddress, $this->baseHttp->getRemoteAddress());
    }

    public function testSetRemoteAddressExceptionLetters()
    {
        $this->setExpectedException('Gass\Exception\InvalidArgumentException');
        $this->baseHttp->setRemoteAddress('abc.def.ghi.jkl');
    }

    public function testSetRemoteAddressExceptionTooHighSegments()
    {
        $this->setExpectedException('Gass\Exception\InvalidArgumentException');
        $this->baseHttp->setRemoteAddress('500.500.500.500');
    }

    public function testSetRemoteAddressExceptionMissingSegments()
    {
        $this->setExpectedException('Gass\Exception\InvalidArgumentException');
        $this->baseHttp->setRemoteAddress('255.255');
    }

    public function testSetRemoteAddressExceptionInteger()
    {
        $this->setExpectedException('Gass\Exception\InvalidArgumentException');
        $this->baseHttp->setRemoteAddress('192');
    }

    public function testSetRemoteAddressExceptionWrongDataType()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->baseHttp->setRemoteAddress(array('255.255.255.0'));
    }

    public function testSetUserAgent()
    {
        $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_4) AppleWebKit/536.11 '.
            '(KHTML, like Gecko) Chrome/20.0.1132.47 Safari/536.11';
        $this->assertInstanceOf('Gass\Http\Base', $this->baseHttp->setUserAgent($userAgent));
        $this->assertEquals($userAgent, $this->baseHttp->getUserAgent());
    }

    public function testSetAcceptLanguageTwoCharPlusCountryValid()
    {
        $this->assertInstanceOf('Gass\Http\Base', $this->baseHttp->setAcceptLanguage('en-gb'));
        $this->assertEquals('en-gb', $this->baseHttp->getAcceptLanguage());
    }

    public function testSetAcceptLanguageThreeCharPlusCountryValid()
    {
        $this->assertInstanceOf('Gass\Http\Base', $this->baseHttp->setAcceptLanguage('fil-ph'));
        $this->assertEquals('fil-ph', $this->baseHttp->getAcceptLanguage());
    }

    public function testSetAcceptLanguageTwoCharValid()
    {
        $this->assertInstanceOf('Gass\Http\Base', $this->baseHttp->setAcceptLanguage('en'));
        $this->assertEquals('en', $this->baseHttp->getAcceptLanguage());
    }

    public function testSetAcceptLanguageThreeCharValid()
    {
        $this->assertInstanceOf('Gass\Http\Base', $this->baseHttp->setAcceptLanguage('fil'));
        $this->assertEquals('fil', $this->baseHttp->getAcceptLanguage());
    }

    public function testSetAcceptLanguageExceptionTooLong()
    {
        $this->setExpectedException('Gass\Exception\InvalidArgumentException');
        $this->baseHttp->setAcceptLanguage('abcd');
    }

    public function testSetAcceptLanguageExceptionTooLong2()
    {
        $this->setExpectedException('Gass\Exception\InvalidArgumentException');
        $this->baseHttp->setAcceptLanguage('AbCDefg');
    }

    public function testSetAcceptLanguageExceptionInvalidCountry()
    {
        $this->setExpectedException('Gass\Exception\InvalidArgumentException');
        $this->baseHttp->setAcceptLanguage('ab-cde');
    }

    public function testSetAcceptLanguageExceptionInvalidLanguage()
    {
        $this->setExpectedException('Gass\Exception\InvalidArgumentException');
        $this->baseHttp->setAcceptLanguage('abcd-ef');
    }

    public function testSetAcceptLanguageExceptionWrongDataType()
    {
        $this->setExpectedException(
            'Gass\Exception\InvalidArgumentException',
            'Accept Language validation errors: The provided language code must be a string.'
        );
        $this->baseHttp->setAcceptLanguage(array('en-gb'));
    }

    public function testSetResponse()
    {
        $response = 'Test Response String';
        $this->assertInstanceOf('Gass\Http\Base', $this->baseHttp->setResponse($response));
        $this->assertEquals($response, $this->baseHttp->getResponse());
    }

    public function testCheckResponseCodeExceptionInvalidArgument()
    {
        $this->setExpectedException('Gass\Exception\InvalidArgumentException', 'HTTP Status Code must be numeric.');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 'TestCode');
    }

    public function testCheckResponseCodeExceptionCode204()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'No Content');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 204);
    }

    public function testCheckResponseCodeExceptionCode205()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'Reset Content');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 205);
    }

    public function testCheckResponseCodeExceptionCode206()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'Partial Content');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 206);
    }

    public function testCheckResponseCodeExceptionCode207()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'Multi-Status');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 207);
    }

    public function testCheckResponseCodeExceptionCode400()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'Bad Request');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 400);
    }

    public function testCheckResponseCodeExceptionCode401()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'Unauthorised Request');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 401);
    }

    public function testCheckResponseCodeExceptionCode402()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'Payment Required');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 402);
    }

    public function testCheckResponseCodeExceptionCode403()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'Forbidden');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 403);
    }

    public function testCheckResponseCodeExceptionCode404()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'Not Found');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 404);
    }

    public function testCheckResponseCodeExceptionCode405()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'Method Not Allowed');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 405);
    }

    public function testCheckResponseCodeExceptionCode406()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'Not Acceptable');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 406);
    }

    public function testCheckResponseCodeExceptionCode407()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'Proxy Authentication Required');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 407);
    }

    public function testCheckResponseCodeExceptionCode408()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'Request Timeout');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 408);
    }

    public function testCheckResponseCodeExceptionCode409()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'Conflict');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 409);
    }

    public function testCheckResponseCodeExceptionCode410()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'Gone');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 410);
    }

    public function testCheckResponseCodeExceptionCode411()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'Length Required');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 411);
    }

    public function testCheckResponseCodeExceptionCode412()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'Precondition Failed');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 412);
    }

    public function testCheckResponseCodeExceptionCode413()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'Request Entity Too Large');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 413);
    }

    public function testCheckResponseCodeExceptionCode414()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'Request-URI Too Long');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 414);
    }

    public function testCheckResponseCodeExceptionCode415()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'Unsupported Media Type');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 415);
    }

    public function testCheckResponseCodeExceptionCode416()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'Request Range Not Satisfiable');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 416);
    }

    public function testCheckResponseCodeExceptionCode417()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'Expectation Failed');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 417);
    }

    public function testCheckResponseCodeExceptionCode418()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'I\'m a Teapot');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 418);
    }

    public function testCheckResponseCodeExceptionCode422()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'Unprocessable Entity (WebDAV)');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 422);
    }

    public function testCheckResponseCodeExceptionCode423()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'Locked (WebDAV)');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 423);
    }

    public function testCheckResponseCodeExceptionCode424()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'Failed Dependancy (WebDAV)');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 424);
    }

    public function testCheckResponseCodeExceptionCode425()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'Unordered Collection');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 425);
    }

    public function testCheckResponseCodeExceptionCode426()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'Upgrade Required');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 426);
    }

    public function testCheckResponseCodeExceptionCode444()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'No Response');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 444);
    }

    public function testCheckResponseCodeExceptionCode449()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'Retry With');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 449);
    }

    public function testCheckResponseCodeExceptionCode450()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'Blocked by Windows Parental Controls');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 450);
    }

    public function testCheckResponseCodeExceptionCode499()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'Client Closed Request');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 499);
    }

    public function testCheckResponseCodeExceptionCode500()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'Internal Server Error');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 500);
    }

    public function testCheckResponseCodeExceptionCode501()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'Not Implemented');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 501);
    }

    public function testCheckResponseCodeExceptionCode502()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'Bad Gateway');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 502);
    }

    public function testCheckResponseCodeExceptionCode503()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'Service Unavailable');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 503);
    }

    public function testCheckResponseCodeExceptionCode504()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'Gateway Timeout');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 504);
    }

    public function testCheckResponseCodeExceptionCode505()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'HTTP Version Not Supported');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 505);
    }

    public function testCheckResponseCodeExceptionCode506()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'Variant Also Negotiates');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 506);
    }

    public function testCheckResponseCodeExceptionCode507()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'Insufficient Storage (WebDAV)');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 507);
    }

    public function testCheckResponseCodeExceptionCode509()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'Bandwidth Limit Exceeded');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 509);
    }

    public function testCheckResponseCodeExceptionCode510()
    {
        $this->setExpectedException('Gass\Exception\RuntimeException', 'Not Exceeded');
        $reflectionMethod = new \ReflectionMethod($this->baseHttp, 'checkResponseCode');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->baseHttp, 510);
    }
}

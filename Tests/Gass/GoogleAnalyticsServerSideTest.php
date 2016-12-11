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

namespace GassTests\Gass;

use Gass\BotInfo\BrowsCap;
use Gass\GoogleAnalyticsServerSide;
use Gass\Http\Http;
use Gass\Http\Stream as HttpStream;

class GoogleAnalyticsServerSideTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $dependecyFilesFolder;

    /**
     * @var GoogleAnalyticsServerSide
     */
    protected $gass;

    /**
     * @var \Gass\BotInfo\BotInfoInterface
     */
    protected $botInfoAdapter;

    /**
     * @var \Gass\Http\HttpInterface
     */
    protected $httpAdapter;

    public function setUp()
    {
        parent::setUp();
        $this->dependecyFilesFolder = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'dependency-files' . DIRECTORY_SEPARATOR;

        $this->initialiseHttpAdapter();

        $this->gass = new GoogleAnalyticsServerSide;

        $this->initialiseBotInfoAdapter();
    }

    public function tearDown()
    {
        $this->gass = null;
        $this->botInfoAdapter = null;
        $this->httpAdapter = null;
    }

    private function initialiseBotInfoAdapter()
    {
        $this->botInfoAdapter = $this->getMock('Gass\BotInfo\BotInfoInterface');
        $this->botInfoAdapter->expects($this->any())
            ->method('setUserAgent')
            ->with($this->isType('string'))
            ->willReturnSelf();
        $this->botInfoAdapter->expects($this->any())
            ->method('setRemoteAddress')
            ->with($this->isType('string'))
            ->willReturnSelf();

        $this->gass->setBotInfo($this->botInfoAdapter);
        return $this;
    }

    private function initialiseHttpAdapter()
    {
        $this->httpAdapter = $this->getMock('Gass\Http\HttpInterface');
        Http::getInstance(array('adapter' => $this->httpAdapter));
        return $this;
    }

    private function expectGifUrlCall()
    {
        $this->httpAdapter->expects($this->once())
            ->method('request')
            ->with($this->stringStartsWith(GoogleAnalyticsServerSide::GIF_URL))
            ->willReturnSelf();
        return $this;
    }

    private function expectJsUrlCall()
    {
        $this->httpAdapter->expects($this->once())
            ->method('request')
            ->with($this->equalTo(GoogleAnalyticsServerSide::JS_URL))
            ->willReturnSelf();
        $this->httpAdapter->expects($this->once())
            ->method('getResponse')
            ->willReturn(file_get_contents($this->dependecyFilesFolder . 'ga.js'));
        return $this;
    }

    private function expectJsAndGifUrlCall()
    {
        $this->httpAdapter->expects($this->atLeast(2))
            ->method('request')
            ->withConsecutive(
                array($this->equalTo(GoogleAnalyticsServerSide::JS_URL)),
                array($this->stringStartsWith(GoogleAnalyticsServerSide::GIF_URL))
            )->willReturnSelf();
        $this->httpAdapter->expects($this->once())
            ->method('getResponse')
            ->willReturn(file_get_contents($this->dependecyFilesFolder . 'ga.js'));
        return $this;
    }

    private function initialiseBrowserDetails()
    {
        $this->gass->setServerName('www.example.com')
            ->setRemoteAddress('123.123.123.123')
            ->setDocumentPath('/path/to/page')
            ->setUserAgent(
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_3) ' .
                'AppleWebKit/536.5 (KHTML, like Gecko) Chrome/19.0.1084.46 Safari/536.5'
            )->setAcceptLanguage('en');
        return $this;
    }

    public function testConstructExceptionWrongOptionsDataType()
    {
        $this->setExpectedException(
            (class_exists('TypeError')) ? 'TypeError' : 'PHPUnit_Framework_Error',
            'Argument 1 passed to Gass\GoogleAnalyticsServerSide::__construct() must be'
        );
        $gass = new GoogleAnalyticsServerSide(new \stdClass);
    }

    public function testSetVersionValid()
    {
        $this->assertSame($this->gass, $this->gass->setVersion('1.2.3'));
        $this->assertEquals('1.2.3', $this->gass->getVersion());
        $this->gass->setVersion('5.20.71');
        $this->assertEquals('5.20.71', $this->gass->getVersion());
    }

    public function testSetVersionExceptionDecimal()
    {
        $this->setExpectedException(
            'Gass\Exception\InvalidArgumentException',
            'Invalid version number provided: 5.23'
        );
        $this->gass->setVersion('5.23');
    }

    public function testSetVersionExceptionInteger()
    {
        $this->setExpectedException('Gass\Exception\InvalidArgumentException', 'Invalid version number provided: 523');
        $this->gass->setVersion('523');
    }

    public function testSetVersionExceptionString()
    {
        $this->setExpectedException('Gass\Exception\InvalidArgumentException', 'Invalid version number provided: abc');
        $this->gass->setVersion('abc');
    }

    public function testSetVersionExceptionWrongDataType()
    {
        $this->setExpectedException('Gass\Exception\InvalidArgumentException', 'Version must be a string.');
        $this->gass->setVersion(array('5.2.3'));
    }

    public function testSetUserAgentValid()
    {
        $userAgent = 'testString';
        $this->httpAdapter->expects($this->once())
            ->method('setUserAgent')
            ->with($this->equalTo($userAgent))
            ->willReturnSelf();
        $this->botInfoAdapter->expects($this->once())
            ->method('setUserAgent')
            ->with($this->equalTo($userAgent))
            ->willReturnSelf();

        $this->assertSame($this->gass, $this->gass->setUserAgent($userAgent));
        $this->assertAttributeEquals($userAgent, 'userAgent', $this->gass);
    }

    public function testSetUserAgentExceptionWrongDataType()
    {
        $this->setExpectedException('Gass\Exception\InvalidArgumentException', 'User Agent must be a string.');
        $this->gass->setUserAgent(array('Mozilla/5.0 (compatible; Konqueror/2.2.2)'));
    }

    /**
     * @dataProvider dataProviderTestSetAcceptLanguageValid
     */
    public function testSetAcceptLanguageValid($fullString, $deducedCode)
    {
        $this->httpAdapter->expects($this->once())
            ->method('setAcceptLanguage')
            ->with($this->equalTo($deducedCode))
            ->willReturnSelf();
        $this->assertSame($this->gass, $this->gass->setAcceptLanguage($fullString));
        $this->assertAttributeEquals($deducedCode, 'acceptLanguage', $this->gass);
    }

    public function dataProviderTestSetAcceptLanguageValid()
    {
        return array(
            array('en-GB,en;q=0.8', 'en-gb'),
            array('fil-PH,fil;q=0.8', 'fil-ph'),
            array('en,en-GB;q=0.8', 'en'),
            array('fil,fil-PH;q=0.8', 'fil'),
        );
    }

    /**
     * @dataProvider dataProviderTestSetAcceptLanguageException
     */
    public function testSetAcceptLanguageException($acceptLanguage, $exceptionMessage)
    {
        $this->setExpectedException('Gass\Exception\InvalidArgumentException', $exceptionMessage);
        $this->gass->setAcceptLanguage($acceptLanguage);
    }

    public function dataProviderTestSetAcceptLanguageException()
    {
        return array(
            array('abcd,efg;q=0.8', 'Accept Language validation errors: '),
            array('AbCDefg', 'Accept Language validation errors: '),
            array('ab-cde', 'Accept Language validation errors: '),
            array('abcd-ef', 'Accept Language validation errors: '),
            array(array('en-gb'), 'Accept Language must be a string.'),
        );
    }

    public function testSetServerNameValid()
    {
        $serverName = 'www.example.com';
        $this->assertSame($this->gass, $this->gass->setServerName($serverName));
        $this->assertEquals($serverName, $this->gass->getServerName());
        $serverName = 'localhost';
        $this->gass->setServerName($serverName);
        $this->assertEquals($serverName, $this->gass->getServerName());
    }

    public function testSetServerNameExceptionWrongDataType()
    {
        $this->setExpectedException('Gass\Exception\InvalidArgumentException', 'Server Name must be a string.');
        $this->gass->setServerName(array('www.example.com'));
    }

    /**
     * @dataProvider dataProviderTestSetRemoteAddressValid
     */
    public function testSetRemoteAddressValid($remoteAddress)
    {
        $this->httpAdapter->expects($this->once())
            ->method('setRemoteAddress')
            ->with($this->equalTo($remoteAddress))
            ->willReturnSelf();
        $this->botInfoAdapter->expects($this->once())
            ->method('setRemoteAddress')
            ->with($this->equalTo($remoteAddress))
            ->willReturnSelf();
        $this->assertSame($this->gass, $this->gass->setRemoteAddress($remoteAddress));
        $this->assertAttributeEquals($remoteAddress, 'remoteAddress', $this->gass);
    }

    public function dataProviderTestSetRemoteAddressValid()
    {
        return array(
            array('192.168.0.1'),
            array('255.255.255.0'),
        );
    }

    /**
     * @dataProvider dataProviderTestSetRemoteAddressException
     */
    public function testSetRemoteAddressException($remoteAddress, $exceptionMessage)
    {
        $this->setExpectedException(
            'Gass\Exception\InvalidArgumentException',
            $exceptionMessage
        );
        $this->gass->setRemoteAddress($remoteAddress);
    }

    public function dataProviderTestSetRemoteAddressException()
    {
        return array(
            array('abc.def.ghi.jkl', 'Remote Address validation errors: '),
            array('500.500.500.500', 'Remote Address validation errors: '),
            array('255.255', 'Remote Address validation errors: '),
            array('192', 'Remote Address validation errors: '),
            array(array('255.255.255.0'), 'Remote Address must be a string.'),
        );
    }

    public function testSetAccountValid()
    {
        $account = 'UA-1234-5';
        $this->assertSame($this->gass, $this->gass->setAccount($account));
        $this->assertEquals($account, $this->gass->getAccount());
        $account = 'MO-1234567-89';
        $this->gass->setAccount($account);
        $this->assertEquals($account, $this->gass->getAccount());
    }

    public function testSetAccountExceptionInvalidFirstSegment()
    {
        $this->setExpectedException('Gass\Exception\InvalidArgumentException');
        $this->gass->setAccount('AB-1234567-0');
    }

    public function testSetAccountExceptionMissingFirstHyphen()
    {
        $this->setExpectedException('Gass\Exception\InvalidArgumentException');
        $this->gass->setAccount('UA1234567-0');
    }

    public function testSetAccountExceptionMissingSecondHyphen()
    {
        $this->setExpectedException('Gass\Exception\InvalidArgumentException');
        $this->gass->setAccount('UA-12345670');
    }

    public function testSetAccountExceptionLowerCaseFirstSegment()
    {
        $this->setExpectedException('Gass\Exception\InvalidArgumentException');
        $this->gass->setAccount('mo-1234567-0');
    }

    public function testSetAccountExceptionWrongDataType()
    {
        $this->setExpectedException('Gass\Exception\InvalidArgumentException', 'Account must be a string.');
        $this->gass->setAccount(array('UA-1234-5'));
    }

    public function testSetDocumentRefererValid()
    {
        $documentReferer = 'http://www.example.com/random.html?a=b';
        $this->assertSame($this->gass, $this->gass->setDocumentReferer($documentReferer));
        $this->assertEquals($documentReferer, $this->gass->getDocumentReferer());
        $documentReferer = 'http://localhost/random';
        $this->gass->setDocumentReferer($documentReferer);
        $this->assertEquals($documentReferer, $this->gass->getDocumentReferer());
    }

    public function testSetDocumentRefererExceptionMissingProtocol()
    {
        $this->setExpectedException('Gass\Exception\InvalidArgumentException');
        $this->gass->setDocumentReferer('www.example.com/random.html?a=b');
    }

    public function testSetDocumentRefererExceptionMissingHostname()
    {
        $this->setExpectedException('Gass\Exception\InvalidArgumentException');
        $this->gass->setDocumentReferer('http:///random.html?a=b');
    }

    public function testSetDocumentRefererExceptionOnlyProtocol()
    {
        $this->setExpectedException('Gass\Exception\InvalidArgumentException');
        $this->gass->setDocumentReferer('http://');
    }

    public function testSetDocumentRefererExceptionInvalidHostname()
    {
        $this->setExpectedException('Gass\Exception\InvalidArgumentException');
        $this->gass->setDocumentReferer('http://www.example_1.com/');
    }

    public function testSetDocumentRefererExceptionInvalidProtocol()
    {
        $this->setExpectedException('Gass\Exception\InvalidArgumentException');
        $this->gass->setDocumentReferer('a%b://www.example.com/');
    }

    public function testSetDocumentRefererExceptionWrongDataType()
    {
        $this->setExpectedException('Gass\Exception\InvalidArgumentException', 'Document Referer must be a string.');
        $this->gass->setDocumentReferer(array('http://localhost/random'));
    }

    public function testSetDocumentPathValid()
    {
        $documentPath = '/abcdefg.html';
        $this->assertSame($this->gass, $this->gass->setDocumentPath($documentPath));
        $this->assertEquals($documentPath, $this->gass->getDocumentPath());
        $this->assertInstanceOf(
            'Gass\GoogleAnalyticsServerSide',
            $this->gass->setDocumentPath('/abcdefg.html?a=b&c=d')
        );
        $this->assertEquals($documentPath, $this->gass->getDocumentPath());
    }

    public function testSetDocumentPathExceptionWrongDataType()
    {
        $this->setExpectedException('Gass\Exception\InvalidArgumentException', 'Document Path must be a string.');
        $this->gass->setDocumentPath(array('/abcdefg.html'));
    }

    public function testSetPageTitleValid()
    {
        $pageTitle = 'Abcdef Ghijk Lmnop';
        $this->assertSame($this->gass, $this->gass->setPageTitle($pageTitle));
        $this->assertEquals($pageTitle, $this->gass->getPageTitle());
    }

    public function testSetPageTitleExceptionWrongDataType()
    {
        $this->setExpectedException('Gass\Exception\InvalidArgumentException', 'Page Title must be a string.');
        $this->gass->setPageTitle(array('Abcdef Ghijk Lmnop'));
    }

    public function testSetCustomVarValid()
    {
        $customVar1 = array(
            'index' => 1,
            'name' => 'Custom Var 1',
            'value' => 'Custom Value 1',
            'scope' => 3,
        );
        $customVar2 = array(
            'index' => 5,
            'name' => 'Custom Var 5',
            'value' => 'Custom Value 5',
            'scope' => 2,
        );
        $this->assertInstanceOf(
            'Gass\GoogleAnalyticsServerSide',
            $this->gass->setCustomVar($customVar1['name'], $customVar1['value'])
        );
        $this->gass->setCustomVar(
            $customVar2['name'],
            $customVar2['value'],
            $customVar2['scope'],
            $customVar2['index']
        );
        $customVars = $this->gass->getCustomVariables();
        $this->assertArrayHasKey('index1', $customVars);
        $this->assertEquals($customVar1, $customVars['index1']);
        $this->assertEquals($customVar1['value'], $this->gass->getVisitorCustomVar(1));
        $this->assertEquals(array(implode('=', $customVar1)), $this->gass->getCustomVarsByScope(3));
        $this->assertArrayHasKey('index5', $customVars);
        $this->assertEquals($customVar2, $customVars['index5']);
        $this->assertEquals($customVar2['value'], $this->gass->getVisitorCustomVar(5));
        $this->assertEquals(array(implode('=', $customVar2)), $this->gass->getCustomVarsByScope(2));
    }

    public function testSetCustomVarExceptionInvalidIndexTooHigh()
    {
        $this->setExpectedException(
            'Gass\Exception\OutOfBoundsException',
            'The index must be an integer between 1 and 5.'
        );
        $this->gass->setCustomVar('Custom Var 1', 'Custom Value 1', 3, 6);
    }

    public function testSetCustomVarExceptionInvalidIndexTooLow()
    {
        $this->setExpectedException(
            'Gass\Exception\OutOfBoundsException',
            'The index must be an integer between 1 and 5.'
        );
        $this->gass->setCustomVar('Custom Var 1', 'Custom Value 1', 3, 0);
    }

    public function testSetCustomVarExceptionInvalidScopeTooHigh()
    {
        $this->setExpectedException(
            'Gass\Exception\InvalidArgumentException',
            'The Scope must be a value between 1 and 3'
        );
        $this->gass->setCustomVar('Custom Var 1', 'Custom Value 1', 4, 1);
    }

    public function testSetCustomVarExceptionInvalidScopeTooLow()
    {
        $this->setExpectedException(
            'Gass\Exception\InvalidArgumentException',
            'The Scope must be a value between 1 and 3'
        );
        $this->gass->setCustomVar('Custom Var 1', 'Custom Value 1', 0, 1);
    }

    public function testSetCustomVarExceptionNameWrongDataType()
    {
        $this->setExpectedException('Gass\Exception\InvalidArgumentException', 'Custom Var Name must be a string.');
        $this->gass->setCustomVar(array('Custom Var 1'), 'Custom Value 1');
    }

    public function testSetCustomVarExceptionValueWrongDataType()
    {
        $this->setExpectedException('Gass\Exception\InvalidArgumentException', 'Custom Var Value must be a string.');
        $this->gass->setCustomVar('Custom Var 1', array('Custom Value 1'));
    }

    public function testSetCustomVarExceptionExceedsByteVarLimit()
    {
        $this->setExpectedException(
            'Gass\Exception\DomainException',
            'The name / value combination exceeds the 128 byte custom var limit.'
        );
        $this->gass->setCustomVar(
            'abcdefghijklmnopqrstuvwxyz1234567890abcdefghijklmnopqrstuvwxyz12',
            'abcdefghijklmnopqrstuvwxyz1234567890abcdefghijklmnopqrstuvwxyz123'
        );
    }

    public function testSetCustomVarExceptionExceededVarCountLimit()
    {
        $this->setExpectedException(
            'Gass\Exception\OutOfBoundsException',
            'You cannot add more than 5 custom variables.'
        );
        $this->gass->setCustomVar('Custom Var 1', 'Custom Value 1');
        $this->gass->setCustomVar('Custom Var 2', 'Custom Value 2');
        $this->gass->setCustomVar('Custom Var 3', 'Custom Value 3');
        $this->gass->setCustomVar('Custom Var 4', 'Custom Value 4');
        $this->gass->setCustomVar('Custom Var 5', 'Custom Value 5');
        $this->gass->setCustomVar('Custom Var 6', 'Custom Value 6');
    }

    public function testGetVisitorCustomVarExceptionInvalidIndex()
    {
        $this->setExpectedException(
            'Gass\Exception\OutOfBoundsException',
            'The index: "10" has not been set.'
        );
        $this->gass->getVisitorCustomVar(10);
    }

    public function testDeleteCustomVarValid()
    {
        $this->gass->setCustomVar('Custom Var 1', 'Custom Value 1');
        $this->gass->setCustomVar('Custom Var 2', 'Custom Value 2');
        $this->gass->setCustomVar('Custom Var 3', 'Custom Value 3');
        $this->assertInstanceOf(
            'Gass\GoogleAnalyticsServerSide',
            $this->gass->deleteCustomVar(2)
        );
        $this->assertArrayNotHasKey('index2', $this->gass->getCustomVariables());
    }

    public function testDeleteCustomVarExceptionWrongDataType()
    {
        $this->setExpectedException('Gass\Exception\InvalidArgumentException', 'Custom Var Index must be a string.');
        $this->gass->deleteCustomVar(array(1));
    }

    public function testSetCharsetValid()
    {
        $charset = 'UTF-8';
        $this->assertInstanceOf(
            'Gass\GoogleAnalyticsServerSide',
            $this->gass->setCharset($charset)
        );
        $this->assertEquals($charset, $this->gass->getCharset());
        $this->gass->setCharset(strtolower($charset));
        $this->assertEquals($charset, $this->gass->getCharset());
    }

    public function testSetCharsetExceptionWrongDataType()
    {
        $this->setExpectedException('Gass\Exception\InvalidArgumentException', 'Charset must be a string.');
        $this->gass->setCharset(array('UTF-8'));
    }

    public function testSetSearchEnginesValid()
    {
        $searchEngines = array(
            'testa' => array('a'),
            'testb' => array('a', 'bcd'),
        );
        $this->assertInstanceOf(
            'Gass\GoogleAnalyticsServerSide',
            $this->gass->setSearchEngines($searchEngines)
        );
        $this->assertEquals($searchEngines, $this->gass->getSearchEngines());
    }

    public function testSetSearchEnginesExceptionWrongDataType()
    {
        $this->setExpectedException(
            (class_exists('TypeError')) ? 'TypeError' : 'PHPUnit_Framework_Error',
            'Argument 1 passed to Gass\GoogleAnalyticsServerSide::setSearchEngines() must be'
        );
        $this->gass->setSearchEngines(new \stdClass);
    }

    public function testSetSearchEnginesExceptionWrongQueryParamsDataType()
    {
        $this->setExpectedException('Gass\Exception\DomainException', 'searchEngines entry testb invalid');
        $this->gass->setSearchEngines(
            array(
                'testa' => array('a'),
                'testb' => new \stdClass,
            )
        );
    }

    public function testSetSearchEnginesExceptionWrongQueryParamsCount()
    {
        $this->setExpectedException('Gass\Exception\DomainException', 'searchEngines entry testa invalid');
        $this->gass->setSearchEngines(
            array(
                'testa' => array(),
                'testb' => array('b'),
            )
        );
    }

    public function testSetSearchEnginesExceptionWrongNameDataType()
    {
        $this->setExpectedException('Gass\Exception\OutOfBoundsException', 'search engine name "1" is invalid');
        $this->gass->setSearchEngines(
            array(
                1 => array('a'),
                'testb' => array('b'),
            )
        );
    }

    public function testSetSearchEnginesExceptionInvalidNameCharacters()
    {
        $this->setExpectedException('Gass\Exception\OutOfBoundsException', 'search engine name "test#" is invalid');
        $this->gass->setSearchEngines(
            array(
                'test#' => array('a'),
                'testb' => array('b'),
            )
        );
    }

    public function testSetSearchEnginesExceptionWrongQueryParameterDataType()
    {
        $this->setExpectedException('Gass\Exception\DomainException', 'search engine query parameter "1" is invalid');
        $this->gass->setSearchEngines(
            array(
                'testa' => array(1),
                'testb' => array('b'),
            )
        );
    }

    public function testSetSearchEnginesExceptionInvalidQueryParameterCharacters()
    {
        $this->setExpectedException('Gass\Exception\DomainException', 'search engine query parameter "a&" is invalid');
        $this->gass->setSearchEngines(
            array(
                'testa' => array('a&'),
                'testb' => array('b'),
            )
        );
    }

    public function testGetSearchEnginesValid()
    {
        $this->gass->setSearchEngines(array());
        $this->assertEmpty($this->gass->getSearchEngines());
    }

    public function testSetBotInfoValid()
    {
        $this->gass->setRemoteAddress('123.123.123.123');

        $this->assertInstanceOf(
            'Gass\GoogleAnalyticsServerSide',
            $this->gass->setBotInfo(true)
        );
        $currentBotInfo = $this->gass->getBotInfo();
        $this->assertInstanceOf('Gass\BotInfo\BotInfo', $currentBotInfo);
        $this->assertInstanceOf('Gass\BotInfo\BrowsCap', $currentBotInfo->getAdapter());

        $browserCap = new BrowsCap(array('browscap' => '/tmp/full_php_browscap.ini'));
        $this->gass->setBotInfo($browserCap);
        $currentBotInfo = $this->gass->getBotInfo();
        $this->assertInstanceOf('Gass\BotInfo\BotInfo', $currentBotInfo);
        $this->assertInstanceOf('Gass\BotInfo\BrowsCap', $currentBotInfo->getAdapter());

        $this->gass->setBotInfo(
            array(
                'adapter' => 'UserAgentStringInfo',
                'cachePath' => '/tmp/',
            )
        );
        $currentBotInfo = $this->gass->getBotInfo();
        $this->assertInstanceOf('Gass\BotInfo\BotInfo', $currentBotInfo);
        $this->assertInstanceOf(
            'Gass\BotInfo\UserAgentStringInfo',
            $currentBotInfo->getAdapter()
        );

        $this->gass->setBotInfo(null);
        $this->assertNull($this->gass->getBotInfo());
    }

    public function testSetBotInfoExceptionWrongDataType()
    {
        $this->setExpectedException(
            'Gass\Exception\InvalidArgumentException',
            'botInfo must be an array, boolean, null or a class which implements Gass\BotInfo\BotInfoInterface.'
        );
        $this->gass->setBotInfo(new \stdClass);
    }

    public function testSetHttpValid()
    {
        $this->gass->setRemoteAddress('123.123.123.123');

        $http = array('adapter' => 'curl');
        $this->assertInstanceOf(
            'Gass\GoogleAnalyticsServerSide',
            $this->gass->setHttp($http)
        );
        $this->assertEquals($http, $this->gass->getHttp());

        $httpAdapter = new HttpStream;
        $this->gass->setHttp($httpAdapter);
        $this->assertEquals($httpAdapter, $this->gass->getHttp());

        $this->gass->setHttp();
        $this->assertNull($this->gass->getHttp());
    }

    public function testSetHttpExceptionWrongDataType()
    {
        $this->setExpectedException(
            'Gass\Exception\InvalidArgumentException',
            'http must be an array, null or a class which implements Gass\Http\Interface.'
        );
        $this->gass->setHttp(new \stdClass);
    }

    public function testSetOptionsValid()
    {
        $this->assertInstanceOf(
            'Gass\GoogleAnalyticsServerSide',
            $this->gass->setOptions(
                array(
                    'AcceptLanguage' => 'en-gb',
                    'remoteAddress' => '123.123.123.123',
                )
            )
        );
    }

    public function testSetOptionsExceptionWrongDataType()
    {
        $this->setExpectedException(
            (class_exists('TypeError')) ? 'TypeError' : 'PHPUnit_Framework_Error',
            'Argument 1 passed to Gass\GoogleAnalyticsServerSide::setOptions() must be'
        );
        $this->gass->setOptions(new \stdClass);
    }

    public function testSetOptionValid()
    {
        $this->assertInstanceOf(
            'Gass\GoogleAnalyticsServerSide',
            $this->gass->setOption('remoteAddress', '123.123.123.123')
        );
        $this->gass->setOption('AcceptLanguage', 'en-gb');
    }

    public function testSetOptionExceptionMissingOption()
    {
        $this->setExpectedException('Gass\Exception\OutOfRangeException', 'Test is not an available option.');
        $this->gass->setOption('Test', 'Value');
    }

    public function testGetEventStringValid()
    {
        $category = 'Test Category';
        $action = 'Test Action';
        $label = 'Test Label';
        $value = 1;
        $this->assertEquals(
            '5(' . $category . '*' . $action . ')',
            $this->gass->getEventString($category, $action)
        );
        $this->assertEquals(
            '5(' . $category . '*' . $action . '*' . $label . ')',
            $this->gass->getEventString($category, $action, $label)
        );
        $this->assertEquals(
            '5(' . $category . '*' . $action . '*' . $label . ')(' . $value . ')',
            $this->gass->getEventString($category, $action, $label, $value)
        );

        // Testing BC
        $this->assertEquals(
            '5(' . $category . '*' . $action . ')',
            $this->gass->getEventString(
                array(
                    'category' => $category,
                    'action' => $action,
                )
            )
        );
        $this->assertEquals(
            '5(' . $category . '*' . $action . '*' . $label . ')',
            $this->gass->getEventString(
                array(
                    'category' => $category,
                    'action' => $action,
                    'label' => $label,
                )
            )
        );
        $this->assertEquals(
            '5(' . $category . '*' . $action . '*' . $label . ')(' . $value . ')',
            $this->gass->getEventString(
                array(
                    'category' => $category,
                    'action' => $action,
                    'label' => $label,
                    'value' => $value,
                )
            )
        );
    }

    public function testGetEventStringExceptionCategoryWrongDataType()
    {
        $this->setExpectedException('Gass\Exception\InvalidArgumentException', 'Event Category must be a string.');
        $this->gass->getEventString(new \stdClass, 'Value');
    }

    public function testGetEventStringExceptionEmptyCategory()
    {
        $this->setExpectedException(
            'Gass\Exception\InvalidArgumentException',
            'An event requires at least a category and action'
        );
        $this->gass->getEventString('', 'Value');
    }

    public function testGetEventStringExceptionActionWrongDataType()
    {
        $this->setExpectedException('Gass\Exception\InvalidArgumentException', 'Event Action must be a string.');
        $this->gass->getEventString('Category', new \stdClass);
    }

    public function testGetEventStringExceptionEmptyAction()
    {
        $this->setExpectedException(
            'Gass\Exception\InvalidArgumentException',
            'An event requires at least a category and action'
        );
        $this->gass->getEventString('Category', '');
    }

    public function testGetEventStringExceptionLabelWrongDataType()
    {
        $this->setExpectedException('Gass\Exception\InvalidArgumentException', 'Event Label must be a string.');
        $this->gass->getEventString('Category', 'Action', new \stdClass);
    }

    public function testGetEventStringExceptionValueWrongDataType()
    {
        $this->setExpectedException('Gass\Exception\InvalidArgumentException', 'Value must be an integer.');
        $this->gass->getEventString('Category', 'Action', 'Label', '1');
    }

    public function testGetCustomVariableStringValid()
    {
        $this->assertNull($this->gass->getCustomVariableString());
        $customVar1 = array(
            'index' => 1,
            'name' => 'Custom Var 1',
            'value' => 'Custom Value 1',
            'scope' => 3,
        );
        $customVar2 = array(
            'index' => 5,
            'name' => 'Custom Var 5',
            'value' => 'Custom Value 5',
            'scope' => 2,
        );
        $this->gass->setCustomVar($customVar1['name'], $customVar1['value']);
        $this->gass->setCustomVar(
            $customVar2['name'],
            $customVar2['value'],
            $customVar2['scope'],
            $customVar2['index']
        );
        $this->assertEquals(
            '8(' . $customVar1['name'] . '*' . $customVar2['name'] . ')' .
            '9(' . $customVar1['value'] . '*' . $customVar2['value'] . ')' .
            '11(5!' . $customVar2['scope'] . ')',
            $this->gass->getCustomVariableString()
        );
    }

    public function testGetIPToReportValid()
    {
        $this->assertEquals('8.8.4.0', $this->gass->getIPToReport());
        $remoteAddress = '123.123.123.123';
        $this->assertEquals('123.123.123.0', $this->gass->getIPToReport($remoteAddress));
        $this->assertEquals($remoteAddress, $this->gass->getRemoteAddress());
    }

    public function testGetDomainHashValid()
    {
        $this->assertEquals(32728376, $this->gass->getDomainHash('www.test.co.uk'));
        $this->assertEquals(217344784, $this->gass->getDomainHash('www.example.com'));
        $this->assertEquals(19229758, $this->gass->getDomainHash('www.unknown.net'));
    }

    public function testSetCookiesValid()
    {
        $this->gass->setServerName('www.example.com');
        $this->gass->disableCookieHeaders();
        $this->assertInstanceOf(
            'Gass\GoogleAnalyticsServerSide',
            $this->gass->setCookies()
        );
        $currentCookies = $this->gass->getCookies();
        $this->assertArrayHasKey('__utma', $currentCookies);
        $this->assertNotEmpty($currentCookies['__utma']);
        $this->assertArrayHasKey('__utmb', $currentCookies);
        $this->assertNotEmpty($currentCookies['__utmb']);
        $this->assertArrayHasKey('__utmc', $currentCookies);
        $this->assertNotEmpty($currentCookies['__utmc']);
        $this->assertArrayHasKey('__utmz', $currentCookies);
        $this->assertNotEmpty($currentCookies['__utmz']);
    }

    public function testGetCookiesStringValid()
    {
        $this->gass->setServerName('www.example.com');
        $this->gass->disableCookieHeaders();
        $this->gass->setCookies();
        $cookieString = $this->gass->getCookiesString();
        $this->assertContains('__utma', $cookieString);
        $this->assertContains('__utmb', $cookieString);
        $this->assertContains('__utmc', $cookieString);
        $this->assertContains('__utmz', $cookieString);
        $this->assertNotContains('__utmv', $cookieString);
    }

    public function testSetSessionCookieTimeoutValid()
    {
        $this->assertSame($this->gass, $this->gass->setSessionCookieTimeout(86400000));
        $this->assertAttributeEquals(86400, 'sessionCookieTimeout', $this->gass);
    }

    public function testSetSessionCookieTimeoutExceptionFloatArgument()
    {
        $this->setExpectedException(
            'Gass\Exception\InvalidArgumentException',
            'Session Cookie Timeout must be an integer.'
        );
        $this->gass->setSessionCookieTimeout(86400.000);
    }

    public function testSetSessionCookieTimeoutExceptionStringArgument()
    {
        $this->setExpectedException(
            'Gass\Exception\InvalidArgumentException',
            'Session Cookie Timeout must be an integer.'
        );
        $this->gass->setSessionCookieTimeout('86400000');
    }

    public function testSetVisitorCookieTimeoutValid()
    {
        $this->assertSame($this->gass, $this->gass->setVisitorCookieTimeout(86400000));
        $this->assertAttributeEquals(86400, 'visitorCookieTimeout', $this->gass);
    }

    public function testSetVisitorCookieTimeoutExceptionFloatArgument()
    {
        $this->setExpectedException(
            'Gass\Exception\InvalidArgumentException',
            'Visitor Cookie Timeout must be an integer.'
        );
        $this->gass->setVisitorCookieTimeout(86400.000);
    }

    public function testSetVisitorCookieTimeoutExceptionStringArgument()
    {
        $this->setExpectedException(
            'Gass\Exception\InvalidArgumentException',
            'Visitor Cookie Timeout must be an integer.'
        );
        $this->gass->setVisitorCookieTimeout('86400000');
    }

    public function testDisableCookieHeadersValid()
    {
        $this->assertSame($this->gass, $this->gass->disableCookieHeaders());
        $this->assertAttributeEquals(false, 'sendCookieHeaders', $this->gass);
    }

    public function testSetVersionFromJsValid()
    {
        $this->expectJsUrlCall();
        $this->assertSame($this->gass, $this->gass->setVersion('1.1.1'));
        $this->assertSame($this->gass, $this->gass->setVersionFromJs());
        $this->assertAttributeEquals('5.6.7', 'version', $this->gass);
    }

    public function testSetSearchEnginesFromJsValid()
    {
        $this->expectJsUrlCall();
        $this->assertSame($this->gass, $this->gass->setSearchEngines(array()));
        $this->assertSame($this->gass, $this->gass->setSearchEnginesFromJs());
        $jsSearchEngines = $this->gass->getSearchEngines();
        $this->assertNotEmpty($jsSearchEngines);
        $this->assertArrayHasKey('google', $jsSearchEngines);
        $this->assertArrayHasKey('yahoo', $jsSearchEngines);
        $this->assertArrayHasKey('ask', $jsSearchEngines);
    }

    public function testTrackPageviewValid()
    {
        $this->initialiseBrowserDetails()
            ->expectJsAndGifUrlCall()
            ->gass->disableCookieHeaders()
            ->setAccount('MO-00000-0');
        $this->gass->setPageTitle('Example Page Title');
        $this->expectJsAndGifUrlCall();
        $this->assertSame($this->gass, $this->gass->trackPageview());
        $this->gass->setCustomVar('Custom Var 5', 'Custom Value 5', 2, 5);
        $this->gass->trackPageview();
        $this->gass->trackPageview('http://www.test.co.uk/example/path?q=other');
        $this->initialiseBotInfoAdapter();
        $this->gass->trackPageview();
    }

    public function testTrackPageviewExceptionInvalidUrl()
    {
        $url = 'www.test.co.uk/example/path?q=other';
        $this->setExpectedException(
            'Gass\Exception\DomainException',
            'Url is invalid: ' . $url
        );
        $this->gass->trackPageview($url);
    }

    public function testTrackPageviewExceptionMissingAccount()
    {
        $this->initialiseBrowserDetails()
            ->gass->disableCookieHeaders();
        $this->setExpectedException(
            'Gass\Exception\DomainException',
            'The account number must be set before any tracking can take place.'
        );
        $this->gass->trackPageview();
    }

    /**
     * @depends testGetEventStringValid
     * @depends testGetEventStringExceptionActionWrongDataType
     * @depends testGetEventStringExceptionCategoryWrongDataType
     * @depends testGetEventStringExceptionEmptyAction
     * @depends testGetEventStringExceptionEmptyCategory
     * @depends testGetEventStringExceptionLabelWrongDataType
     */
    public function testTrackEventValid()
    {
        $this->initialiseBrowserDetails()
            ->expectJsAndGifUrlCall()
            ->gass->disableCookieHeaders()
            ->setAccount('MO-00000-0');
        $category = 'Test Category';
        $action = 'Test Action';
        $label = 'Test Label';
        $value = 1;
        $this->assertInstanceOf(
            'Gass\GoogleAnalyticsServerSide',
            $this->gass->trackEvent($category, $action, $label, $value)
        );
        $this->gass->setCustomVar('Custom Var 5', 'Custom Value 5', 2, 5);
        $this->gass->trackEvent($category, $action, $label, $value);
        $this->initialiseBotInfoAdapter();
        $this->gass->trackEvent($category, $action, $label, $value, true);
    }

    public function testTrackEventExceptionMissingAccount()
    {
        $this->initialiseBrowserDetails()
            ->gass->disableCookieHeaders();
        $this->setExpectedException(
            'Gass\Exception\DomainException',
            'The account number must be set before any tracking can take place.'
        );
        $this->gass->trackEvent('Test Category', 'Test Action', 'Test Label', 1);
    }

    public function testTrackEventExceptionWrongNonInteractionDataType()
    {
        $this->initialiseBrowserDetails()
            ->gass->disableCookieHeaders();
        $this->setExpectedException(
            'Gass\Exception\InvalidArgumentException',
            'NonInteraction must be a boolean.'
        );
        $this->gass->trackEvent('Test Category', 'Test Action', 'Test Label', 1, 1);
    }
}

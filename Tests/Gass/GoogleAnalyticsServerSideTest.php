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

namespace GassTests\Gass;

use Gass\BotInfo\BotInfo;
use Gass\GoogleAnalyticsServerSide;
use GassTests\TestAbstract;
use Mockery as m;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class GoogleAnalyticsServerSideTest extends TestAbstract
{
    /**
     * The contents of ga.js
     *
     * @var string
     */
    protected $jsFileContents;

    /**
     * Take copy of ga.js so these tests will run a little bit quicker
     */
    protected function setUp()
    {
        parent::setup();
        $ds = DIRECTORY_SEPARATOR;

        $this->jsFileContents = file_get_contents(
            realpath(__DIR__ . $ds . '..' . $ds . 'dependency-files' . $ds . 'ga.js')
        );
    }

    private function expectGifUrlCall($http)
    {
        $http->shouldReceive('request')
            ->atLeast(1)
            ->with(matchesPattern('/^' . preg_quote(GoogleAnalyticsServerSide::GIF_URL, '/') . '/'))
            ->andReturnSelf();
        return $this;
    }

    private function expectJsUrlCall($http)
    {
        $http->shouldReceive('request')
            ->once()
            ->with(GoogleAnalyticsServerSide::JS_URL)
            ->andReturnSelf();
        $http->shouldReceive('getResponse')
            ->once()
            ->withNoArgs()
            ->andReturn($this->jsFileContents);
        return $this;
    }

    private function expectJsAndGifUrlCall($http)
    {
        return $this->expectGifUrlCall($http)
            ->expectJsUrlCall($http);
    }

    public function testConstructWithoutOptions()
    {
        $envServerName = $this->getEnvVar('SERVER_NAME');
        $envRemoteAddress = $this->getEnvVar('REMOTE_ADDR');
        $envRequestUri = $this->getEnvVar('REQUEST_URI');
        $envUserAgent = $this->getEnvVar('HTTP_USER_AGENT');
        $envDocumentReferer = $this->getEnvVar('HTTP_REFERER');
        $envAcceptLanguage = $this->getEnvVar('HTTP_ACCEPT_LANGUAGE');

        $http = m::mock('overload:Gass\Http\Http');
        if (!empty($envUserAgent)) {
            $http->shouldReceive('setUserAgent')
                ->once()
                ->with($envUserAgent)
                ->andReturnSelf();
        }
        if (!empty($envRemoteAddress)) {
            $http->shouldReceive('setRemoteAddress')
                ->once()
                ->with($envRemoteAddress)
                ->andReturnSelf();
        }
        if (!empty($envAcceptLanguage)) {
            $http->shouldReceive('setAcceptLanguage')
                ->once()
                ->with($envAcceptLanguage)
                ->andReturnSelf();
        }

        $botInfo = m::mock('overload:Gass\BotInfo\BotInfo');

        $ipValidator = m::mock('overload:Gass\Validate\IpAddress', IpAddressInterfaceStub::class);
        if (!empty($envRemoteAddress)) {
            $ipValidator->shouldReceive('isValid')
                ->once()
                ->with($envRemoteAddress)
                ->andReturn(true);
        }

        $langValidator = m::mock('overload:Gass\Validate\LanguageCode');
        if (!empty($envAcceptLanguage)) {
            $langValidator->shouldReceive('isValid')
                ->once()
                ->with($envAcceptLanguage)
                ->andReturn(true);
        }

        $urlValidator = m::mock('overload:Gass\Validate\Url');
        if (!empty($envDocumentReferer)) {
            $urlValidator->shouldReceive('isValid')
                ->once()
                ->with($envDocumentReferer)
                ->andReturn(true);
        }

        $gass = new GoogleAnalyticsServerSide;
        if (!empty($envServerName)) {
            $this->assertAttributeEquals($envServerName, 'serverName', $gass);
        }
        if (!empty($envRemoteAddress)) {
            $this->assertAttributeEquals($envRemoteAddress, 'remoteAddress', $gass);
        }
        if (!empty($envRequestUri)) {
            $this->assertAttributeEquals($envRequestUri, 'documentPath', $gass);
        }
        if (!empty($envUserAgent)) {
            $this->assertAttributeEquals($envUserAgent, 'userAgent', $gass);
        }
        if (!empty($envDocumentReferer)) {
            $this->assertAttributeEquals($envDocumentReferer, 'documentReferer', $gass);
        }
        if (!empty($envAcceptLanguage)) {
            $this->assertAttributeEquals($envAcceptLanguage, 'acceptLanguage', $gass);
        }
    }

    public function testConstructWithOptions()
    {
        $envServerName = $this->getEnvVar('SERVER_NAME');
        $envRemoteAddress = $this->getEnvVar('REMOTE_ADDR');
        $envRequestUri = $this->getEnvVar('REQUEST_URI');
        $envUserAgent = $this->getEnvVar('HTTP_USER_AGENT');
        $envDocumentReferer = $this->getEnvVar('HTTP_REFERER');
        $envAcceptLanguage = $this->getEnvVar('HTTP_ACCEPT_LANGUAGE');

        $http = m::mock('overload:Gass\Http\Http');
        if (!empty($envUserAgent)) {
            $http->shouldReceive('setUserAgent')
                ->once()
                ->with($envUserAgent)
                ->andReturnSelf();
        }
        if (!empty($envRemoteAddress)) {
            $http->shouldReceive('setRemoteAddress')
                ->once()
                ->with($envRemoteAddress)
                ->andReturnSelf();
        }
        if (!empty($envAcceptLanguage)) {
            $http->shouldReceive('setAcceptLanguage')
                ->once()
                ->with($envAcceptLanguage)
                ->andReturnSelf();
        }

        $botInfo = m::mock('overload:Gass\BotInfo\BotInfo');

        $ipValidator = m::mock('overload:Gass\Validate\IpAddress', IpAddressInterfaceStub::class);
        if (!empty($envRemoteAddress)) {
            $ipValidator->shouldReceive('isValid')
                ->once()
                ->with($envRemoteAddress)
                ->andReturn(true);
        }

        $langValidator = m::mock('overload:Gass\Validate\LanguageCode');
        if (!empty($envAcceptLanguage)) {
            $langValidator->shouldReceive('isValid')
                ->once()
                ->with($envAcceptLanguage)
                ->andReturn(true);
        }

        $urlValidator = m::mock('overload:Gass\Validate\Url');
        if (!empty($envDocumentReferer)) {
            $urlValidator->shouldReceive('isValid')
                ->once()
                ->with($envDocumentReferer)
                ->andReturn(true);
        }

        $options = array(
            'charset' => 'foo',
            'pageTitle' => 'bar',
        );

        $gass = new GoogleAnalyticsServerSide($options);
        if (!empty($envServerName)) {
            $this->assertAttributeEquals($envServerName, 'serverName', $gass);
        }
        if (!empty($envRemoteAddress)) {
            $this->assertAttributeEquals($envRemoteAddress, 'remoteAddress', $gass);
        }
        if (!empty($envRequestUri)) {
            $this->assertAttributeEquals($envRequestUri, 'documentPath', $gass);
        }
        if (!empty($envUserAgent)) {
            $this->assertAttributeEquals($envUserAgent, 'userAgent', $gass);
        }
        if (!empty($envDocumentReferer)) {
            $this->assertAttributeEquals($envDocumentReferer, 'documentReferer', $gass);
        }
        if (!empty($envAcceptLanguage)) {
            $this->assertAttributeEquals($envAcceptLanguage, 'acceptLanguage', $gass);
        }
        $this->assertAttributeEquals(strtoupper($options['charset']), 'charset', $gass);
        $this->assertAttributeEquals($options['pageTitle'], 'pageTitle', $gass);
    }

    public function testSetVersionValid()
    {
        list($gass) = $this->getGassAndDependencies();
        $testValue = '1.2.3';
        $this->assertSame($gass, $gass->setVersion($testValue));
        $this->assertAttributeEquals($testValue, 'version', $gass);
    }

    /**
     * @dataProvider dataProviderTestSetVersionExceptionInvalidArgument
     */
    public function testSetVersionExceptionInvalidArgument($version)
    {
        list($gass) = $this->getGassAndDependencies();
        $this->setExpectedException(
            'Gass\Exception\InvalidArgumentException',
            (!is_array($version) && !is_object($version))
                ? 'Invalid version number provided: ' . $version
                : 'Version must be a string'
        );
        $gass->setVersion($version);
    }

    public function dataProviderTestSetVersionExceptionInvalidArgument()
    {
        return array(
            array(null),
            array('5.23'),
            array('523'),
            array('abc'),
            array(array('5.2.3')),
            array(new \stdClass),
        );
    }

    /**
     * @depends testSetVersionValid
     */
    public function testGetVersion()
    {
        list($gass) = $this->getGassAndDependencies();
        $testValue = '1.2.3';
        $gass->setVersion($testValue);
        $this->assertEquals($testValue, $gass->getVersion());
    }

    public function testSetUserAgentValid()
    {
        list(
            $gass,
            $http,
            $botInfo
        ) = $this->getGassAndDependencies();

        $userAgent = 'testString';

        $botInfo->shouldReceive('setUserAgent')
            ->once()
            ->with($userAgent)
            ->andReturnSelf();

        $http->shouldReceive('setUserAgent')
            ->once()
            ->with($userAgent)
            ->andReturnSelf();

        $gass->setBotInfo(m::mock('Gass\BotInfo\BotInfoInterface'));

        $this->assertSame($gass, $gass->setUserAgent($userAgent));
        $this->assertAttributeEquals($userAgent, 'userAgent', $gass);
    }

    /**
     * @dataProvider dataProviderStringCastWrongDataType
     */
    public function testSetUserAgentExceptionWrongDataType($userAgent)
    {
        list($gass) = $this->getGassAndDependencies();
        $this->setExpectedException('Gass\Exception\InvalidArgumentException', 'User Agent must be a string.');
        $gass->setUserAgent($userAgent);
    }

    /**
     * @depends testSetUserAgentValid
     */
    public function testGetUserAgent()
    {
        list(
            $gass,
            $http,
            $botInfo
        ) = $this->getGassAndDependencies();

        $userAgent = 'testString';

        $botInfo->shouldReceive('setUserAgent');
        $http->shouldReceive('setUserAgent');
        $gass->setBotInfo(m::mock('Gass\BotInfo\BotInfoInterface'));

        $gass->setUserAgent($userAgent);
        $this->assertEquals($userAgent, $gass->getUserAgent());
    }

    /**
     * @dataProvider dataProviderTestSetAcceptLanguageValid
     */
    public function testSetAcceptLanguageValid($fullString, $deducedCode)
    {
        $setAcceptanceLanguageCalls = 0;
        $envAcceptLanguage = $this->getEnvVar('HTTP_ACCEPT_LANGUAGE');

        /*
         * A little bit hackery due to the class being instantiated each call to setAcceptLanguage
         * including via the constructor with the HTTP_ACCEPT_LANGUAGE header value in phpunit.xml.dist
         */
        $langValidator = m::mock('overload:Gass\Validate\LanguageCode');
        $langValidator->shouldReceive('isValid')
            ->once()
            ->with(m::anyOf($envAcceptLanguage, $deducedCode))
            ->andReturnUsing(
                function ($acceptLanguage) use (&$setAcceptanceLanguageCalls, $envAcceptLanguage, $deducedCode) {
                    if ((1 === ++$setAcceptanceLanguageCalls && $envAcceptLanguage === $acceptLanguage)
                        || (2 === $setAcceptanceLanguageCalls && $deducedCode === $acceptLanguage)
                    ) {
                        return true;
                    }
                    throw new \BadMethodCallException(
                        'Unexpected call to Gass\Validate\LanguageCode::isValid on iteration ' .
                            $setAcceptanceLanguageCalls
                    );
                }
            );

        list(
            $gass,
            $http
        ) = $this->getGassAndDependencies(true, true, false);

        $http->shouldReceive('setAcceptLanguage')
            ->once()
            ->with($deducedCode)
            ->andReturnSelf();

        $this->assertSame($gass, $gass->setAcceptLanguage($fullString));
        $this->assertAttributeEquals($deducedCode, 'acceptLanguage', $gass);
    }

    public function dataProviderTestSetAcceptLanguageValid()
    {
        return array(
            array('en-US,en;q=0.8', 'en-us'),
            array('fil-PH,fil;q=0.8', 'fil-ph'),
            array('en,en-US;q=0.8', 'en'),
            array('fil,fil-PH;q=0.8', 'fil'),
        );
    }

    public function testSetAcceptLanguageExceptionInvalidArgumentLanguageCodeNotValidFormat()
    {
        $setAcceptanceLanguageCalls = 0;
        $invalidLanguage = 'foo';
        $expectedMessages = array('baz', 'qux');
        $envAcceptLanguage = $this->getEnvVar('HTTP_ACCEPT_LANGUAGE');

        /*
         * A little bit hackery due to the class being instantiated each call to setAcceptLanguage
         * including via the constructor with the HTTP_ACCEPT_LANGUAGE header value in phpunit.xml.dist
         */
        $langValidator = m::mock('overload:Gass\Validate\LanguageCode');
        $langValidator->shouldReceive('isValid')
            ->once()
            ->with(m::anyOf($envAcceptLanguage, $invalidLanguage))
            ->andReturnUsing(
                function ($acceptLanguage) use (&$setAcceptanceLanguageCalls, $envAcceptLanguage, $invalidLanguage) {
                    if (1 === ++$setAcceptanceLanguageCalls && $envAcceptLanguage === $acceptLanguage) {
                        return true;
                    } elseif (2 === $setAcceptanceLanguageCalls && $invalidLanguage === $acceptLanguage) {
                        return false;
                    }
                    throw new \BadMethodCallException(
                        'Unexpected call to Gass\Validate\LanguageCode::isValid on iteration ' .
                            $setAcceptanceLanguageCalls
                    );
                }
            );
        $langValidator->shouldReceive('getMessages')
            ->between(0, 1)
            ->withNoArgs()
            ->andReturnUsing(
                function () use (&$setAcceptanceLanguageCalls, $expectedMessages) {
                    if (2 === $setAcceptanceLanguageCalls) {
                        return $expectedMessages;
                    }
                    throw new \BadMethodCallException(
                        'Unexpected call to Gass\Validate\LanguageCode::getMessages on iteration ' .
                            $setAcceptanceLanguageCalls
                    );
                }
            );

        list(
            $gass,
            $http
        ) = $this->getGassAndDependencies(true, true, false);

        $http->shouldNotReceive('setAcceptLanguage')
            ->with($invalidLanguage);

        $this->setExpectedException(
            'Gass\Exception\InvalidArgumentException',
            'Accept Language validation errors: ' . implode(', ', $expectedMessages)
        );
        $gass->setAcceptLanguage($invalidLanguage);
    }

    /**
     * @dataProvider dataProviderStringCastWrongDataType
     */
    public function testSetAcceptLanguageExceptionInvalidArgumentLanguageCodeWrongDataType($invalidLanguage)
    {
        $envAcceptLanguage = $this->getEnvVar('HTTP_ACCEPT_LANGUAGE');

        $langValidator = m::mock('overload:Gass\Validate\LanguageCode');
        $langValidator->shouldReceive('isValid')
            ->once()
            ->with($envAcceptLanguage)
            ->andReturn(true);

        list($gass) = $this->getGassAndDependencies(true, true, false);

        $this->setExpectedException('Gass\Exception\InvalidArgumentException', 'Accept Language must be a string.');
        $gass->setAcceptLanguage($invalidLanguage);
    }

    /**
     * @depends testSetAcceptLanguageValid
     */
    public function testGetAcceptLanguage()
    {
        $langValidator = m::mock('overload:Gass\Validate\LanguageCode');
        $langValidator->shouldReceive('isValid')->andReturn(true);

        list(
            $gass,
            $http
        ) = $this->getGassAndDependencies(true, true, false);
        $http->shouldReceive('setAcceptLanguage');

        $acceptLanguage = 'foo';

        $gass->setAcceptLanguage($acceptLanguage);
        $this->assertEquals($acceptLanguage, $gass->getAcceptLanguage());
    }

    public function testSetServerNameValid()
    {
        list($gass) = $this->getGassAndDependencies();
        $serverName = 'foo';
        $this->assertSame($gass, $gass->setServerName($serverName));
        $this->assertAttributeEquals($serverName, 'serverName', $gass);
    }

    /**
     * @dataProvider dataProviderStringCastWrongDataType
     */
    public function testSetServerNameExceptionWrongDataType($serverName)
    {
        list($gass) = $this->getGassAndDependencies();
        $this->setExpectedException('Gass\Exception\InvalidArgumentException', 'Server Name must be a string.');
        $gass->setServerName($serverName);
    }

    /**
     * @depends testSetServerNameValid
     */
    public function testGetServerName()
    {
        list($gass) = $this->getGassAndDependencies();
        $serverName = 'foo';
        $gass->setServerName($serverName);
        $this->assertEquals($serverName, $gass->getServerName());
    }

    /**
     * @dataProvider dataProviderBooleans
     */
    public function testSetRemoteAddressValid($setBotInfo)
    {
        $setRemoteAddressCalls = 0;
        $envRemoteAddress = $this->getEnvVar('REMOTE_ADDR');
        $remoteAddress = 'foo';

        /*
         * A little bit hackery due to the class being instantiated each call to setRemoteAddress
         * including via the constructor with the REMOTE_ADDR header value in phpunit.xml.dist
         */
        $ipValidator = m::mock('overload:Gass\Validate\IpAddress', IpAddressInterfaceStub::class);
        $ipValidator->shouldReceive('isValid')
            ->once()
            ->with(m::anyOf($envRemoteAddress, $remoteAddress))
            ->andReturnUsing(
                function ($ipAddress) use (&$setRemoteAddressCalls, $envRemoteAddress, $remoteAddress) {
                    if ((1 === ++$setRemoteAddressCalls && $envRemoteAddress === $ipAddress)
                        || (2 === $setRemoteAddressCalls && $remoteAddress === $ipAddress)
                    ) {
                        return true;
                    }
                    throw new \BadMethodCallException(
                        'Unexpected call to Gass\Validate\IpAddress::isValid on iteration ' . $setRemoteAddressCalls
                    );
                }
            );

        $rc = new \ReflectionClass('Gass\Validate\IpAddress');

        list(
            $gass,
            $http,
            $botInfo
        ) = $this->getGassAndDependencies($setBotInfo, false);

        $http->shouldReceive('setRemoteAddress')
            ->once()
            ->with($remoteAddress)
            ->andReturnSelf();

        if ($setBotInfo) {
            $botInfo->shouldReceive('setRemoteAddress')
                ->once()
                ->with($remoteAddress)
                ->andReturnSelf();
            $gass->setBotInfo(m::mock('Gass\BotInfo\BotInfoInterface'));
        }

        $this->assertSame($gass, $gass->setRemoteAddress($remoteAddress));
        $this->assertAttributeEquals($remoteAddress, 'remoteAddress', $gass);
    }

    public function testSetRemoteAddressExceptionInvalidArgumentIpAddressInvalidV4()
    {
        $setRemoteAddressCalls = 0;
        $envRemoteAddress = $this->getEnvVar('REMOTE_ADDR');
        $invalidRemoteAddress = 'foo';
        $expectedMessages = array('baz', 'qux');

        /*
         * A little bit hackery due to the class being instantiated each call to setRemoteAddress
         * including via the constructor with the REMOTE_ADDR header value in phpunit.xml.dist
         */
        $ipValidator = m::mock('overload:Gass\Validate\IpAddress', IpAddressInterfaceStub::class);
        $ipValidator->shouldReceive('isValid')
            ->once()
            ->with(m::anyOf($envRemoteAddress, $invalidRemoteAddress))
            ->andReturnUsing(
                function ($acceptLanguage) use (&$setRemoteAddressCalls, $envRemoteAddress, $invalidRemoteAddress) {
                    if (1 === ++$setRemoteAddressCalls && $envRemoteAddress === $acceptLanguage) {
                        return true;
                    } elseif (2 === $setRemoteAddressCalls && $invalidRemoteAddress === $acceptLanguage) {
                        return false;
                    }
                    throw new \BadMethodCallException(
                        'Unexpected call to Gass\Validate\IpAddress::isValid on iteration ' . $setRemoteAddressCalls
                    );
                }
            );
        $ipValidator->shouldReceive('getMessages')
            ->between(0, 1)
            ->withNoArgs()
            ->andReturnUsing(
                function () use (&$setRemoteAddressCalls, $expectedMessages) {
                    if (2 === $setRemoteAddressCalls) {
                        return $expectedMessages;
                    }
                    throw new \BadMethodCallException(
                        'Unexpected call to Gass\Validate\IpAddress::getMessages on iteration ' .
                            $setRemoteAddressCalls
                    );
                }
            );

        list($gass) = $this->getGassAndDependencies();
        $gass->setBotInfo(m::mock('Gass\BotInfo\BotInfoInterface'));

        $this->setExpectedException(
            'Gass\Exception\InvalidArgumentException',
            'Remote Address validation errors: ' . implode(', ', $expectedMessages)
        );
        $gass->setRemoteAddress($invalidRemoteAddress);
    }

    /**
     * @dataProvider dataProviderStringCastWrongDataType
     */
    public function testSetRemoteAddressExceptionInvalidArgumentIpAddressWrongDataType($invalidRemoteAddress)
    {
        $envRemoteAddress = $this->getEnvVar('REMOTE_ADDR');

        $ipValidator = m::mock('overload:Gass\Validate\IpAddress', IpAddressInterfaceStub::class);
        $ipValidator->shouldReceive('isValid')
            ->once()
            ->with($envRemoteAddress)
            ->andReturn(true);

        list($gass) = $this->getGassAndDependencies();
        $gass->setBotInfo(m::mock('Gass\BotInfo\BotInfoInterface'));

        $this->setExpectedException('Gass\Exception\InvalidArgumentException', 'Remote Address must be a string.');
        $gass->setRemoteAddress($invalidRemoteAddress);
    }

    /**
     * @depends testSetRemoteAddressValid
     */
    public function testGetRemoteAddress()
    {
        $ipValidator = m::mock('overload:Gass\Validate\IpAddress', IpAddressInterfaceStub::class);
        $ipValidator->shouldReceive('isValid')->andReturn(true);

        list(
            $gass,
            $http
        ) = $this->getGassAndDependencies(false, false);
        $http->shouldReceive('setRemoteAddress');

        $remoteAddress = 'foo';

        $gass->setRemoteAddress($remoteAddress);
        $this->assertEquals($remoteAddress, $gass->getRemoteAddress());
    }

    /**
     * @dataProvider dataProviderTestSetAccountValid
     */
    public function testSetAccountValid($account)
    {
        list($gass) = $this->getGassAndDependencies();
        $this->assertSame($gass, $gass->setAccount($account));
        $this->assertAttributeEquals($account, 'account', $gass);
    }

    public function dataProviderTestSetAccountValid()
    {
        return array(
            array('UA-1234-5'),
            array('MO-1234567-89'),
        );
    }

    /**
     * @dataProvider dataProviderTestSetAccountExceptionInvalidArgument
     */
    public function testSetAccountExceptionInvalidArgument($account, $exceptionMessage = null)
    {
        $exceptionMessage = (null !== $exceptionMessage)
            ? $exceptionMessage
            : 'Google Analytics user account must be in the format: UA-XXXXXXX-X or MO-XXXXXXX-X';

        list($gass) = $this->getGassAndDependencies();

        $this->setExpectedException(
            'Gass\Exception\InvalidArgumentException',
            $exceptionMessage
        );
        $gass->setAccount($account);
    }

    public function dataProviderTestSetAccountExceptionInvalidArgument()
    {
        return array(
            array('AB-1234567-0'),
            array('UA1234567-0'),
            array('UA-12345670'),
            array('mo-1234567-0'),
            array(array('UA-1234-5'), 'Account must be a string.'),
            array(new \stdClass, 'Account must be a string.'),
        );
    }

    /**
     * @depends testSetAccountValid
     */
    public function testGetAccount()
    {
        list($gass) = $this->getGassAndDependencies();
        $account = 'MO-1234-1';
        $gass->setAccount($account);
        $this->assertEquals($account, $gass->getAccount());
    }

    public function testSetDocumentRefererValid()
    {
        $envReferer = $this->getEnvVar('HTTP_REFERER');
        $documentReferer = 'foo';

        $urlValidator = m::mock('overload:Gass\Validate\Url');
        $urlValidator->shouldReceive('isValid')
            ->once()
            ->with($envReferer)
            ->andReturn(true);
        $urlValidator->shouldReceive('isValid')
            ->once()
            ->with($documentReferer)
            ->andReturn(true);

        list($gass) = $this->getGassAndDependencies(true, true, true, false);

        $this->assertSame($gass, $gass->setDocumentReferer($documentReferer));
        $this->assertAttributeEquals($documentReferer, 'documentReferer', $gass);
    }

    public function testSetDocumentRefererExceptionInvalidArgumentUrlNotValid()
    {
        $setDocumentRefererCalls = 0;
        $envReferer = $this->getEnvVar('HTTP_REFERER');
        $invalidDocumentReferer = 'foo';

        $urlValidator = m::mock('overload:Gass\Validate\Url');
        $urlValidator->shouldReceive('isValid')
            ->once()
            ->with($envReferer)
            ->andReturn(true);
        $urlValidator->shouldReceive('isValid')
            ->once()
            ->with($invalidDocumentReferer)
            ->andReturn(false);

        list($gass) = $this->getGassAndDependencies();

        $this->setExpectedException(
            'Gass\Exception\InvalidArgumentException',
            'Document Referer must be a valid URL.'
        );
        $gass->setDocumentReferer($invalidDocumentReferer);
    }

    /**
     * @dataProvider dataProviderStringCastWrongDataType
     */
    public function testSetDocumentRefererExceptionInvalidArgumentRefererWrongDataType($invalidReferer)
    {
        $envReferer = $this->getEnvVar('HTTP_REFERER');

        $ipValidator = m::mock('overload:Gass\Validate\Url');
        $ipValidator->shouldReceive('isValid')
            ->once()
            ->with($envReferer)
            ->andReturn(true);

        list($gass) = $this->getGassAndDependencies();

        $this->setExpectedException('Gass\Exception\InvalidArgumentException', 'Document Referer must be a string.');
        $gass->setDocumentReferer($invalidReferer);
    }

    /**
     * @depends testSetDocumentRefererValid
     */
    public function testGetDocumentReferer()
    {
        $documentReferer = 'foo';

        $urlValidator = m::mock('overload:Gass\Validate\Url');
        $urlValidator->shouldReceive('isValid')->andReturn(true);

        list($gass) = $this->getGassAndDependencies(true, true, true, false);

        $gass->setDocumentReferer($documentReferer);
        $this->assertEquals($documentReferer, $gass->getDocumentReferer());
    }

    /**
     * @dataProvider dataProviderTestSetDocumentPathValid
     */
    public function testSetDocumentPathValid($documentPath)
    {
        $expectedPath = $documentPath;
        if (false !== ($queryPos = strpos($documentPath, '?'))) {
            $expectedPath = substr($documentPath, 0, $queryPos);
        }
        list($gass) = $this->getGassAndDependencies();
        $this->assertSame($gass, $gass->setDocumentPath($documentPath));
        $this->assertAttributeEquals($expectedPath, 'documentPath', $gass);
    }

    public function dataProviderTestSetDocumentPathValid()
    {
        return array(
            array('/abcdefg.html'),
            array('/hijk/lmno/1234/pqr?a=b&c=d'),
        );
    }

    /**
     * @dataProvider dataProviderStringCastWrongDataType
     */
    public function testSetDocumentPathExceptionWrongDataType($documentPath)
    {
        list($gass) = $this->getGassAndDependencies();
        $this->setExpectedException('Gass\Exception\InvalidArgumentException', 'Document Path must be a string.');
        $gass->setDocumentPath($documentPath);
    }

    /**
     * @depends testSetDocumentPathValid
     */
    public function testGetDocumentPath()
    {
        $documentPath = 'foo';
        list($gass) = $this->getGassAndDependencies();
        $gass->setDocumentPath($documentPath);
        $this->assertEquals($documentPath, $gass->getDocumentPath());
    }

    public function testSetPageTitleValid()
    {
        list($gass) = $this->getGassAndDependencies();
        $pageTitle = 'Abcdef Ghijk Lmnop';
        $this->assertSame($gass, $gass->setPageTitle($pageTitle));
        $this->assertAttributeEquals($pageTitle, 'pageTitle', $gass);
    }

    /**
     * @dataProvider dataProviderStringCastWrongDataType
     */
    public function testSetPageTitleExceptionWrongDataType($pageTitle)
    {
        list($gass) = $this->getGassAndDependencies();
        $this->setExpectedException('Gass\Exception\InvalidArgumentException', 'Page Title must be a string.');
        $gass->setPageTitle($pageTitle);
    }

    /**
     * @depends testSetPageTitleValid
     */
    public function testGetPageTitle()
    {
        list($gass) = $this->getGassAndDependencies();
        $pageTitle = 'Abcdef Ghijk Lmnop';
        $gass->setPageTitle($pageTitle);
        $this->assertEquals($pageTitle, $gass->getPageTitle());
    }

    public function testSetCustomVarValidBasic()
    {
        list($gass) = $this->getGassAndDependencies();
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
        $this->assertSame($gass, $gass->setCustomVar($customVar1['name'], $customVar1['value']));
        $this->assertSame(
            $gass,
            $gass->setCustomVar(
                $customVar2['name'],
                $customVar2['value'],
                $customVar2['scope'],
                $customVar2['index']
            )
        );
        $this->assertAttributeEquals(
            array(
                'index1' => $customVar1,
                'index5' => $customVar2,
            ),
            'customVariables',
            $gass
        );
    }

    /**
     * @depends testSetCustomVarValidBasic
     */
    public function testSetCustomVarValidFillsInEmptyIndexes()
    {
        list($gass) = $this->getGassAndDependencies();
        $gass->setCustomVar('Custom Var 1', 'Custom Value 1');
        $gass->setCustomVar('Custom Var 2', 'Custom Value 2');
        $gass->setCustomVar('Custom Var 3', 'Custom Value 3', 1, 4);
        $gass->setCustomVar('Custom Var 4', 'Custom Value 4', 2, 5);
        $gass->setCustomVar('Custom Var 5', 'Custom Value 5');
        $this->assertAttributeArraySubSet(
            array(
                'index3' => array(
                    'index' => 3,
                    'name' => 'Custom Var 5',
                    'value' => 'Custom Value 5',
                    'scope' => 3,
                ),
            ),
            'customVariables',
            $gass
        );
    }

    /**
     * @dataProvider dataProviderTestSetCustomVarExceptionInvalidIndex
     */
    public function testSetCustomVarExceptionInvalidIndex($index)
    {
        list($gass) = $this->getGassAndDependencies();
        $this->setExpectedException(
            'Gass\Exception\OutOfBoundsException',
            'The index must be an integer between 1 and 5.'
        );
        $gass->setCustomVar('Custom Var 1', 'Custom Value 1', 3, $index);
    }

    public function dataProviderTestSetCustomVarExceptionInvalidIndex()
    {
        return array(
            array(6),
            array(0),
        );
    }

    /**
     * @dataProvider dataProviderTestSetCustomVarExceptionInvalidScope
     */
    public function testSetCustomVarExceptionInvalidScope($scope)
    {
        list($gass) = $this->getGassAndDependencies();
        $this->setExpectedException(
            'Gass\Exception\InvalidArgumentException',
            'The Scope must be a value between 1 and 3'
        );
        $gass->setCustomVar('Custom Var 1', 'Custom Value 1', $scope, 1);
    }

    public function dataProviderTestSetCustomVarExceptionInvalidScope()
    {
        return array(
            array(4),
            array(0),
        );
    }

    /**
     * @dataProvider dataProviderStringCastWrongDataType
     */
    public function testSetCustomVarExceptionNameWrongDataType($name)
    {
        list($gass) = $this->getGassAndDependencies();
        $this->setExpectedException('Gass\Exception\InvalidArgumentException', 'Custom Var Name must be a string.');
        $gass->setCustomVar($name, 'Custom Value 1');
    }

    /**
     * @dataProvider dataProviderStringCastWrongDataType
     */
    public function testSetCustomVarExceptionValueWrongDataType($value)
    {
        list($gass) = $this->getGassAndDependencies();
        $this->setExpectedException('Gass\Exception\InvalidArgumentException', 'Custom Var Value must be a string.');
        $gass->setCustomVar('Custom Var 1', $value);
    }

    public function testSetCustomVarExceptionExceedsByteVarLimit()
    {
        list($gass) = $this->getGassAndDependencies();
        $this->setExpectedException(
            'Gass\Exception\DomainException',
            'The name / value combination exceeds the 128 byte custom var limit.'
        );
        $gass->setCustomVar(
            'abcdefghijklmnopqrstuvwxyz1234567890abcdefghijklmnopqrstuvwxyz12',
            'abcdefghijklmnopqrstuvwxyz1234567890abcdefghijklmnopqrstuvwxyz123'
        );
    }

    public function testSetCustomVarExceptionExceededVarCountLimit()
    {
        list($gass) = $this->getGassAndDependencies();
        $this->setExpectedException(
            'Gass\Exception\OutOfBoundsException',
            'You cannot add more than 5 custom variables.'
        );
        $gass->setCustomVar('Custom Var 1', 'Custom Value 1');
        $gass->setCustomVar('Custom Var 2', 'Custom Value 2');
        $gass->setCustomVar('Custom Var 3', 'Custom Value 3');
        $gass->setCustomVar('Custom Var 4', 'Custom Value 4');
        $gass->setCustomVar('Custom Var 5', 'Custom Value 5');
        $gass->setCustomVar('Custom Var 6', 'Custom Value 6');
    }

    /**
     * @dataProvider dataProviderCustomVariables
     * @depends testSetCustomVarValidBasic
     */
    public function testGetCustomVariables($variables)
    {
        list($gass) = $this->getGassAndDependencies();
        foreach ($variables as $index => $data) {
            $gass->setCustomVar(
                $data['name'],
                $data['value'],
                $data['scope'],
                $data['index']
            );
        }
        $this->assertEquals($variables, $gass->getCustomVariables());
    }

    public function dataProviderCustomVariables()
    {
        return array(
            array(
                array(
                    'index1' => array(
                        'index' => 1,
                        'name' => 'Custom Var 1',
                        'value' => 'Custom Value 1',
                        'scope' => 2,
                    ),
                    'index2' => array(
                        'index' => 2,
                        'name' => 'Custom Var 2',
                        'value' => 'Custom Value 2',
                        'scope' => 1,
                    ),
                    'index3' => array(
                        'index' => 3,
                        'name' => 'Custom Var 3',
                        'value' => 'Custom Value 3',
                        'scope' => 2,
                    ),
                    'index4' => array(
                        'index' => 4,
                        'name' => 'Custom Var 4',
                        'value' => 'Custom Value 4',
                        'scope' => 3,
                    ),
                    'index5' => array(
                        'index' => 5,
                        'name' => 'Custom Var 5',
                        'value' => 'Custom Value 5',
                        'scope' => 1,
                    ),
                ),
            ),
        );
    }

    /**
     * @depends testSetCustomVarValidBasic
     */
    public function testGetVisitorCustomVarValid()
    {
        list($gass) = $this->getGassAndDependencies();
        $customVar = array(
            'index' => 5,
            'name' => 'Custom Var 5',
            'value' => 'Custom Value 5',
            'scope' => 2,
        );
        $gass->setCustomVar($customVar['name'], $customVar['value'], $customVar['scope'], $customVar['index']);
        $this->assertEquals($customVar['value'], $gass->getVisitorCustomVar($customVar['index']));
    }

    public function testGetVisitorCustomVarExceptionInvalidIndex()
    {
        list($gass) = $this->getGassAndDependencies();
        $this->setExpectedException(
            'Gass\Exception\OutOfBoundsException',
            'The index: "4" has not been set.'
        );
        $gass->getVisitorCustomVar(4);
    }

    /**
     * @dataProvider dataProviderCustomVariables
     * @depends testSetCustomVarValidBasic
     */
    public function testGetCustomVarsByScopeWithoutArg($variables)
    {
        list($gass) = $this->getGassAndDependencies();
        $expected = array();
        foreach ($variables as $index => $data) {
            $gass->setCustomVar(
                $data['name'],
                $data['value'],
                $data['scope'],
                $data['index']
            );
            if (3 === $data['scope']) {
                $expected[] = implode('=', $data);
            }
        }
        $this->assertEquals($expected, $gass->getCustomVarsByScope());
    }

    /**
     * @dataProvider dataProviderTestGetCustomVarsByScopeWithArg
     * @depends testSetCustomVarValidBasic
     */
    public function testGetCustomVarsByScopeWithArg($variables, $scope)
    {
        list($gass) = $this->getGassAndDependencies();
        $expected = array();
        foreach ($variables as $index => $data) {
            $gass->setCustomVar(
                $data['name'],
                $data['value'],
                $data['scope'],
                $data['index']
            );
            if ($scope === $data['scope']) {
                $expected[] = implode('=', $data);
            }
        }
        $this->assertEquals($expected, $gass->getCustomVarsByScope($scope));
    }

    public function dataProviderTestGetCustomVarsByScopeWithArg()
    {
        $data = $this->dataProviderCustomVariables();
        $retVal = array();
        for ($i = 1; $i <= 3; $i++) {
            $level = $data[0];
            $level[] = $i;
            $retVal[] = $level;
        }
        return $retVal;
    }

    public function testDeleteCustomVarValid()
    {
        list($gass) = $this->getGassAndDependencies();
        $gass->setCustomVar('Custom Var 1', 'Custom Value 1');
        $gass->setCustomVar('Custom Var 2', 'Custom Value 2');
        $gass->setCustomVar('Custom Var 3', 'Custom Value 3');
        $this->assertSame($gass, $gass->deleteCustomVar(2));
        $this->assertAttributeArrayNotHasKey('index2', 'customVariables', $gass);
    }

    /**
     * @dataProvider dataProviderStringCastWrongDataType
     */
    public function testDeleteCustomVarExceptionWrongDataType($index)
    {
        list($gass) = $this->getGassAndDependencies();
        $this->setExpectedException('Gass\Exception\InvalidArgumentException', 'Custom Var Index must be a string.');
        $gass->deleteCustomVar($index);
    }

    /**
     * @dataProvider dataProviderTestSetCharsetValid
     */
    public function testSetCharsetValid($charset, $expectedClassValue)
    {
        list($gass) = $this->getGassAndDependencies();
        $this->assertSame($gass, $gass->setCharset($charset));
        $this->assertAttributeEquals($expectedClassValue, 'charset', $gass);
    }

    public function dataProviderTestSetCharsetValid()
    {
        return array(
            array('FOO', 'FOO'),
            array('bar', 'BAR'),
        );
    }

    /**
     * @dataProvider dataProviderStringCastWrongDataType
     */
    public function testSetCharsetExceptionWrongDataType($charset)
    {
        list($gass) = $this->getGassAndDependencies();
        $this->setExpectedException('Gass\Exception\InvalidArgumentException', 'Charset must be a string.');
        $gass->setCharset($charset);
    }

    public function testSetSearchEnginesValid()
    {
        list($gass) = $this->getGassAndDependencies();
        $searchEngines = array(
            'testa' => array('a'),
            'testb' => array('a', 'bcd'),
        );
        $this->assertSame($gass, $gass->setSearchEngines($searchEngines));
        $this->assertAttributeEquals($searchEngines, 'searchEngines', $gass);
    }

    /**
     * @dataProvider dataProviderAllDataTypesButArray
     */
    public function testSetSearchEnginesExceptionWrongDataType($searchEngines)
    {
        list($gass) = $this->getGassAndDependencies();
        $this->setExpectedException(
            (class_exists('TypeError')) ? 'TypeError' : 'PHPUnit_Framework_Error',
            'Argument 1 passed to Gass\GoogleAnalyticsServerSide::setSearchEngines() must be'
        );
        $gass->setSearchEngines($searchEngines);
    }

    public function testSetSearchEnginesExceptionWrongQueryParamsDataType()
    {
        list($gass) = $this->getGassAndDependencies();
        $this->setExpectedException('Gass\Exception\DomainException', 'searchEngines entry testb invalid');
        $gass->setSearchEngines(
            array(
                'testa' => array('a'),
                'testb' => new \stdClass,
            )
        );
    }

    public function testSetSearchEnginesExceptionWrongQueryParamsCount()
    {
        list($gass) = $this->getGassAndDependencies();
        $this->setExpectedException('Gass\Exception\DomainException', 'searchEngines entry testa invalid');
        $gass->setSearchEngines(
            array(
                'testa' => array(),
                'testb' => array('b'),
            )
        );
    }

    /**
     * @dataProvider dataProviderTestSetSearchEnginesExceptionNameInvalid
     */
    public function testSetSearchEnginesExceptionNameInvalid($name)
    {
        list($gass) = $this->getGassAndDependencies();
        $this->setExpectedException(
            'Gass\Exception\DomainException',
            'search engine name "' . $name . '" is invalid'
        );
        $gass->setSearchEngines(
            array(
                $name => array('a'),
                'testb' => array('b'),
            )
        );
    }

    public function dataProviderTestSetSearchEnginesExceptionNameInvalid()
    {
        return array(
            array(1),
            array('test#'),
        );
    }

    /**
     * @dataProvider dataProviderTestSetSearchEnginesExceptionQueryParameterInvalid
     */
    public function testSetSearchEnginesExceptionQueryParameterInvalid($queryParameter)
    {
        list($gass) = $this->getGassAndDependencies();
        $arrayOrObject = (is_array($queryParameter) || is_object($queryParameter));
        $this->setExpectedException(
            'Gass\Exception\\' . ($arrayOrObject ? 'InvalidArgument' : 'Domain') . 'Exception',
            $arrayOrObject
                ? 'Search engine query parameter must be a string'
                : 'Search engine query parameter "' . $queryParameter . '" is invalid'
        );
        $gass->setSearchEngines(
            array(
                'testa' => array($queryParameter),
                'testb' => array('b'),
            )
        );
    }

    public function dataProviderTestSetSearchEnginesExceptionQueryParameterInvalid()
    {
        $data = $this->dataProviderStringCastWrongDataType();
        $data[] = array(1);
        $data[] = array('a&');
        return $data;
    }

    /**
     * @depends testSetSearchEnginesValid
     */
    public function testGetSearchEnginesValidBasic()
    {
        list($gass) = $this->getGassAndDependencies();
        $searchEngines = array(
            'testa' => array('a'),
            'testb' => array('a', 'bcd'),
        );
        $gass->setSearchEngines($searchEngines);
        $this->assertEquals($searchEngines, $gass->getSearchEngines());
    }

    /**
     * @depends testSetSearchEnginesValid
     */
    public function testGetSearchEnginesValidSetsFromJsIfSetNotAlreadyCalled()
    {
        list(
            $gass,
            $http
        ) = $this->getGassAndDependencies();
        $this->expectJsUrlCall($http);

        $rp = new \ReflectionProperty(get_class($gass), 'searchEngines');
        $rp->setAccessible(true);
        $defaultSearchEngines = $rp->getValue($gass);
        $rp->setValue($gass, array());

        $this->assertAttributeEmpty('searchEngines', $gass);
        $this->assertEquals($defaultSearchEngines, $gass->getSearchEngines());
    }

    public function testSetBotInfoValidWithAdapterClass()
    {
        list(
            $gass,
            ,
            $botInfo
        ) = $this->getGassAndDependencies();

        $envUserAgent = $this->getEnvVar('HTTP_USER_AGENT');
        $envRemoteAddress = $this->getEnvVar('REMOTE_ADDR');

        $botInfoAdapter = m::mock('Gass\BotInfo\BotInfoInterface');

        $expectedBotInfo = new BotInfo(array(), $botInfoAdapter);
        $expectedBotInfo->setUserAgent($envUserAgent);
        $expectedBotInfo->setRemoteAddress($envRemoteAddress);

        $this->assertSame($gass, $gass->setBotInfo($botInfoAdapter));
        $this->assertAttributeEquals($expectedBotInfo, 'botInfo', $gass);
    }

    public function testSetBotInfoValidWithArrayOptions()
    {
        list(
            $gass,
            ,
            $botInfo
        ) = $this->getGassAndDependencies();

        $botInfoOptions = array(
            'foo' => 'bar',
            'baz' => 'qux',
        );

        $envUserAgent = $this->getEnvVar('HTTP_USER_AGENT');
        $envRemoteAddress = $this->getEnvVar('REMOTE_ADDR');

        $expectedBotInfo = new BotInfo($botInfoOptions);
        $expectedBotInfo->setUserAgent($envUserAgent);
        $expectedBotInfo->setRemoteAddress($envRemoteAddress);

        $this->assertSame($gass, $gass->setBotInfo($botInfoOptions));
        $this->assertAttributeEquals($expectedBotInfo, 'botInfo', $gass);
    }

    public function testSetBotInfoValidWithBoolean()
    {
        list($gass) = $this->getGassAndDependencies();

        $botInfo = true;

        $envUserAgent = $this->getEnvVar('HTTP_USER_AGENT');
        $envRemoteAddress = $this->getEnvVar('REMOTE_ADDR');

        $expectedBotInfo = new BotInfo;
        $expectedBotInfo->setUserAgent($envUserAgent);
        $expectedBotInfo->setRemoteAddress($envRemoteAddress);

        $this->assertSame($gass, $gass->setBotInfo($botInfo));
        $this->assertAttributeEquals($expectedBotInfo, 'botInfo', $gass);
    }

    public function testSetBotInfoValidWithNull()
    {
        list($gass) = $this->getGassAndDependencies();

        $botInfo = null;

        $envUserAgent = $this->getEnvVar('HTTP_USER_AGENT');
        $envRemoteAddress = $this->getEnvVar('REMOTE_ADDR');

        $this->assertSame($gass, $gass->setBotInfo($botInfo));
        $this->assertAttributeEquals(null, 'botInfo', $gass);
    }

    /**
     * @dataProvider dataProviderTestSetBotInfoExceptionInvalidArgument
     */
    public function testSetBotInfoExceptionInvalidArgument($botInfo)
    {
        list($gass) = $this->getGassAndDependencies();
        $this->setExpectedException(
            'Gass\Exception\InvalidArgumentException',
            'botInfo must be an array, true, null or a class which implements Gass\BotInfo\BotInfoInterface.'
        );
        $gass->setBotInfo($botInfo);
    }

    public function dataProviderTestSetBotInfoExceptionInvalidArgument()
    {
        return array(
            array(false),
            array(new \stdClass),
            array(123),
            array(45.67),
            array('foo'),
        );
    }

    /**
     * @depends testSetBotInfoValidWithAdapterClass
     */
    public function testGetBotInfo()
    {
        list(
            $gass,
            ,
            $botInfo
        ) = $this->getGassAndDependencies();

        $envUserAgent = $this->getEnvVar('HTTP_USER_AGENT');
        $envRemoteAddress = $this->getEnvVar('REMOTE_ADDR');

        $botInfoAdapter = m::mock('Gass\BotInfo\BotInfoInterface');

        $expectedBotInfo = new BotInfo(array(), $botInfoAdapter);
        $expectedBotInfo->setUserAgent($envUserAgent);
        $expectedBotInfo->setRemoteAddress($envRemoteAddress);

        $gass->setBotInfo($botInfoAdapter);
        $this->assertEquals($expectedBotInfo, $gass->getBotInfo());
    }

    public function testSetHttpValidWithAdapter()
    {
        $botInfo = m::mock('overload:Gass\BotInfo\BotInfo');

        $envAcceptLanguage = $this->getEnvVar('HTTP_ACCEPT_LANGUAGE');
        $envRemoteAddress = $this->getEnvVar('REMOTE_ADDR');
        $envUserAgent = $this->getEnvVar('HTTP_USER_AGENT');
        $envDocumentReferer = $this->getEnvVar('HTTP_REFERER');

        $httpAdapter = m::mock('Gass\Http\HttpInterface');

        $http = m::mock('overload:Gass\Http\Http');
        $http->shouldReceive('getInstance')
            ->once()
            ->with(array(), $httpAdapter)
            ->andReturnSelf();
        if (!empty($envUserAgent)) {
            $http->shouldReceive('setUserAgent')
                ->once()
                ->with($envUserAgent)
                ->andReturnSelf();
        }

        $ipValidator = m::mock('overload:Gass\Validate\IpAddress', IpAddressInterfaceStub::class);
        if (!empty($envRemoteAddress)) {
            $http->shouldReceive('setRemoteAddress')
                ->once()
                ->with($envRemoteAddress)
                ->andReturnSelf();
            $ipValidator->shouldReceive('isValid')
                ->once()
                ->with($envRemoteAddress)
                ->andReturn(true);
        }
        $langValidator = m::mock('overload:Gass\Validate\LanguageCode');
        if (!empty($envAcceptLanguage)) {
            $http->shouldReceive('setAcceptLanguage')
                ->once()
                ->with($envAcceptLanguage)
                ->andReturnSelf();
            $langValidator->shouldReceive('isValid')
                ->once()
                ->with($envAcceptLanguage)
                ->andReturn(true);
        }

        $urlValidator = m::mock('overload:Gass\Validate\Url');
        if (!empty($envDocumentReferer)) {
            $urlValidator->shouldReceive('isValid')
                ->once()
                ->with($envDocumentReferer)
                ->andReturn(true);
        }

        $gass = new GoogleAnalyticsServerSide;
        $this->assertSame($gass, $gass->setHttp($httpAdapter));
    }

    public function testSetHttpValidWithArray()
    {
        $botInfo = m::mock('overload:Gass\BotInfo\BotInfo');

        $envAcceptLanguage = $this->getEnvVar('HTTP_ACCEPT_LANGUAGE');
        $envRemoteAddress = $this->getEnvVar('REMOTE_ADDR');
        $envUserAgent = $this->getEnvVar('HTTP_USER_AGENT');
        $envDocumentReferer = $this->getEnvVar('HTTP_REFERER');

        $httpOptions = array(
            'foo' => 'bar',
            'baz' => 'qux',
        );

        $http = m::mock('overload:Gass\Http\Http');
        $http->shouldReceive('getInstance')
            ->once()
            ->with($httpOptions)
            ->andReturnSelf();
        if (!empty($envUserAgent)) {
            $http->shouldReceive('setUserAgent')
                ->once()
                ->with($envUserAgent)
                ->andReturnSelf();
        }

        $ipValidator = m::mock('overload:Gass\Validate\IpAddress', IpAddressInterfaceStub::class);
        if (!empty($envRemoteAddress)) {
            $http->shouldReceive('setRemoteAddress')
                ->once()
                ->with($envRemoteAddress)
                ->andReturnSelf();
            $ipValidator->shouldReceive('isValid')
                ->once()
                ->with($envRemoteAddress)
                ->andReturn(true);
        }
        $langValidator = m::mock('overload:Gass\Validate\LanguageCode');
        if (!empty($envAcceptLanguage)) {
            $http->shouldReceive('setAcceptLanguage')
                ->once()
                ->with($envAcceptLanguage)
                ->andReturnSelf();
            $langValidator->shouldReceive('isValid')
                ->once()
                ->with($envAcceptLanguage)
                ->andReturn(true);
        }

        $urlValidator = m::mock('overload:Gass\Validate\Url');
        if (!empty($envDocumentReferer)) {
            $urlValidator->shouldReceive('isValid')
                ->once()
                ->with($envDocumentReferer)
                ->andReturn(true);
        }

        $gass = new GoogleAnalyticsServerSide;
        $this->assertSame($gass, $gass->setHttp($httpOptions));
    }

    public function testSetHttpValidWithNull()
    {
        $botInfo = m::mock('overload:Gass\BotInfo\BotInfo');

        $envAcceptLanguage = $this->getEnvVar('HTTP_ACCEPT_LANGUAGE');
        $envRemoteAddress = $this->getEnvVar('REMOTE_ADDR');
        $envUserAgent = $this->getEnvVar('HTTP_USER_AGENT');
        $envDocumentReferer = $this->getEnvVar('HTTP_REFERER');

        $http = m::mock('overload:Gass\Http\Http');
        $http->shouldNotReceive('getInstance');
        $http->shouldReceive('setUserAgent')
            ->once()
            ->with($envUserAgent)
            ->andReturnSelf();
        $http->shouldReceive('setRemoteAddress')
            ->once()
            ->with($envRemoteAddress)
            ->andReturnSelf();
        $http->shouldReceive('setAcceptLanguage')
            ->once()
            ->with($envAcceptLanguage)
            ->andReturnSelf();

        $ipValidator = m::mock('overload:Gass\Validate\IpAddress', IpAddressInterfaceStub::class);
        if (!empty($envRemoteAddress)) {
            $ipValidator->shouldReceive('isValid')
                ->once()
                ->with($envRemoteAddress)
                ->andReturn(true);
        }
        $langValidator = m::mock('overload:Gass\Validate\LanguageCode');
        if (!empty($envAcceptLanguage)) {
            $langValidator->shouldReceive('isValid')
                ->once()
                ->with($envAcceptLanguage)
                ->andReturn(true);
        }

        $urlValidator = m::mock('overload:Gass\Validate\Url');
        if (!empty($envDocumentReferer)) {
            $urlValidator->shouldReceive('isValid')
                ->once()
                ->with($envDocumentReferer)
                ->andReturn(true);
        }

        $gass = new GoogleAnalyticsServerSide;
        $this->assertSame($gass, $gass->setHttp(null));
    }

    /**
     * @dataProvider dataProviderTestSetHttpExceptionInvalidArgument
     */
    public function testSetHttpExceptionInvalidArgument($http)
    {
        list($gass) = $this->getGassAndDependencies();
        $this->setExpectedException(
            'Gass\Exception\InvalidArgumentException',
            'http must be an array, null or a class which implements Gass\Http\Interface.'
        );
        $gass->setHttp($http);
    }

    public function dataProviderTestSetHttpExceptionInvalidArgument()
    {
        return array(
            array(true),
            array(false),
            array(new \stdClass),
            array(123),
            array(45.67),
            array('foo'),
        );
    }

    public function testGetHttp()
    {
        list(
            $gass,
            $http
        ) = $this->getGassAndDependencies();
        $http->shouldReceive('getInstance')
            ->once()
            ->withNoArgs()
            ->andReturnSelf();
        $this->assertInstanceOf('Gass\Http\Http', $gass->getHttp());
    }

    public function testSetOptionsValid()
    {
        list($gass) = $this->getGassAndDependencies();
        $this->assertSame(
            $gass,
            $gass->setOptions(
                array(
                    'charset' => 'foo',
                    'pageTitle' => 'bar',
                )
            )
        );
    }

    /**
     * @dataProvider dataProviderAllDataTypesButArray
     */
    public function testSetOptionsExceptionWrongDataType($options)
    {
        list($gass) = $this->getGassAndDependencies();
        $this->setExpectedException(
            (class_exists('TypeError')) ? 'TypeError' : 'PHPUnit_Framework_Error',
            'Argument 1 passed to Gass\GoogleAnalyticsServerSide::setOptions() must be'
        );
        $gass->setOptions($options);
    }

    public function testSetOptionValid()
    {
        list($gass) = $this->getGassAndDependencies();
        $this->assertSame($gass, $gass->setOption('serverName', 'baz'));
    }

    public function testSetOptionReturnsThisWhenGetMEthodExistsButSetDeosnt()
    {
        list($gass) = $this->getGassAndDependencies();
        $this->assertSame($gass, $gass->setOption('serverName', 'baz'));
    }

    /**
     * @dataProvider dataProviderTestSetOptionExceptionOutOfRange
     */
    public function testSetOptionExceptionOutOfRangeMissingSetOrGetMethods($optionName, $exceptionMessage)
    {
        $exceptionMessage = $optionName . ' ' . $exceptionMessage;

        list($gass) = $this->getGassAndDependencies();

        $this->setExpectedException('Gass\Exception\OutOfRangeException', $exceptionMessage);
        $gass->setOption($optionName, 'Value');
    }

    public function dataProviderTestSetOptionExceptionOutOfRange()
    {
        return array(
            array('cookie', 'is not an available option.'),
            array('foo', 'is not an available option.'),
            array('cookiesString', 'is not a writable option.'),
            array('domainHash', 'is not a writable option.'),
        );
    }

    public function testSetOptionExceptionOutOfRangeHasGetButSetMethodNotPubliclyAccessible()
    {
        $optionName = 'currentJsFile';

        list(
            $gass,
            $http
        ) = $this->getGassAndDependencies();

        $http->shouldReceive('request')
            ->once()
            ->with(GoogleAnalyticsServerSide::JS_URL)
            ->andReturnSelf();
        $http->shouldReceive('getResponse')
            ->once()
            ->withNoArgs()
            ->andReturn($this->jsFileContents);

        $this->setExpectedException('Gass\Exception\OutOfRangeException', $optionName . ' is not a writable option.');
        $gass->setOption($optionName, 'Value');
    }

    /**
     * @depends testSetServerNameValid
     */
    public function testGetOptionValid()
    {
        list($gass) = $this->getGassAndDependencies();
        $value = 'foo';
        $gass->setServerName($value);
        $this->assertEquals($value, $gass->getOption('serverName'));
    }

    /**
     * @dataProvider dataProviderTestGetOptionExceptionOutOfBounds
     */
    public function testGetOptionExceptionOutOfBounds($name)
    {
        list($gass) = $this->getGassAndDependencies();
        $this->setExpectedException(
            'Gass\Exception\OutOfRangeException',
            $name . ' is not an available option.'
        );
        $gass->getOption($name);
    }

    public function dataProviderTestGetOptionExceptionOutOfBounds()
    {
        return array(
            array('Test'), // No Available Method
            array('cookie'), // Method not Publicly Accessibile
        );
    }

    /**
     * @dataProvider dataProviderTestGetEventStringValidIndividualParams
     */
    public function testGetEventStringValidIndividualParams(
        $expectedString,
        $category,
        $action,
        $label = null,
        $value = null
    ) {
        list($gass) = $this->getGassAndDependencies();
        $this->assertEquals($expectedString, $gass->getEventString($category, $action, $label, $value));
    }

    public function dataProviderTestGetEventStringValidIndividualParams()
    {
        $category = 'Test Category';
        $action = 'Test Action';
        $label = 'Test Label';
        $value = 1;

        return array(
            array('5(' . $category . '*' . $action . ')', $category, $action),
            array('5(' . $category . '*' . $action . '*' . $label . ')', $category, $action, $label),
            array(
                '5(' . $category . '*' . $action . '*' . $label . ')(' . $value . ')',
                $category,
                $action,
                $label,
                $value,
            ),
        );
    }

    /**
     * @dataProvider dataProviderTestGetEventStringValidBackwardsCompatibilityArrayAsParam
     */
    public function testGetEventStringValidBackwardsCompatibilityArrayAsParam($expectedString, array $eventData)
    {
        list($gass) = $this->getGassAndDependencies();
        $this->assertEquals($expectedString, $gass->getEventString($eventData));
    }

    public function dataProviderTestGetEventStringValidBackwardsCompatibilityArrayAsParam()
    {
        $category = 'Test Category';
        $action = 'Test Action';
        $label = 'Test Label';
        $value = 1;

        return array(
            array(
                '5(' . $category . '*' . $action . ')',
                array('category' => $category, 'action' => $action),
            ),
            array(
                '5(' . $category . '*' . $action . '*' . $label . ')',
                array('category' => $category, 'action' => $action, 'label' => $label),
            ),
            array(
                '5(' . $category . '*' . $action . '*' . $label . ')(' . $value . ')',
                array('category' => $category, 'action' => $action, 'label' => $label, 'value' => $value),
            ),
        );
    }

    public function testGetEventStringExceptionCategoryWrongDataType()
    {
        list($gass) = $this->getGassAndDependencies();
        $this->setExpectedException('Gass\Exception\InvalidArgumentException', 'Event Category must be a string.');
        $gass->getEventString(new \stdClass, 'Value');
    }

    /**
     * @dataProvider dataProviderStringCastWrongDataType
     */
    public function testGetEventStringExceptionActionWrongDataType($action)
    {
        list($gass) = $this->getGassAndDependencies();
        $this->setExpectedException('Gass\Exception\InvalidArgumentException', 'Event Action must be a string.');
        $gass->getEventString('Category', $action);
    }

    /**
     * @dataProvider dataProviderTestGetEventStringExceptionEmptyCategory
     */
    public function testGetEventStringExceptionEmptyCategory($category)
    {
        list($gass) = $this->getGassAndDependencies();
        $this->setExpectedException(
            'Gass\Exception\InvalidArgumentException',
            'An event requires at least a category and action'
        );
        $gass->getEventString($category, 'Value');
    }

    public function dataProviderTestGetEventStringExceptionEmptyCategory()
    {
        return array(
            array(''),
            array(array('FooBarBazQux')),
        );
    }

    public function testGetEventStringExceptionEmptyAction()
    {
        list($gass) = $this->getGassAndDependencies();
        $this->setExpectedException(
            'Gass\Exception\InvalidArgumentException',
            'An event requires at least a category and action'
        );
        $gass->getEventString('Category', '');
    }

    /**
     * @dataProvider dataProviderStringCastWrongDataType
     */
    public function testGetEventStringExceptionLabelWrongDataType($label)
    {
        list($gass) = $this->getGassAndDependencies();
        $this->setExpectedException('Gass\Exception\InvalidArgumentException', 'Event Label must be a string.');
        $gass->getEventString('Category', 'Action', $label);
    }

    /**
     * @dataProvider dataProviderTestGetEventStringExceptionValueWrongDataType
     */
    public function testGetEventStringExceptionValueWrongDataType($value)
    {
        list($gass) = $this->getGassAndDependencies();
        $this->setExpectedException('Gass\Exception\InvalidArgumentException', 'Value must be an integer.');
        $gass->getEventString('Category', 'Action', 'Label', $value);
    }

    public function dataProviderTestGetEventStringExceptionValueWrongDataType()
    {
        return array(
            array(new \stdClass),
            array(array()),
            array('foo'),
            array(123.456),
            array(true),
        );
    }

    public function testGetCustomVariableStringValid()
    {
        list($gass) = $this->getGassAndDependencies();
        $this->assertNull($gass->getCustomVariableString());
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
        $gass->setCustomVar($customVar1['name'], $customVar1['value']);
        $gass->setCustomVar(
            $customVar2['name'],
            $customVar2['value'],
            $customVar2['scope'],
            $customVar2['index']
        );
        $this->assertEquals(
            '8(' . $customVar1['name'] . '*' . $customVar2['name'] . ')' .
            '9(' . $customVar1['value'] . '*' . $customVar2['value'] . ')' .
            '11(5!' . $customVar2['scope'] . ')',
            $gass->getCustomVariableString()
        );
    }

    /**
     * @dataProvider dataProviderTestGetIPToReportValid
     */
    public function testGetIPToReportValidWithParam($remoteAddress, $expectedIpToReport)
    {
        $setRemoteAddressCalls = 0;
        $envRemoteAddress = $this->getEnvVar('REMOTE_ADDR');

        /*
         * A little bit hackery due to the class being instantiated each call to setRemoteAddress
         * including via the constructor with the REMOTE_ADDR header value in phpunit.xml.dist
         */
        $ipValidator = m::mock('overload:Gass\Validate\IpAddress', IpAddressInterfaceStub::class);
        $ipValidator->shouldReceive('isValid')
            ->once(3)
            ->with(m::anyOf($envRemoteAddress, $remoteAddress))
            ->andReturnUsing(
                function ($ipAddress) use (&$setRemoteAddressCalls, $envRemoteAddress, $remoteAddress) {
                    if ((1 === ++$setRemoteAddressCalls && $envRemoteAddress === $ipAddress)
                        || ((2 === $setRemoteAddressCalls || 3 === $setRemoteAddressCalls) && $remoteAddress === $ipAddress)
                    ) {
                        return true;
                    }
                    throw new \BadMethodCallException(
                        'Unexpected call to Gass\Validate\IpAddress::isValid on iteration ' . $setRemoteAddressCalls . ' called with "' . $ipAddress . '"'
                    );
                }
            );

        list(
            $gass,
            $http,
            $botInfo
        ) = $this->getGassAndDependencies(false, false);

        $http->shouldReceive('setRemoteAddress')
            ->once()
            ->with($remoteAddress)
            ->andReturnSelf();

        $this->assertEquals($expectedIpToReport, $gass->getIPToReport($remoteAddress));
        $this->assertAttributeEquals($remoteAddress, 'remoteAddress', $gass);
    }

    /**
     * @dataProvider dataProviderTestGetIPToReportValid
     */
    public function testGetIPToReportValidWithoutParam($remoteAddress, $expectedIpToReport)
    {
        $setRemoteAddressCalls = 0;
        $envRemoteAddress = $this->getEnvVar('REMOTE_ADDR');

        /*
         * A little bit hackery due to the class being instantiated each call to setRemoteAddress
         * including via the constructor with the REMOTE_ADDR header value in phpunit.xml.dist
         */
        $ipValidator = m::mock('overload:Gass\Validate\IpAddress', IpAddressInterfaceStub::class);
        $ipValidator->shouldReceive('isValid')
            ->once()
            ->with(m::anyOf($envRemoteAddress, $remoteAddress))
            ->andReturnUsing(
                function ($ipAddress) use (&$setRemoteAddressCalls, $envRemoteAddress, $remoteAddress) {
                    if ((1 === ++$setRemoteAddressCalls && $envRemoteAddress === $ipAddress)
                        || ((2 === $setRemoteAddressCalls || 3 === $setRemoteAddressCalls) && $remoteAddress === $ipAddress)
                    ) {
                        return true;
                    }
                    throw new \BadMethodCallException(
                        'Unexpected call to Gass\Validate\IpAddress::isValid on iteration ' . $setRemoteAddressCalls . ' called with "' . $ipAddress . '"'
                    );
                }
            );

        list(
            $gass,
            $http,
            $botInfo
        ) = $this->getGassAndDependencies(false, false);

        $http->shouldReceive('setRemoteAddress')
            ->once()
            ->with($remoteAddress)
            ->andReturnSelf();

        $gass->setRemoteAddress($remoteAddress);
        $this->assertEquals($expectedIpToReport, $gass->getIPToReport());
    }

    public function dataProviderTestGetIPToReportValid()
    {
        return array(
            array('8.8.4.4', '8.8.4.0'),
            array('123.123.123.123', '123.123.123.0'),
            array('192.168.0.254', '192.168.0.0'),
        );
    }

    public function testGetIpToReportValidEmptyWhenNoCurrentlySetRemoteAddress()
    {
        unset($_SERVER['REMOTE_ADDR']);

        $ipValidator = m::mock('overload:Gass\Validate\IpAddress', IpAddressInterfaceStub::class);

        list(
            $gass,
            $http,
            $botInfo
        ) = $this->getGassAndDependencies(false, false);

        $this->assertEquals('', $gass->getIPToReport());
    }

    /**
     * @dataProvider dataProviderTestGetDomainHashValid
     */
    public function testGetDomainHashValidWithParam($domainHash, $domain)
    {
        list($gass) = $this->getGassAndDependencies();
        $this->assertEquals($domainHash, $gass->getDomainHash($domain));
    }

    /**
     * @dataProvider dataProviderTestGetDomainHashValid
     */
    public function testGetDomainHashValidWithoutParam($domainHash, $domain)
    {
        list($gass) = $this->getGassAndDependencies();
        $gass->setServerName($domain);
        $this->assertEquals($domainHash, $gass->getDomainHash());
    }

    public function dataProviderTestGetDomainHashValid()
    {
        return array(
            array(32728376, 'www.test.co.uk'),
            array(217344784, 'www.example.com'),
            array(19229758, 'www.unknown.net'),
        );
    }

    /**
     * @depends testGetDomainHashValidWithParam
     * @depends testSetServerNameValid
     */
    public function testSetCookiesValid()
    {
        list($gass) = $this->getGassAndDependencies();

        $gass->setServerName('www.example.com');
        $gass->disableCookieHeaders();
        $this->assertSame($gass, $gass->setCookies());

        $rp = new \ReflectionProperty(get_class($gass), 'cookies');
        $rp->setAccessible(true);
        $currentCookies = $rp->getValue($gass);

        $domainHash = $gass->getDomainHash();

        $this->assertArrayHasKey('__utma', $currentCookies);
        $utma = $currentCookies['__utma'];
        $this->assertNotEmpty($utma);
        $utmaParts = explode('.', $utma, 6);
        $this->assertCount(6, $utmaParts);
        foreach ($utmaParts as $utmaPart) {
            $this->assertNotEmpty($utmaPart);
            $this->assertInternalType(\PHPUnit_Framework_Constraint_IsType::TYPE_NUMERIC, $utmaPart);
        }
        $this->assertEquals($domainHash, $utmaParts[0]);

        $this->assertArrayHasKey('__utmb', $currentCookies);
        $utmb = $currentCookies['__utmb'];
        $this->assertNotEmpty($utmb);
        $utmbParts = explode('.', $utmb, 4);
        $this->assertCount(4, $utmbParts);
        foreach ($utmbParts as $utmbPart) {
            $this->assertNotEmpty($utmbPart);
            $this->assertInternalType(\PHPUnit_Framework_Constraint_IsType::TYPE_NUMERIC, $utmbPart);
        }
        $this->assertEquals($domainHash, $utmbParts[0]);

        $this->assertArrayHasKey('__utmc', $currentCookies);
        $utmc = $currentCookies['__utmc'];
        $this->assertNotEmpty($utmc);
        $this->assertInternalType(\PHPUnit_Framework_Constraint_IsType::TYPE_NUMERIC, $utmc);
        $this->assertEquals($domainHash, $utmc);

        $this->assertArrayHasKey('__utmv', $currentCookies);
        $this->assertAttributeEmpty('customVariables', $gass);
        $this->assertEmpty($currentCookies['__utmv']);

        $this->assertArrayHasKey('__utmz', $currentCookies);
        $utmz = $currentCookies['__utmz'];
        $this->assertNotEmpty($utmz);
        $utmzParts = explode('.', $utmz, 5);
        $this->assertCount(5, $utmzParts);
        foreach ($utmzParts as $key => $utmzPart) {
            $this->assertNotEmpty($utmzPart);
            if ($key < 4) {
                $this->assertInternalType(\PHPUnit_Framework_Constraint_IsType::TYPE_NUMERIC, $utmzPart);
            }
        }
        $this->assertEquals($domainHash, $utmzParts[0]);
        $this->assertInternalType(\PHPUnit_Framework_Constraint_IsType::TYPE_STRING, $utmzPart[5]);
        $this->assertContains('utmcsr=', $utmzParts[4]);
        $this->assertContains('utmccn=', $utmzParts[4]);
        $this->assertContains('utmcmd=', $utmzParts[4]);
        if (strpos($utmzParts[4], 'utmcmd=referral')) {
            $this->assertContains('utmcct=', $utmzParts[4]);
        } elseif (strpos($utmzParts[4], 'utmcmd=organic')) {
            $this->assertContains('utmctr=', $utmzParts[4]);
        }

        $this->assertEquals($utmaParts[2], $utmzParts[1]);
        $this->assertEquals($utmaParts[4], $utmbParts[3]);
        $this->assertEquals($utmaParts[5], $utmbParts[2]);
        $this->assertEquals($utmaParts[5], $utmzParts[2]);
    }

    public function testSetCookiesDisablesFutureSettingOfCookieResponseHeaders()
    {
        list($gass) = $this->getGassAndDependencies();
        $gass->setCookies();
        $this->assertAttributeEquals(false, 'sendCookieHeaders', $gass);
    }

    /**
     * @depends testSetCookiesValid
     */
    public function testSetCookiesCallsSetCookiesFromRequestHeadersCorrectly()
    {
        list($gass) = $this->getGassAndDependencies();
        $gass->disableCookieHeaders();
        $this->assertAttributeEquals(false, 'setCookiesCalled', $gass);
        $gass->setCookies();
        $this->assertAttributeEquals(true, 'setCookiesCalled', $gass);
        $this->assertAttributeArraySubset(
            array(
                '__utmc' => !empty($_COOKIE['__utmc']) ? $_COOKIE['__utmc'] : null,
                '__utmv' => !empty($_COOKIE['__utmv']) ? $_COOKIE['__utmv'] : null,
                '__utmz' => !empty($_COOKIE['__utmz']) ? $_COOKIE['__utmz'] : null,
            ),
            'cookies',
            $gass
        );
    }

    /**
     * @dataProvider dataProviderTestSetCustomVarsFromCookieValid
     * @depends testSetCustomVarValidBasic
     * @depends testSetCookiesValid
     */
    public function testSetCustomVarsFromCookieValid($cookieValueString, array $expectedCustomVars = array())
    {
        list($gass) = $this->getGassAndDependencies();
        $cookies = array(
            '__utmv' => $gass->getDomainHash() . '.|' . $cookieValueString,
        );
        $gass->disableCookieHeaders();
        $gass->setCookies($cookies);
        $this->assertAttributeEquals($expectedCustomVars, 'customVariables', $gass);
    }

    public function dataProviderTestSetCustomVarsFromCookieValid()
    {
        return array(
            array(
                '1=foo=bar=3',
                array(
                    'index1' => array(
                        'index' => 1,
                        'name' => 'foo',
                        'value' => 'bar',
                        'scope' => 3,
                    ),
                ),
            ),
            array(
                '3=foo=bar=2^5=baz=qux=1',
                array(
                    'index3' => array(
                        'index' => 3,
                        'name' => 'foo',
                        'value' => 'bar',
                        'scope' => 2,
                    ),
                    'index5' => array(
                        'index' => 5,
                        'name' => 'baz',
                        'value' => 'qux',
                        'scope' => 1,
                    ),
                ),
            ),
        );
    }

    /**
     * @depends testSetCookiesValid
     */
    public function testSetCookiesIncreasesSessionWhenNoUtmbAndUtmzCookies()
    {
        list($gass) = $this->getGassAndDependencies();
        $gass->disableCookieHeaders();
        $cookies = array(
            '__utma' => '217344784.1277951898.1353238970.1359405715.1363640418.6',
            '__utmc' => '217344784',
        );

        $gass->setCookies($cookies);
        $rp = new \ReflectionProperty(get_class($gass), 'cookies');
        $rp->setAccessible(true);
        $actual = $rp->getValue($gass);
        $this->assertArrayHasKey('__utma', $actual);
        $this->assertRegExp('/^217344784\.1277951898\.1353238970\.1363640418\.[0-9]{10,}\.7$/', $actual['__utma']);
        $this->assertArrayHasKey('__utmb', $actual);
        $this->assertRegExp('/^217344784\.1\.7\.[0-9]{10,}$/', $actual['__utmb']);
        $this->assertArrayHasKey('__utmc', $actual);
        $this->assertEquals('217344784', $actual['__utmc']);
        $this->assertArrayHasKey('__utmv', $actual);
        $this->assertEquals(null, $actual['__utmv']);
        $this->assertArrayHasKey('__utmz', $actual);
        $this->assertEquals(
            '217344784.1353238970.7.1.utmcsr=google|utmccn=(organic)|utmcmd=organic|utmctr=example.com',
            $actual['__utmz']
        );
    }

    /**
     * @dataProvider dataProviderReferers
     * @depends testSetCookiesValid
     */
    public function testSetCookiesDifferentReferers($referer, $expectedUtmzSuffixRegExp)
    {
        list($gass) = $this->getGassAndDependencies();
        $gass->setDocumentReferer($referer);
        $gass->disableCookieHeaders();

        $domainHash = $gass->getDomainHash();
        $gass->setCookies(array('__utmc' => $domainHash));

        $rp = new \ReflectionProperty(get_class($gass), 'cookies');
        $rp->setAccessible(true);
        $actual = $rp->getValue($gass);

        $this->assertArrayHasKey('__utmz', $actual);
        $this->assertRegExp(
            '/^' . $domainHash . '\.[0-9]{10,}\.1\.1\.' . $expectedUtmzSuffixRegExp . '$/',
            $actual['__utmz']
        );
    }

    /**
     * @depends testSetCookiesValid
     */
    public function testSetCookiesIncreasesCampaignNumberWhenRefererChanges()
    {
        $referers = $this->dataProviderReferers();

        list($gass) = $this->getGassAndDependencies();
        $gass->disableCookieHeaders();

        $domainHash = $gass->getDomainHash();

        $rp = new \ReflectionProperty(get_class($gass), 'cookies');
        $rp->setAccessible(true);

        $cookies = array('__utmc' => $domainHash);
        foreach ($referers as $key => $refererDetails) {
            $gass->setDocumentReferer($refererDetails['referer']);
            $gass->setCookies($cookies);

            $actual = $rp->getValue($gass);

            $this->assertArrayHasKey('__utmz', $actual);
            $expectedCampaignNumber = $key + 1;
            $this->assertRegExp(
                '/^' .
                    $domainHash .
                    '\.[0-9]{10,}\.1\.' .
                    $expectedCampaignNumber .
                    '\.' .
                    $refererDetails['suffixRegExp'] .
                    '$/',
                $actual['__utmz']
            );
            $cookies = array_merge($cookies, $actual);
        }
    }

    public function dataProviderReferers()
    {
        return array(
            array(
                'referer' => '',
                'suffixRegExp' => 'utmcsr=\(direct\)|utmccn=\(direct\)|utmcmd=\(none\)',
            ),
            array(
                'referer' => 'https://www.google.co.uk/search?q=example.com',
                'suffixRegExp' => 'utmcsr=google|utmccn=\(organic\)|utmcmd=organic|utmctr=example\.com',
            ),
            array(
                'referer' => 'http://www.test.com/path/to/page',
                'suffixRegExp' => 'utmcsr=www\.test\.com|utmccn=\(referral\)|utmcmd=referral|utmcct=\/path\/to\/page',
            ),
        );
    }

    public function testSetCookiesExceptionInvalidCookieName()
    {
        list($gass) = $this->getGassAndDependencies();
        $cookies = array(
            'foo' => 'bar',
        );
        $gass->disableCookieHeaders();
        $this->setExpectedException(
            'Gass\Exception\OutOfBoundsException',
            'Cookie by name: foo is not related to Google Analytics.'
        );
        $gass->setCookies($cookies);
    }

    public function testGetCookiesValidSetCookiesNotCalled()
    {
        list($gass) = $this->getGassAndDependencies();
        $this->assertEquals(
            array('__utma' => null, '__utmb' => null, '__utmc' => null, '__utmv' => null, '__utmz' => null),
            $gass->getCookies()
        );
    }

    /**
     * @depends testSetCookiesValid
     */
    public function testGetCookiesValidSetCookiesCalled()
    {
        list($gass) = $this->getGassAndDependencies();

        $gass->setServerName('www.example.com');
        $gass->disableCookieHeaders();
        $gass->setCookies();

        $domainHash = (string) $gass->getDomainHash();

        $expectedCookieNames = array('__utma', '__utmb', '__utmc', '__utmv', '__utmz');
        $cookies = $gass->getCookies();
        foreach ($expectedCookieNames as $cookieName) {
            $this->assertArrayHasKey($cookieName, $cookies);
            $cookieValue = $cookies[$cookieName];
            if ($cookieName === '__utmv') {
                $this->assertEmpty($cookieValue);
            } else {
                $this->assertNotEmpty($cookieValue);
                $this->assertStringStartsWith($domainHash, $cookieValue);
            }
        }
    }

    /**
     * @depends testSetCookiesValid
     * @depends testGetCookiesValidSetCookiesCalled
     */
    public function testGetCookiesStringValid()
    {
        list($gass) = $this->getGassAndDependencies();
        $gass->setServerName('www.example.com');
        $gass->disableCookieHeaders();
        $gass->setCookies();

        $cookieParts = array();
        $currentCookies = $gass->getCookies();
        unset($currentCookies['__utmv']);
        foreach ($currentCookies as $name => $value) {
            $value = trim($value);
            if (!empty($value)) {
                $cookieParts[] = $name . '=' . $value . ';';
            }
        }
        $expectedCookieString = implode($cookieParts, ' ');

        $actualCookieString = $gass->getCookiesString();
        $this->assertEquals($expectedCookieString, $actualCookieString);
        $this->assertNotContains('__utmv', $actualCookieString);
    }

    public function testSetSessionCookieTimeoutValid()
    {
        list($gass) = $this->getGassAndDependencies();
        $timeoutValue = 86400000;
        $this->assertSame($gass, $gass->setSessionCookieTimeout($timeoutValue));
        $this->assertAttributeEquals($timeoutValue / 1000, 'sessionCookieTimeout', $gass);
    }

    /**
     * @dataProvider dataProviderCookieTimeoutInvalidValues
     */
    public function testSetSessionCookieTimeoutExceptionInvalidArgument($sessionCookieTimeout)
    {
        list($gass) = $this->getGassAndDependencies();
        $this->setExpectedException(
            'Gass\Exception\InvalidArgumentException',
            'Session Cookie Timeout must be an integer.'
        );
        $gass->setSessionCookieTimeout($sessionCookieTimeout);
    }

    public function dataProviderCookieTimeoutInvalidValues()
    {
        return array(
            array(86400.000),
            array('86400000'),
            array(new \stdClass),
            array(array(86400000)),
            array(true),
            array(null),
        );
    }

    public function testSetVisitorCookieTimeoutValid()
    {
        list($gass) = $this->getGassAndDependencies();
        $timeoutValue = 86400000;
        $this->assertSame($gass, $gass->setVisitorCookieTimeout($timeoutValue));
        $this->assertAttributeEquals($timeoutValue / 1000, 'visitorCookieTimeout', $gass);
    }

    /**
     * @dataProvider dataProviderCookieTimeoutInvalidValues
     */
    public function testSetVisitorCookieTimeoutExceptionFloatArgument($visitorCookieTimeout)
    {
        list($gass) = $this->getGassAndDependencies();
        $this->setExpectedException(
            'Gass\Exception\InvalidArgumentException',
            'Visitor Cookie Timeout must be an integer.'
        );
        $gass->setVisitorCookieTimeout($visitorCookieTimeout);
    }

    public function testDisableCookieHeadersValid()
    {
        list($gass) = $this->getGassAndDependencies();
        $this->assertSame($gass, $gass->disableCookieHeaders());
        $this->assertAttributeEquals(false, 'sendCookieHeaders', $gass);
    }

    /**
     * @depends testGetCookiesValidSetCookiesNotCalled
     */
    public function testSetCookiesFromRequestHeaders()
    {
        list($gass) = $this->getGassAndDependencies();
        $expectedCookieNames = array('__utma', '__utmb', '__utmc', '__utmv', '__utmz');
        $expectedCookies = array();
        foreach ($expectedCookieNames as $cookieName) {
            if (!empty($_COOKIE[$cookieName])) {
                $expectedCookies[$cookieName] = $_COOKIE[$cookieName];
            }
        }
        if (empty($expectedCookies)) {
            $this->markTestSkipped('No cookies with values to test setting from super global');
        }

        $expectedCookies = array_merge(array_fill_keys($expectedCookieNames, null), $expectedCookies);
        $gass->setCookiesFromRequestHeaders();

        $this->assertAttributeEquals($expectedCookies, 'cookies', $gass);
    }

    /**
     * @depends testSetVersionValid
     */
    public function testSetVersionFromJsValidWithCorrectJsFile()
    {
        list(
            $gass,
            $http
        ) = $this->getGassAndDependencies();
        $this->expectJsUrlCall($http);
        $this->assertSame($gass, $gass->setVersion('1.1.1'));
        $this->assertSame($gass, $gass->setVersionFromJs());
        $this->assertAttributeEquals('5.7.2', 'version', $gass);
    }

    /**
     * @depends testSetVersionValid
     */
    public function testSetVersionFromJsValidDoesntUpdateVersionWhenJsEmpty()
    {
        list(
            $gass,
            $http
        ) = $this->getGassAndDependencies();

        $http->shouldReceive('request')
            ->once()
            ->with(GoogleAnalyticsServerSide::JS_URL)
            ->andReturnSelf();
        $http->shouldReceive('getResponse')
            ->once()
            ->withNoArgs()
            ->andReturn('');

        $versionNotReplaced = '1.1.1';
        $this->assertSame($gass, $gass->setVersion($versionNotReplaced));
        $this->assertSame($gass, $gass->setVersionFromJs());
        $this->assertAttributeEquals($versionNotReplaced, 'version', $gass);
    }

    /**
     * @depends testSetVersionValid
     */
    public function testSetVersionFromJsValidDoesntUpdateVersionWhenJsDoesntContainValidVersion()
    {
        list(
            $gass,
            $http
        ) = $this->getGassAndDependencies();

        $http->shouldReceive('request')
            ->once()
            ->with(GoogleAnalyticsServerSide::JS_URL)
            ->andReturnSelf();
        $http->shouldReceive('getResponse')
            ->once()
            ->withNoArgs()
            ->andReturn('foo=function(){return"1.2";};a=1;');

        $versionNotReplaced = '1.1.1';
        $this->assertSame($gass, $gass->setVersion($versionNotReplaced));
        $this->assertSame($gass, $gass->setVersionFromJs());
        $this->assertAttributeEquals($versionNotReplaced, 'version', $gass);
    }

    /**
     * @depends testSetSearchEnginesValid
     */
    public function testSetSearchEnginesFromJsValid()
    {
        list(
            $gass,
            $http
        ) = $this->getGassAndDependencies();
        $this->expectJsUrlCall($http);
        $this->assertSame($gass, $gass->setSearchEngines(array()));
        $this->assertSame($gass, $gass->setSearchEnginesFromJs());
        $this->assertAttributeNotEmpty('searchEngines', $gass);
        $this->assertAttributeArrayHasKey('google', 'searchEngines', $gass);
        $this->assertAttributeArrayHasKey('yahoo', 'searchEngines', $gass);
        $this->assertAttributeArrayHasKey('ask', 'searchEngines', $gass);
    }

    /**
     * @dataProvider dataProviderTestTrackSingleValid
     */
    public function testTrackPageviewSingleValid($account, array $extraParams, array $customVars)
    {
        list(
            $gass,
            $http,
            $botInfo
        ) = $this->getGassAndDependencies();
        $botInfo->shouldReceive('isBot')
            ->withNoArgs()
            ->andReturn(false);

        $pageTitle = 'Example Page Title';

        $gass->setBotInfo(true)
            ->disableCookieHeaders()
            ->setAccount($account)
            ->setPageTitle($pageTitle);

        foreach ($customVars as $customVar) {
            $gass->setCustomVar(
                $customVar['name'],
                $customVar['value'],
                $customVar['scope'],
                $customVar['index']
            );
        }
        $this->expectJsUrlCall($http);

        $documentReferer = (string) $gass->getDocumentReferer();
        $documentReferer = (empty($documentReferer) && $documentReferer !== '0')
            ? '-'
            : urldecode($documentReferer);

        $expectedGifParams = array_merge(
            array(
                'utmwv' => $gass->getVersion(),
                'utmn' => 'REPLACEME',
                'utmhn' => (string) $this->getEnvVar('SERVER_NAME'),
                'utmr' => $documentReferer,
                'utmac' => (string) $gass->getAccount(),
                'utmcc' => $gass->getCookiesString(),
                'utmul' => $gass->getAcceptLanguage(),
                'utmcs' => $gass->getCharset(),
                'utmu' => 'q~',
                'aip' => 1,
                'utmip' => null,
                'utmp' => urldecode((string) $this->getEnvVar('REQUEST_URI')),
                'utmdt' => $pageTitle,
            ),
            $extraParams
        );

        $http->shouldReceive('request')
            ->once()
            ->with(
                matchesPattern(
                    '/^' .
                        preg_quote(GoogleAnalyticsServerSide::GIF_URL, '/') .
                        '\?' .
                        str_replace(
                            array('utmn\=REPLACEME', 'utmcc\=&'),
                            array('utmn\=.+?', 'utmcc\=.+?&'),
                            preg_quote(http_build_query($expectedGifParams, null, '&'))
                        ) .
                        '$/'
                )
            )->andReturnSelf();
        $this->assertSame($gass, $gass->trackPageview());
    }

    public function dataProviderTestTrackSingleValid()
    {
        return array(
            array(
                'UA-00000-0',
                array(),
                array(),
            ),
            array(
                'MO-00000-0',
                array(
                    'utmip' => preg_replace('/^((\d{1,3}\.){3})\d{1,3}$/', '$1', $this->getEnvVar('REMOTE_ADDR')) . '0',
                ),
                array(),
            ),
            array(
                'UA-00000-0',
                array(
                    'utme' => '8(foo*baz)9(bar*qux)11(2*3!1)',
                ),
                array(
                    'index1' => array(
                        'index' => 1,
                        'name' => 'foo',
                        'value' => 'bar',
                        'scope' => 2,
                    ),
                    'index3' => array(
                        'index' => 3,
                        'name' => 'baz',
                        'value' => 'qux',
                        'scope' => 1,
                    ),
                ),
            ),
            array(
                'MO-00000-0',
                array(
                    'utmip' => preg_replace('/^((\d{1,3}\.){3})\d{1,3}$/', '$1', $this->getEnvVar('REMOTE_ADDR')) . '0',
                    'utme' => '8(foo)9(bar)11(2)',
                ),
                array(
                    'index1' => array(
                        'index' => 1,
                        'name' => 'foo',
                        'value' => 'bar',
                        'scope' => 2,
                    ),
                ),
            ),
        );
    }

    public function testTrackPageviewMultipleValid()
    {
        list(
            $gass,
            $http,
            $botInfo
        ) = $this->getGassAndDependencies();
        $botInfo->shouldReceive('isBot')
            ->withNoArgs()
            ->andReturn(false);
        $gass->setBotInfo(true)
            ->disableCookieHeaders()
            ->setAccount('MO-00000-0');
        $gass->setPageTitle('Example Page Title');
        $this->expectJsAndGifUrlCall($http);
        $this->assertSame($gass, $gass->trackPageview());
        $gass->setCustomVar('Custom Var 5', 'Custom Value 5', 2, 5);
        $gass->trackPageview();
        $gass->trackPageview('http://www.test.co.uk/example/path?q=other');
        $gass->setBotInfo(m::mock('Gass\BotInfo\BotInfoInterface'));
        $gass->trackPageview();
    }

    public function testTrackPageviewReturnsFalseWhenClientIsBot()
    {
        list(
            $gass,
            $http,
            $botInfo
        ) = $this->getGassAndDependencies();
        $botInfo->shouldReceive('isBot')
            ->once()
            ->withNoArgs()
            ->andReturn(true);
        $gass->setBotInfo(true)
            ->disableCookieHeaders()
            ->setAccount('MO-00000-0');
        $gass->setPageTitle('Example Page Title');
        $this->expectJsUrlCall($http);
        $this->assertFalse($gass->trackPageview());
    }

    public function testTrackPageviewExceptionInvalidUrl()
    {
        $envDocumentReferer = $this->getEnvVar('HTTP_REFERER');
        $url = 'www.test.co.uk/example/path?q=other';

        $urlValidator = m::mock('overload:Gass\Validate\Url');
        if (!empty($envDocumentReferer)) {
            $urlValidator->shouldReceive('isValid')
                ->once()
                ->with($envDocumentReferer)
                ->andReturn(true);
        }
        $urlValidator->shouldReceive('isValid')
            ->once()
            ->with($url)
            ->andReturn(false);

        list($gass) = $this->getGassAndDependencies(true, true, true, false);
        $this->setExpectedException(
            'Gass\Exception\DomainException',
            'Url is invalid: ' . $url
        );
        $gass->trackPageview($url);
    }

    public function testTrackPageviewExceptionMissingAccount()
    {
        list($gass) = $this->getGassAndDependencies();
        $gass->disableCookieHeaders();
        $this->setExpectedException(
            'Gass\Exception\DomainException',
            'The account number must be set before any tracking can take place.'
        );
        $gass->trackPageview();
    }

    /**
     * @dataProvider dataProviderTestTrackSingleValid
     */
    public function testTrackEventSingleValid($account, array $extraParams, array $customVars)
    {
        list(
            $gass,
            $http,
            $botInfo
        ) = $this->getGassAndDependencies();
        $botInfo->shouldReceive('isBot')
            ->withNoArgs()
            ->andReturn(false);

        $pageTitle = 'Example Page Title';

        $gass->setBotInfo(true)
            ->disableCookieHeaders()
            ->setAccount($account)
            ->setPageTitle($pageTitle);

        foreach ($customVars as $customVar) {
            $gass->setCustomVar(
                $customVar['name'],
                $customVar['value'],
                $customVar['scope'],
                $customVar['index']
            );
        }
        $this->expectJsUrlCall($http);

        $documentReferer = (string) $gass->getDocumentReferer();
        $documentReferer = (empty($documentReferer) && $documentReferer !== '0')
            ? '-'
            : urldecode($documentReferer);

        $expectedGifParams = array(
            'utmwv' => $gass->getVersion(),
            'utmn' => 'REPLACEME',
            'utmhn' => (string) $this->getEnvVar('SERVER_NAME'),
            'utmr' => $documentReferer,
            'utmac' => (string) $gass->getAccount(),
            'utmcc' => $gass->getCookiesString(),
            'utmul' => $gass->getAcceptLanguage(),
            'utmcs' => $gass->getCharset(),
            'utmu' => 'q~',
            'aip' => 1,
            'utmip' => null,
            'utmt' => 'event',
            'utme' => '5(foo*bar)' . (isset($extraParams['utme']) ? $extraParams['utme'] : ''),
            'utmni' => '1',
        );
        unset($extraParams['utme']);
        $expectedGifParams = array_merge($expectedGifParams, $extraParams);

        $http->shouldReceive('request')
            ->once()
            ->with(
                matchesPattern(
                    '/^' .
                        preg_quote(GoogleAnalyticsServerSide::GIF_URL, '/') .
                        '\?' .
                        str_replace(
                            array('utmn\=REPLACEME', 'utmcc\=&'),
                            array('utmn\=.+?', 'utmcc\=.+?&'),
                            preg_quote(http_build_query($expectedGifParams, null, '&'))
                        ) .
                        '$/'
                )
            )->andReturnSelf();
        $this->assertSame($gass, $gass->trackEvent('foo', 'bar', null, null, true));
    }

    /**
     * @depends testGetEventStringValidIndividualParams
     * @depends testGetEventStringExceptionActionWrongDataType
     * @depends testGetEventStringExceptionCategoryWrongDataType
     * @depends testGetEventStringExceptionEmptyAction
     * @depends testGetEventStringExceptionEmptyCategory
     * @depends testGetEventStringExceptionLabelWrongDataType
     */
    public function testTrackEventMultipleValid()
    {
        list(
            $gass,
            $http,
            $botInfo
        ) = $this->getGassAndDependencies();
        $botInfo->shouldReceive('isBot')
            ->withNoArgs()
            ->andReturn(false);
        $this->expectJsAndGifUrlCall($http);
        $gass->setBotInfo(true)
            ->disableCookieHeaders()
            ->setAccount('MO-00000-0');
        $category = 'Test Category';
        $action = 'Test Action';
        $label = 'Test Label';
        $value = 1;
        $this->assertSame(
            $gass,
            $gass->trackEvent($category, $action, $label, $value)
        );
        $gass->setCustomVar('Custom Var 5', 'Custom Value 5', 2, 5);
        $gass->trackEvent($category, $action, $label, $value);
        $gass->setBotInfo(m::mock('Gass\BotInfo\BotInfoInterface'));
        $gass->trackEvent($category, $action, $label, $value, true);
    }

    public function testTrackEventExceptionMissingAccount()
    {
        list($gass) = $this->getGassAndDependencies();
        $gass->disableCookieHeaders();
        $this->setExpectedException(
            'Gass\Exception\DomainException',
            'The account number must be set before any tracking can take place.'
        );
        $gass->trackEvent('Test Category', 'Test Action', 'Test Label', 1);
    }

    public function dataProviderStringCastWrongDataType()
    {
        return array(
            array(array('FooBarBazQux')),
            array(new \stdClass),
        );
    }

    public function testTrackEventExceptionWrongNonInteractionDataType()
    {
        list(
            $gass,
            $http,
            $botInfo
        ) = $this->getGassAndDependencies();
        $gass->disableCookieHeaders();
        $this->setExpectedException(
            'Gass\Exception\InvalidArgumentException',
            'NonInteraction must be a boolean.'
        );
        $gass->trackEvent('Test Category', 'Test Action', 'Test Label', 1, 1);
    }

    private function getGassAndDependencies(
        $setBotInfo = true,
        $ipValidator = true,
        $langValidator = true,
        $urlValidator = true
    ) {
        $envAcceptLanguage = $this->getEnvVar('HTTP_ACCEPT_LANGUAGE');
        $envRemoteAddress = $this->getEnvVar('REMOTE_ADDR');
        $envUserAgent = $this->getEnvVar('HTTP_USER_AGENT');
        $envDocumentReferer = $this->getEnvVar('HTTP_REFERER');

        $constructOptions = array();

        $http = m::mock('overload:Gass\Http\Http');
        if (!empty($envUserAgent)) {
            $http->shouldReceive('setUserAgent')
                ->once()
                ->with($envUserAgent)
                ->andReturnSelf();
        }
        if (!empty($envRemoteAddress)) {
            $http->shouldReceive('setRemoteAddress')
                ->once()
                ->with($envRemoteAddress)
                ->andReturnSelf();
        }
        if (!empty($envAcceptLanguage)) {
            $http->shouldReceive('setAcceptLanguage')
                ->once()
                ->with($envAcceptLanguage)
                ->andReturnSelf();
        }

        $botInfo = m::mock('overload:Gass\BotInfo\BotInfo');
        if ($setBotInfo) {
            if (!empty($envUserAgent)) {
                $botInfo->shouldReceive('setUserAgent')
                    ->once()
                    ->with($envUserAgent)
                    ->andReturnSelf();
            }
            if (!empty($envRemoteAddress)) {
                $botInfo->shouldReceive('setRemoteAddress')
                    ->once()
                    ->with($envRemoteAddress)
                    ->andReturnSelf();
            }
        }

        if ($ipValidator) {
            $ipValidator = m::mock('overload:Gass\Validate\IpAddress', IpAddressInterfaceStub::class);
            if (!empty($envRemoteAddress)) {
                $ipValidator->shouldReceive('isValid')
                    ->once()
                    ->with($envRemoteAddress)
                    ->andReturn(true);
            }
            $ipValidator->shouldReceive('isValid')
                ->zeroOrMoreTimes()
                ->with(m::type('string'))
                ->andReturn(true);
        }

        if ($langValidator) {
            $langValidator = m::mock('overload:Gass\Validate\LanguageCode');
            if (!empty($envAcceptLanguage)) {
                $langValidator->shouldReceive('isValid')
                    ->once()
                    ->with($envAcceptLanguage)
                    ->andReturn(true);
            }
            $langValidator->shouldReceive('isValid')
                ->zeroOrMoreTimes()
                ->with(m::type('string'))
                ->andReturn(true);
        }

        if ($urlValidator) {
            $urlValidator = m::mock('overload:Gass\Validate\Url');
            if (!empty($envDocumentReferer)) {
                $urlValidator->shouldReceive('isValid')
                    ->once()
                    ->with($envDocumentReferer)
                    ->andReturn(true);
            }
            $urlValidator->shouldReceive('isValid')
                ->zeroOrMoreTimes()
                ->with(m::type('string'))
                ->andReturn(true);
        }

        $gass = new GoogleAnalyticsServerSide($constructOptions);

        return array(
            $gass,
            $http,
            $botInfo,
            $ipValidator,
            $langValidator,
            $urlValidator,
        );
    }

    private function getEnvVar($name)
    {
        $retVal = null;
        switch ($name) {
            case 'HTTP_ACCEPT_LANGUAGE':
                if (isset($_SERVER[$name])) {
                    $retVal = 'en-gb';
                }
                break;
            default:
                if (isset($_SERVER[$name])) {
                    $retVal = $_SERVER[$name];
                }
        }
        return $retVal;
    }
}

interface IpAddressInterfaceStub
{
    const OPT_ALLOW_IPV4 = 'allowIpV4';
    const OPT_ALLOW_IPV6 = 'allowIpV6';
}
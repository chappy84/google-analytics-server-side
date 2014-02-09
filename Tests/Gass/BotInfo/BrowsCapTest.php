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
 * @package     Gass
 * @subpackage  BotInfo
 */

namespace GassTests\Gass\BotInfo;

class BrowsCapTest extends \PHPUnit_Framework_TestCase
{
    private $iniFileLocation;

    public function setUp()
    {
        parent::setUp();
        \Gass\Http\Http::getInstance(array(), new \Gass\Http\Test);
        $this->iniFileLocation = realpath(
            dirname(dirname(__DIR__)).DIRECTORY_SEPARATOR.'dependency-files'.DIRECTORY_SEPARATOR.'php_browscap.ini'
        );
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function getBrowscapWithIni()
    {
        return new \Gass\BotInfo\BrowsCap(
            array(
                'browscap' => $this->iniFileLocation
            )
        );
    }

    public function setTestHttpForVersionDateFile()
    {
        $latestVersionDateFile = dirname($this->iniFileLocation).DIRECTORY_SEPARATOR.'latestVersionDate.txt';
        $httpAdapter = new \Gass\Http\Test;
        $httpAdapter->addRequestQueueItem(
            \Gass\BotInfo\BrowsCap::VERSION_DATE_URL,
            'HTTP/1.1 200 Ok'."\n".
            'Connection:Keep-Alive'."\n".
            'Content-Length:31'."\n".
            'Content-Type:text/html'."\n".
            'Date:Sun, 17 Feb 2013 00:06:15 GMT'."\n".
            'Keep-Alive:timeout=5, max=100'."\n".
            'Server:Apache/2.0.64 (Unix) mod_ssl/2.0.64 OpenSSL/0.9.8e-fips-rhel5 '.
            'mod_auth_passthrough/2.1 mod_bwlimited/1.4 FrontPage/5.0.2.2635'."\n".
            'X-Powered-By:PHP/5.3.19',
            file_get_contents($latestVersionDateFile)
        );
        \Gass\Http\Http::getInstance(array(), $httpAdapter);
    }

    public function testConstructValidNoArguments()
    {
        $browsCap = new \Gass\BotInfo\BrowsCap;
        $this->assertInstanceOf('Gass\BotInfo\BrowsCap', $browsCap);
    }

    public function testConstructValidBrowscapInOptions()
    {
        $browsCap = $this->getBrowscapWithIni();
        $this->assertInstanceOf('Gass\BotInfo\BrowsCap', $browsCap);
        $this->assertEquals($this->iniFileLocation, $browsCap->getOption('browscap'));
    }

    public function testConstructValidUnknownOptions()
    {
        $browsCap = new \Gass\BotInfo\BrowsCap(
            array(
                'tripe' => $this->iniFileLocation
            )
        );
        $this->assertInstanceOf('Gass\BotInfo\BrowsCap', $browsCap);
        $this->assertEquals($this->iniFileLocation, $browsCap->getOption('tripe'));
    }

    public function testGetLatestVersionDate()
    {
        $browsCap = $this->getBrowscapWithIni();
        $this->setTestHttpForVersionDateFile();
        $this->assertInstanceOf('Gass\BotInfo\BrowsCap', $browsCap);
        $this->assertEquals($this->iniFileLocation, $browsCap->getOption('browscap'));
        $this->assertEquals(
            strtotime(file_get_contents(dirname($this->iniFileLocation).DIRECTORY_SEPARATOR.'latestVersionDate.txt')),
            $browsCap->getLatestVersionDate()
        );
    }

    public function testGetBrowserValid()
    {
        $browsCap = $this->getBrowscapWithIni();
        $firefoxUserAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:17.0) Gecko/17.0 Firefox/17.0';
        $browserResult = $browsCap->getBrowser($firefoxUserAgent);
        $this->assertEquals($firefoxUserAgent, $browsCap->getUserAgent());
        $this->assertInstanceOf('stdClass', $browserResult);
        $this->assertEquals('MacOSX', $browserResult->platform);
        $this->assertEquals('Firefox', $browserResult->browser);
        $this->assertEquals('17.0', $browserResult->version);
        $this->assertEquals('17', $browserResult->majorver);
        $this->assertEquals('0', $browserResult->minorver);
        $this->assertEquals(true, $browserResult->cookies);
        $this->assertEquals(true, $browserResult->javascript);

        $googleBotUserAgent = 'Mozilla/5.0 (compatible; Googlebot/2.0; +http://www.google.com/bot.html)';
        $crawlerResult = $browsCap->getBrowser($googleBotUserAgent);
        $this->assertEquals($googleBotUserAgent, $browsCap->getUserAgent());
        $this->assertInstanceOf('stdClass', $crawlerResult);
        $this->assertEquals('unknown', $crawlerResult->platform);
        $this->assertEquals('Googlebot', $crawlerResult->browser);
        $this->assertEquals('2.0', $crawlerResult->version);
        $this->assertEquals('2', $crawlerResult->majorver);
        $this->assertEquals('0', $crawlerResult->minorver);
        $this->assertEquals(false, $crawlerResult->cookies);
        $this->assertEquals(false, $crawlerResult->javascript);
    }

    public function testGetBrowserValidReturnArray()
    {
        $browsCap = $this->getBrowscapWithIni();
        $firefoxUserAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:17.0) Gecko/17.0 Firefox/17.0';
        $browserResult = $browsCap->getBrowser($firefoxUserAgent, true);
        $this->assertTrue(is_array($browserResult));
        $this->assertNotEmpty($browserResult);
        $this->assertArrayHasKey('browser', $browserResult);
    }

    public function testGetBrowserNoArguments()
    {
        $browsCap = $this->getBrowscapWithIni();
        $this->assertEquals(false, $browsCap->getBrowser());
        $firefoxUserAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:17.0) Gecko/17.0 Firefox/17.0';
        $this->assertInstanceOf('Gass\BotInfo\BrowsCap', $browsCap->setUserAgent($firefoxUserAgent));
        $browserResult = $browsCap->getBrowser();
        $this->assertEquals($firefoxUserAgent, $browsCap->getUserAgent());
        $this->assertInstanceOf('stdClass', $browserResult);
    }

    public function testGetBrowserEmptyUserAgent()
    {
        $browsCap = $this->getBrowscapWithIni();
        $this->assertEquals(false, $browsCap->getBrowser(''));
    }

    public function testGetIsBotValid()
    {
        $browsCap = $this->getBrowscapWithIni();
        $firefoxUserAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:17.0) Gecko/17.0 Firefox/17.0';
        $this->assertFalse($browsCap->getIsBot($firefoxUserAgent));
        $this->assertEquals($firefoxUserAgent, $browsCap->getUserAgent());
        $googleBotUserAgent = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';
        $this->assertTrue($browsCap->getIsBot($googleBotUserAgent));
        $this->assertEquals($googleBotUserAgent, $browsCap->getUserAgent());
        $this->assertTrue($browsCap->getIsBot(''));
        $this->assertEquals('', $browsCap->getUserAgent());
    }

    public function testGetIsBotSetRemoteAddress()
    {
        $browsCap = $this->getBrowscapWithIni();
        $testIpAddress = '123.123.123.123';
        $browsCap->getIsBot('', $testIpAddress);
        $this->assertEquals($testIpAddress, $browsCap->getRemoteAddress());
    }

    public function testCheckIniFileEmptyBrowscapRuntimeException()
    {
        $this->setExpectedException(
            'Gass\Exception\RuntimeException',
            'The browscap option has not been specified, please set this and try again.'
        );
        $browscap = new \Gass\BotInfo\Browscap;
        $browscap->getBrowser();
    }
}

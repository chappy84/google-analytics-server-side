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
 * @subpackage  BotInfo
 */

namespace GASSTests\GASS\BotInfo;

class BrowsCapTest extends \PHPUnit_Framework_TestCase
{
    private $iniFileLocation;


    public function setUp()
    {
        parent::setUp();
        \GASS\Http\Http::getInstance(array(), new \GASS\Http\Test);
        $this->iniFileLocation = realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR
                                          .'dependency-files'.DIRECTORY_SEPARATOR.'php_browscap.ini');
    }


    public function tearDown()
    {
        parent::tearDown();
    }


    public function testConstructValidNoArguments()
    {
        $browsCap = new \GASS\BotInfo\BrowsCap();
        $this->assertInstanceOf('GASS\BotInfo\BrowsCap', $browsCap);
    }


    public function testConstructValidBrowscapInOptions()
    {
        $browsCap = new \GASS\BotInfo\BrowsCap(array(
                        'browscap' => $this->iniFileLocation
                    ));
        $this->assertInstanceOf('GASS\BotInfo\BrowsCap', $browsCap);
        $this->assertEquals($this->iniFileLocation, $browsCap->getOption('browscap'));
    }


    public function testConstructValidUnknownOptions()
    {
        $browsCap = new \GASS\BotInfo\BrowsCap(array(
                        'tripe' => $this->iniFileLocation
                    ));
        $this->assertInstanceOf('GASS\BotInfo\BrowsCap', $browsCap);
        $this->assertEquals($this->iniFileLocation, $browsCap->getOption('tripe'));
    }


    public function testGetLatestVersionDate()
    {
        $browsCap = new \GASS\BotInfo\BrowsCap(array(
                        'browscap' => $this->iniFileLocation
                    ));
        $this->assertInstanceOf('GASS\BotInfo\BrowsCap', $browsCap);
        $this->assertEquals($this->iniFileLocation, $browsCap->getOption('browscap'));
        $this->assertEquals(strtotime(file_get_contents(dirname($this->iniFileLocation).DIRECTORY_SEPARATOR.'latestVersionDate.txt')),
                            $browsCap->getLatestVersionDate());
    }


    public function testGetBrowserValid()
    {
        $browsCap = new \GASS\BotInfo\BrowsCap(array(
                        'browscap' => $this->iniFileLocation
                    ));
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

        $googleBotUserAgent = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';
        $crawlerResult = $browsCap->getBrowser($googleBotUserAgent);
        $this->assertEquals($googleBotUserAgent, $browsCap->getUserAgent());
        $this->assertInstanceOf('stdClass', $crawlerResult);
        $this->assertEquals('unknown', $crawlerResult->platform);
        $this->assertEquals('Googlebot', $crawlerResult->browser);
        $this->assertEquals('2.1', $crawlerResult->version);
        $this->assertEquals('2', $crawlerResult->majorver);
        $this->assertEquals('1', $crawlerResult->minorver);
        $this->assertEquals(false, $crawlerResult->cookies);
        $this->assertEquals(false, $crawlerResult->javascript);
    }


    public function testGetBrowserValidReturnArray()
    {
        $browsCap = new \GASS\BotInfo\BrowsCap(array(
                'browscap' => $this->iniFileLocation
        ));
        $firefoxUserAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:17.0) Gecko/17.0 Firefox/17.0';
        $browserResult = $browsCap->getBrowser($firefoxUserAgent, true);
        $this->assertTrue(is_array($browserResult));
        $this->assertNotEmpty($browserResult);
        $this->assertArrayHasKey('browser', $browserResult);
    }

    public function testGetBrowserNoArguments()
    {
        $browsCap = new \GASS\BotInfo\BrowsCap(array(
                'browscap' => $this->iniFileLocation
        ));
        $this->assertEquals(false, $browsCap->getBrowser());
        $firefoxUserAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:17.0) Gecko/17.0 Firefox/17.0';
        $this->assertInstanceOf('GASS\BotInfo\BrowsCap', $browsCap->setUserAgent($firefoxUserAgent));
        $browserResult = $browsCap->getBrowser();
        $this->assertEquals($firefoxUserAgent, $browsCap->getUserAgent());
        $this->assertInstanceOf('stdClass', $browserResult);
    }

    public function testGetBrowserEmptyUserAgent()
    {
        $browsCap = new \GASS\BotInfo\BrowsCap(array(
                        'browscap' => $this->iniFileLocation
                    ));
        $this->assertEquals(false, $browsCap->getBrowser(''));
    }

    public function testGetIsBotValid()
    {
        $browsCap = new \GASS\BotInfo\BrowsCap(array(
                'browscap' => $this->iniFileLocation
        ));
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
        $browsCap = new \GASS\BotInfo\BrowsCap(array(
                'browscap' => $this->iniFileLocation
        ));
        $testIpAddress = '123.123.123.123';
        $browsCap->getIsBot('', $testIpAddress);
        $this->assertEquals($testIpAddress, $browsCap->getRemoteAddress());
    }
}
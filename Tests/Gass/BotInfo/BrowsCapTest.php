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

namespace GassTests\Gass\BotInfo;

use Gass\BotInfo\BrowsCap;
use Mockery as m;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class BrowsCapTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        vfsStreamWrapper::register();
    }

    /**
     * Take virtual copy of the filesystem so that these tests will run a little bit quicker
     */
    public function setup()
    {
        parent::setup();
        $baseDir = realpath(__DIR__ . '/../../dependency-files');
        $fsRoot = vfsStream::copyFromFileSystem(
            $baseDir,
            vfsStream::newDirectory('temp', 0777),
            filesize($baseDir . '/test_php_browscap.ini')
        );
        foreach ($fsRoot->getChildren() as $child) {
            $child->chmod(0777);
            $child->lastAttributeModified(
                filectime($baseDir . '/' . $child->getName())
            );
            $child->lastModified(
                filemtime($baseDir . '/' . $child->getName())
            );
        }
        clearstatcache();
        vfsStreamWrapper::setRoot($fsRoot);
    }

    /**
     * Unregister VFS from available protocols list so it doesn't (potentially) affect other test classes
     */
    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        vfsStreamWrapper::unregister();
    }

    public function setTestHttpForVersionDateFile()
    {
        $httpAdapter = m::mock('Gass\Http\HttpInterface');
        $httpAdapter->shouldReceive('request')
            ->once()
            ->with(BrowsCap::VERSION_DATE_URL)
            ->andReturnSelf();
        $httpAdapter->shouldReceive('getResponse')
            ->once()
            ->withNoArgs()
            ->andReturn(vfsStreamWrapper::getRoot()->getChild('latestVersionDate.txt')->getContent());

        $httpMock = m::mock('overload:Gass\Http\Http');
        $httpMock->shouldReceive('getInstance')
            ->once()
            ->withNoArgs()
            ->andReturn($httpAdapter);
    }

    public function testConstructValidNoArguments()
    {
        $existingIniSetting = trim(ini_get(BrowsCap::OPT_BROWSCAP));
        $browsCap = new BrowsCap;

        $this->assertAttributeEquals(
            array(
                BrowsCap::OPT_INI_FILE => !empty($existingIniSetting) ? basename($existingIniSetting) : null,
                BrowsCap::OPT_SAVE_PATH => !empty($existingIniSetting) ? dirname($existingIniSetting) : null,
                BrowsCap::OPT_LATEST_VERSION_DATE_FILE => 'latestVersionDate.txt',
            ),
            'options',
            $browsCap
        );
    }

    public function testConstructValidBrowscapInOptions()
    {
        $browsCap = $this->getBrowscapWithIni();
        $iniFile = vfsStreamWrapper::getRoot()->getChild('test_php_browscap.ini');

        $this->assertAttributeEquals(
            array(
                BrowsCap::OPT_INI_FILE => $iniFile->getName(),
                BrowsCap::OPT_SAVE_PATH => dirname($iniFile->url()),
                BrowsCap::OPT_LATEST_VERSION_DATE_FILE => 'latestVersionDate.txt',
            ),
            'options',
            $browsCap
        );
    }

    public function testGetLatestVersionDate()
    {
        $browsCap = $this->getBrowscapWithIni();
        $iniFile = vfsStreamWrapper::getRoot()->getChild('test_php_browscap.ini');

        $this->assertAttributeEquals(
            array(
                BrowsCap::OPT_INI_FILE => $iniFile->getName(),
                BrowsCap::OPT_SAVE_PATH => dirname($iniFile->url()),
                BrowsCap::OPT_LATEST_VERSION_DATE_FILE => 'latestVersionDate.txt',
            ),
            'options',
            $browsCap
        );
        $this->assertEquals(
            strtotime(vfsStreamWrapper::getRoot()->getChild('latestVersionDate.txt')->getContent()),
            $browsCap->getLatestVersionDate()
        );
    }

    public function testGetBrowserValid()
    {
        $browsCap = $this->getBrowscapWithIni();

        $ua4UserAgent = 'UA4';
        $browserResult = $browsCap->getBrowser($ua4UserAgent);
        $this->assertEquals($ua4UserAgent, $browsCap->getUserAgent());
        $this->assertInstanceOf('stdClass', $browserResult);
        $this->assertEquals('UA4', $browserResult->browser);
        $this->assertEquals('1.0', $browserResult->version);
        $this->assertEquals('1', $browserResult->majorver);
        $this->assertEquals('0', $browserResult->minorver);
        $this->assertEquals(true, $browserResult->cookies);
        $this->assertEquals(true, $browserResult->javascript);
        $this->assertEquals(false, $browserResult->crawler);

        $ua3CrawlerUserAgent = '\'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.3; Trident/7.0)';
        $crawlerResult = $browsCap->getBrowser($ua3CrawlerUserAgent);
        $this->assertEquals($ua3CrawlerUserAgent, $browsCap->getUserAgent());
        $this->assertInstanceOf('stdClass', $crawlerResult);
        $this->assertEquals('UA3', $crawlerResult->browser);
        $this->assertEquals('1.0', $crawlerResult->version);
        $this->assertEquals('1', $crawlerResult->majorver);
        $this->assertEquals('0', $crawlerResult->minorver);
        $this->assertEquals(false, $crawlerResult->cookies);
        $this->assertEquals(false, $crawlerResult->javascript);
        $this->assertEquals(true, $crawlerResult->crawler);
    }

    public function testGetBrowserValidReturnArray()
    {
        $browsCap = $this->getBrowscapWithIni();
        $browserResult = $browsCap->getBrowser('UA4', true);
        $this->assertInternalType('array', $browserResult);
        $this->assertNotEmpty($browserResult);
        $this->assertArrayHasKey('browser', $browserResult);
    }

    public function testGetBrowserNoArguments()
    {
        $browsCap = $this->getBrowscapWithIni();
        $this->assertEquals(false, $browsCap->getBrowser());
        $ua3CookiesUserAgent = '`Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.3; Trident/7.0)';
        $this->assertSame($browsCap, $browsCap->setUserAgent($ua3CookiesUserAgent));
        $browserResult = $browsCap->getBrowser();
        $this->assertEquals($ua3CookiesUserAgent, $browsCap->getUserAgent());
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

        $ua4UserAgent = 'UA4';
        $this->assertFalse($browsCap->isBot($ua4UserAgent));
        $this->assertEquals($ua4UserAgent, $browsCap->getUserAgent());

        $ua3CrawlerUserAgent = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';
        $this->assertTrue($browsCap->isBot($ua3CrawlerUserAgent));
        $this->assertEquals($ua3CrawlerUserAgent, $browsCap->getUserAgent());

        $this->assertTrue($browsCap->isBot(null));
        $this->assertEquals(null, $browsCap->getUserAgent());
    }

    public function testGetIsBotSetRemoteAddress()
    {
        $browsCap = $this->getBrowscapWithIni();
        $testIpAddress = '123.123.123.123';

        $ipValidator = m::mock('overload:Gass\Validate\IpAddress');
        $ipValidator->shouldReceive('isValid')
            ->with($testIpAddress)
            ->once()
            ->andReturn(true);

        $this->assertTrue($browsCap->isBot(null, $testIpAddress));
        $this->assertEquals($testIpAddress, $browsCap->getRemoteAddress());
    }

    public function testCheckIniFileEmptyBrowscapRuntimeException()
    {
        $this->setExpectedException(
            'Gass\Exception\RuntimeException',
            'The browscap option has not been specified, please set this and try again.'
        );
        $browscap = new Browscap;
        $browscap->getBrowser();
    }

    private function getBrowscapWithIni()
    {
        $this->setTestHttpForVersionDateFile();
        $iniFile = vfsStreamWrapper::getRoot()->getChild('test_php_browscap.ini');

        return new BrowsCap(
            array(
                BrowsCap::OPT_INI_FILE => $iniFile->getName(),
                BrowsCap::OPT_SAVE_PATH => dirname($iniFile->url()),
            )
        );
    }
}

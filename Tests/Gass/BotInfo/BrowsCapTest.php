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
 * @copyright   Copyright (c) 2011-2017 Tom Chapman (http://tom-chapman.uk/)
 * @license     BSD 3-clause "New" or "Revised" License
 * @link        http://github.com/chappy84/google-analytics-server-side
 */

namespace GassTests\Gass\BotInfo;

use Gass\BotInfo\BrowsCap;
use GassTests\TestAbstract;
use Mockery as m;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class BrowsCapTest extends TestAbstract
{
    protected $trackErrors = null;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        vfsStreamWrapper::register();
    }

    /**
     * Take virtual copy of the filesystem so that these tests will run a little bit quicker
     */
    protected function setUp()
    {
        parent::setup();
        $ds = DIRECTORY_SEPARATOR;
        $baseDir = realpath(__DIR__ . $ds . '..' . $ds . '..' . $ds . 'dependency-files');
        $fsRoot = vfsStream::copyFromFileSystem(
            $baseDir,
            vfsStream::newDirectory('temp', 0777),
            filesize($baseDir . $ds . 'test_php_browscap.ini')
        );
        foreach ($fsRoot->getChildren() as $child) {
            $child->chmod(0777);
            $child->lastAttributeModified(
                filectime($baseDir . $ds . $child->getName())
            );
            $child->lastModified(
                filemtime($baseDir . $ds . $child->getName())
            );
        }
        clearstatcache();
        vfsStreamWrapper::setRoot($fsRoot);
        $this->trackErrors = ini_get('track_errors');
    }

    protected function tearDown()
    {
        parent::tearDown();
        ini_set('track_errors', $this->trackErrors);
    }

    /**
     * Unregister VFS from available protocols list so it doesn't (potentially) affect other test classes
     */
    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        vfsStreamWrapper::unregister();
    }

    public function setRecentVersionDateFileAndBasicHttpOverride()
    {
        $latestVersionFile = vfsStreamWrapper::getRoot()->getChild('latestVersionDate.txt');
        $latestVersionFile->lastModified(time());
        clearstatcache();

        m::mock('overload:Gass\Http\Http');
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

    public function testConstructValidWithOptions()
    {
        $root = vfsStreamWrapper::getRoot();
        $iniFile = $root->getChild('test_php_browscap.ini');
        $options = array(
            BrowsCap::OPT_INI_FILE => $iniFile->getName(),
            BrowsCap::OPT_SAVE_PATH => $root->url(),
        );
        $browsCap = new BrowsCap($options);

        $options[BrowsCap::OPT_LATEST_VERSION_DATE_FILE] = 'latestVersionDate.txt';

        $this->assertAttributeEquals($options, 'options', $browsCap);
    }

    /**
     * @dataProvider dataProviderTestsIniFileSavePathOptions
     */
    public function testConstructValidIgnoresIniBrowsCapWhenIniFileOrSavePathOptions(array $options)
    {
        $existingIniSetting = trim(ini_get(BrowsCap::OPT_BROWSCAP));
        $expectedOptions = array_merge(
            array(
                BrowsCap::OPT_INI_FILE => null,
                BrowsCap::OPT_SAVE_PATH => null,
                BrowsCap::OPT_LATEST_VERSION_DATE_FILE => 'latestVersionDate.txt',
            ),
            $options
        );
        $browsCap = new BrowsCap($options);

        $this->assertAttributeEquals($expectedOptions, 'options', $browsCap);
    }

    public function testSetOptionBrowscapNoOptionIniFileOrSavePath()
    {
        $root = vfsStreamWrapper::getRoot();
        $iniFile = $root->getChild('test_php_browscap.ini');
        $provisionalOptions = array(
            BrowsCap::OPT_INI_FILE => null,
            BrowsCap::OPT_SAVE_PATH => null,
            BrowsCap::OPT_LATEST_VERSION_DATE_FILE => null,
        );

        $browsCap = new BrowsCap;
        $browsCap->setOptions($provisionalOptions);

        $this->assertAttributeEquals($provisionalOptions, 'options', $browsCap);
        $this->assertSame($browsCap, $browsCap->setOption(BrowsCap::OPT_BROWSCAP, $iniFile->url()));
        $expectedOptions = array_merge(
            $provisionalOptions,
            array(
                BrowsCap::OPT_INI_FILE => $iniFile->getName(),
                BrowsCap::OPT_SAVE_PATH => $root->url(),
            )
        );
        $this->assertAttributeEquals($expectedOptions, 'options', $browsCap);
    }

    /**
     * @dataProvider dataProviderTestsIniFileSavePathOptions
     */
    public function testSetOptionWithBrowscapDoestOverrideIniFileOrSavePath(array $options)
    {
        $provisionalOptions = array_merge(
            array(
                BrowsCap::OPT_INI_FILE => null,
                BrowsCap::OPT_SAVE_PATH => null,
                BrowsCap::OPT_LATEST_VERSION_DATE_FILE => null,
            ),
            $options
        );

        $iniFile = vfsStreamWrapper::getRoot()->getChild('test_php_browscap.ini');
        $browsCap = new BrowsCap;
        $browsCap->setOptions($provisionalOptions);

        $this->assertAttributeEquals($provisionalOptions, 'options', $browsCap);
        $this->assertSame($browsCap, $browsCap->setOption(BrowsCap::OPT_BROWSCAP, $iniFile->url()));
        $this->assertAttributeEquals($provisionalOptions, 'options', $browsCap);
    }

    public function testSetOptionSetGenericOption()
    {
        $options = array(
            BrowsCap::OPT_INI_FILE => null,
            BrowsCap::OPT_SAVE_PATH => null,
            BrowsCap::OPT_LATEST_VERSION_DATE_FILE => null,
        );

        $browsCap = new BrowsCap;
        $browsCap->setOptions($options);
        $name = 'foo';
        $value = 'bar';
        $expectedOptions = array_merge($options, array($name => $value));

        $browsCap->setOption($name, $value);

        $this->assertAttributeEquals($expectedOptions, 'options', $browsCap);
    }

    public function testGetOptionBrowsCapValid()
    {
        $root = vfsStreamWrapper::getRoot();
        $iniFile = $root->getChild('test_php_browscap.ini');
        $browsCap = new BrowsCap(
            array(
                BrowsCap::OPT_INI_FILE => $iniFile->getName(),
                BrowsCap::OPT_SAVE_PATH => $root->url(),
            )
        );

        $this->assertEquals($iniFile->url(), $browsCap->getOption(BrowsCap::OPT_BROWSCAP));
    }

    public function testGetOptionBrowsCapValidRemovesExcessDirectorySeparators()
    {
        $root = vfsStreamWrapper::getRoot();
        $iniFile = $root->getChild('test_php_browscap.ini');
        $browsCap = new BrowsCap(
            array(
                BrowsCap::OPT_INI_FILE => $iniFile->getName(),
                BrowsCap::OPT_SAVE_PATH => $root->url() . '///',
            )
        );

        $this->assertEquals($iniFile->url(), $browsCap->getOption(BrowsCap::OPT_BROWSCAP));
    }

    /**
     * @dataProvider dataProviderTestsOneOrOtherIniFileSavePathOptions
     */
    public function testGetOptionBrowsCapNullWhenMissingOptions(array $constructOptions)
    {
        $browsCap = new BrowsCap($constructOptions);

        $this->assertNull($browsCap->getOption(BrowsCap::OPT_BROWSCAP));
    }

    /**
     * @dataProvider dataProviderBooleans
     */
    public function testGetLatestVersionDateValidFileNotExists($trackErrors)
    {
        ini_set('track_errors', $trackErrors);
        $root = vfsStreamWrapper::getRoot();
        $latestVersionFileName = 'latestVersionDate.txt';
        $latestVersionFile = $root->getChild($latestVersionFileName);
        $latestVersion = $latestVersionFile->getContent();
        $root->removeChild($latestVersionFileName);
        $this->assertFalse($root->hasChild($latestVersionFileName));

        $httpAdapter = m::mock('Gass\Http\HttpInterface');
        $httpAdapter->shouldReceive('request')
            ->once()
            ->with(BrowsCap::VERSION_DATE_URL)
            ->andReturnSelf();
        $httpAdapter->shouldReceive('getResponse')
            ->once()
            ->withNoArgs()
            ->andReturn($latestVersion);

        $httpMock = m::mock('overload:Gass\Http\Http');
        $httpMock->shouldReceive('getInstance')
            ->once()
            ->withNoArgs()
            ->andReturn($httpAdapter);

        $browsCap = new BrowsCap(array(BrowsCap::OPT_SAVE_PATH => $root->url()));

        // Due to an issue with vfsStream and file_put_contents: https://github.com/mikey179/vfsStream/wiki/Known-Issues
        $this->setExpectedException(
            'Gass\Exception\RuntimeException',
            'Cannot save file ' .
                $root->url() .
                DIRECTORY_SEPARATOR .
                $latestVersionFileName .
                ' due to: ' .
                $this->getErrorMsgOrSilencedDefault(
                    'file_put_contents(): Exclusive locks may only be set for regular files'
                )
        );
        $browsCap->getLatestVersionDate();
    }

    /**
     * @dataProvider dataProviderBooleans
     */
    public function testGetLatestVersionDateValidFileSavedOverOneDayAgoAndWritable($trackErrors)
    {
        ini_set('track_errors', $trackErrors);

        $this->setTestHttpForVersionDateFile();

        $root = vfsStreamWrapper::getRoot();
        $latestVersionFile = $root->getChild('latestVersionDate.txt');
        $latestVersionFile->lastModified(time() - 86401);
        clearstatcache();

        $browsCap = new BrowsCap(array(BrowsCap::OPT_SAVE_PATH => $root->url()));

        // Due to an issue with vfsStream and file_put_contents: https://github.com/mikey179/vfsStream/wiki/Known-Issues
        $this->setExpectedException(
            'Gass\Exception\RuntimeException',
            'Cannot save file ' .
                $latestVersionFile->url() .
                ' due to: ' .
                $this->getErrorMsgOrSilencedDefault(
                    'file_put_contents(): Exclusive locks may only be set for regular files'
                )
        );
        $browsCap->getLatestVersionDate();
    }

    public function testGetLatestVersionDateValidFileSavedWithinLastDay()
    {
        $root = vfsStreamWrapper::getRoot();
        $latestVersionFile = $root->getChild('latestVersionDate.txt');
        $filemtime = time() - 86400;
        $latestVersionFile->lastModified($filemtime);
        clearstatcache();

        $browsCap = new BrowsCap(array(BrowsCap::OPT_SAVE_PATH => $root->url()));

        $httpMock = m::mock('overload:Gass\Http\Http');
        $httpMock->shouldNotReceive('getInstance');
        $this->assertEquals(
            strtotime($latestVersionFile->getContent()),
            $browsCap->getLatestVersionDate()
        );
        clearstatcache();
        $this->assertEquals($filemtime, $latestVersionFile->filemtime());
    }

    public function testGetLatestVersionDateNotSetOrSavedWhenHttpReturnsInvalidDate()
    {
        $root = vfsStreamWrapper::getRoot();
        $latestVersionFile = $root->getChild('latestVersionDate.txt');
        $filemtime = time() - 86401;
        $latestVersionFile->lastModified($filemtime);
        clearstatcache();

        $browsCap = new BrowsCap(array(BrowsCap::OPT_SAVE_PATH => $root->url()));

        $httpAdapter = m::mock('Gass\Http\HttpInterface');
        $httpAdapter->shouldReceive('request')
            ->once()
            ->with(BrowsCap::VERSION_DATE_URL)
            ->andReturnSelf();
        $httpAdapter->shouldReceive('getResponse')
            ->once()
            ->withNoArgs()
            ->andReturn('most definitely an invalid date format');

        $httpMock = m::mock('overload:Gass\Http\Http');
        $httpMock->shouldReceive('getInstance')
            ->once()
            ->withNoArgs()
            ->andReturn($httpAdapter);

        $this->assertNull($browsCap->getLatestVersionDate());
        clearstatcache();
        $this->assertEquals($filemtime, $latestVersionFile->filemtime());
    }

    public function testGetLatestVersionDateExceptionRuntimeFilePathNotDeducable()
    {
        $browsCap = new BrowsCap;
        $browsCap->setOptions(array(BrowsCap::OPT_SAVE_PATH => null));

        $httpMock = m::mock('overload:Gass\Http\Http');
        $httpMock->shouldNotReceive('getInstance');
        $this->setExpectedException(
            'Gass\Exception\DomainException',
            'Cannot deduce latest version date file location. Please set the required options.'
        );
        $browsCap->getLatestVersionDate();
    }

    public function testGetLatestVersionDateExceptionRuntimeFileNotWritable()
    {
        $this->setTestHttpForVersionDateFile();

        $root = vfsStreamWrapper::getRoot();
        $latestVersionFile = $root->getChild('latestVersionDate.txt');
        $latestVersionFile->chmod(0444);
        $filemtime = time() - 86401;
        $latestVersionFile->lastModified($filemtime);
        clearstatcache();

        $browsCap = new BrowsCap(array(BrowsCap::OPT_SAVE_PATH => $root->url()));

        $this->setExpectedException(
            'Gass\Exception\RuntimeException',
            'Cannot save file ' .
                $latestVersionFile->url() .
                ' due to: File is not writable'
        );
        $browsCap->getLatestVersionDate();
    }

    public function testGetLatestVersionDateExceptionRuntimeFolderNotWritable()
    {
        $root = vfsStreamWrapper::getRoot();
        $latestVersionFileName = 'latestVersionDate.txt';
        $latestVersionFile = $root->getChild($latestVersionFileName);
        $latestVersion = $latestVersionFile->getContent();
        $root->removeChild($latestVersionFileName);
        $this->assertFalse($root->hasChild($latestVersionFileName));
        $root->chmod(0555);

        $httpAdapter = m::mock('Gass\Http\HttpInterface');
        $httpAdapter->shouldReceive('request')
            ->once()
            ->with(BrowsCap::VERSION_DATE_URL)
            ->andReturnSelf();
        $httpAdapter->shouldReceive('getResponse')
            ->once()
            ->withNoArgs()
            ->andReturn($latestVersion);

        $httpMock = m::mock('overload:Gass\Http\Http');
        $httpMock->shouldReceive('getInstance')
            ->once()
            ->withNoArgs()
            ->andReturn($httpAdapter);

        $browsCap = new BrowsCap(array(BrowsCap::OPT_SAVE_PATH => $root->url()));

        $this->setExpectedException(
            'Gass\Exception\RuntimeException',
            'Cannot save file ' .
                $root->url() .
                DIRECTORY_SEPARATOR .
                $latestVersionFileName .
                ' due to: Folder ' .
                $root->url() .
                ' is not writable'
        );
        $browsCap->getLatestVersionDate();
    }

    /**
     * @dataProvider dataProviderBooleans
     */
    public function testGetLatestVersionDateExceptionRuntimeFileNotReadable($trackErrors)
    {
        ini_set('track_errors', $trackErrors);
        $root = vfsStreamWrapper::getRoot();
        $latestVersionFile = $root->getChild('latestVersionDate.txt');
        $latestVersionFile->chmod(0111);
        $latestVersionFile->lastModified(time() - 86400);
        clearstatcache();

        $browsCap = new BrowsCap(array(BrowsCap::OPT_SAVE_PATH => $root->url()));

        $httpMock = m::mock('overload:Gass\Http\Http');
        $httpMock->shouldNotReceive('getInstance');

        $this->setExpectedException(
            'Gass\Exception\RuntimeException',
            'Couldn\'t read latest version date file: ' .
                $latestVersionFile->url() .
                ' due to: ' .
                $this->getErrorMsgOrSilencedDefault(
                    'file_get_contents(' . $latestVersionFile->url() . '): failed to open stream'
                )
        );
        $browsCap->getLatestVersionDate();
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
        $this->assertFalse($browsCap->getBrowser(''));
    }

    public function testGetBrowserDetailsBrowserDoesntMatchIndex()
    {
        $root = vfsStreamWrapper::getRoot();
        $latestVersionFile = $root->getChild('latestVersionDate.txt');
        $latestVersionFile->lastModified(time());
        $iniFile = $root->getChild('test_php_browscap.ini');
        $iniFile->lastModified(strtotime($latestVersionFile->getContent()));
        clearstatcache();

        $browsCap = $this->getBrowscapWithIni();

        $browsCap->getBrowser('');

        $rm = new \ReflectionMethod(get_class($browsCap), 'getBrowserDetails');
        $rm->setAccessible(true);
        $this->assertFalse($rm->invoke($browsCap, 'FooBarBaz'));
    }

    public function testGetBrowserDetailsBrowserParentDoesntExist()
    {
        $root = vfsStreamWrapper::getRoot();
        $latestVersionFile = $root->getChild('latestVersionDate.txt');
        $latestVersionFile->lastModified(time());
        $iniFile = $root->getChild('test_php_browscap.ini');
        $iniFile->lastModified(strtotime($latestVersionFile->getContent()));
        clearstatcache();

        $browsCap = $this->getBrowscapWithIni();

        $browsCap->getBrowser('');

        $rm = new \ReflectionMethod(get_class($browsCap), 'getBrowserDetails');
        $rm->setAccessible(true);
        $this->assertFalse($rm->invoke($browsCap, 'DoesntHaveAParent'));
    }

    public function testCheckIniFileExceptionRuntimeUnreadableIniFile()
    {
        $root = vfsStreamWrapper::getRoot();
        $iniFile = $root->getChild('test_php_browscap.ini');
        $iniFile->chmod(0111);

        $browsCap = $this->getBrowscapWithIni();

        $this->setExpectedException(
            'Gass\Exception\RuntimeException',
            'The browscap ini file ' .
                $root->url() .
                DIRECTORY_SEPARATOR .
                $iniFile->getName() .
                ' is un-readable, please ensure the permissions are correct and try again.'
        );
        $browsCap->getBrowser();
    }

    /**
     * @dataProvider dataProviderTestsOneOrOtherIniFileSavePathOptions
     */
    public function testCheckIniFileExceptionDomainCannotDeduceIniFileLocation(array $options)
    {
        $provisionalOptions = array_merge(
            array(
                BrowsCap::OPT_INI_FILE => null,
                BrowsCap::OPT_SAVE_PATH => null,
                BrowsCap::OPT_LATEST_VERSION_DATE_FILE => null,
            ),
            $options
        );

        $browsCap = new BrowsCap($provisionalOptions);

        $this->setExpectedException(
            'Gass\Exception\DomainException',
            'Cannot deduce browscap ini file location. Please set the required options.'
        );
        $browsCap->getBrowser();
    }

    /**
     * @dataProvider dataProviderBooleans
     */
    public function testCheckIniFileDownloadsFileWhenDoesntExist($trackErrors)
    {
        ini_set('track_errors', $trackErrors);
        $root = vfsStreamWrapper::getRoot();
        $iniFileName = 'test_php_browscap.ini';
        $iniFileContent = $root->getChild($iniFileName)->getContent();
        $root->removeChild($iniFileName);
        $options = array(
            BrowsCap::OPT_INI_FILE => $iniFileName,
            BrowsCap::OPT_SAVE_PATH => $root->url(),
        );

        $browsCap = new BrowsCap($options);

        $httpAdapter = m::mock('Gass\Http\HttpInterface');
        $httpAdapter->shouldReceive('getUserAgent')
            ->once()
            ->withNoArgs()
            ->andReturn('UA4');
        $httpAdapter->shouldReceive('request')
            ->once()
            ->with(BrowsCap::BROWSCAP_URL)
            ->andReturnSelf();
        $httpAdapter->shouldReceive('getResponse')
            ->once()
            ->withNoArgs()
            ->andReturn($iniFileContent);

        $httpMock = m::mock('overload:Gass\Http\Http');
        $httpMock->shouldReceive('getInstance')
            ->once()
            ->withNoArgs()
            ->andReturn($httpAdapter);

        // Due to an issue with vfsStream and file_put_contents: https://github.com/mikey179/vfsStream/wiki/Known-Issues
        $this->setExpectedException(
            'Gass\Exception\RuntimeException',
            'Cannot save file ' .
                $root->url() .
                DIRECTORY_SEPARATOR .
                $iniFileName .
                ' due to: ' .
                $this->getErrorMsgOrSilencedDefault(
                    'file_put_contents(): Exclusive locks may only be set for regular files'
                )
        );
        $browsCap->getBrowser();
    }

    /**
     * @dataProvider dataProviderTestUpdateIniFileExceptionDomainMissingHttpUserAgent
     */
    public function testUpdateIniFileExceptionDomainMissingHttpUserAgent($userAgentValue)
    {
        $root = vfsStreamWrapper::getRoot();
        $iniFileName = 'test_php_browscap.ini';
        $iniFile = $root->removeChild($iniFileName);
        $options = array(
            BrowsCap::OPT_INI_FILE => $iniFileName,
            BrowsCap::OPT_SAVE_PATH => $root->url(),
        );

        $browsCap = new BrowsCap($options);

        $httpAdapter = m::mock('Gass\Http\HttpInterface');
        $httpAdapter->shouldReceive('getUserAgent')
            ->once()
            ->withNoArgs()
            ->andReturn($userAgentValue);

        $httpMock = m::mock('overload:Gass\Http\Http');
        $httpMock->shouldReceive('getInstance')
            ->once()
            ->withNoArgs()
            ->andReturn($httpAdapter);

        $this->setExpectedException(
            'Gass\Exception\DomainException',
            'A user-agent has not beeen set in the Gass\Http adapter.' .
                ' The remote server rejects requests without a user-agent.'
        );
        $browsCap->getBrowser();
    }

    public function dataProviderTestUpdateIniFileExceptionDomainMissingHttpUserAgent()
    {
        return array(
            array(null),
            array(''),
            array(' '),
            array("\t"),
            array("\n\r"),
        );
    }

    public function testUpdateIniFileExceptionRuntimeEmptyIniFile()
    {
        $root = vfsStreamWrapper::getRoot();
        $iniFileName = 'test_php_browscap.ini';
        $iniFile = $root->removeChild($iniFileName);
        $options = array(
            BrowsCap::OPT_INI_FILE => $iniFileName,
            BrowsCap::OPT_SAVE_PATH => $root->url(),
        );

        $browsCap = new BrowsCap($options);

        $httpAdapter = m::mock('Gass\Http\HttpInterface');
        $httpAdapter->shouldReceive('getUserAgent')
            ->once()
            ->withNoArgs()
            ->andReturn('UA4');
        $httpAdapter->shouldReceive('request')
            ->once()
            ->with(BrowsCap::BROWSCAP_URL)
            ->andReturnSelf();
        $httpAdapter->shouldReceive('getResponse')
            ->once()
            ->withNoArgs()
            ->andReturn('');

        $httpMock = m::mock('overload:Gass\Http\Http');
        $httpMock->shouldReceive('getInstance')
            ->once()
            ->withNoArgs()
            ->andReturn($httpAdapter);

        $this->setExpectedException(
            'Gass\Exception\RuntimeException',
            'browscap ini file retrieved from external source seems to be empty. ' .
                'Please ensure the ini file file can be retrieved.'
        );
        $browsCap->getBrowser();
    }

    /**
     * @dataProvider dataProviderBooleans
     */
    public function testCheckIniFileDownloadsFileWhenFileExpired($trackErrors)
    {
        ini_set('track_errors', $trackErrors);
        $root = vfsStreamWrapper::getRoot();
        $latestVersionFile = $root->getChild('latestVersionDate.txt');
        $latestVersionFile->lastModified(time());
        $iniFile = $root->getChild('test_php_browscap.ini');
        $iniFile->lastModified(strtotime($latestVersionFile->getContent()) - 1);
        clearstatcache();
        $options = array(
            BrowsCap::OPT_INI_FILE => $iniFile->getName(),
            BrowsCap::OPT_SAVE_PATH => $root->url(),
            BrowsCap::OPT_LATEST_VERSION_DATE_FILE => $latestVersionFile->getName(),
        );

        $browsCap = new BrowsCap($options);

        $httpAdapter = m::mock('Gass\Http\HttpInterface');
        $httpAdapter->shouldReceive('getUserAgent')
            ->once()
            ->withNoArgs()
            ->andReturn('UA4');
        $httpAdapter->shouldReceive('request')
            ->once()
            ->with(BrowsCap::BROWSCAP_URL)
            ->andReturnSelf();
        $httpAdapter->shouldReceive('getResponse')
            ->once()
            ->withNoArgs()
            ->andReturn($latestVersionFile->getContent());

        $httpMock = m::mock('overload:Gass\Http\Http');
        $httpMock->shouldReceive('getInstance')
            ->twice()
            ->withNoArgs()
            ->andReturn($httpAdapter);

        // Due to an issue with vfsStream and file_put_contents: https://github.com/mikey179/vfsStream/wiki/Known-Issues
        $this->setExpectedException(
            'Gass\Exception\RuntimeException',
            'Cannot save file ' .
                $root->url() .
                DIRECTORY_SEPARATOR .
                $iniFile->getName() .
                ' due to: ' .
                $this->getErrorMsgOrSilencedDefault(
                    'file_put_contents(): Exclusive locks may only be set for regular files'
                )
        );
        $browsCap->getBrowser();
    }

    public function testCheckIniFileDoesntDownloadFileWhenFileNotExpired()
    {
        $root = vfsStreamWrapper::getRoot();
        $latestVersionFile = $root->getChild('latestVersionDate.txt');
        $latestVersionFile->lastModified(time());
        $iniFile = $root->getChild('test_php_browscap.ini');
        $iniFile->lastModified(strtotime($latestVersionFile->getContent()));
        clearstatcache();
        $options = array(
            BrowsCap::OPT_INI_FILE => $iniFile->getName(),
            BrowsCap::OPT_SAVE_PATH => $root->url(),
            BrowsCap::OPT_LATEST_VERSION_DATE_FILE => $latestVersionFile->getName(),
        );

        $browsCap = new BrowsCap($options);

        $httpMock = m::mock('overload:Gass\Http\Http');
        $httpMock->shouldNotReceive('getInstance');

        $this->assertInstanceOf('stdClass', $browsCap->getBrowser('UA4'));
    }

    public function testLoadIniFileExceptionRuntimeNoBrowserDetailsFound()
    {
        $root = vfsStreamWrapper::getRoot();
        $latestVersionFile = $root->getChild('latestVersionDate.txt');
        $latestVersionFile->lastModified(time());
        $iniFile = $root->getChild('test_php_browscap.ini');
        $iniFile->lastModified(time());
        $iniFile->setContent('foo');
        clearstatcache();
        $options = array(
            BrowsCap::OPT_INI_FILE => $iniFile->getName(),
            BrowsCap::OPT_SAVE_PATH => $root->url(),
            BrowsCap::OPT_LATEST_VERSION_DATE_FILE => $latestVersionFile->getName(),
        );

        $browsCap = new BrowsCap($options);

        $httpMock = m::mock('overload:Gass\Http\Http');
        $httpMock->shouldNotReceive('getInstance');

        $this->setExpectedException(
            'Gass\Exception\RuntimeException',
            'Browscap ini file could not be parsed.'
        );
        $browsCap->getBrowser();
    }

    /**
     * @dataProvider dataProviderTestGetIsBotValid
     */
    public function testGetIsBotValid($userAgent, $expectedReturn)
    {
        $browsCap = $this->getBrowscapWithIni();

        $this->assertEquals($expectedReturn, $browsCap->isBot($userAgent));
        $this->assertEquals($userAgent, $browsCap->getUserAgent());
    }

    public function dataProviderTestGetIsBotValid()
    {
        return array(
            array('UA4', false),
            array('`Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.3; Trident/7.0)', true),
            array('\'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.3; Trident/7.0)', true),
            array('"Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.3; Trident/7.0)', true),
            array('"UA3', true),
            array('UA2', true),
            array('UA1', true),
            array(null, true),
        );
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

    public function dataProviderTestsOneOrOtherIniFileSavePathOptions()
    {
        return array(
            array(array(BrowsCap::OPT_INI_FILE => 'foo')),
            array(array(BrowsCap::OPT_SAVE_PATH => 'bar')),
        );
    }

    public function dataProviderTestsIniFileSavePathOptions()
    {
        $data = $this->dataProviderTestsOneOrOtherIniFileSavePathOptions();
        $data[] = array(array(BrowsCap::OPT_INI_FILE => 'baz', BrowsCap::OPT_SAVE_PATH => 'qux'));
        return $data;
    }

    private function getBrowscapWithIni()
    {
        $this->setRecentVersionDateFileAndBasicHttpOverride();
        $iniFile = vfsStreamWrapper::getRoot()->getChild('test_php_browscap.ini');

        return new BrowsCap(
            array(
                BrowsCap::OPT_INI_FILE => $iniFile->getName(),
                BrowsCap::OPT_SAVE_PATH => dirname($iniFile->url()),
            )
        );
    }
}

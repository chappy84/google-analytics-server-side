<?php
/*
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

namespace GassTests\Gass\BotInfo;

use Gass\BotInfo\UserAgentStringInfo;
use GassTests\TestAbstract;
use Mockery as m;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class UserAgentStringInfoTest extends TestAbstract
{
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
            filesize($baseDir . $ds . 'botIP.csv')
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
    }

    /**
     * Unregister VFS from available protocols list so it doesn't (potentially) affect other test classes
     */
    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        vfsStreamWrapper::unregister();
    }

    public function testSetWithoutCacheSettingsValidWebResponse()
    {
        $csvFile = vfsStreamWrapper::getRoot()->getChild('botIP.csv');
        $fh = fopen($csvFile->url(), 'r');
        $expectedDistinctBots = array();
        $expectedDistinctIps = array();
        for ($i = 0; $i < 50; ++$i) {
            $csvLine = fgetcsv($fh);
            if (!isset($expectedDistinctBots[$csvLine[0]])) {
                $expectedDistinctBots[$csvLine[0]] = $csvLine[6];
            }
            if (!isset($expectedDistinctIps[$csvLine[1]])) {
                $expectedDistinctIps[$csvLine[1]] = $csvLine[0];
            }
        }

        $httpAdapter = m::mock('Gass\Http\HttpInterface');
        $httpAdapter->shouldReceive('request')
            ->once()
            ->with(UserAgentStringInfo::CSV_URL)
            ->andReturnSelf();
        $httpAdapter->shouldReceive('getResponse')
            ->once()
            ->withNoArgs()
            ->andReturn($csvFile->getContent());

        $httpMock = m::mock('overload:Gass\Http\Http');
        $httpMock->shouldReceive('getInstance')
            ->once()
            ->withNoArgs()
            ->andReturn($httpAdapter);

        $uasi = new UserAgentStringInfo;
        $this->assertSame($uasi, $uasi->set());
        $this->assertAttributeNotEmpty('bots', $uasi);
        $this->assertAttributeArraySubset($expectedDistinctBots, 'bots', $uasi);
        $this->assertAttributeNotEmpty('botIps', $uasi);
        $this->assertAttributeArraySubset($expectedDistinctIps, 'botIps', $uasi);
        $this->assertAttributeEquals(null, 'cacheDate', $uasi);
    }

    public function testSetWithoutCacheSettingsEmptyWebResponse()
    {
        $httpAdapter = m::mock('Gass\Http\HttpInterface');
        $httpAdapter->shouldReceive('request')
            ->once()
            ->with(UserAgentStringInfo::CSV_URL)
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

        $uasi = new UserAgentStringInfo;
        $this->setExpectedException(
            'Gass\Exception\RuntimeException',
            'Bots CSV retrieved from external source seems to be empty. ' .
                'Please either set botInfo to null or ensure the bots csv file can be retrieved.'
        );
        $uasi->set();
    }

    public function testSetWithCacheSettingsCacheExpiredLongTimeAgo()
    {
        $fsRoot = vfsStreamWrapper::getRoot();
        $csvFile = $fsRoot->getChild('botIP.csv');

        $httpAdapter = m::mock('Gass\Http\HttpInterface');
        $httpAdapter->shouldReceive('request')
            ->once()
            ->with(UserAgentStringInfo::CSV_URL)
            ->andReturnSelf();
        $httpAdapter->shouldReceive('getResponse')
            ->once()
            ->withNoArgs()
            ->andReturn($csvFile->getContent());

        $httpMock = m::mock('overload:Gass\Http\Http');
        $httpMock->shouldReceive('getInstance')
            ->once()
            ->withNoArgs()
            ->andReturn($httpAdapter);

        $uasi = new UserAgentStringInfo(
            array(
                UserAgentStringInfo::OPT_CACHE_PATH => $fsRoot->url(),
                UserAgentStringInfo::OPT_CACHE_FILENAME => $csvFile->getName(),
                UserAgentStringInfo::OPT_CACHE_LIFETIME => 1,
            )
        );
        $this->assertSame($uasi, $uasi->set());
        $this->assertNull($fsRoot->getChild($csvFile->getName()));
        // Due to an issue with vfsStream and file_put_contents: https://github.com/mikey179/vfsStream/wiki/Known-Issues
        $this->setExpectedException(
            'Gass\Exception\RuntimeException',
            'Unable to write to file ' .
                $fsRoot->url() .
                DIRECTORY_SEPARATOR .
                $csvFile->getName() .
                ' due to: file_put_contents(): Exclusive locks may only be set for regular files'
        );
    }

    public function testSetWithCacheSettingsCacheExpiredJustNow()
    {
        $fsRoot = vfsStreamWrapper::getRoot();
        $csvFile = $fsRoot->getChild('botIP.csv');

        $httpAdapter = m::mock('Gass\Http\HttpInterface');
        $httpAdapter->shouldReceive('request')
            ->once()
            ->with(UserAgentStringInfo::CSV_URL)
            ->andReturnSelf();
        $httpAdapter->shouldReceive('getResponse')
            ->once()
            ->withNoArgs()
            ->andReturn($csvFile->getContent());

        $httpMock = m::mock('overload:Gass\Http\Http');
        $httpMock->shouldReceive('getInstance')
            ->once()
            ->withNoArgs()
            ->andReturn($httpAdapter);

        $uasi = new UserAgentStringInfo(
            array(
                UserAgentStringInfo::OPT_CACHE_PATH => $fsRoot->url(),
                UserAgentStringInfo::OPT_CACHE_FILENAME => $csvFile->getName(),
                UserAgentStringInfo::OPT_CACHE_LIFETIME => time() - $csvFile->filemtime(),
            )
        );
        $this->assertSame($uasi, $uasi->set());
        $this->assertNull($fsRoot->getChild($csvFile->getName()));
        // Due to an issue with vfsStream and file_put_contents: https://github.com/mikey179/vfsStream/wiki/Known-Issues
        $this->setExpectedException(
            'Gass\Exception\RuntimeException',
            'Unable to write to file ' .
                $fsRoot->url() .
                DIRECTORY_SEPARATOR .
                $csvFile->getName() .
                ' due to: file_put_contents(): Exclusive locks may only be set for regular files'
        );
    }

    public function testSetWithCacheSettingsCacheNotExpiredNoWebRequest()
    {
        $fsRoot = vfsStreamWrapper::getRoot();
        $csvFile = $fsRoot->getChild('botIP.csv');

        $fh = fopen($csvFile->url(), 'r');
        $expectedDistinctBots = array();
        $expectedDistinctIps = array();
        for ($i = 0; $i < 50; ++$i) {
            $csvLine = fgetcsv($fh);
            if (!isset($expectedDistinctBots[$csvLine[0]])) {
                $expectedDistinctBots[$csvLine[0]] = $csvLine[6];
            }
            if (!isset($expectedDistinctIps[$csvLine[1]])) {
                $expectedDistinctIps[$csvLine[1]] = $csvLine[0];
            }
        }

        $httpMock = m::mock('overload:Gass\Http\Http');
        $httpMock->shouldReceive('getInstance')
            ->once()
            ->withNoArgs()
            ->andReturn(m::mock('Gass\Http\HttpInterface'));

        $cacheLifetimesToTest = array(
            time() - vfsStreamWrapper::getRoot()->getChild('botIP.csv')->filemtime() + 1,
            315360000,
        );
        foreach ($cacheLifetimesToTest as $cacheLifetime) {
            $uasi = new UserAgentStringInfo(
                array(
                    UserAgentStringInfo::OPT_CACHE_PATH => $fsRoot->url(),
                    UserAgentStringInfo::OPT_CACHE_FILENAME => $csvFile->getName(),
                    UserAgentStringInfo::OPT_CACHE_LIFETIME => $cacheLifetime,
                )
            );
            $this->assertSame($uasi, $uasi->set());
            $this->assertAttributeNotEmpty('bots', $uasi);
            $this->assertAttributeArraySubset($expectedDistinctBots, 'bots', $uasi);
            $this->assertAttributeNotEmpty('botIps', $uasi);
            $this->assertAttributeArraySubset($expectedDistinctIps, 'botIps', $uasi);
            $this->assertAttributeEquals($csvFile->filemtime(), 'cacheDate', $uasi);
        }
    }

    public function testSetWithCacheSettingsCacheDoesNotExistValidWebResponse()
    {
        $fsRoot = vfsStreamWrapper::getRoot();
        $csvFile = $fsRoot->getChild('botIP.csv');
        $fh = fopen($csvFile->url(), 'r');
        $expectedDistinctBots = array();
        $expectedDistinctIps = array();
        for ($i = 0; $i < 50; ++$i) {
            $csvLine = fgetcsv($fh);
            if (!isset($expectedDistinctBots[$csvLine[0]])) {
                $expectedDistinctBots[$csvLine[0]] = $csvLine[6];
            }
            if (!isset($expectedDistinctIps[$csvLine[1]])) {
                $expectedDistinctIps[$csvLine[1]] = $csvLine[0];
            }
        }
        $nonExistentFileName = 'definitelyNonExistent.csv';

        $httpAdapter = m::mock('Gass\Http\HttpInterface');
        $httpAdapter->shouldReceive('request')
            ->once()
            ->with(UserAgentStringInfo::CSV_URL)
            ->andReturnSelf();
        $httpAdapter->shouldReceive('getResponse')
            ->once()
            ->withNoArgs()
            ->andReturn($csvFile->getContent());

        $httpMock = m::mock('overload:Gass\Http\Http');
        $httpMock->shouldReceive('getInstance')
            ->once()
            ->withNoArgs()
            ->andReturn($httpAdapter);

        $uasi = new UserAgentStringInfo(
            array(
                UserAgentStringInfo::OPT_CACHE_PATH => $fsRoot->url(),
                UserAgentStringInfo::OPT_CACHE_FILENAME => $nonExistentFileName,
            )
        );
        $this->assertSame($uasi, $uasi->set());
        $this->assertAttributeNotEmpty('bots', $uasi);
        $this->assertAttributeArraySubset($expectedDistinctBots, 'bots', $uasi);
        $this->assertAttributeNotEmpty('botIps', $uasi);
        $this->assertAttributeArraySubset($expectedDistinctIps, 'botIps', $uasi);
        $this->assertAttributeEquals(null, 'cacheDate', $uasi);
        // Due to an issue with vfsStream and file_put_contents: https://github.com/mikey179/vfsStream/wiki/Known-Issues
        $this->setExpectedException(
            'Gass\Exception\RuntimeException',
            'Unable to write to file ' .
                $fsRoot->url() .
                DIRECTORY_SEPARATOR .
                $nonExistentFileName .
                ' due to: file_put_contents(): Exclusive locks may only be set for regular files'
        );
    }

    public function testSetWithCacheSettingsCacheExistsButNonReadableValidWebResponse()
    {
        $fsRoot = vfsStreamWrapper::getRoot();
        $csvFile = $fsRoot->getChild('botIP.csv');
        $fh = fopen($csvFile->url(), 'r');
        $expectedDistinctBots = array();
        $expectedDistinctIps = array();
        for ($i = 0; $i < 50; ++$i) {
            $csvLine = fgetcsv($fh);
            if (!isset($expectedDistinctBots[$csvLine[0]])) {
                $expectedDistinctBots[$csvLine[0]] = $csvLine[6];
            }
            if (!isset($expectedDistinctIps[$csvLine[1]])) {
                $expectedDistinctIps[$csvLine[1]] = $csvLine[0];
            }
        }

        $httpAdapter = m::mock('Gass\Http\HttpInterface');
        $httpAdapter->shouldReceive('request')
            ->once()
            ->with(UserAgentStringInfo::CSV_URL)
            ->andReturnSelf();
        $httpAdapter->shouldReceive('getResponse')
            ->once()
            ->withNoArgs()
            ->andReturn($csvFile->getContent());

        $httpMock = m::mock('overload:Gass\Http\Http');
        $httpMock->shouldReceive('getInstance')
            ->once()
            ->withNoArgs()
            ->andReturn($httpAdapter);

        $nonReadableCsv = clone $csvFile;
        $nonReadableCsv->rename('nonReadable.csv');
        $nonReadableCsv->chmod(0111);
        $fsRoot->addChild($nonReadableCsv);

        $uasi = new UserAgentStringInfo(
            array(
                UserAgentStringInfo::OPT_CACHE_PATH => $fsRoot->url(),
                UserAgentStringInfo::OPT_CACHE_FILENAME => $nonReadableCsv->getName(),
            )
        );
        $this->assertSame($uasi, $uasi->set());
        $this->assertAttributeNotEmpty('bots', $uasi);
        $this->assertAttributeArraySubset($expectedDistinctBots, 'bots', $uasi);
        $this->assertAttributeNotEmpty('botIps', $uasi);
        $this->assertAttributeArraySubset($expectedDistinctIps, 'botIps', $uasi);
        $this->assertAttributeEquals(null, 'cacheDate', $uasi);
        // Due to an issue with vfsStream and file_put_contents: https://github.com/mikey179/vfsStream/wiki/Known-Issues
        $this->setExpectedException(
            'Gass\Exception\RuntimeException',
            'Unable to write to file ' .
                $fsRoot->url() .
                DIRECTORY_SEPARATOR .
                $nonReadableCsv->getName() .
                ' due to: file_put_contents(): Exclusive locks may only be set for regular files'
        );
    }

    public function testSetCannotDeleteUnwritableCacheFile()
    {
        $fsRoot = vfsStreamWrapper::getRoot();
        $csvFile = $fsRoot->getChild('botIP.csv');

        $httpMock = m::mock('overload:Gass\Http\Http');
        $httpMock->shouldReceive('getInstance')
            ->once()
            ->withNoArgs()
            ->andReturn(m::mock('Gass\Http\HttpInterface'));

        $filemtime = $csvFile->filemtime();
        $filectime = $csvFile->filectime();
        $csvFile->chmod(0444);
        $csvFile->lastAttributeModified($filectime);
        $csvFile->lastModified($filemtime);
        $fsRoot->chmod(0444);
        clearstatcache();

        $uasi = new UserAgentStringInfo(
            array(
                UserAgentStringInfo::OPT_CACHE_PATH => $fsRoot->url(),
                UserAgentStringInfo::OPT_CACHE_FILENAME => $csvFile->getName(),
                UserAgentStringInfo::OPT_CACHE_LIFETIME => 0,
            )
        );
        $this->setExpectedException(
            'Gass\Exception\RuntimeException',
            'Cannot delete "' . $csvFile->url() . '". Please check permissions.'
        );
        $uasi->set();
    }

    public function testGet()
    {
        $fsRoot = vfsStreamWrapper::getRoot();
        $csvFile = $fsRoot->getChild('botIP.csv');
        $fh = fopen($csvFile->url(), 'r');
        $expectedDistinctBots = array();
        $expectedDistinctIps = array();
        for ($i = 0; $i < 50; ++$i) {
            $csvLine = fgetcsv($fh);
            if (!isset($expectedDistinctBots[$csvLine[0]])) {
                $expectedDistinctBots[$csvLine[0]] = $csvLine[6];
            }
            if (!isset($expectedDistinctIps[$csvLine[1]])) {
                $expectedDistinctIps[$csvLine[1]] = $csvLine[0];
            }
        }

        $httpMock = m::mock('overload:Gass\Http\Http');
        $httpMock->shouldReceive('getInstance')
            ->once()
            ->withNoArgs()
            ->andReturn(m::mock('Gass\Http\HttpInterface'));

        $uasi = new UserAgentStringInfo(
            array(
                UserAgentStringInfo::OPT_CACHE_PATH => $fsRoot->url(),
                UserAgentStringInfo::OPT_CACHE_FILENAME => $csvFile->getName(),
                UserAgentStringInfo::OPT_CACHE_LIFETIME => 315360000,
            )
        );
        $this->assertSame($uasi, $uasi->set());
        $this->assertNotEmpty($uasi->get());
        $this->assertArraySubset($expectedDistinctBots, $uasi->get());
    }

    public function testDestructSavesToCacheFileWhenNotExists()
    {
        $fsRoot = vfsStreamWrapper::getRoot();
        $csvFile = $fsRoot->getChild('botIP.csv');
        $cacheFilename = 'nonExistent.csv';

        $httpAdapter = m::mock('Gass\Http\HttpInterface');
        $httpAdapter->shouldReceive('request')
            ->once()
            ->with(UserAgentStringInfo::CSV_URL)
            ->andReturnSelf();
        $httpAdapter->shouldReceive('getResponse')
            ->once()
            ->withNoArgs()
            ->andReturn($csvFile->getContent());

        $httpMock = m::mock('overload:Gass\Http\Http');
        $httpMock->shouldReceive('getInstance')
            ->once()
            ->withNoArgs()
            ->andReturn($httpAdapter);

        $uasi = new UserAgentStringInfo(
            array(
                UserAgentStringInfo::OPT_CACHE_PATH => $fsRoot->url(),
                UserAgentStringInfo::OPT_CACHE_FILENAME => $cacheFilename,
            )
        );
        $this->assertNull($fsRoot->getChild($cacheFilename));
        $this->assertSame($uasi, $uasi->set());
        // Due to an issue with vfsStream and file_put_contents: https://github.com/mikey179/vfsStream/wiki/Known-Issues
        $this->setExpectedException(
            'Gass\Exception\RuntimeException',
            'Unable to write to file ' .
                $fsRoot->url() .
                DIRECTORY_SEPARATOR .
                $cacheFilename .
                ' due to: file_put_contents(): Exclusive locks may only be set for regular files'
        );
        unset($uasi);
    }

    public function testSetCacheDateRequiresNumericOrNullWhenArgumentPassed()
    {
        $uasi = new UserAgentStringInfo;
        $rm = new \ReflectionMethod(get_class($uasi), 'setCacheDate');
        $rm->setAccessible(true);
        $this->setExpectedException('Gass\Exception\DomainException', 'cacheDate must be numeric or null');
        $rm->invoke($uasi, 'foo');
    }

    public function testGetCacheDate()
    {
        $uasi = new UserAgentStringInfo;
        $testCacheDate = time();
        $rm = new \ReflectionMethod(get_class($uasi), 'setCacheDate');
        $rm->setAccessible(true);
        $rm->invoke($uasi, $testCacheDate);
        $this->assertEquals($testCacheDate, $uasi->getCacheDate());
    }

    public function testIsBotNoArgsBotNameUserAgentAndIpNotFound()
    {
        $csvFile = vfsStreamWrapper::getRoot()->getChild('botIP.csv');
        $fh = fopen($csvFile->url(), 'r');
        $expectedDistinctBots = array();
        $expectedDistinctIps = array();
        for ($i = 0; $i < 50; ++$i) {
            $csvLine = fgetcsv($fh);
            if (!isset($expectedDistinctBots[$csvLine[0]])) {
                $expectedDistinctBots[$csvLine[0]] = $csvLine[6];
            }
            if (!isset($expectedDistinctIps[$csvLine[1]])) {
                $expectedDistinctIps[$csvLine[1]] = $csvLine[0];
            }
        }

        $httpAdapter = m::mock('Gass\Http\HttpInterface');
        $httpAdapter->shouldReceive('request')
            ->once()
            ->with(UserAgentStringInfo::CSV_URL)
            ->andReturnSelf();
        $httpAdapter->shouldReceive('getResponse')
            ->once()
            ->withNoArgs()
            ->andReturn($csvFile->getContent());

        $httpMock = m::mock('overload:Gass\Http\Http');
        $httpMock->shouldReceive('getInstance')
            ->once()
            ->withNoArgs()
            ->andReturn($httpAdapter);

        $remoteAddress = '0.0.0.0';

        $ipValidatorMock = m::mock('overload:Gass\Validate\IpAddress');
        $ipValidatorMock->shouldReceive('isValid')
            ->once()
            ->with($remoteAddress)
            ->andReturn(true);

        $uasi = new UserAgentStringInfo;
        $uasi->setRemoteAddress($remoteAddress)
            ->setUserAgent('FooBarBazQux');
        $this->assertFalse($uasi->isBot());
        $this->assertAttributeNotEmpty('bots', $uasi);
        $this->assertAttributeArraySubset($expectedDistinctBots, 'bots', $uasi);
        $this->assertAttributeNotEmpty('botIps', $uasi);
        $this->assertAttributeArraySubset($expectedDistinctIps, 'botIps', $uasi);
        $this->assertAttributeEquals(null, 'cacheDate', $uasi);
    }

    public function testIsBotNoArgsBotNameFound()
    {
        $csvFile = vfsStreamWrapper::getRoot()->getChild('botIP.csv');
        $fh = fopen($csvFile->url(), 'r');

        $csvLine = fgetcsv($fh);
        $botName = $csvLine[0];

        $httpAdapter = m::mock('Gass\Http\HttpInterface');
        $httpAdapter->shouldReceive('request')
            ->once()
            ->with(UserAgentStringInfo::CSV_URL)
            ->andReturnSelf();
        $httpAdapter->shouldReceive('getResponse')
            ->once()
            ->withNoArgs()
            ->andReturn($csvFile->getContent());

        $httpMock = m::mock('overload:Gass\Http\Http');
        $httpMock->shouldReceive('getInstance')
            ->once()
            ->withNoArgs()
            ->andReturn($httpAdapter);

        $uasi = new UserAgentStringInfo;
        $uasi->setUserAgent($botName);
        $this->assertTrue($uasi->isBot());
    }

    public function testIsBotNoArgsBotUserAgentFound()
    {
        $csvFile = vfsStreamWrapper::getRoot()->getChild('botIP.csv');
        $fh = fopen($csvFile->url(), 'r');

        $csvLine = fgetcsv($fh);
        $botUserAgent = $csvLine[6];

        $httpAdapter = m::mock('Gass\Http\HttpInterface');
        $httpAdapter->shouldReceive('request')
            ->once()
            ->with(UserAgentStringInfo::CSV_URL)
            ->andReturnSelf();
        $httpAdapter->shouldReceive('getResponse')
            ->once()
            ->withNoArgs()
            ->andReturn($csvFile->getContent());

        $httpMock = m::mock('overload:Gass\Http\Http');
        $httpMock->shouldReceive('getInstance')
            ->once()
            ->withNoArgs()
            ->andReturn($httpAdapter);

        $uasi = new UserAgentStringInfo;
        $uasi->setUserAgent($botUserAgent);
        $this->assertTrue($uasi->isBot());
    }

    public function testIsBotNoArgsBotIpAddressFound()
    {
        $csvFile = vfsStreamWrapper::getRoot()->getChild('botIP.csv');
        $fh = fopen($csvFile->url(), 'r');

        $csvLine = fgetcsv($fh);
        $botIpAddress = $csvLine[1];

        $httpAdapter = m::mock('Gass\Http\HttpInterface');
        $httpAdapter->shouldReceive('request')
            ->once()
            ->with(UserAgentStringInfo::CSV_URL)
            ->andReturnSelf();
        $httpAdapter->shouldReceive('getResponse')
            ->once()
            ->withNoArgs()
            ->andReturn($csvFile->getContent());

        $httpMock = m::mock('overload:Gass\Http\Http');
        $httpMock->shouldReceive('getInstance')
            ->once()
            ->withNoArgs()
            ->andReturn($httpAdapter);

        $ipValidatorMock = m::mock('overload:Gass\Validate\IpAddress');
        $ipValidatorMock->shouldReceive('isValid')
            ->once()
            ->with($botIpAddress)
            ->andReturn(true);

        $uasi = new UserAgentStringInfo;
        $uasi->setRemoteAddress($botIpAddress);
        $this->assertTrue($uasi->isBot());
    }

    public function testIsBotWithUserAgentArg()
    {
        $csvFile = vfsStreamWrapper::getRoot()->getChild('botIP.csv');
        $fh = fopen($csvFile->url(), 'r');

        $csvLine = fgetcsv($fh);
        $botUserAgent = $csvLine[6];

        $httpAdapter = m::mock('Gass\Http\HttpInterface');
        $httpAdapter->shouldReceive('request')
            ->once()
            ->with(UserAgentStringInfo::CSV_URL)
            ->andReturnSelf();
        $httpAdapter->shouldReceive('getResponse')
            ->once()
            ->withNoArgs()
            ->andReturn($csvFile->getContent());

        $httpMock = m::mock('overload:Gass\Http\Http');
        $httpMock->shouldReceive('getInstance')
            ->once()
            ->withNoArgs()
            ->andReturn($httpAdapter);

        $uasi = new UserAgentStringInfo;
        $this->assertTrue($uasi->isBot($botUserAgent));
        $this->assertAttributeEquals($botUserAgent, 'userAgent', $uasi);
    }

    public function testIsBotWithRemoteAddressArg()
    {
        $csvFile = vfsStreamWrapper::getRoot()->getChild('botIP.csv');
        $fh = fopen($csvFile->url(), 'r');

        $csvLine = fgetcsv($fh);
        $botIpAddress = $csvLine[1];

        $httpAdapter = m::mock('Gass\Http\HttpInterface');
        $httpAdapter->shouldReceive('request')
            ->once()
            ->with(UserAgentStringInfo::CSV_URL)
            ->andReturnSelf();
        $httpAdapter->shouldReceive('getResponse')
            ->once()
            ->withNoArgs()
            ->andReturn($csvFile->getContent());

        $httpMock = m::mock('overload:Gass\Http\Http');
        $httpMock->shouldReceive('getInstance')
            ->once()
            ->withNoArgs()
            ->andReturn($httpAdapter);

        $ipValidatorMock = m::mock('overload:Gass\Validate\IpAddress');
        $ipValidatorMock->shouldReceive('isValid')
            ->once()
            ->with($botIpAddress)
            ->andReturn(true);

        $uasi = new UserAgentStringInfo;
        $this->assertTrue($uasi->isBot(null, $botIpAddress));
        $this->assertAttributeEquals($botIpAddress, 'remoteAddress', $uasi);
    }
}

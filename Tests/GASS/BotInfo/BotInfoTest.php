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

class BotInfoTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
    }


    public function tearDown()
    {
        parent::tearDown();
    }


    public function testConstructValidNoArguments()
    {
        $botInfo = new \GASS\BotInfo\BotInfo();
        $this->assertInstanceOf('GASS\BotInfo\BotInfo', $botInfo);
        $this->assertInstanceOf('GASS\BotInfo\BrowsCap', $botInfo->getAdapter());
    }


    public function testConstructValidAdapterInOptions()
    {
        $botInfo = new \GASS\BotInfo\BotInfo(array('adapter' => 'UserAgentStringInfo'));
        $this->assertInstanceOf('GASS\BotInfo\BotInfo', $botInfo);
        $this->assertInstanceOf('GASS\BotInfo\UserAgentStringInfo', $botInfo->getAdapter());
    }


    public function testConstructValidAdapterParameter()
    {
        $botInfo = new \GASS\BotInfo\BotInfo(array(), 'UserAgentStringInfo');
        $this->assertInstanceOf('GASS\BotInfo\BotInfo', $botInfo);
        $this->assertInstanceOf('GASS\BotInfo\UserAgentStringInfo', $botInfo->getAdapter());
    }


    public function testConstructValidOptions()
    {
        $browscapLocation = '/tmp/php_browscap.ini';
        $botInfo = new \GASS\BotInfo\BotInfo(array('browscap' => $browscapLocation));
        $this->assertInstanceOf('GASS\BotInfo\BotInfo', $botInfo);
        $this->assertInstanceOf('GASS\BotInfo\BrowsCap', $botInfoAdapter = $botInfo->getAdapter());
        $this->assertArrayHasKey('browscap', $botInfoAdapter->getOptions());
        $this->assertEquals($browscapLocation, $botInfoAdapter->getOption('browscap'));
    }


    public function testSetAdapterValidString()
    {
        $botInfo = new \GASS\BotInfo\BotInfo();
        $botInfo->setAdapter('BrowsCap');
        $this->assertInstanceOf('GASS\BotInfo\BrowsCap', $botInfo->getAdapter());
        $botInfo->setAdapter('userAgentStringInfo');
        $this->assertInstanceOf('GASS\BotInfo\UserAgentStringInfo', $botInfo->getAdapter());
    }


    public function testSetAdapterValidClass()
    {
        $botInfo = new \GASS\BotInfo\BotInfo();
        $botInfo->setAdapter(new \GASS\BotInfo\BrowsCap());
        $this->assertInstanceOf('GASS\BotInfo\BrowsCap', $botInfo->getAdapter());
        $botInfo->setAdapter(new \GASS\BotInfo\UserAgentStringInfo());
        $this->assertInstanceOf('GASS\BotInfo\UserAgentStringInfo', $botInfo->getAdapter());
    }


    public function testSetAdapterExceptionAdapterWrongInstance()
    {
        $this->setExpectedException(
            'GASS\Exception\InvalidArgumentException',
            'The GASS\BotInfo adapter must implement GASS\BotInfo\BotInfoInterface.'
        );
        $botInfo = new \GASS\BotInfo\BotInfo();
        $botInfo->setAdapter(new \stdClass());
    }


    public function testSetAdapterExceptionAdapterWrongDataType()
    {
        $this->setExpectedException(
            'GASS\Exception\InvalidArgumentException',
            'The GASS\BotInfo adapter must implement GASS\BotInfo\BotInfoInterface.'
        );
        $botInfo = new \GASS\BotInfo\BotInfo();
        $botInfo->setAdapter(1);
    }


    public function testSetAdapterExceptionAdapterMissingString()
    {
        $this->setExpectedException(
            'RuntimeException',
            'File could not be found for GASS\BotInfo\Test'
        );
        $botInfo = new \GASS\BotInfo\BotInfo();
        $botInfo->setAdapter('Test');
    }


    public function testCallMagicMethodValid()
    {
        $browscapLocation = '/tmp/php_browscap.ini';
        $botInfo = new \GASS\BotInfo\BotInfo(array('browscap' => $browscapLocation));
        $this->assertInstanceOf('GASS\BotInfo\BotInfo', $botInfo);
        $this->assertInstanceOf('GASS\BotInfo\BrowsCap', $botInfoAdapter = $botInfo->getAdapter());
        $this->assertArrayHasKey('browscap', $botInfoAdapter->getOptions());
        $this->assertEquals($browscapLocation, $botInfoAdapter->getOption('browscap'));
    }


    public function testCallMagicMethodExceptionNoAdapter()
    {
        $botInfo = new \GASS\BotInfo\BotInfo();
        $reflectionProperty = new \ReflectionProperty('GASS\BotInfo\BotInfo', 'adapter');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($botInfo, null);
        $this->setExpectedException(
            'GASS\Exception\DomainException',
            'Adapter has not been set. Please set an adapter before calling setOption'
        );
        $botInfo->setOption('browscap', '/tmp/php_browscap.ini');
    }


    public function testCallMagicMethodExceptionMissingMethod()
    {
        $botInfo = new \GASS\BotInfo\BotInfo();
        $this->setExpectedException(
            'GASS\Exception\BadMethodCallException',
            'Method GASS\BotInfo\BrowsCap::testMethod does not exist.'
        );
        $botInfo->testMethod();
    }


    public function testAdapterBaseSetRemoteAddressExceptionInvalidAddress()
    {
        $botInfo = new \GASS\BotInfo\BotInfo();
        $this->setExpectedException('GASS\Exception\InvalidArgumentException');
        $botInfo->setRemoteAddress('test');
    }
}

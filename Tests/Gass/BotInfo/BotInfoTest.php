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

class BotInfoTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructValidNoArguments()
    {
        $botInfo = new \Gass\BotInfo\BotInfo;
        $this->assertInstanceOf('Gass\BotInfo\BotInfo', $botInfo);
        $this->assertInstanceOf('Gass\BotInfo\BrowsCap', $botInfo->getAdapter());
    }

    public function testConstructValidAdapterInOptions()
    {
        $botInfo = new \Gass\BotInfo\BotInfo(array('adapter' => 'UserAgentStringInfo'));
        $this->assertInstanceOf('Gass\BotInfo\BotInfo', $botInfo);
        $this->assertInstanceOf('Gass\BotInfo\UserAgentStringInfo', $botInfo->getAdapter());
    }

    public function testConstructValidAdapterParameter()
    {
        $botInfo = new \Gass\BotInfo\BotInfo(array(), 'UserAgentStringInfo');
        $this->assertInstanceOf('Gass\BotInfo\BotInfo', $botInfo);
        $this->assertInstanceOf('Gass\BotInfo\UserAgentStringInfo', $botInfo->getAdapter());
    }

    public function testConstructValidOptions()
    {
        $browscapLocation = '/tmp/full_php_browscap.ini';
        $botInfo = new \Gass\BotInfo\BotInfo(array('browscap' => $browscapLocation));
        $this->assertInstanceOf('Gass\BotInfo\BotInfo', $botInfo);
        $this->assertInstanceOf('Gass\BotInfo\BrowsCap', $botInfoAdapter = $botInfo->getAdapter());
        $this->assertArrayHasKey('browscap', $botInfoAdapter->getOptions());
        $this->assertEquals($browscapLocation, $botInfoAdapter->getOption('browscap'));
    }

    public function testSetAdapterValidString()
    {
        $botInfo = new \Gass\BotInfo\BotInfo;
        $botInfo->setAdapter('BrowsCap');
        $this->assertInstanceOf('Gass\BotInfo\BrowsCap', $botInfo->getAdapter());
        $botInfo->setAdapter('userAgentStringInfo');
        $this->assertInstanceOf('Gass\BotInfo\UserAgentStringInfo', $botInfo->getAdapter());
    }

    public function testSetAdapterValidClass()
    {
        $botInfo = new \Gass\BotInfo\BotInfo;
        $botInfo->setAdapter(new \Gass\BotInfo\BrowsCap);
        $this->assertInstanceOf('Gass\BotInfo\BrowsCap', $botInfo->getAdapter());
        $botInfo->setAdapter(new \Gass\BotInfo\UserAgentStringInfo);
        $this->assertInstanceOf('Gass\BotInfo\UserAgentStringInfo', $botInfo->getAdapter());
    }

    public function testSetAdapterExceptionAdapterWrongInstance()
    {
        $this->setExpectedException(
            'Gass\Exception\InvalidArgumentException',
            'The Gass\BotInfo adapter must implement Gass\BotInfo\BotInfoInterface.'
        );
        $botInfo = new \Gass\BotInfo\BotInfo;
        $botInfo->setAdapter(new \stdClass);
    }

    public function testSetAdapterExceptionAdapterWrongDataType()
    {
        $this->setExpectedException(
            'Gass\Exception\InvalidArgumentException',
            'The Gass\BotInfo adapter must implement Gass\BotInfo\BotInfoInterface.'
        );
        $botInfo = new \Gass\BotInfo\BotInfo;
        $botInfo->setAdapter(1);
    }

    public function testCallMagicMethodValid()
    {
        $browscapLocation = '/tmp/full_php_browscap.ini';
        $botInfo = new \Gass\BotInfo\BotInfo(array('browscap' => $browscapLocation));
        $this->assertInstanceOf('Gass\BotInfo\BotInfo', $botInfo);
        $this->assertInstanceOf('Gass\BotInfo\BrowsCap', $botInfoAdapter = $botInfo->getAdapter());
        $this->assertArrayHasKey('browscap', $botInfoAdapter->getOptions());
        $this->assertEquals($browscapLocation, $botInfoAdapter->getOption('browscap'));
    }

    public function testCallMagicMethodExceptionNoAdapter()
    {
        $botInfo = new \Gass\BotInfo\BotInfo;
        $reflectionProperty = new \ReflectionProperty('Gass\BotInfo\BotInfo', 'adapter');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($botInfo, null);
        $this->setExpectedException(
            'Gass\Exception\DomainException',
            'Adapter has not been set. Please set an adapter before calling setOption'
        );
        $botInfo->setOption('browscap', '/tmp/full_php_browscap.ini');
    }

    public function testCallMagicMethodExceptionMissingMethod()
    {
        $botInfo = new \Gass\BotInfo\BotInfo;
        $this->setExpectedException(
            'Gass\Exception\BadMethodCallException',
            'Method Gass\BotInfo\BrowsCap::testMethod does not exist.'
        );
        $botInfo->testMethod();
    }

    public function testAdapterBaseSetRemoteAddressExceptionInvalidAddress()
    {
        $botInfo = new \Gass\BotInfo\BotInfo;
        $this->setExpectedException('Gass\Exception\InvalidArgumentException');
        $botInfo->setRemoteAddress('test');
    }
}

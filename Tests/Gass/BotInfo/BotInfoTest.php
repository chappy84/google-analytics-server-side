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

namespace GassTests\Gass\BotInfo;

use Gass\BotInfo\BotInfo;
use Gass\BotInfo\BrowsCap;
use Gass\BotInfo\TestAdapter;
use GassTests\TestAbstract;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class BotInfoTest extends TestAbstract
{
    protected function setUp()
    {
        parent::setUp();
        $ds = DIRECTORY_SEPARATOR;
        require_once dirname(dirname(__DIR__)) . $ds . 'TestDoubles' . $ds . 'BotInfo' . $ds . 'BrowsCap.php';
        require_once dirname(dirname(__DIR__)) . $ds . 'TestDoubles' . $ds . 'BotInfo' . $ds . 'TestAdapter.php';
    }

    public function testConstructNoArgs()
    {
        $botInfo = new BotInfo;
        $this->assertAttributeInstanceOf('Gass\BotInfo\BrowsCap', 'adapter', $botInfo);
    }

    public function testConstructOptionsWithClassAdapter()
    {
        $options = array(
            'foo' => 'bar',
            'baz' => 'qux',
        );
        $adapter = $this->getMock('Gass\BotInfo\BotInfoInterface');
        $adapter->expects($this->once())
            ->method('setOptions')
            ->with($this->equalTo($options))
            ->willReturnSelf();
        $options['adapter'] = $adapter;
        $botInfo = new BotInfo($options);
        $this->assertAttributeSame($adapter, 'adapter', $botInfo);
    }

    public function testConstructOptionsAndAdapterParams()
    {
        $options = array(
            'foo' => 'bar',
            'baz' => 'qux',
        );
        $adapter = $this->getMock('Gass\BotInfo\BotInfoInterface');
        $adapter->expects($this->once())
            ->method('setOptions')
            ->with($this->equalTo($options))
            ->willReturnSelf();
        $botInfo = new BotInfo($options, $adapter);
        $this->assertAttributeSame($adapter, 'adapter', $botInfo);
    }

    public function testCallMagicMethodValid()
    {
        $testRetVal = 'testRetVal';
        $argument1 = 'argument1';
        $argument2 = 'argument2';
        $adapter = $this->getMock('Gass\BotInfo\BotInfoInterface');
        $adapter->expects($this->once())
            ->method('isBot')
            ->with($this->equalTo($argument1), $this->equalTo($argument2))
            ->willReturn($testRetVal);
        $botInfo = new BotInfo(array(), $adapter);
        $this->assertEquals($testRetVal, $botInfo->isBot($argument1, $argument2));
    }

    public function testCallMagicMethodExceptionBadMethodCall()
    {
        $adapter = $this->getMock('Gass\BotInfo\BotInfoInterface');
        $botInfo = new BotInfo(array(), $adapter);
        $this->setExpectedException(
            'Gass\Exception\BadMethodCallException',
            'Method ' . get_class($adapter) . '::fooBar does not exist.'
        );
        $botInfo->fooBar();
    }

    public function testSetAdapterValidWithString()
    {
        $botInfo = new BotInfo();
        $this->assertSame($botInfo, $botInfo->setAdapter('TestAdapter'));
        $this->assertAttributeInstanceOf('Gass\BotInfo\TestAdapter', 'adapter', $botInfo);
    }

    public function testSetAdapterValidWithClass()
    {
        $botInfo = new BotInfo();
        $testAdapter = new \Gass\BotInfo\TestAdapter;
        $this->assertSame($botInfo, $botInfo->setAdapter($testAdapter));
        $this->assertAttributeSame($testAdapter, 'adapter', $botInfo);
    }

    public function testSetAdapterExceptionInvalidArgument()
    {
        $botInfo = new BotInfo();
        $this->setExpectedException(
            'Gass\Exception\InvalidArgumentException',
            'The Gass\BotInfo adapter must implement Gass\BotInfo\BotInfoInterface.'
        );
        $botInfo->setAdapter(new \stdClass);
    }

    /**
     * @depends testConstructOptionsAndAdapterParams
     */
    public function testGetAdapter()
    {
        $adapter = $this->getMock('Gass\BotInfo\BotInfoInterface');
        $botInfo = new BotInfo(array(), $adapter);
        $this->assertSame($adapter, $botInfo->getAdapter());
    }
}

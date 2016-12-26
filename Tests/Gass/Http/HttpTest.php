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

namespace GassTests\Gass\Http;

use Gass\Http\Http;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class HttpTest extends \PHPUnit_Framework_TestCase
{
    private $defaultAdapter;

    protected function setUp()
    {
        parent::setUp();
        $adapterToLoad = extension_loaded('curl') ? 'Curl' : 'Stream';
        $this->defaultAdapter = 'Gass\Http\\' . $adapterToLoad;
        $ds = DIRECTORY_SEPARATOR;
        require_once dirname(dirname(__DIR__)) . $ds . 'TestDoubles' . $ds . 'Http' . $ds . $adapterToLoad . '.php';
        require_once dirname(dirname(__DIR__)) . $ds . 'TestDoubles' . $ds . 'Http' . $ds . 'TestAdapter.php';
    }

    public function testGetInstanceNoArgs()
    {
        $this->assertAttributeInstanceOf($this->defaultAdapter, 'adapter', Http::getInstance());
    }

    public function testGetInstanceOptionsWithClassAdapter()
    {
        $options = array(
            'foo' => 'bar',
            'baz' => 'qux',
        );
        $adapter = $this->getMock('Gass\Http\HttpInterface');
        $adapter->expects($this->once())
            ->method('setOptions')
            ->with($this->equalTo($options))
            ->willReturnSelf();
        $options['adapter'] = $adapter;
        $this->assertAttributeSame($adapter, 'adapter', Http::getInstance($options));
    }

    public function testGetInstanceOptionsAndAdapterParams()
    {
        $options = array(
            'foo' => 'bar',
            'baz' => 'qux',
        );
        $adapter = $this->getMock('Gass\Http\HttpInterface');
        $adapter->expects($this->once())
            ->method('setOptions')
            ->with($this->equalTo($options))
            ->willReturnSelf();
        $this->assertAttributeSame($adapter, 'adapter', Http::getInstance($options, $adapter));
    }

    public function testGetInstanceReturnsSameInstance()
    {
        $instance1 = Http::getInstance();
        $this->assertSame($instance1, Http::getInstance());
    }

    public function testGetInstanceSubsequentCallsOptionsWithClassAdapter()
    {
        $instance = Http::getInstance();
        $this->assertAttributeInstanceOf($this->defaultAdapter, 'adapter', $instance);

        $options = array(
            'foo' => 'bar',
            'baz' => 'qux',
        );
        $adapter = $this->getMock('Gass\Http\HttpInterface');
        $adapter->expects($this->once())
            ->method('setOptions')
            ->with($this->equalTo($options))
            ->willReturnSelf();
        $options['adapter'] = $adapter;
        Http::getInstance($options);
        $this->assertAttributeSame($adapter, 'adapter', $instance);
    }

    public function testGetInstanceSubsequentCallsOptionsAndAdapterParams()
    {
        $instance = Http::getInstance();
        $this->assertAttributeInstanceOf($this->defaultAdapter, 'adapter', $instance);

        $options = array(
            'foo' => 'bar',
            'baz' => 'qux',
        );
        $adapter = $this->getMock('Gass\Http\HttpInterface');
        $adapter->expects($this->once())
            ->method('setOptions')
            ->with($this->equalTo($options))
            ->willReturnSelf();
        Http::getInstance($options, $adapter);
        $this->assertAttributeSame($adapter, 'adapter', $instance);
    }

    public function testCloneInvalid()
    {
        $instance = Http::getInstance();
        $this->setExpectedException('Gass\Exception\RuntimeException', 'You cannot clone Gass\Http\Http');
        clone $instance;
    }

    public function testCallMagicMethodValid()
    {
        $testRetVal = 'testRetVal';
        $argument1 = 'argument1';
        $argument2 = array('argument2');
        $adapter = $this->getMock('Gass\Http\HttpInterface');
        $adapter->expects($this->once())
            ->method('request')
            ->with($this->equalTo($argument1), $this->equalTo($argument2))
            ->willReturn($testRetVal);
        $instance = Http::getInstance(array(), $adapter);
        $this->assertEquals($testRetVal, $instance->request($argument1, $argument2));
    }

    public function testCallMagicMethodExceptionBadMethodCall()
    {
        $adapter = $this->getMock('Gass\Http\HttpInterface');
        $instance = Http::getInstance(array(), $adapter);
        $this->setExpectedException(
            'Gass\Exception\BadMethodCallException',
            'Method ' . get_class($adapter) . '::fooBar does not exist.'
        );
        $instance->fooBar();
    }

    public function testCallStaticMagicMethod()
    {
        $testRetVal = 'testRetVal';
        $argument1 = 'argument1';
        $argument2 = array('argument2');
        $adapter = $this->getMock('Gass\Http\HttpInterface');
        $adapter->expects($this->once())
            ->method('request')
            ->with($this->equalTo($argument1), $this->equalTo($argument2))
            ->willReturn($testRetVal);
        $instance = Http::getInstance(array(), $adapter);
        $this->assertEquals($testRetVal, $instance::request($argument1, $argument2));
    }

    public function testCallStaticMagicMethodExceptionBadMethodCall()
    {
        $adapter = $this->getMock('Gass\Http\HttpInterface');
        $instance = Http::getInstance(array(), $adapter);
        $this->setExpectedException(
            'Gass\Exception\BadMethodCallException',
            'Method ' . get_class($adapter) . '::fooBar does not exist.'
        );
        $instance::fooBar();
    }

    public function testSetAdapterValidWithString()
    {
        $instance = Http::getInstance();
        $this->assertSame($instance, $instance->setAdapter('TestAdapter'));
        $this->assertAttributeInstanceOf('Gass\Http\TestAdapter', 'adapter', $instance);
    }

    public function testSetAdapterValidWithClass()
    {
        $instance = Http::getInstance();
        $testAdapter = new \Gass\Http\TestAdapter;
        $this->assertSame($instance, $instance->setAdapter($testAdapter));
        $this->assertAttributeSame($testAdapter, 'adapter', $instance);
    }

    public function testSetAdapterExceptionInvalidArgument()
    {
        $instance = Http::getInstance();
        $this->setExpectedException(
            'Gass\Exception\InvalidArgumentException',
            'The Gass\Http adapter must implement Gass\Http\HttpInterface.'
        );
        $instance->setAdapter(new \stdClass);
    }

    public function testGetAdapter()
    {
        $adapter = $this->getMock('Gass\Http\HttpInterface');
        $instance = Http::getInstance(array(), $adapter);
        $this->assertSame($adapter, $instance->getAdapter());
    }
}

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

namespace GassTests\Gass\Adapter;

use Gass\Adapter\Multi;
use GassTests\ExampleClasses\Adapter\InvalidDefaultInterface;
use GassTests\TestAbstract;

class MultiTest extends TestAbstract
{
    public function testConstructNoArguments()
    {
        $multiAdapter = $this->getMultiAdapter();
        $this->assertAttributeEquals(Multi::DEFAULT_INTERFACE, 'requiredClass', $multiAdapter);
    }

    public function testConstructWithAdapters()
    {
        $adapter1 = $this->getMock('Gass\Adapter\AdapterInterface');
        $adapter2 = $this->getMock('Gass\Adapter\AdapterInterface');
        $multiAdapter = $this->getMockForAbstractClass(
            'Gass\Adapter\Multi',
            array(
                array($adapter1, $adapter2),
            )
        );
        $this->assertAttributeEquals(Multi::DEFAULT_INTERFACE, 'requiredClass', $multiAdapter);
        $this->assertAttributeEquals(
            array(
                get_class($adapter1) => $adapter1,
                get_class($adapter2) => $adapter2,
            ),
            'adapters',
            $multiAdapter
        );
    }

    public function testConstructWithInvalidRequiredClass()
    {
        $this->setExpectedException(
            'Gass\Exception\DomainException',
            'stdClass must implement ' . Multi::DEFAULT_INTERFACE
        );
        $multi = new \GassTests\ExampleClasses\Adapter\InvalidRequiredClass;
    }

    public function testConstructWithInvalidDefaultInterface()
    {
        $this->setExpectedException(
            'Gass\Exception\DomainException',
            InvalidDefaultInterface::DEFAULT_INTERFACE . ' must implement ' . Multi::DEFAULT_INTERFACE
        );
        $multi = new InvalidDefaultInterface;
    }

    public function testAddAdapterValidNoName()
    {
        $multiAdapter = $this->getMultiAdapter();
        $adapter1 = $this->getMock('Gass\Adapter\AdapterInterface');
        $adapter2 = $this->getMock('Gass\Adapter\AdapterInterface');
        $this->assertSame($multiAdapter, $multiAdapter->addAdapter($adapter1));
        $this->assertAttributeEquals(array(get_class($adapter1) => $adapter1), 'adapters', $multiAdapter);
        $this->assertSame($multiAdapter, $multiAdapter->addAdapter($adapter1));
        $this->assertAttributeEquals(
            array(get_class($adapter1) => $adapter1, get_class($adapter2) => $adapter2),
            'adapters',
            $multiAdapter
        );
    }

    public function testAddAdapterValidWithName()
    {
        $multiAdapter = $this->getMultiAdapter();
        $adapter1Name = 'foo';
        $adapter1 = $this->getMock('Gass\Adapter\AdapterInterface');
        $adapter2Name = 'bar';
        $adapter2 = $this->getMock('Gass\Adapter\AdapterInterface');
        $this->assertSame($multiAdapter, $multiAdapter->addAdapter($adapter1, $adapter1Name));
        $this->assertAttributeEquals(array($adapter1Name => $adapter1), 'adapters', $multiAdapter);
        $this->assertSame($multiAdapter, $multiAdapter->addAdapter($adapter2, $adapter2Name));
        $this->assertAttributeEquals(
            array($adapter1Name => $adapter1, $adapter2Name => $adapter2),
            'adapters',
            $multiAdapter
        );
    }

    public function testAddAdapterInvalidArgumentExceptionAdapter()
    {
        $multiAdapter = $this->getMultiAdapter();
        $this->setExpectedException(
            'Gass\Exception\InvalidArgumentException',
            'stdClass does not implement ' . Multi::DEFAULT_INTERFACE
        );
        $multiAdapter->addAdapter(new \stdClass);
    }

    public function testAddAdapterInvalidArgumentExceptionName()
    {
        $multiAdapter = $this->getMultiAdapter();
        $this->setExpectedException('Gass\Exception\InvalidArgumentException', '$name must be a string');
        $multiAdapter->addAdapter(
            $this->getMock('Gass\Adapter\AdapterInterface'),
            new \stdClass
        );
    }

    /**
     * @dataProvider dataProviderSetAdapters
     * @depends testAddAdapterValidNoName
     * @depends testAddAdapterValidWithName
     */
    public function testSetAdapters($toSet, $expected = null)
    {
        if ($expected === null) {
            $expected = $toSet;
        }
        $multiAdapter = $this->getMultiAdapter();
        $this->assertSame($multiAdapter, $multiAdapter->setAdapters($toSet));
        $this->assertAttributeEquals($expected, 'adapters', $multiAdapter);
    }

    public function dataProviderSetAdapters()
    {
        $adapter = $this->getMock('Gass\Adapter\AdapterInterface');
        return array(
            array(
                array($this->getMock('Gass\Adapter\AdapterInterface')),
                array(
                    get_class($adapter) => $adapter,
                ),
            ),
            array(
                array(
                    'foo' => $this->getMock('Gass\Adapter\AdapterInterface'),
                    'bar' => $this->getMock('Gass\Adapter\AdapterInterface'),
                ),
            ),
        );
    }

    /**
     * @depends testSetAdapters
     */
    public function testSetAdaptersRemovesPreviousAdaptersWithMultipleCalls()
    {
        $adapter1 = $this->getMock('Gass\Adapter\AdapterInterface');
        $adapter2 = $this->getMock('Gass\Adapter\AdapterInterface');
        $multiAdapter = $this->getMultiAdapter();
        $this->assertSame($multiAdapter, $multiAdapter->setAdapters(array($adapter1)));
        $this->assertAttributeContains($adapter1, 'adapters', $multiAdapter);
        $this->assertSame($multiAdapter, $multiAdapter->setAdapters(array($adapter2)));
        $this->assertAttributeNotContains($adapter1, 'adapters', $multiAdapter);
        $this->assertAttributeContains($adapter2, 'adapters', $multiAdapter);
    }

    /**
     * @depends testAddAdapterValidNoName
     */
    public function testGetAdapters()
    {
        $multiAdapter = $this->getMultiAdapter();
        $this->assertEmpty($multiAdapter->getAdapters());
        $adapter1 = $this->getMock('Gass\Adapter\AdapterInterface');
        $multiAdapter->addAdapter($adapter1);
        $adapter2 = $this->getMock('Gass\Adapter\AdapterInterface');
        $multiAdapter->addAdapter($adapter2);
        $this->assertEquals(
            array(get_class($adapter1) => $adapter1, get_class($adapter2) => $adapter2),
            $multiAdapter->getAdapters()
        );
    }

    /**
     * @depends testAddAdapterValidNoName
     */
    public function testGetAdapterValid()
    {
        $multiAdapter = $this->getMultiAdapter();
        $adapter1 = $this->getMock('Gass\Adapter\AdapterInterface');
        $multiAdapter->addAdapter($adapter1);
        $this->assertSame($adapter1, $multiAdapter->getAdapter(get_class($adapter1)));
    }

    public function testGetAdapterDomainExceptionMissingAdapter()
    {
        $multiAdapter = $this->getMultiAdapter();
        $missingAdapterName = 'foo';
        $this->setExpectedException(
            'Gass\Exception\DomainException',
            $missingAdapterName . ' is not currently set as an adapter'
        );
        $multiAdapter->getAdapter($missingAdapterName);
    }

    /**
     * @depends testSetAdapters
     */
    public function testResetAdapters()
    {
        $adapter1 = $this->getMock('Gass\Adapter\AdapterInterface');
        $adapter2 = $this->getMock('Gass\Adapter\AdapterInterface');
        $multiAdapter = $this->getMultiAdapter();
        $this->assertSame($multiAdapter, $multiAdapter->setAdapters(array('foo' => $adapter1, 'bar' => $adapter2)));
        $multiAdapter->resetAdapters();
        $this->assertAttributeNotContains($adapter1, 'adapters', $multiAdapter);
        $this->assertAttributeNotContains($adapter2, 'adapters', $multiAdapter);
        $this->assertAttributeEmpty('adapters', $multiAdapter);
    }

    public function testSetOptionsBadMethodCallException()
    {
        $multiAdapter = $this->getMultiAdapter();
        $this->setExpectedException(
            'Gass\Exception\BadMethodCallException',
            'setOptions cannot be called on ' . get_class($multiAdapter)
        );
        $multiAdapter->setOptions(array());
    }

    public function testSetOptionBadMethodCallException()
    {
        $multiAdapter = $this->getMultiAdapter();
        $this->setExpectedException(
            'Gass\Exception\BadMethodCallException',
            'setOption cannot be called on ' . get_class($multiAdapter)
        );
        $multiAdapter->setOption('foo', 'bar');
    }

    public function testGetOptionsBadMethodCallException()
    {
        $multiAdapter = $this->getMultiAdapter();
        $this->setExpectedException(
            'Gass\Exception\BadMethodCallException',
            'getOptions cannot be called on ' . get_class($multiAdapter)
        );
        $multiAdapter->getOptions();
    }

    public function testGetOptionBadMethodCallException()
    {
        $multiAdapter = $this->getMultiAdapter();
        $this->setExpectedException(
            'Gass\Exception\BadMethodCallException',
            'getOption cannot be called on ' . get_class($multiAdapter)
        );
        $multiAdapter->getOption('foo');
    }

    private function getMultiAdapter()
    {
        return $this->getMockForAbstractClass('Gass\Adapter\Multi');
    }
}

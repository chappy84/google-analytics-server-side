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

namespace GassTests\Gass\Adapter;

class BaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Gass\BotInfo\Base
     */
    private $baseAdapter;

    public function setUp()
    {
        parent::setUp();
        $this->baseAdapter = $this->getMockForAbstractClass('Gass\Adapter\Base');
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testSetOptionsValid()
    {
        $options = array('testOption1' => 'testValue1');
        $this->assertInstanceOf('Gass\Adapter\Base', $this->baseAdapter->setOptions($options));
        $this->assertEquals($options, $this->baseAdapter->getOptions());
    }

    public function testSetOptionValid()
    {
        $optionName = 'testOption2';
        $optionValue = 'testValue2';
        $this->assertInstanceOf('Gass\Adapter\Base', $this->baseAdapter->setOption($optionName, $optionValue));
        $this->assertEquals($optionValue, $this->baseAdapter->getOption($optionName));
    }

    public function testGetOptionNotSetOption()
    {
        $this->assertNull($this->baseAdapter->getOption('notSetOption'));
    }
}

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

use GassTests\TestAbstract;

class BaseTest extends TestAbstract
{
    public function testSetOptionsValid()
    {
        $baseAdapter = $this->getBaseAdapter();
        $options = array('testOption1' => 'testValue1', 'testOption2' => 'testValue2');

        $this->assertEquals($baseAdapter, $baseAdapter->setOptions($options));
        $this->assertAttributeEquals($options, 'options', $baseAdapter);
    }

    public function testSetOptionValid()
    {
        $baseAdapter = $this->getBaseAdapter();
        $optionName1 = 'testOption1';
        $optionName2 = 'testOption2';
        $optionValue1 = 'testValue1';
        $optionValue2 = 'testValue2';
        $fullOptions = array(
            $optionName1 => $optionValue1,
            $optionName2 => $optionValue2,
        );

        $this->assertEquals($baseAdapter, $baseAdapter->setOption($optionName1, $optionValue1));
        $this->assertEquals($baseAdapter, $baseAdapter->setOption($optionName2, $optionValue2));
        $this->assertAttributeEquals($fullOptions, 'options', $baseAdapter);
    }

    /**
     * @depends testSetOptionsValid
     */
    public function testGetOptionsValid()
    {
        $baseAdapter = $this->getBaseAdapter();
        $options = array('testOption1' => 'testValue1', 'testOption2' => 'testValue2');

        $this->assertEquals($baseAdapter, $baseAdapter->setOptions($options));
        $this->assertEquals($options, $baseAdapter->getOptions());
    }

    /**
     * @depends testSetOptionsValid
     */
    public function testGetOptionValid()
    {
        $baseAdapter = $this->getBaseAdapter();
        $optionName1 = 'testOption1';
        $optionName2 = 'testOption2';
        $optionValue1 = 'testValue1';
        $optionValue2 = 'testValue2';
        $fullOptions = array(
            $optionName1 => $optionValue1,
            $optionName2 => $optionValue2,
        );
        $this->assertEquals($baseAdapter, $baseAdapter->setOptions($fullOptions));

        $this->assertEquals($optionValue1, $baseAdapter->getOption($optionName1));
        $this->assertEquals($optionValue2, $baseAdapter->getOption($optionName2));
    }

    public function testGetOptionMissing()
    {
        $baseAdapter = $this->getBaseAdapter();
        $this->assertNull($baseAdapter->getOption('notSetOption'));
    }

    private function getBaseAdapter()
    {
        return $this->getMockForAbstractClass('Gass\Adapter\Base');
    }
}

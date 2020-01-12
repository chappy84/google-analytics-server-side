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

namespace GassTests;

use Mockery as m;

abstract class TestAbstract extends \PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    public function dataProviderBooleans()
    {
        return array(
            array(true),
            array(false),
        );
    }

    public function dataProviderAllDataTypesButArray()
    {
        return array(
            array(new \ArrayObject),
            array(new \stdClass),
            array(null),
            array(true),
            array(1234567890),
            array(1.234567890),
            array('foo'),
        );
    }

    protected function assertAttributeArraySubset($expected, $attribute, $class)
    {
        $rp = new \ReflectionProperty(get_class($class), $attribute);
        $rp->setAccessible(true);
        $this->assertArraySubset($expected, $rp->getValue($class));
    }

    protected function assertAttributeArrayHasKey($key, $attribute, $class)
    {
        $rp = new \ReflectionProperty(get_class($class), $attribute);
        $rp->setAccessible(true);
        $this->assertArrayHasKey($key, $rp->getValue($class));
    }

    protected function assertAttributeArrayNotHasKey($key, $attribute, $class)
    {
        $rp = new \ReflectionProperty(get_class($class), $attribute);
        $rp->setAccessible(true);
        $this->assertArrayNotHasKey($key, $rp->getValue($class));
    }
}

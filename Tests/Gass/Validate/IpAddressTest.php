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

namespace GassTests\Gass\Validate;

use Gass\Validate\IpAddress;
use GassTests\TestAbstract;

class IpAddressTest extends TestAbstract
{
    /**
     * @dataProvider dataProviderTestIsValidValidipv4Addreses
     */
    public function testIsValidValidipv4Addreses($value)
    {
        $ipValidator = new IpAddress;
        $this->assertTrue($ipValidator->isValid($value));
        $this->assertAttributeEmpty('messages', $ipValidator);
    }

    public function dataProviderTestIsValidValidipv4Addreses()
    {
        return array(
            array('0.0.0.0'),
            array('1.1.1.1'),
            array('10.0.0.1'),
            array('10.255.255.255'),
            array('99.99.99.99'),
            array('127.0.0.1'),
            array('172.16.0.1'),
            array('172.31.255.255'),
            array('192.168.0.1'),
            array('192.168.255.255'),
            array('199.199.199.199'),
            array('255.255.255.255'),
        );
    }

    /**
     * @dataProvider dataProviderTestIsValidInvalidIpv4Addresses
     */
    public function testIsValidInvalidIpv4Addresses($value, $message)
    {
        $ipValidator = new IpAddress;
        $this->assertFalse($ipValidator->isValid($value));
        $this->assertAttributeEquals(array($message), 'messages', $ipValidator);
    }

    public function dataProviderTestIsValidInvalidIpv4Addresses()
    {
        return array(
            array('255.255.255.256', '"255.255.255.256" is an invalid IPv4 address'),
            // Lets test if Numb3rs is wrong or not: http://www.youtube.com/watch?v=5ceaqtWhdnI
            array('275.3.6.128', '"275.3.6.128" is an invalid IPv4 address'),
            array('999.999.999.999', '"999.999.999.999" is an invalid IPv4 address'),
            array('::1', '"::1" is an invalid IPv4 address'),
            array('1024.1024.1024.1024', '"1024.1024.1024.1024" is an invalid IPv4 address'),
            array(new \stdClass, 'The provided IP address must be a string.'),
        );
    }
}

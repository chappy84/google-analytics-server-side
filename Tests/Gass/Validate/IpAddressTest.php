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
 * @subpackage  Validate
 */

namespace GassTests\Gass\Validate;

class IpAddressTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Gass\Validate\IpAddress
     * @access private
     */
    private $ipValidator;

    public function setUp()
    {
        $this->ipValidator = new \Gass\Validate\IpAddress;
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testIsValidValidIPv4Address()
    {
        $this->assertTrue($this->ipValidator->isValid('0.0.0.0'));
        $this->assertTrue($this->ipValidator->isValid('1.1.1.1'));
        $this->assertTrue($this->ipValidator->isValid('10.0.0.1'));
        $this->assertTrue($this->ipValidator->isValid('10.255.255.255'));
        $this->assertTrue($this->ipValidator->isValid('99.99.99.99'));
        $this->assertTrue($this->ipValidator->isValid('127.0.0.1'));
        $this->assertTrue($this->ipValidator->isValid('172.16.0.1'));
        $this->assertTrue($this->ipValidator->isValid('172.31.255.255'));
        $this->assertTrue($this->ipValidator->isValid('192.168.0.1'));
        $this->assertTrue($this->ipValidator->isValid('192.168.255.255'));
        $this->assertTrue($this->ipValidator->isValid('199.199.199.199'));
        $this->assertTrue($this->ipValidator->isValid('255.255.255.255'));
    }

    public function testIsValidInvalidAddresses()
    {
        $this->assertFalse($this->ipValidator->isValid('255.255.255.256'));
        // Lets test if Numb3rs is wrong or not: http://www.youtube.com/watch?v=5ceaqtWhdnI
        $this->assertFalse($this->ipValidator->isValid('275.3.6.128'));
        $this->assertFalse($this->ipValidator->isValid('999.999.999.999'));
        $this->assertFalse($this->ipValidator->isValid('::1'));
        $this->assertFalse($this->ipValidator->isValid('1024.1024.1024.1024'));
    }

    public function testMessagesEmptyWhenValid()
    {
        $this->assertTrue($this->ipValidator->isValid('127.0.0.1'));
        $this->assertEmpty($this->ipValidator->getMessages());
    }

    public function testMessagesWhenInvalid()
    {
        $this->assertFalse($this->ipValidator->isValid('::1'));
        $this->assertNotEmpty($validationMessages = $this->ipValidator->getMessages());
        $this->assertEquals(1, count($validationMessages));
        $this->assertEquals('"::1" is an invalid IPv4 address', $validationMessages[0]);
    }
}

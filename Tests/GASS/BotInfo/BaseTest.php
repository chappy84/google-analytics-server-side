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

class BaseTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var GASS\BotInfo\Base
     * @access private
     */
    private $baseBotInfo;


    public function setUp()
    {
        parent::setUp();
        $this->baseBotInfo = $this->getMockForAbstractClass('GASS\BotInfo\Base');
    }


    public function tearDown()
    {
        parent::tearDown();
    }


    public function testSetRemoteAddressValid()
    {
        $validRemoteAddress = '192.168.0.1';
        $this->assertInstanceOf('GASS\BotInfo\Base', $this->baseBotInfo->setRemoteAddress($validRemoteAddress));
        $this->assertEquals($validRemoteAddress, $this->baseBotInfo->getRemoteAddress());
    }


    public function testSetRemoteAddressExceptionLetters()
    {
        $this->setExpectedException('GASS\Exception\InvalidArgumentException');
        $this->baseBotInfo->setRemoteAddress('abc.def.ghi.jkl');
    }


    public function testSetRemoteAddressExceptionTooHighSegments()
    {
        $this->setExpectedException('GASS\Exception\InvalidArgumentException');
        $this->baseBotInfo->setRemoteAddress('500.500.500.500');
    }


    public function testSetRemoteAddressExceptionMissingSegments()
    {
        $this->setExpectedException('GASS\Exception\InvalidArgumentException');
        $this->baseBotInfo->setRemoteAddress('255.255');
    }


    public function testSetRemoteAddressExceptionInteger()
    {
        $this->setExpectedException('GASS\Exception\InvalidArgumentException');
        $this->baseBotInfo->setRemoteAddress('192');
    }


    public function testSetRemoteAddressExceptionWrongDataType()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->baseBotInfo->setRemoteAddress(array('255.255.255.0'));
    }


    public function testSetUserAgent()
    {
        $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_4) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.47 Safari/536.11';
        $this->assertInstanceOf('GASS\BotInfo\Base', $this->baseBotInfo->setUserAgent($userAgent));
        $this->assertEquals($userAgent, $this->baseBotInfo->getUserAgent());
    }
}

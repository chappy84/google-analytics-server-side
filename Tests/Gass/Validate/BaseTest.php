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
 * @package     Gass
 * @subpackage  Http
 */

namespace GassTests\Gass\Validate;

class BaseTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Gass\Validate\Base
     */
    private $baseValidator;

    public function setUp()
    {
        parent::setUp();
        $this->baseValidator = $this->getMockForAbstractClass('Gass\Validate\Base');
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testSetMessagesValidPopulatedArray()
    {
        $testMessages = array('Test Message 1', 'Test Message 2');
        $this->assertInstanceOf('Gass\Validate\Base', $this->baseValidator->setMessages($testMessages));
        $this->assertEquals($testMessages, $this->baseValidator->getMessages());
    }

    public function testSetMessagesValidEmptyArray()
    {
        $this->assertInstanceOf('Gass\Validate\Base', $this->baseValidator->setMessages(array()));
        $this->assertEquals(array(), $this->baseValidator->getMessages());
    }

    public function testSetMessagesInvalidDataType()
    {
        $this->setExpectedException('PHPUnit_Framework_Error');
        $this->baseValidator->setMessages('');
    }

    public function testSetValue()
    {
        $this->assertInstanceOf('Gass\Validate\Base', $this->baseValidator->setValue(array()));
        $this->assertEquals(array(), $this->baseValidator->getValue());
        $testString = 'TestValue';
        $this->baseValidator->setValue($testString);
        $this->assertEquals($testString, $this->baseValidator->getValue());
        $testClass = new \stdClass;
        $this->baseValidator->setValue($testClass);
        $this->assertEquals($testClass, $this->baseValidator->getValue());
        $testInteger = 1;
        $this->baseValidator->setValue($testInteger);
        $this->assertEquals($testInteger, $this->baseValidator->getValue());
    }

    public function testAddMessage()
    {
        $this->assertInstanceOf(
            'Gass\Validate\Base',
            $this->baseValidator->addMessage('"%value%" is a test value for test message 1', 'Test value')
                ->setValue(2)
                ->addMessage('Test message 2 had value "%value%"')
        );
        $this->assertEquals(
            array('"Test value" is a test value for test message 1', 'Test message 2 had value "2"'),
            $this->baseValidator->getMessages()
        );
    }
}

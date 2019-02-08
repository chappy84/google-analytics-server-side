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
 * @copyright   Copyright (c) 2011-2019 Tom Chapman (http://tom-chapman.uk/)
 * @license     BSD 3-clause "New" or "Revised" License
 * @link        http://github.com/chappy84/google-analytics-server-side
 */

namespace GassTests\Gass\Validate;

use GassTests\TestAbstract;

class BaseTest extends TestAbstract
{
    /**
     * @dataProvider dataProviderTestSetMessagesValid
     */
    public function testSetMessagesValid($testMessages)
    {
        $baseValidator = $this->getBaseValidator();
        $this->assertSame($baseValidator, $baseValidator->setMessages($testMessages));
        $this->assertAttributeEquals($testMessages, 'messages', $baseValidator);
    }

    public function dataProviderTestSetMessagesValid()
    {
        return array(
            array(array('Test Message 1', 'Test Message 2')),
            array(array()),
        );
    }

    public function testSetMessagesInvalidDataType()
    {
        $baseValidator = $this->getBaseValidator();
        $this->setExpectedException(
            (class_exists('TypeError')) ? 'TypeError' : 'PHPUnit_Framework_Error'
        );
        $baseValidator->setMessages('');
    }

    /**
     * @depends testSetMessagesValid
     */
    public function testGetMessages()
    {
        $testMessages = array('Test Message 1', 'Test Message 2');
        $baseValidator = $this->getBaseValidator();
        $this->assertSame($baseValidator, $baseValidator->setMessages($testMessages));
        $this->assertEquals($testMessages, $baseValidator->getMessages());
    }

    /**
     * @dataProvider dataProviderTestSetValue
     */
    public function testSetValue($testValue)
    {
        $baseValidator = $this->getBaseValidator();
        $this->assertSame($baseValidator, $baseValidator->setValue($testValue));
        $this->assertAttributeEquals($testValue, 'value', $baseValidator);
    }

    public function dataProviderTestSetValue()
    {
        return array(
            array(new \stdClass),
            array('baz'),
            array(1234567890),
            array(1.234567890),
            array(array()),
            array(true),
            array(null),
        );
    }

    /**
     * @depends testSetValue
     */
    public function testGetValue()
    {
        $testValue = 'foo';
        $baseValidator = $this->getBaseValidator();
        $this->assertSame($baseValidator, $baseValidator->setValue($testValue));
        $this->assertEquals($testValue, $baseValidator->getValue());
    }

    public function testAddMessageOneArgumentWithValue()
    {
        $message = '"%value%" is a test value for a test message';
        $value = 'foo';

        $baseValidator = $this->getBaseValidator();
        $baseValidator->setValue($value);

        $this->assertSame($baseValidator, $baseValidator->addMessage($message));
        $this->assertAttributeEquals(
            array(str_replace('%value%', (string) $value, $message)),
            'messages',
            $baseValidator
        );
    }

    public function testAddMessageOneArgumentNoValue()
    {
        $message = '"%value%" is a test value for a test message';

        $baseValidator = $this->getBaseValidator();

        $this->assertSame($baseValidator, $baseValidator->addMessage($message));
        $this->assertAttributeEquals(array(str_replace('%value%', '', $message)), 'messages', $baseValidator);
    }

    public function testAddMessageOneArgumentWithValueNoPlaceHolder()
    {
        $message = 'This is a test value for a test message';

        $baseValidator = $this->getBaseValidator();
        $baseValidator->setValue('foo');

        $this->assertSame($baseValidator, $baseValidator->addMessage($message));
        $this->assertAttributeEquals(array($message), 'messages', $baseValidator);
    }

    public function testAddMessageTwoArgumentWithoutValue()
    {
        $message = '"%value%" is a test value for a test message';
        $value = 'foo';

        $baseValidator = $this->getBaseValidator();

        $this->assertSame($baseValidator, $baseValidator->addMessage($message, $value));
        $this->assertAttributeEmpty('value', $baseValidator);
        $this->assertAttributeEquals(
            array(str_replace('%value%', (string) $value, $message)),
            'messages',
            $baseValidator
        );
    }

    public function testAddMultipleMessages()
    {
        $message1 = '"%value%" is a test value for test message 1';
        $value1 = 'foo';
        $message2 = 'Test message 2 had value "%value%"';
        $value2 = 2;
        $message3 = 'This is a test for value "%value%" for test message 3';
        $message4 = 'This is a test value for test message 4';

        $baseValidator = $this->getBaseValidator();

        $this->assertSame($baseValidator, $baseValidator->addMessage($message1, $value1));
        $baseValidator->setValue($value2);
        $this->assertSame($baseValidator, $baseValidator->addMessage($message2));
        $this->assertSame($baseValidator, $baseValidator->addMessage($message3));
        $this->assertSame($baseValidator, $baseValidator->addMessage($message4));
        $this->assertAttributeEquals(
            array(
                str_replace('%value%', (string) $value1, $message1),
                str_replace('%value%', (string) $value2, $message2),
                str_replace('%value%', (string) $value2, $message3),
                $message4,
            ),
            'messages',
            $baseValidator
        );
    }

    private function getBaseValidator()
    {
        return $this->getMockForAbstractClass('Gass\Validate\Base');
    }
}

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

namespace GassTests\Gass\BotInfo;

use Mockery as m;

class BaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSetRemoteAddressValid()
    {
        $validRemoteAddress = 'testString';
        $botInfo = $this->getBotInfoBase();
        $ipValidator = m::mock('overload:Gass\Validate\IpAddress');
        $ipValidator->shouldReceive('isValid')
            ->once()
            ->with($validRemoteAddress)
            ->andReturn(true);
        $this->assertEquals($botInfo, $botInfo->setRemoteAddress($validRemoteAddress));
        $this->assertAttributeEquals($validRemoteAddress, 'remoteAddress', $botInfo);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @depends testSetRemoteAddressValid
     */
    public function testGetRemoteAddressValid()
    {
        $validRemoteAddress = 'testString';
        $ipValidator = m::mock('overload:Gass\Validate\IpAddress');
        $ipValidator->shouldReceive('isValid')
            ->once()
            ->with($validRemoteAddress)
            ->andReturn(true);
        $botInfo = $this->getBotInfoBase();
        $this->assertEquals($botInfo, $botInfo->setRemoteAddress($validRemoteAddress));
        $this->assertEquals($validRemoteAddress, $botInfo->getRemoteAddress());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSetRemoteAddressExceptionInvalidArgument()
    {
        $remoteAddress = 'testString';
        $testValidationMessages = array('Test Message 1', 'Test Message 2');

        $botInfo = $this->getBotInfoBase();
        $ipValidator = m::mock('overload:Gass\Validate\IpAddress');
        $ipValidator->shouldReceive('isValid')
            ->once()
            ->with($remoteAddress)
            ->andReturn(false);
        $ipValidator->shouldReceive('getMessages')
            ->withNoArgs()
            ->once()
            ->andReturn($testValidationMessages);

        $this->setExpectedException(
            'Gass\Exception\InvalidArgumentException',
            'Remote Address validation errors: ' . implode(', ', $testValidationMessages)
        );
        $botInfo->setRemoteAddress($remoteAddress);
    }

    public function testSetUserAgent()
    {
        $botInfo = $this->getBotInfoBase();
        $userAgent = 'TestUserAgent';
        $this->assertEquals($botInfo, $botInfo->setUserAgent($userAgent));
        $this->assertAttributeEquals($userAgent, 'userAgent', $botInfo);
    }

    /**
     * @depends testSetUserAgent
     */
    public function testGetUserAgent()
    {
        $botInfo = $this->getBotInfoBase();
        $userAgent = 'TestUserAgent';
        $this->assertEquals($botInfo, $botInfo->setUserAgent($userAgent));
        $this->assertEquals($userAgent, $botInfo->getUserAgent());
    }

    private function getBotInfoBase()
    {
        return m::mock('Gass\BotInfo\Base[]');
    }
}

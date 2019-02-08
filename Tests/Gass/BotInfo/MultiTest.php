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

namespace GassTests\Gass\BotInfo;

use Gass\BotInfo\Multi;
use GassTests\TestAbstract;

class MultiTest extends TestAbstract
{
    public function testGetRemoteAddressBadMethodCallException()
    {
        $botInfoMulti = new Multi;
        $this->setExpectedException(
            'Gass\Exception\BadMethodCallException',
            'getRemoteAddress cannot be called on Gass\BotInfo\Multi'
        );
        $botInfoMulti->getRemoteAddress();
    }

    public function testGetUserAgentBadMethodCallException()
    {
        $botInfoMulti = new Multi;
        $this->setExpectedException(
            'Gass\Exception\BadMethodCallException',
            'getUserAgent cannot be called on Gass\BotInfo\Multi'
        );
        $botInfoMulti->getUserAgent();
    }

    public function testSetRemoteAddressNoAdapters()
    {
        $botInfoMulti = new Multi;
        $this->assertSame($botInfoMulti, $botInfoMulti->setRemoteAddress('foo'));
    }

    public function testSetRemoteAddressWithAdapters()
    {
        $botInfoMulti = new Multi;
        $remoteAddress = 'foo';
        $adapter1 = $this->getMock('Gass\BotInfo\BotInfoInterface');
        $adapter1->expects($this->once())
            ->method('setRemoteAddress')
            ->with($this->equalTo($remoteAddress))
            ->willReturnSelf();
        $botInfoMulti->addAdapter($adapter1, 'bar');
        $adapter2 = $this->getMock('Gass\BotInfo\BotInfoInterface');
        $adapter2->expects($this->once())
            ->method('setRemoteAddress')
            ->with($this->equalTo($remoteAddress))
            ->willReturnSelf();
        $botInfoMulti->addAdapter($adapter2, 'baz');
        $this->assertSame($botInfoMulti, $botInfoMulti->setRemoteAddress($remoteAddress));
    }

    public function testSetUserAgentNoAdapters()
    {
        $botInfoMulti = new Multi;
        $this->assertSame($botInfoMulti, $botInfoMulti->setUserAgent('bar'));
    }

    public function testSetUserAgentWithAdapters()
    {
        $botInfoMulti = new Multi;
        $userAgent = 'foo';
        $adapter1 = $this->getMock('Gass\BotInfo\BotInfoInterface');
        $adapter1->expects($this->once())
            ->method('setUserAgent')
            ->with($this->equalTo($userAgent))
            ->willReturnSelf();
        $botInfoMulti->addAdapter($adapter1, 'bar');
        $adapter2 = $this->getMock('Gass\BotInfo\BotInfoInterface');
        $adapter2->expects($this->once())
            ->method('setUserAgent')
            ->with($this->equalTo($userAgent))
            ->willReturnSelf();
        $botInfoMulti->addAdapter($adapter2, 'baz');
        $this->assertSame($botInfoMulti, $botInfoMulti->setUserAgent($userAgent));
    }

    public function testIsBotNoAdapters()
    {
        $botInfoMulti = new Multi;
        $this->assertFalse($botInfoMulti->isBot());
    }

    /**
     * @dataProvider dataProviderTestIsBotWithAdapters
     */
    public function testIsBotWithAdapters($firstReturn, $secondReturn, $expectedReturn)
    {
        $botInfoMulti = new Multi;
        $userAgent = 'foo';
        $adapter1 = $this->getMock('Gass\BotInfo\BotInfoInterface');
        $adapter1->expects($this->once())
            ->method('isBot')
            ->with($this->isNull(), $this->isNull())
            ->willReturn($firstReturn);
        $botInfoMulti->addAdapter($adapter1, 'bar');
        $adapter2 = $this->getMock('Gass\BotInfo\BotInfoInterface');
        if ($firstReturn === false) {
            $adapter2->expects($this->once())
                ->method('isBot')
                ->with($this->isNull(), $this->isNull())
                ->willReturn($secondReturn);
        }
        $botInfoMulti->addAdapter($adapter2, 'baz');
        $this->assertEquals($expectedReturn, $botInfoMulti->isBot());
    }

    public function dataProviderTestIsBotWithAdapters()
    {
        return array(
            array(false, false, false),
            array(false, true, true),
            array(true, false, true),
            array(true, true, true),
        );
    }
}

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
 * @copyright   Copyright (c) 2011-2015 Tom Chapman (http://tom-chapman.uk/)
 * @license     BSD 3-clause "New" or "Revised" License
 * @link        http://github.com/chappy84/google-analytics-server-side
 */
namespace Gass\BotInfo;

use Gass\Adapter;
use Gass\Exception;
use Gass\Validate;

/**
 * Base class of all BotInfo adapters
 *
 * @see         Gass\Adapter
 * @see         Gass\Exception
 * @see         Gass\Validate
 * @author      Tom Chapman
 * @package     Gass\BotInfo
 */
abstract class Base extends Adapter\Base implements BotInfoInterface
{
    /**
     * The remote user's ip address
     *
     * @var string
     */
    protected $remoteAddress;

    /**
     * The current user-agent
     *
     * @var string
     */
    protected $userAgent;

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getRemoteAddress()
    {
        return $this->remoteAddress;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $remoteAddress
     * @return \Gass\BotInfo\Base
     */
    public function setRemoteAddress($remoteAddress)
    {
        $ipValidator = new Validate\IpAddress;
        if (!$ipValidator->isValid($remoteAddress)) {
            throw new Exception\InvalidArgumentException(
                'Remote Address validation errors: ' .
                implode(', ', $ipValidator->getMessages())
            );
        }
        $this->remoteAddress = $remoteAddress;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $userAgent
     * @return \Gass\BotInfo\Base
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
        return $this;
    }
}

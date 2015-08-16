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

use Gass\Exception;
use Gass\Adapter;

/**
 * Class for combining multiple BotInfo adapters
 *
 * @see         Gass\Adapter
 * @see         Gass\Exception
 * @author      Tom Chapman
 * @package     Gass\BotInfo
 */
class Multi extends Adapter\Multi implements BotInfoInterface
{
    /**
     * {@inheritdoc}
     *
     * @var string
     */
    protected $requiredClass = 'Gass\BotInfo\BotInfoInterface';

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getRemoteAddress()
    {
        throw new Exception\BadMethodCallException(__METHOD__ . ' cannot be called on ' . __CLASS__);
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getUserAgent()
    {
        throw new Exception\BadMethodCallException(__METHOD__ . ' cannot be called on ' . __CLASS__);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $remoteAddress
     * @return \Gass\BotInfo\Multi
     */
    public function setRemoteAddress($remoteAddress)
    {
        foreach ($this->adapters as $adapter) {
            $adapter->{__METHOD__}($remoteAddress);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $userAgent
     * @return \Gass\BotInfo\Multi
     */
    public function setUserAgent($userAgent)
    {
        foreach ($this->adapters as $adapter) {
            $adapter->{__METHOD__}($remoteAddress);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $userAgent [optional]
     * @param string $remoteAddress [optional]
     * @return boolean
     */
    public function isBot($userAgent = null, $remoteAddress = null)
    {
        foreach ($this->adapters as $adapter) {
            if (true === $adapter->{__METHOD__}($userAgent, $remoteAddress)) {
                return true;
            }
        }
        return false;
    }
}

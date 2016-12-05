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

namespace Gass\BotInfo;

use Gass\Adapter\Multi as AdapterMulti;
use Gass\Exception\BadMethodCallException;

/**
 * Class for combining multiple BotInfo adapters
 *
 * @see         Gass\Adapter\Multi
 * @see         Gass\Exception\BadMethodCallException
 * @author      Tom Chapman
 */
class Multi extends AdapterMulti implements BotInfoInterface
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
     * @throws BadMethodCallException
     * @return string
     */
    public function getRemoteAddress()
    {
        throw new BadMethodCallException(__METHOD__ . ' cannot be called on ' . __CLASS__);
    }

    /**
     * {@inheritdoc}
     *
     * @throws BadMethodCallException
     * @return string
     */
    public function getUserAgent()
    {
        throw new BadMethodCallException(__METHOD__ . ' cannot be called on ' . __CLASS__);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $remoteAddress
     * @return $this
     */
    public function setRemoteAddress($remoteAddress)
    {
        foreach ($this->getAdapters() as $adapter) {
            $adapter->{__FUNCTION__}($remoteAddress);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $userAgent
     * @return $this
     */
    public function setUserAgent($userAgent)
    {
        foreach ($this->getAdapters() as $adapter) {
            $adapter->{__FUNCTION__}($userAgent);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $userAgent [optional]
     * @param string $remoteAddress [optional]
     *
     * @return bool
     */
    public function isBot($userAgent = null, $remoteAddress = null)
    {
        foreach ($this->getAdapters() as $adapter) {
            if (true === $adapter->{__FUNCTION__}($userAgent, $remoteAddress)) {
                return true;
            }
        }
        return false;
    }
}

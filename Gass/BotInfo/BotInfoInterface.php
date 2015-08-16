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

/**
 * Interface all BotInfo adapters should conform to
 *
 * @see         Gass\Adapter
 * @author      Tom Chapman
 * @package     Gass\BotInfo
 */
interface BotInfoInterface extends Adapter\AdapterInterface
{
    /**
     * Returns whether or not the provided user-agent is a bot or not
     *
     * @param string $userAgent [optional]
     * @param string $remoteAddress [optional]
     * @return boolean
     */
    public function isBot($userAgent = null, $remoteAddress = null);

    /**
     * Get the remote user's ip address
     *
     * @return string
     */
    public function getRemoteAddress();

    /**
     * Get the user agent used in the class
     *
     * @return string
     */
    public function getUserAgent();

    /**
     * Sets the remote user's ip address
     *
     * @param string $remoteAddress
     * @return \Gass\BotInfo\BotInfoInterface
     */
    public function setRemoteAddress($remoteAddress);

    /**
     * Set the current user agent
     *
     * @param string $userAgent
     * @return \Gass\BotInfo\BotInfoInterface
     */
    public function setUserAgent($userAgent);
}

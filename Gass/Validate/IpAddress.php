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
 * @copyright   Copyright (c) 2011-2020 Tom Chapman (http://tom-chapman.uk/)
 * @license     BSD 3-clause "New" or "Revised" License
 * @link        http://github.com/chappy84/google-analytics-server-side
 */

namespace Gass\Validate;

use Gass\Exception\InvalidArgumentException;

/**
 * IP Address v4 Validator
 *
 * @author      Tom Chapman
 */
class IpAddress extends Base
{
    /**
     * Allow IPv4 option index
     *
     * @var string
     */
    const OPT_ALLOW_IPV4 = 'allowIPv4';

    /**
     * Allow IPv6 option index
     *
     * @var string
     */
    const OPT_ALLOW_IPV6 = 'allowIPv6';

    /**
     * Adapter options passed in as part of construct or setOption/s
     *
     * @var array
     */
    protected $options = array(
        self::OPT_ALLOW_IPV4 => true,
        self::OPT_ALLOW_IPV6 => true,
    );

    /**
     * {@inheritdoc}
     *
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options)
    {
        parent::setOptions($options);
        if (!$this->getOption(self::OPT_ALLOW_IPV4) && !$this->getOption(self::OPT_ALLOW_IPV6)) {
            throw new InvalidArgumentException('Cannot validate with all IP versions disabled');
        }
        return $this;
    }

    /**
     * Returns whether or not the value is valid
     *
     * @param mixed $value
     * @return bool
     */
    public function isValid($value)
    {
        $value = $this->setValue($value)->getValue();
        if (!is_string($value)) {
            $this->addMessage('The provided IP address must be a string.');
            return false;
        }
        $allowIPv4 = $this->getOption(self::OPT_ALLOW_IPV4);
        if ($allowIPv4 && false !== filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return true;
        }
        $allowIPv6 = $this->getOption(self::OPT_ALLOW_IPV6);
        if ($allowIPv6 && false !== filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return true;
        }

        // Generate correct failed validation message
        $version = '';
        if ($allowIPv4 && !$allowIPv6) {
            $version = 'v4';
        }
        if (!$allowIPv4 && $allowIPv6) {
            $version = 'v6';
        }
        $this->addMessage('"%value%" is an invalid IP' . $version . ' address', $value);
        return false;
    }
}

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
 * @copyright   Copyright (c) 2011-2017 Tom Chapman (http://tom-chapman.uk/)
 * @license     BSD 3-clause "New" or "Revised" License
 * @link        http://github.com/chappy84/google-analytics-server-side
 */

namespace Gass\BotInfo;

use Gass\Exception\BadMethodCallException;
use Gass\Exception\DomainException;
use Gass\Exception\InvalidArgumentException;
use Gass\Proxy\ProxyInterface;

/**
 * Proxy class for dealing with all BotInfo requests regardless of adapter
 *
 * @see         Gass\Exception\BadMethodCallException
 * @see         Gass\Exception\DomainException
 * @see         Gass\Exception\InvalidArgumentException
 * @see         Gass\Proxy\ProxyInterface
 * @author      Tom Chapman
 */
class BotInfo implements ProxyInterface
{
    /**
     * The current adapter in use
     *
     * @var Gass\BotInfo\BotInfoInterface
     */
    private $adapter;

    /**
     * Class Constructor
     *
     * @param array $options
     * @param string $adapter [optional] - can be provided in $options aswell
     */
    public function __construct(array $options = array(), $adapter = null)
    {
        if (null === $adapter) {
            $adapter = (isset($options['adapter'])) ? $options['adapter'] : 'BrowsCap';
            unset($options['adapter']);
        }
        $this->setAdapter($adapter);
        if (0 < func_num_args()) {
            $this->setOptions($options);
        }
    }

    /**
     * Call magic method
     *
     * @param string $name
     * @param array $arguments
     * @throws DomainException
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this->adapter, $name)) {
            return call_user_func_array(array($this->adapter, $name), $arguments);
        }
        throw new BadMethodCallException(
            'Method ' . get_class($this->adapter) . '::' . $name . ' does not exist.'
        );
    }

    /**
     * Sets the current adapter to use
     *
     * @param string|BotInfoInterface $adapter
     * @throws InvalidArgumentException
     * @return $this
     */
    public function setAdapter($adapter)
    {
        if (is_string($adapter)) {
            $adapterName = 'Gass\BotInfo\\' . ucfirst($adapter);
            $adapter = new $adapterName();
        }
        if ($adapter instanceof BotInfoInterface) {
            $this->adapter = $adapter;
            return $this;
        }
        throw new InvalidArgumentException(
            'The Gass\BotInfo adapter must implement Gass\BotInfo\BotInfoInterface.'
        );
    }

    /**
     * Returns the current Adapter
     *
     * @return BotInfoInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }
}

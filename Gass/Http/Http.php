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

namespace Gass\Http;

use Gass\Exception\BadMethodCallException;
use Gass\Exception\InvalidArgumentException;
use Gass\Exception\RuntimeException;

/**
 * Proxy class for dealing with all Http requests regardless of adapter
 *
 * @see         Gass\Exception\BadMethodCallException
 * @see         Gass\Exception\InvalidArgumentException
 * @see         Gass\Exception\RuntimeException
 * @author      Tom Chapman
 */
class Http
{
    /**
     * The current adapter in use
     *
     * @var string
     */
    private $adapter;

    /**
     * Singleton instance of Gass\Http
     *
     * @var Gass\Http
     * @static
     */
    protected static $instance;

    /**
     * Class Constructor
     *
     * @param array $options
     * @param string|\Gass\Http\HttpInterface $adapter [optional] - can be provided in $options aswell
     */
    private function __construct(array $options = array(), $adapter = null)
    {
        if (null === $adapter) {
            if (isset($options['adapter'])) {
                $adapter = $options['adapter'];
                unset($options['adapter']);
            } else {
                $adapter = extension_loaded('curl') ? 'Curl' : 'Stream';
            }
        }
        $this->setAdapter($adapter);
        if (0 < func_num_args()) {
            $this->setOptions($options);
        }
    }

    /**
     * Protect against class clone to ensure singleton anti-pattern
     *
     * @throws RuntimeException
     * @final
     */
    final public function __clone()
    {
        throw new RuntimeException('You cannot clone ' . __CLASS__);
    }

    /**
     * Returns the current instance of Gass\Http
     * Accepts the same parameters as __construct
     *
     * @see Gass\Http::__construct
     * @param array $options
     * @param string|\Gass\Http\HttpInterface $adapter
     * @return $this
     * @static
     */
    public static function getInstance(array $options = array(), $adapter = null)
    {
        $className = __CLASS__;
        if (static::$instance === null || !static::$instance instanceof $className) {
            static::$instance = new $className($options, $adapter);
        } elseif (0 < func_num_args()) {
            if ($adapter === null && !empty($options['adapter'])) {
                $adapter = $options['adapter'];
                unset($options['adapter']);
            }
            if ($adapter !== null) {
                static::$instance->setAdapter($adapter);
            }
            static::$instance->setOptions($options);
        }
        return static::$instance;
    }

    /**
     * Call magic method
     *
     * @param string $name
     * @param array $arguments
     * @throws Exception\BadMethodCallException
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
     * Call Static magic method
     *
     * @param string $name
     * @param array $arguments
     * @throws Exception\BadMethodCallException
     * @return mixed
     * @static
     */
    public static function __callStatic($name, $arguments)
    {
        $instance = static::getInstance();
        $adapter = $instance->getAdapter();
        if (method_exists($adapter, $name)) {
            return call_user_func_array(array($adapter, $name), $arguments);
        }
        throw new BadMethodCallException(
            'Method ' . get_class($adapter) . '::' . $name . ' does not exist.'
        );
    }

    /**
     * Sets the current adapter to use
     *
     * @param string $adapter
     * @throws InvalidArgumentException
     * @return $this
     */
    public function setAdapter($adapter)
    {
        if (is_string($adapter)) {
            $adapterName = 'Gass\Http\\' . ucfirst($adapter);
            $adapter = new $adapterName();
        }
        if ($adapter instanceof HttpInterface) {
            $this->adapter = $adapter;
            return $this;
        }
        throw new InvalidArgumentException('The Gass\Http adapter must implement Gass\Http\HttpInterface.');
    }

    /**
     * Returns the current adapter in use
     *
     * @return \Gass\Http\HttpInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }
}

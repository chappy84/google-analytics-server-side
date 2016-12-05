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

namespace Gass\Adapter;

use Gass\Exception\BadMethodCallException;
use Gass\Exception\DomainException;
use Gass\Exception\InvalidArgumentException;

/**
 * Class for combining multiple adapters
 *
 * @see         Gass\Exception\BadMethodCallException
 * @see         Gass\Exception\DomainException
 * @see         Gass\Exception\InvalidArgumentException
 * @author      Tom Chapman
 */
abstract class Multi implements AdapterInterface
{
    /**
     * Default class required for the adapters to implement
     */
    const DEFAULT_INTERFACE = 'Gass\Adapter\AdapterInterface';

    /**
     * Class required for the adapters to implement
     *
     * @var string
     */
    protected $requiredClass;

    /**
     * Adapters
     *
     * @var array
     */
    private $adapters = array();

    /**
     * Class level constructor
     *
     * @param array $adapters [optional]
     * @throws DomainException
     */
    public function __construct(array $adapters = array())
    {
        if (static::DEFAULT_INTERFACE != self::DEFAULT_INTERFACE
            && !is_subclass_of(static::DEFAULT_INTERFACE, self::DEFAULT_INTERFACE)
        ) {
            throw new DomainException(static::DEFAULT_INTERFACE . ' must implement ' . self::DEFAULT_INTERFACE);
        }
        if (empty($this->requiredClass)) {
            $this->requiredClass = static::DEFAULT_INTERFACE;
        }
        if ($this->requiredClass != static::DEFAULT_INTERFACE
            && !is_subclass_of($this->requiredClass, static::DEFAULT_INTERFACE)
        ) {
            throw new DomainException($this->requiredClass . ' must implement ' . static::DEFAULT_INTERFACE);
        }
        $this->setAdapters($adapters);
    }

    /**
     * Returns the current adapters
     *
     * @return array
     */
    public function getAdapters()
    {
        return $this->adapters;
    }

    /**
     * Returns the specified adapter
     *
     * @param string $name
     * @throws DomainException
     * @return AdapterInterface
     */
    public function getAdapter($name)
    {
        if (isset($this->adapters[$name])) {
            return $this->adapters[$name];
        }
        throw new DomainException($name . ' is not currently set as an adapter');
    }

    /**
     * Set the current adapters
     *
     * @param array $adapters
     * @return $this
     */
    public function setAdapters(array $adapters)
    {
        $this->adapters = array();
        foreach ($adapters as $name => $adapter) {
            $this->addAdapter($adapter, (is_string($name)) ? $name : null);
        }
        return $this;
    }

    /**
     * Add a specified adapter
     *
     * @param array $adapter
     * @param string $name
     * @throws InvalidArgumentException
     * @return $this
     */
    public function addAdapter($adapter, $name = null)
    {
        if (!$adapter instanceof $this->requiredClass) {
            throw new InvalidArgumentException(
                get_class($adapter) . ' does not implement ' . $this->requiredClass
            );
        }
        if (!empty($name) && !is_string($name)) {
            throw new InvalidArgumentException('$name must be a string');
        }
        if (empty($name)) {
            $name = get_class($adapter);
        }
        $this->adapters[$name] = $adapter;
        return $this;
    }

    /**
     * Reset the stored list of adapters
     *
     * @return $this
     */
    public function resetAdapters()
    {
        return $this->setAdapters(array());
    }

    /**
     * {@inheritdoc}
     *
     * @param array $options
     * @throws BadMethodCallException
     */
    public function setOptions(array $options)
    {
        throw new BadMethodCallException(__METHOD__ . ' cannot be called on ' . get_class($this));
    }

    /**
     * {@inheritdoc}
     *
     * @param string $name
     * @param mixed $value
     * @throws BadMethodCallException
     */
    public function setOption($name, $value)
    {
        throw new BadMethodCallException(__METHOD__ . ' cannot be called on ' . get_class($this));
    }

    /**
     * {@inheritdoc}
     *
     * @throws BadMethodCallException
     */
    public function getOptions()
    {
        throw new BadMethodCallException(__METHOD__ . ' cannot be called on ' . get_class($this));
    }

    /**
     * {@inheritdoc}
     *
     * @param string $name
     * @throws BadMethodCallException
     */
    public function getOption($name)
    {
        throw new BadMethodCallException(__METHOD__ . ' cannot be called on ' . get_class($this));
    }
}

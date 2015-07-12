<?php
/**
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * Google Analytics Server Side is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or any later
 * version.
 *
 * The GNU General Public License can be found at:
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * N/B: This code is nether written or endorsed by Google or any of it's
 *      employees. "Google" and "Google Analytics" are trademarks of
 *      Google Inc. and it's respective subsidiaries.
 *
 * @copyright   Copyright (c) 2011-2015 Tom Chapman (http://tom-chapman.uk/)
 * @license     http://www.gnu.org/copyleft/gpl.html  GPL
 * @author      Tom Chapman
 * @link        http://github.com/chappy84/google-analytics-server-side
 * @category    GoogleAnalyticsServerSide
 * @package     Gass
 * @subpackage  Adapter
 */

/**
 * @namespace
 */
namespace Gass\Adapter;

use Gass\Exception;

/**
 * Class for combining multiple adapters
 *
 * @uses        Gass\Exception
 * @copyright   Copyright (c) 2011-2015 Tom Chapman (http://tom-chapman.uk/)
 * @license     http://www.gnu.org/copyleft/gpl.html  GPL
 * @author      Tom Chapman
 * @category    GoogleAnalyticsServerSide
 * @package     Gass
 * @subpackage  Adapter
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
     * @param array $cacheOptions
     */
    public function __construct(array $adapters = array())
    {
        if (empty($this->requiredClass)) {
            $this->requiredClass = self::DEFAULT_INTERFACE;
        }
        if ($this->requiredClass != self::DEFAULT_INTERFACE
                && !is_subclass_of($this->requiredClass, self::DEFAULT_INTERFACE)) {
            throw new Exception\DomainException($this->requiredClass . ' must implement ' . self::DEFAULT_INTERFACE);
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
     * @return Gass\Adapter\AdapterInterface
     * @throws Gass\Exception\DomainException
     */
    public function getAdapter($name)
    {
        if (isset($this->adapters[$name])) {
            return $this->adapters[$name];
        }
        throw new Exception\DomainException($name . ' is not currently set as an adapter');
    }

    /**
     * Set the current adapters
     *
     * @param array $adapters
     * @return Gass\Adapter\Multi
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
     * @return Gass\Adapter\Multi
     * @throws Gass\Exception\InvalidArgumentException
     */
    public function addAdapter($adapter, $name = null)
    {
        if (!$adapter instanceof $this->requiredClass) {
            throw new Exception\InvalidArgumentException(
                get_class($adapter) . ' does not implement ' . $this->requiredClass
            );
        }
        if (!empty($name) && !is_string($name)) {
            throw new Exception\InvalidArgumentException('$name must be a string');
        } elseif (empty($name)) {
            $name = get_class($adapter);
        }
        $this->adapters[$name] = $adapter;
        return $this;
    }

    /**
     * Reset the stored list of adapters
     *
     * @return Gass\Adapter\Multi
     */
    public function resetAdapters()
    {
        return $this->setAdapters(array());
    }

    /**
     * {@inheritdoc}
     *
     * @param array $options
     * @throws Gass\Exception\BadMethodCallException
     */
    public function setOptions(array $options)
    {
        throw new Exception\BadMethodCallException(__METHOD__ . ' cannot be called on ' . __CLASS__);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $name
     * @param mixed $value
     * @throws Gass\Exception\BadMethodCallException
     */
    public function setOption($name, $value)
    {
        throw new Exception\BadMethodCallException(__METHOD__ . ' cannot be called on ' . __CLASS__);
    }

    /**
     * {@inheritdoc}
     *
     * @throws Gass\Exception\BadMethodCallException
     */
    public function getOptions()
    {
        throw new Exception\BadMethodCallException(__METHOD__ . ' cannot be called on ' . __CLASS__);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $name
     * @throws Gass\Exception\BadMethodCallException
     */
    public function getOption($name)
    {
        throw new Exception\BadMethodCallException(__METHOD__ . ' cannot be called on ' . __CLASS__);
    }
}

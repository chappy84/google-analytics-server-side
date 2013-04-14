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
 * @copyright   Copyright (c) 2011-2013 Tom Chapman (http://tom-chapman.co.uk/)
 * @license     http://www.gnu.org/copyleft/gpl.html  GPL
 * @author      Tom Chapman
 * @link        http://github.com/chappy84/google-analytics-server-side
 * @category    GoogleAnalyticsServerSide
 * @package     GoogleAnalyticsServerSide
 * @subpackage  Http
 */

/**
 * @namespace
 */
namespace Gass\Http;

use Gass\Exception;

/**
 * Proxy class for dealing with all Http requests regardless of adapter
 *
 * @uses        Gass\Exception
 * @copyright   Copyright (c) 2011-2013 Tom Chapman (http://tom-chapman.co.uk/)
 * @license     http://www.gnu.org/copyleft/gpl.html  GPL
 * @author      Tom Chapman
 * @category    GoogleAnalyticsServerSide
 * @package     GoogleAnalyticsServerSide
 * @subpackage  Http
 */
class Http
{

    /**
     * The current adapter in use
     *
     * @var string
     * @access private
     */
    private $adapter;

    /**
     * Singleton instance of Gass\Http
     *
     * @var Gass\Http
     * @static
     * @access protected
     */
    protected static $instance;

    /**
     * Class Constructor
     *
     * @param array $options
     * @param string|Gass\Http\HttpInterface $adapter [optional] - can be provided in $options aswell
     * @access protected
     */
    public function __construct(array $options = array(), $adapter = null)
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
     * @throws Gass\Exception\RuntimeException
     * @final
     * @access public
     */
    final public function __clone()
    {
        throw new Exception\RuntimeException('You cannot clone '.__CLASS__);
    }

    /**
     * Returns the current instance of Gass\Http
     * Accepts the same parameters as __construct
     *
     * @see Gass\Http::__construct
     * @param array $options
     * @param string|Gass\Http\HttpInterface $adapter
     * @return Gass\Http
     * @static
     * @access public
     */
    public static function getInstance(array $options = array(), $adapter = null)
    {
        $className = __CLASS__;
        if (self::$instance === null || !self::$instance instanceof $className) {
            self::$instance = new $className($options, $adapter);
        } elseif (0 < func_num_args()) {
            if ($adapter === null && !empty($options['adapter'])) {
                $adapter = $options['adapter'];
                unset($options['adapter']);
            }
            if ($adapter !== null) {
                self::$instance->setAdapter($adapter);
            }
            self::$instance->setOptions($options);
        }
        return self::$instance;
    }

    /**
     * Call magic method
     *
     * @param string $name
     * @param array $arguments
     * @throws Exception\BadMethodCallException
     * @return mixed
     * @access public
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this->adapter, $name)) {
            return call_user_func_array(array($this->adapter, $name), $arguments);
        }
        throw new Exception\BadMethodCallException(
            __METHOD__.' is not an available method in '.get_class($this->adapter)
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
     * @access public
     */
    public static function __callStatic($name, $arguments)
    {
        $instance = self::getInstance();
        $adapter = $instance->getAdapter();
        if (method_exists($adapter, $name)) {
            return call_user_func_array(array($adapter, $name), $arguments);
        }
        throw new Exception\BadMethodCallException(__METHOD__.' is not an available method in '.get_class($adapter));
    }

    /**
     * Sets the current adapter to use
     *
     * @param string $adapter
     * @throws Gass\Exception\InvalidArgumentException
     * @return Gass\Http
     * @access public
     */
    public function setAdapter($adapter)
    {
        if (is_string($adapter)) {
            $adapterName = 'Gass\Http\\'.ucfirst($adapter);
            $adapter = new $adapterName();
        }
        if ($adapter instanceof HttpInterface) {
            $this->adapter = $adapter;
            return $this;
        }
        throw new Exception\InvalidArgumentException('The Gass\Http adapter must implement Gass\Http\HttpInterface.');
    }

    /**
     * Returns the current adapter in use
     *
     * @return Gass\Http\HttpInterface
     * @access public
     */
    public function getAdapter()
    {
        return $this->adapter;
    }
}

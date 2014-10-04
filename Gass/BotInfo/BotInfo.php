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
 * @copyright   Copyright (c) 2011-2014 Tom Chapman (http://tom-chapman.uk/)
 * @license     http://www.gnu.org/copyleft/gpl.html  GPL
 * @author      Tom Chapman
 * @link        http://github.com/chappy84/google-analytics-server-side
 * @category    GoogleAnalyticsServerSide
 * @package     Gass
 * @subpackage  BotInfo
 */

/**
 * @namespace
 */
namespace Gass\BotInfo;

use Gass\Exception;
use Gass\Proxy;

/**
 * Proxy class for dealing with all BotInfo requests regardless of adapter
 *
 * @uses        Gass\Exception
 * @uses        Gass\Proxy
 * @copyright   Copyright (c) 2011-2014 Tom Chapman (http://tom-chapman.uk/)
 * @license     http://www.gnu.org/copyleft/gpl.html  GPL
 * @author      Tom Chapman
 * @category    GoogleAnalyticsServerSide
 * @package     Gass
 * @subpackage  BotInfo
 */
class BotInfo implements Proxy\ProxyInterface
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
     * @throws Gass\Exception\DomainException
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if ($this->adapter instanceof BotInfoInterface) {
            if (method_exists($this->adapter, $name)) {
                return call_user_func_array(array($this->adapter, $name), $arguments);
            }
            throw new Exception\BadMethodCallException(
                'Method '.get_class($this->adapter).'::'.$name.' does not exist.'
            );
        }
        throw new Exception\DomainException('Adapter has not been set. Please set an adapter before calling '.$name);
    }

    /**
     * Sets the current adapter to use
     *
     * @param string|Gass\BotInfo\BotInfoInterface $adapter
     * @throws Gass\Exception\InvalidArgumentException
     * @return Gass\BotInfo
     */
    public function setAdapter($adapter)
    {
        if (is_string($adapter)) {
            $adapterName = 'Gass\BotInfo\\'.ucfirst($adapter);
            $adapter = new $adapterName();
        }
        if ($adapter instanceof BotInfoInterface) {
            $this->adapter = $adapter;
            return $this;
        }
        throw new Exception\InvalidArgumentException(
            'The Gass\BotInfo adapter must implement Gass\BotInfo\BotInfoInterface.'
        );
    }

    /**
     * @return the $adapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }
}

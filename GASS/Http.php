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
 * 		employees. "Google" and "Google Analytics" are trademarks of
 * 		Google Inc. and it's respective subsidiaries.
 *
 * @copyright	Copyright (c) 2011 Tom Chapman (http://tom-chapman.co.uk/)
 * @license		http://www.gnu.org/copyleft/gpl.html  GPL
 * @author 		Tom Chapman
 * @link		http://github.com/chappy84/google-analytics-server-side
 */
class GASS_Http
{

	/**
	 * The current adapter in use
	 *
	 * @var string
	 * @access private
	 */
	private $adapter;


	/**
	 * Singleton instance of GASS_Http
	 *
	 * @var GASS_Http
	 * @static
	 * @access protected
	 */
	protected static $instance;


	/**
	 * Class Constructor
	 *
	 * @param array $options
	 * @param string $adapter [optional] - can be provided in $options aswell
	 * @access protected
	 */
	public function __construct(array $options = array(), $adapter = null) {
		if (null === $adapter) {
			$adapter = (isset($options['adapter'])) ? $options['adapter'] : 'Stream';
			unset($options['adapter']);
		}
		$this->setAdapter($adapter);
		if (0 < func_num_args()) {
			$this->setOptions($options);
		}
	}


	/**
	 * @throws RuntimeException
	 */
	final public function __clone() {
		throw new RuntimeException('You cannot clone '.__CLASS__);
	}


	/**
	 * Returns the current instance of GASS_Http
	 * Accepts the same parameters as __construct
	 *
	 * @return GASS_Http
	 * @access public
	 * @static
	 * @see GASS_Http::__construct
	 */
	public static function getInstance(array $options = array(), $adapter = null) {
		$className = __CLASS__;
		if (self::$instance === null || !self::$instance instanceof $className) {
			self::$instance = new $className($options, $adapter);
		}
		return self::$instance;
	}


	/**
	 * Call magic method
	 *
	 * N/B: Cannot implement __callStatic due to PHP 5.2 backwards compatability
	 *
	 * @param string $name
	 * @param array $arguments
	 * @throws DomainException
	 * @return mixed
	 */
	public function __call($name, $arguments) {
		if ($this->adapter instanceof GASS_Http_Interface) {
			return call_user_func_array(array($this->adapter, $name), $arguments);
		}
		throw new DomainException('Adapter has not been set. Please set an adapter before calling '.$name);
	}


	/**
	 * Sets the current adapter to use
	 *
	 * @param string $adapter
	 * @access public
	 */
	public function setAdapter($adapter) {
		if (is_string($adapter)) {
			$adapterName = 'GASS_Http_'.ucfirst($adapter);
			$adapter = new $adapterName();
		}
		if ($adapter instanceof GASS_Http_Interface) {
			$this->adapter = $adapter;
			return $this;
		}
		throw new InvalidArgumentException('The GASS_Http adapter must implement GASS_Http_Interface.');
	}


	/**
	 * Returns the current adapter in use
	 *
	 * @return GASS_Http_Interface
	 * @access public
	 */
	public function getAdapter() {
		return $this->adapter;
	}
}
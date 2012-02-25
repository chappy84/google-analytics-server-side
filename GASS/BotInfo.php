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
class GASS_BotInfo
	implements GASS_Interface
{

	/**
	 * The current adapter in use
	 *
	 * @var GASS_BotInfo_Interface
	 * @access private
	 */
	private $adapter;


	/**
	 * Class Constructor
	 *
	 * @param array $options
	 * @param string $adapter [optional] - can be provided in $options aswell
	 * @access public
	 */
	public function __construct(array $options = array(), $adapter = null) {
		if (null === $adapter) {
			$adapter = (isset($options['adapter'])) ? $options['adapter'] : 'BrowserCap';
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
	 * @access public
	 */
	public function __call($name, $arguments) {
		if ($this->adapter instanceof GASS_BotInfo_Interface) {
			return call_user_func_array(array($this->adapter, $name), $arguments);
		}
		throw new DomainException('Adapter has not been set. Please set an adapter before calling '.$name);
	}


	/**
	 * Sets the current adapter to use
	 *
	 * @param string|GASS_BotInfo_Interface $adapter
	 * @throws InvalidArgumentException
	 * @return GASS_BotInfo
	 * @access public
	 */
	public function setAdapter($adapter) {
		if (is_string($adapter)) {
			$adapterName = 'GASS_BotInfo_'.ucfirst($adapter);
			$adapter = new $adapterName();
		}
		if ($adapter instanceof GASS_BotInfo_Interface) {
			$this->adapter = $adapter;
			return $this;
		}
		throw new InvalidArgumentException('The GASS_BotInfo adapter must implement GASS_BotInfo_Interface.');
	}


	/**
	 * @return the $adapter
	 * @access public
	 */
	public function getAdapter() {
		return $this->adapter;
	}
}
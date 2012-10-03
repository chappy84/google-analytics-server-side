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
 * @copyright	Copyright (c) 2011-2012 Tom Chapman (http://tom-chapman.co.uk/)
 * @license		http://www.gnu.org/copyleft/gpl.html  GPL
 * @author 		Tom Chapman
 * @link		http://github.com/chappy84/google-analytics-server-side
 * @category	GoogleAnalyticsServerSide
 * @package		GoogleAnalyticsServerSide
 * @subpackage	BotInfo
 */

/**
 * @namespace
 */
namespace GASS\BotInfo;
use GASS\Adapter;
use GASS\Exception;
use GASS\Validate;


/**
 * Base class of all BotInfo adapters
 *
 * @uses		GASS\Validate
 * @copyright	Copyright (c) 2011-2012 Tom Chapman (http://tom-chapman.co.uk/)
 * @license		http://www.gnu.org/copyleft/gpl.html  GPL
 * @author 		Tom Chapman
 * @category	GoogleAnalyticsServerSide
 * @package		GoogleAnalyticsServerSide
 * @subpackage	BotInfo
 */
abstract class Base extends Adapter\Base implements BotInfoInterface
{

	/**
	 * The remote user's ip address
	 *
	 * @var string
	 * @access protected
	 */
	protected $remoteAddress;


	/**
	 * The current user-agent
	 *
	 * @var string
	 * @access protected
	 */
	protected $userAgent;


	/**
	 * Class options
	 *
	 * @var array
	 * @access protected
	 */
	protected $options = array();


	/**
	 * {@inheritdoc}
	 *
	 * @return string
	 * @access public
	 */
	public function getRemoteAddress() 
	{
		return $this->remoteAddress;
	}


	/**
	 * {@inheritdoc}
	 *
	 * @return string
	 * @access public
	 */
	public function getUserAgent() 
	{
		return $this->userAgent;
	}


	/**
	 * {@inheritdoc}
	 *
	 * @param string $remoteAddress
	 * @return GASS\BotInfo\Base
	 * @access public
	 */
	public function setRemoteAddress($remoteAddress) 
	{
		$ipValidator = new Validate\IpAddress();
		if (!$ipValidator->isValid($remoteAddress)) {
			throw new Exception\InvalidArgumentException('Remote Address validation errors: '.implode(', ', $ipValidator->getMessages()));
		}
		$this->remoteAddress = $remoteAddress;
		return $this;
	}


	/**
	 * {@inheritdoc}
	 *
	 * @param string $userAgent
	 * @return GASS\BotInfo\Base
	 * @access public
	 */
	public function setUserAgent($userAgent) 
	{
		$this->userAgent = $userAgent;
		return $this;
	}
}
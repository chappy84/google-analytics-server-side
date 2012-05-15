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
 * @subpackage	Http
 */

/**
 * @namespace
 */
namespace GASS\Http;
use GASS\Exception;

/**
 * cURL adapter for Http
 *
 * @copyright	Copyright (c) 2011-2012 Tom Chapman (http://tom-chapman.co.uk/)
 * @license		http://www.gnu.org/copyleft/gpl.html  GPL
 * @author 		Tom Chapman
 * @category	GoogleAnalyticsServerSide
 * @package		GoogleAnalyticsServerSide
 * @subpackage	Http
 */
class Curl
	extends Base
{

	/**
	 * Curl instance
	 *
	 * @var resource
	 * @access protected
	 */
	protected $curl;


	/**
	 * Class options
	 *
	 * @var array
	 * @access protected
	 */
	protected $options = array(	CURLOPT_RETURNTRANSFER	=> 1
							,	CURLOPT_CONNECTTIMEOUT	=> 5
							,	CURLOPT_FOLLOWLOCATION	=> true);


	/**
	 * Class Constructor
	 *
	 * @param array $options
	 * @throws GASS\Exception\RuntimeException
	 * @access public
	 */
	public function __construct(array $options = array()) {
		if (!extension_loaded('curl')) {
			throw new Exception\RuntimeException('cURL PHP extension is not loaded.');
		}
		parent::__construct($options);
	}


	/**
	 * {@inheritdoc}
	 *
	 * @param mixed $index [optional]
	 * @return mixed
	 * @access public
	 */
	public function getInfo($index = null) {
		if (is_resource($this->curl)) {
			if ($index === null) {
				$index = 0;
			}
			return curl_getinfo($this->curl, $index);
		}
		throw new Exception\DomainException('A cURL request has not been made yet.');
	}


	/**
	 * {@inheritdoc}
	 *
	 * @param string $url
	 * @return GASS\Http\Curl
	 * @access public
	 */
	public function setUrl($url) {
		return $this->setOption(CURLOPT_URL, $url);
	}


	/**
	 * Closes the curl connection if one is present
	 *
	 * @return GASS\Http\Curl
	 * @access protected
	 */
	protected function close() {
		if (is_resource($this->curl)){
			curl_close($this->curl);
		}
		$this->curl = null;
		return $this;
	}


	/**
	 * {@inheritdoc}
	 *
	 * @param string $url
	 * @param array $options
	 * @return GASS\Http\Curl
	 * @access public
	 */
	public function request($url = null, array $options = array()) {

		parent::request($url, $options);

		$this->close();
		$this->curl = curl_init();

		if (null !== ($userAgent = $this->getUserAgent())) {
			curl_setopt($this->curl, CURLOPT_USERAGENT, $userAgent);
		}
		$extraHeaders = array();
		if (null !== ($acceptedLanguage = $this->getAcceptLanguage())) {
			$extraHeaders[] = 'Accepts-Language: '.$acceptedLanguage;
		}
		if (null !== ($remoteAddress = $this->getRemoteAddress())) {
			$extraHeaders[] = 'X-Forwarded-For: '.$remoteAddress;
		}
		if (!empty($extraHeaders)) {
			curl_setopt($this->curl, CURLOPT_HTTPHEADER, $extraHeaders);
		}

		$extraCurlOptions = $this->getOptions();
		if (!empty($extraCurlOptions) && false === curl_setopt_array($this->curl, $extraCurlOptions)) {
			throw new Exception\UnexpectedValueException('One of the extra curl options specified is invalid. Error: '.curl_error($this->curl));
		}

		if (false === ($response = curl_exec($this->curl))) {
			throw new Exception\RuntimeException('Source could not be retrieved. Error: '.curl_error($this->curl));
		}

		$statusCode = $this->getInfo(CURLINFO_HTTP_CODE);
		$this->checkResponseCode($statusCode);

		$this->setResponse($response);

		return $this;
	}


	/**
	 * Class Destructor
	 *
	 * @access public
	 */
	public function __destruct() {
		$this->close();
	}
}
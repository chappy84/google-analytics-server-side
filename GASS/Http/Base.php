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
abstract class GASS_Http_Base
	extends GASS_Adapter_Base
	implements GASS_Http_Interface
{

	/**
	 * The Accepted-Language for the sent HTTP headers
	 *
	 * @var string
	 * @access private
	 */
	private $acceptLanguage;


	/**
	 * The IP address sent in the X-Forwarded-For header
	 *
	 * @var string
	 * @access private
	 */
	private $remoteAddress;


	/**
	 * Response to the http request
	 *
	 * @var mixed
	 * @access private
	 */
	private $response;


	/**
	 * The User-Agent for the sent HTTP headers
	 *
	 * @var string
	 * @access private
	 */
	private $userAgent;


	/* (non-PHPdoc)
	 * @see GASS_Http_Interface::getAcceptLanguage()
	 */
	public function getAcceptLanguage() {
		return $this->acceptLanguage;
	}


	/* (non-PHPdoc)
	 * @see GASS_Http_Interface::getRemoteAddress()
	 */
	public function getRemoteAddress() {
		return $this->remoteAddress;
	}


	/* (non-PHPdoc)
	 * @see GASS_Http_Interface::getResponse()
	 */
	public function getResponse() {
		return $this->response;
	}


	/* (non-PHPdoc)
	 * @see GASS_Http_Interface::getUserAgent()
	 */
	public function getUserAgent() {
		return $this->userAgent;
	}


	/* (non-PHPdoc)
	 * @see GASS_Http_Interface::setAcceptLanguage()
	 */
	public function setAcceptLanguage($acceptLanguage) {
		if (1 !== preg_match('/^([a-z]{2})(-[a-z]{2})??$/i', $acceptLanguage)) {
			throw new InvalidArgumentException('The Accepted language must be composed of internationally recognised language iso codes.');
		}
		$this->acceptLanguage = $acceptLanguage;
		return $this;
	}


	/* (non-PHPdoc)
	 * @see GASS_Http_Interface::setRemoteAddress()
	 */
	public function setRemoteAddress($remoteAddress) {
		if (1 !== preg_match('/^(\d{1,3}\.){3}\d{1,3}$/', $remoteAddress)) {
			throw new InvalidArgumentException('The Remote Address must be an IP address.');
		}
		$this->remoteAddress = $remoteAddress;
		return $this;
	}


	/* (non-PHPdoc)
	 * @see GASS_Http_Interface::setResponse()
	 */
	public function setResponse($response) {
		$this->response = $response;
		return $this;
	}


	/* (non-PHPdoc)
	 * @see GASS_Http_Interface::setUserAgent()
	 */
	public function setUserAgent($userAgent) {
		$this->userAgent = $userAgent;
		return $this;
	}


	/**
	 * Checks the return code and throws an exception if an issue with the response
	 *
	 * @param integer $code
	 * @throws InvalidArgumentException
	 * @throws RuntimeException
	 * @access protected
	 */
	protected function checkResponseCode($code) {
		if (!is_numeric($code)) {
			throw new InvalidArgumentException('HTTP Status Code must be numeric.');
		}
		switch ($code) {
			case '204':
				$message = 'No Content';
				break;
			case '205':
				$message = 'Reset Content';
				break;
			case '206':
				$message = 'Partial Content';
				break;
			case '207':
				$message = 'Multi-Status';
				break;
			case '400':
				$message = 'Bad Request';
				break;
			case '401':
				$message = 'Unauthorised Request';
				break;
			case '402':
				$message = 'Payment Required';
				break;
			case '403':
				$message = 'Forbidden';
				break;
			case '404':
				$message = 'Not Found';
				break;
			case '405':
				$message = 'Method Not Allowed';
				break;
			case '406':
				$message = 'Not Acceptable';
				break;
			case '407':
				$message = 'Proxy Authentication Required';
				break;
			case '408':
				$message = 'Request Timeout';
				break;
			case '409':
				$message = 'Conflict';
				break;
			case '410':
				$message = 'Gone';
				break;
			case '411':
				$message = 'Length Required';
				break;
			case '412':
				$message = 'Precondition Failed';
				break;
			case '413':
				$message = 'Request Entity Too Large';
				break;
			case '414':
				$message = 'Request-URI Too Long';
				break;
			case '415':
				$message = 'Unsupported Media Type';
				break;
			case '416':
				$message = 'Request Range Not Satisfiable';
				break;
			case '417':
				$message = 'Expectation Failed';
				break;
			case '418':
				$message = 'I\'m a Teapot';
				break;
			case '422':
				$message = 'Unprocessable Entity (WebDAV)';
				break;
			case '423':
				$message = 'Locked (WebDAV)';
				break;
			case '424':
				$message = 'Failed Dependancy (WebDAV)';
				break;
			case '425':
				$message = 'Unordered Collection';
				break;
			case '426':
				$message = 'Upgrade Required';
				break;
			case '444':
				$message = 'No Response';
				break;
			case '449':
				$message = 'Retry With';
				break;
			case '450':
				$message = 'Blocked by Windows Parental Controls';
				break;
			case '499':
				$message = 'Client Closed Request';
				break;
			case '500':
				$message = 'Internal Server Error';
				break;
			case '501':
				$message = 'Not Implemented';
				break;
			case '502':
				$message = 'Bad Gateway';
				break;
			case '503':
				$message = 'Service Unavailable';
				break;
			case '504':
				$message = 'Gateway Timeout';
				break;
			case '505':
				$message = 'HTTP Version Not Supported';
				break;
			case '506':
				$message = 'Variant Also Negotiates';
				break;
			case '507':
				$message = 'Insufficient Storage (WebDAV)';
				break;
			case '509':
				$message = 'Bandwidth Limit Exceeded';
				break;
			case '510':
				$message = 'Not Exceeded';
				break;
			default:
		}
		if (isset($message)) {
			throw new RuntimeException($message, $code);
		}
	}


	/**
	 * Makes a request with either the existing options set or the ones provided
	 *
	 * @param string $url
	 * @param array $options
	 * @return GASS_Http_Base
	 */
	public function request($url = null, array $options = array()) {
		if ($url !== null) {
			$this->setUrl($url);
		}
		if (is_array($options) && !empty($options)) {
			$this->setOptions($options);
		}
		return $this;
	}
}
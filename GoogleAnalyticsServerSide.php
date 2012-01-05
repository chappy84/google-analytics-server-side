<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'core.php';
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
 * @version		0.7.0 Beta
 * @example		$gass = new GoogleAnalyticsServerSide();
 *	    		$gass->setAccount('UA-XXXXXXX-X')
 *					 ->createPageView();
 */
class GoogleAnalyticsServerSide
{

	/**
	 * The path the cookie will be available to.
	 *
	 * @var string
	 */
	const COOKIE_PATH = '/';


	/**
	 * Location of the google analytics gif
	 *
	 * @var string
	 */
	const GIF_URL = 'http://www.google-analytics.com/__utm.gif';


	/**
	 * Location of the current JS file
	 *
	 * @var string
	 */
	const JS_URL = 'http://www.google-analytics.com/ga.js';


	/**
	 * Google Analytics Tracker Version
	 *
	 * @var string
	 * @access private
	 */
	private $version = '5.2.2';


	/**
	 * Browser User Agent
	 *
	 * @var string
	 * @access private
	 */
	private $userAgent;


	/**
	 * Accept Language
	 *
	 * @var string
	 * @access private
	 */
	private $acceptLanguage = 'en';


	/**
	 * Server Name
	 *
	 * @var string
	 * @access private
	 */
	private $serverName;


	/**
	 * The User's IP Address
	 *
	 * @var string
	 * @access private
	 */
	private $remoteAddress;


	/**
	 * Google Analytics Account ID for the site
	 * value for utmac
	 *
	 * @var string
	 * @access private
	 */
	private $account;


	/**
	 * Document Referer
	 * value for utmr
	 *
	 * @var string
	 * @access private
	 */
	private $documentReferer;


	/**
	 * Documment Path
	 * value for utmp
	 *
	 * @var string
	 * @access public
	 */
	private $documentPath;


	/**
	 * Title of the current page
	 *
	 * @var string
	 * @access private
	 */
	private $pageTitle;


	/**
	 * Information related to the event
	 *
	 * @var array
	 * @access private
	 */
	private $event = array();


	/**
	 * Whether the event is a non-interaction or not
	 *
	 * @var boolean
	 * @access private
	 */
	private $nonInteraction = false;


	/**
	 * Data for the custom variables
	 *
	 * @var array
	 * @access private
	 */
	private $customVariables = array();


	/**
	 * CharacterSet the displayed page is encoded in.
	 *
	 * @var string
	 * @access private
	 */
	private $charset = 'UTF-8';


	/**
	 * Whether or not to send the cookies when send
	 *
	 * @var boolean
	 * @access private
	 */
	private $sendCookieHeaders = true;


	/**
	 * Contains all the details of the analytics cookies
	 *
	 * @var array
	 * @access private
	 */
	private $cookies = array(	'__utma'	=> null
							,	'__utmb'	=> null
							,	'__utmc'	=> null
							,	'__utmz'	=> null);


	/**
	 * Class to check if the current request is a bot or not
	 *
	 * @var null|GASS_BotInfo
	 * @access private
	 */
	private $botInfo;


	/**
	 * Options to pass to GASS_Http
	 *
	 * @var null|array|GASS_Http_Interface
	 * @access private
	 */
	private $http;


	/**
	 * Class Level Constructor
	 * Sets all the variables it can from the request headers received from the Browser
	 *
	 * @param array $options
	 * @throws InvalidArgumentException
	 * @access public
	 */
	public function __construct(array $options = array()) {
		if (!is_array($options)) {
			throw new InvalidArgumentException('Argument $options must be an array.');
		}
		if (isset($_SERVER['SERVER_NAME']) && !empty($_SERVER['SERVER_NAME'])) {
			$this->setServerName($_SERVER['SERVER_NAME']);
		}
		if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) {
			$this->setRemoteAddress($_SERVER['REMOTE_ADDR']);
		}
		if (isset($_SERVER['REQUEST_URI']) && !empty($_SERVER['REQUEST_URI'])) {
			$this->setDocumentPath($_SERVER['REQUEST_URI']);
		}
		if (isset($_SERVER['HTTP_USER_AGENT']) && !empty($_SERVER['HTTP_USER_AGENT'])) {
			$this->setUserAgent($_SERVER['HTTP_USER_AGENT']);
		}
		if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
			$this->setDocumentReferer($_SERVER['HTTP_REFERER']);
		}
		if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && !empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			$this->setAcceptLanguage($_SERVER['HTTP_ACCEPT_LANGUAGE']);
		}
		foreach ($this->getCookies() as $name => $value) {
			if (isset($_COOKIE[$name]) && !empty($_COOKIE[$name])) {
				$this->setCookie($name, $_COOKIE[$name], false);
			}
		}
		$this->setOptions($options);
		$this->setLatestVersionFromJs();
	}


	/**
	 * @return string
	 * @access public
	 */
	public function getVersion() {
		return $this->version;
	}


	/**
	 * @return string
	 * @access public
	 */
	public function getUserAgent() {
		return $this->userAgent;
	}


	/**
	 * @return string
	 * @access public
	 */
	public function getAcceptLanguage() {
		return $this->acceptLanguage;
	}


	/**
	 * @return string
	 * @access public
	 */
	public function getServerName() {
		return $this->serverName;
	}


	/**
	 * @return string
	 * @access public
	 */
	public function getRemoteAddress() {
		return $this->remoteAddress;
	}


	/**
	 * @return string
	 * @access public
	 */
	public function getAccount() {
		return $this->account;
	}


	/**
	 * @return string
	 * @access public
	 */
	public function getDocumentReferer() {
		return $this->documentReferer;
	}


	/**
	 * @return string
	 * @access public
	 */
	public function getDocumentPath() {
		return $this->documentPath;
	}


	/**
	 * @return string
	 * @access public
	 */
	public function getPageTitle() {
		return $this->pageTitle;
	}


	/**
	 * @return string
	 * @access public
	 */
	public function getEvent() {
		return $this->event;
	}


	/**
	 * @return string
	 * @access public
	 */
	public function getCustomVariables() {
		return $this->customVariables;
	}


	/**
	 * @return string
	 * @access public
	 */
	public function getCharset() {
		return $this->charset;
	}


	/**
	 * @return null|GASS_BotInfo
	 * @access public
	 */
	public function getBotInfo() {
		return $this->botInfo;
	}


	/**
	 * @return null|array|GASS_Http_Interface
	 * @access public
	 */
	public function getHttp() {
		return $this->http;
	}


	/**
	 * Gets a specific option
	 *
	 * @param string $name
	 * @throws OutOfRangeException
	 * @return mixed
	 * @access public
	 */
	public function getOption($name) {
		$methodName = 'get'.ucfirst($name);
		if (method_exists($this, $methodName)) {
			$reflectionMethod = new ReflectionMethod($this, $methodName);
			if ($reflectionMethod->isPublic()) {
				return $this->$methodName();
			}
		}
		throw new OutOfRangeException($name.' is not an available option.');
	}


	/**
	 * @param string $version
	 * @return GoogleAnalyticsServerSide
	 * @throws InvalidArgumentException
	 * @access public
	 */
	public function setVersion($version) {
		if (1 !== preg_match('/^(\d+\.){2}\d+$/', $version)) {
			throw new InvalidArgumentException('Invalid version number provided: '.$version);
		}
		$this->version = $version;
		return $this;
	}


	/**
	 * @param string $userAgent
	 * @return GoogleAnalyticsServerSide
	 * @access public
	 */
	public function setUserAgent($userAgent) {
		$this->userAgent = $userAgent;
		GASS_Http::getInstance()->setUserAgent($this->userAgent);
		return $this;
	}


	/**
	 * @param string $acceptLanguage
	 * @return GoogleAnalyticsServerSide
	 * @access public
	 */
	public function setAcceptLanguage($acceptLanguage) {
		if (false !== strpos($acceptLanguage, ';')) {
			list($acceptLanguage, $other) = explode(';', $acceptLanguage, 2);
		}
		if (false !== strpos($acceptLanguage, ',')) {
			list($acceptLanguage, $other) = explode(',', $acceptLanguage, 2);
		}
		$this->acceptLanguage = strtolower($acceptLanguage);
		GASS_Http::getInstance()->setAcceptLanguage($this->acceptLanguage);
		return $this;
	}


	/**
	 * @param string $serverName
	 * @return GoogleAnalyticsServerSide
	 * @access public
	 */
	public function setServerName($serverName) {
		$this->serverName = $serverName;
		return $this;
	}


	/**
	 * @param string $remoteAddress
	 * @return GoogleAnalyticsServerSide
	 * @throws InvalidArgumentException
	 * @access public
	 */
	public function setRemoteAddress($remoteAddress) {
		if (1 !== preg_match('/^(\d{1,3}\.){3}\d{1,3}$/', $remoteAddress)) {
			throw new InvalidArgumentException('The Remote Address must be an IP address.');
		}
		$this->remoteAddress = $remoteAddress;
		GASS_Http::getInstance()->setRemoteAddress($this->remoteAddress);
		return $this;
	}


	/**
	 * @param string $account
	 * @return GoogleAnalyticsServerSide
	 * @throws InvalidArgumentException
	 * @access public
	 */
	public function setAccount($account) {
		if (1 !== preg_match('/^UA-\d{4,}-\d+$/',$account)) {
			throw new InvalidArgumentException('Google Analytics user account must be in the format: UA-XXXXXXX-X');
		}
		$this->account = $account;
		return $this;
	}


	/**
	 * @param string $documentReferer
	 * @return GoogleAnalyticsServerSide
	 * @throws InvalidArgumentException
	 * @access public
	 */
	public function setDocumentReferer($documentReferer) {
		$documentReferer = trim($documentReferer);
		if (!empty($documentReferer) && false === parse_url($documentReferer)) {
			throw new InvalidArgumentException('Document Referer must be a valid URL.');
		}
		$this->documentReferer = $documentReferer;
		return $this;
	}


	/**
	 * @param string $documentPath
	 * @return GoogleAnalyticsServerSide
	 * @access public
	 */
	public function setDocumentPath($documentPath) {
		if (false !== ($queryPos = strpos($documentPath, '?'))) {
			$documentPath = substr($documentPath, 0, $queryPos);
		}
		$this->documentPath = $documentPath;
		return $this;
	}


	/**
	 * @param string $pageTitle
	 * @return GoogleAnalyticsServerSide
	 * @access public
	 */
	public function setPageTitle($pageTitle) {
		$this->pageTitle = $pageTitle;
		return $this;
	}


	/**
	 * Set the Event to be sent.
	 *
	 * @param string $category
	 * @param string $action
	 * @param string $label [optional]
	 * @param integer $value [optional]
	 * @param boolean $nonInteraction [optional]
	 * @throws InvalidArgumentException
	 * @return GoogleAnalyticsServerSide
	 * @access public
	 */
	public function setEvent($category, $action, $label = null, $value = null, $nonInteraction = false) {
		if (($category === null && $action !== null) || ($category !== null && $action === null)) {
			throw new InvalidArgumentException('Category and Action must be set for an Event');
		}
		if ($value !== null && !is_int($value)) {
			throw new InvalidArgumentException('Value must be an integer.');
		}
		if (!is_bool($nonInteraction)) {
			throw new InvalidArgumentException('NonInteraction must be a boolean.');
		}
		$this->event = array(	'category'	=> $category
							,	'action'	=> $action
							,	'label'		=> $label
							,	'value'		=> $value);
		$this->nonInteraction = $nonInteraction;
		return $this;
	}


	/**
	 * Adds a custom variable to the passed data
	 *
	 * @see http://code.google.com/apis/analytics/docs/tracking/gaTrackingCustomVariables.html
	 *
	 * @param string $name
	 * @param string $value
	 * @param integer $scope [optional]
	 * @param integer $index [optional]
	 * @throws OutOfBoundsException
	 * @throws InvalidArgumentException
	 * @access public
	 */
	public function setCustomVariable($name, $value, $scope = 3, $index = null) {
		if ($index === null) {
			$index = count($this->customVariables) + 1;
			if ($index > 5) {
				throw new OutOfBoundsException('You cannot add more than 5 custom variables.');
			}
		} elseif (!is_int($index) || $index < 1 || $index > 5) {
			throw new InvalidArgumentException('The index must be an integer between 1 and 5.');
		}
		if (!is_int($scope) || $scope < 1 || $scope > 3) {
			throw new InvalidArgumentException('The Scope must be a value between 1 and 3');
		}
		$this->customVariables['index'.$index] = array(	'index'	=> (int)$index
													,	'name'	=> (string)$name
													,	'value'	=> (string)$value
													,	'scope' => (int)$scope);
	}


	/**
	 * @param string $charset
	 * @return GoogleAnalyticsServerSide
	 * @access public
	 */
	public function setCharset($charset) {
		$this->charset = strtoupper($charset);
		return $this;
	}


	/**
	 * Sets confguration options for the BotInfo adapter to use, or the class adapter to use itself
	 *
	 * @param array|boolean|GASS_BotInfo_Interface|null $botInfo
	 * @throws InvalidArgumentException
	 * @return GoogleAnalyticsServerSide
	 * @access public
	 */
	public function setBotInfo($botInfo) {
		if (!is_array($botInfo) && !is_bool($botInfo) && $botInfo !== null
				&& !$botInfo instanceof GASS_BotInfo_Interface) {
			throw new InvalidArgumentException($name.' must be an array, boolean, null'
												.' or a class which implements GASS_BotInfo_Interface.');
		} elseif ($botInfo !== null && $botInfo !== false) {
			if ($botInfo instanceof GASS_BotInfo_Interface) {
				$this->botInfo = new GASS_BotInfo(array(), $botInfo);
			} elseif (is_array($botInfo)) {
				$this->botInfo = new GASS_BotInfo($botInfo);
			} else {
				$this->botInfo = new GASS_BotInfo();
			}
		} else {
			$this->botInfo = null;
		}
		return $this;
	}


	/**
	 * @param null|array|GASS_Http_Interface $http
	 * @throws InvalidArgumentException
	 * @return GoogleAnalyticsServerSide
	 */
	public function setHttp($http) {
		if ($http !== null && !is_array($http)
				&& !$http instanceof GASS_Http_Interface) {
			throw new InvalidArgumentException($name.' must be an array, null'
												.' or a class which implements GASS_Http_Interface.');
		}
		if ($http !== null) {
			if ($http instanceof GASS_Http_Interface) {
				GASS_Http::getInstance(array(), $http);
			} elseif (is_array($http)) {
				GASS_Http::getInstance($http);
			}
		}
		$this->http = $http;
		return $this;
	}


	/**
	 * @param array $options
	 * @return GoogleAnalyticsServerSide
	 * @access public
	 */
	public function setOptions(array $options = array()) {
		if (!is_array($options)) {
			throw new InvalidArgumentException(__FUNCTION__.' must be called with an array as an argument');
		}
		foreach ($options as $name => $value) {
			$this->setOption($name, $value);
		}
		return $this;
	}


	/**
	 * Set a specific option related to the
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return GoogleAnalyticsServerSide
	 * @access public
	 */
	public function setOption($name, $value) {
		$this->getOption($name);
		$methodName = 'set'.ucfirst($name);
		if (method_exists($this, $methodName)) {
			$reflectionMethod = new ReflectionMethod($this, $methodName);
			if ($reflectionMethod->isPublic()) {
				return $this->$methodName($value);
			}
		}
		return $this;
	}


	/**
	 * Returns the last saved event as a string for the URL parameters
	 *
	 * @return string
	 * @throws DomainException
	 * @access public
	 */
	public function getEventString() {
		$event = $this->getEvent();
		$value = $event['value'];
		unset($event['value']);
		$eventValues = array();
		foreach ($event as $key => $value) {
			if (!empty($value)) {
				$eventValues[] = $value;
			}
		}
		if (empty($eventValues)) {
			throw new DomainException('Event Cannot be Empty! setEvent must be called or parameters must be passed to createEvent.');
		}
		return '5('.implode($eventValues, '*').')'.(($value === null) ? '('.$value.')' : '');
	}


	/**
	 * Returns the saved custom variables as a string for the URL parameters
	 *
	 * @return string|null
	 * @access public
	 */
	public function getCustomVariableString() {
		$customVars = $this->getCustomVariables();
		if (!empty($customVars)) {
			$names = array();
			$values = array();
			$scopes = array();
			foreach ($customVars as $key => $value) {
				$names[] = $value['name'];
				$values[] = $value['value'];
				$scopes[] = $value['scope'];
			}
			return '8('.implode($names, '*').')9('.implode($values, '*').')11('.implode($scopes, '*').')';
		}
		return null;
	}


	/**
	 * The last octect of the IP address is removed to anonymize the user.
	 *
	 * @access public
	 * @param string $remoteAddress [optional]
	 * @return string
	 */
	public function getIPToReport($remoteAddress = null) {
		$remoteAddress = (empty($remoteAddress)) ? $this->remoteAddress : $remoteAddress;
		if (empty($remoteAddress)) {
			return '';
		}

		// Capture the first three octects of the IP address and replace the forth
		// with 0, e.g. 124.455.3.123 becomes 124.455.3.0
		if (preg_match('/^((\d{1,3}\.){3})\d{1,3}$/', $remoteAddress, $matches)) {
			return $matches[1] . '0';
		}
		return '';
	}


	/**
	 * Generates a random hash for the domain provided, sourced from the ga.js and converted to php
	 * see: http://www.google.com/support/forum/p/Google%20Analytics/thread?tid=626b0e277aaedc3c
	 *
	 * @param string $domain [optional]
	 * @return integer
	 * @access public
	 */
	public function getDomainHash($domain = null){
		$domain = ($domain === null) ? $this->serverName : $domain;
		$a = 1;
		$c = 0;
		if (!empty($domain)) {
			$a = 0;
			for($h = strlen($domain)-1; $h>=0; $h--){
				$o = ord($domain[$h]);
				$a = ($a << 6 & 268435455) + $o + ($o << 14);
				$c = $a & 266338304;
				$a = ($c != 0) ? $a ^ $c >> 21 : $a;
			}
		}
		return $a;
	}


	/**
	 * Sets the google analytics cookies with the relevant values. For the relevant sections
	 * see: http://www.analyticsevangelist.com/google-analytics/how-to-read-google-analytics-cookies/
	 *
	 * @access public
	 * @param array $cookies [optional]
	 * @return GoogleAnalyticsServerSide
	 */
	public function setCookies(array $cookies = array()) {
		$cookies = (empty($cookies)) ? $this->getCookies() : $cookies;

		// Check the cookies provided are valid for this class, getCookie will throw the exception if the name isn't valid
		foreach ($cookies as $name => $value) {
			$this->getCookie($name);
		}

		/**
		 * Get the correct values out of the google analytics cookies
		 */
		if (isset($cookies['__utma']) && null !== $cookies['__utma']) {
			list($domainId, $visitorId, $firstVisit, $lastVisit, $currentVisit, $session) = explode('.', $cookies['__utma'], 6);
		}
		if (isset($cookies['__utmb']) && null !== $cookies['__utmb']) {
			list($domainId, $pageVisits, $session, $currentVisit) = explode('.', $cookies['__utmb'], 4);
		}
		if (isset($cookies['__utmc']) && null !== $cookies['__utmc']) {
			$domainId = $cookies['__utmc'];
		}
		if (isset($cookies['__utmz']) && null !== $cookies['__utmz']) {
			list($domainId, $firstVisit, $session, $sessionVisits, $trafficSourceString) = explode('.', $cookies['__utmz'], 5);
		}

		/**
		 * Set the new section values for the cookies
		 */
		if (!isset($domainId) || !is_numeric($domainId)) {
			$domainId = $this->getDomainHash();
		}
		if (!isset($visitorId) || !is_numeric($visitorId)) {
			$visitorId = rand(0,999999999);
		}
		if (!isset($firstVisit) || !is_numeric($firstVisit)) {
			$firstVisit = time();
		}
		if (!isset($session) || !is_numeric($session)) {
			$session = 1;
		} elseif (!isset($cookies['__utmz'],$cookies['__utmb'])) {
			$session++;
		}
		$sessionVisits = 1;
		$pageVisits = (!isset($pageVisits) || !is_numeric($pageVisits)) ? 1 : ++$pageVisits;
		$lastVisit = (!isset($currentVisit) || !is_numeric($currentVisit)) ? time() : $currentVisit;
		$currentVisit = time();

		/**
		 * Works out where the traffic came from and sets the end part of the utmz cookie accordingly
		 */
		$referer = $this->getDocumentReferer();
		$serverName = $this->getServerName();
		if (!empty($referer) && !empty($serverName) && false === strpos($referer, $serverName)
				&& false !== ($refererParts = parse_url($referer)) && isset($refererParts['host'], $refererParts['path'])) {
			$trafficSourceString = 'utmcsr='.$refererParts['host'].'|utmccn=(referral)|utmcmd=referral|utmcct='.$refererParts['path'];
		}
		if (!isset($trafficSourceString) || false === strpos($trafficSourceString, 'utmcsr=')) {
			$trafficSourceString = 'utmcsr=(direct)|utmccn=(direct)|utmcmd=(none)';
		}

		/**
		 * Set the cookies to the required values
		 */
		$this->setCookie('__utma', $domainId.'.'.$visitorId.'.'.$firstVisit.'.'.$lastVisit.'.'.$currentVisit.'.'.$session, $this->sendCookieHeaders);
		$this->setCookie('__utmb', $domainId.'.'.$pageVisits.'.'.$session.'.'.$currentVisit, $this->sendCookieHeaders);
		$this->setCookie('__utmc', $domainId, $this->sendCookieHeaders);
		$this->setCookie('__utmz', $domainId.'.'.$firstVisit.'.'.$session.'.'.$sessionVisits.'.'.$trafficSourceString, $this->sendCookieHeaders);
		$this->disableCookieHeaders();

		return $this;
	}


	/**
	 * Returns all the google analytics cookies as an array
	 *
	 * @return array
	 * @access public
	 */
	public function getCookies() {
		return $this->cookies;
	}


	/**
	 * Returns the google analytics cookies as a string ready to be set to google analytics
	 *
	 * @return string
	 * @access public
	 */
	public function getCookiesString() {
		$cookieParts = array();
		foreach ($this->getCookies() as $name => $value) {
			$value = trim($value);
			if (!empty($value)) {
				$cookieParts[] = $name.'='.$value.';';
			}
		}
		return implode($cookieParts, ' ');
	}


	/**
	 * Sets a cookie for the user for the name and value provided
	 *
	 * @param string $name
	 * @param string $value
	 * @throws LengthException
	 * @throws OutOfBoundsException
	 * @return GoogleAnalyticsServerSide
	 */
	private function setCookie($name, $value, $setHeader = true) {
		$value = trim($value);
		if (array_key_exists($name, $this->cookies) && !empty($value)) {
			$this->cookies[$name] = $value;
			switch ($name) {
				case '__utmb':
					$cookieLife = time() + (60*30); // 1/2 Hour Cookie
					break;
				case '__utmc':
					$cookieLife = 0; // Session Cookie
					break;
				case '__utmz':
					$cookieLife = time() + (((60*60)*24)*90); // 3-Month Cookie
					break;
				default:
					$cookieLife = time() + 63072000;
			}
			if ($setHeader) {
				setrawcookie($name, $value, $cookieLife, self::COOKIE_PATH, '.'.$this->getServerName());
			}
			return $this;
		}
		if (empty($value)) {
			throw new LengthException('Cookie cannot have an empty value');
		}
		throw new OutOfBoundsException('Cookie by name: '.$name.' is not related to Google Analytics.');
	}


	/**
	 * Disables whether or not the cookie headers are sent when setCookies is called
	 *
	 * @access public
	 */
	public function disableCookieHeaders() {
		$this->sendCookieHeaders = false;
	}


	/**
	 * Returns the current value of a google analytics cookie
	 *
	 * @param string $name
	 * @throws OutOfBoundsException
	 * @return string
	 */
	private function getCookie($name) {
		if (array_key_exists($name, $this->cookies)) {
			return $this->cookies[$name];
		}
		throw new OutOfBoundsException('Cookie by name: '.$name.' is not related to Google Analytics.');
	}


	/**
	 * Retreives the latest version of Google Analytics from the ga.js file
	 *
	 * @return GoogleAnalyticsServerSide
	 * @access public
	 */
	public function setLatestVersionFromJs() {
		$currentJs = GASS_Http::getInstance()->request(self::JS_URL)->getResponse();
		$version = preg_replace('/^[\s\S]+\=function\(\)\{return[\'"]((\d+\.){2}\d+)[\'"][\s\S]+$/i', '$1', $currentJs);
		if (preg_match('/^(\d+\.){2}\d+$/', $version)) {
			$this->setVersion($version);
		}
		return $this;
	}


	/**
	 * Creates a Google Analytics Page View
	 *
	 * @return GoogleAnalyticsServerSide
	 * @access public
	 */
	public function createPageView() {
		$queryParams = array();
		$documentPath = $this->getDocumentPath();
		$documentPath = (empty($documentPath)) ? '' : urldecode($documentPath);
		$queryParams['utmp'] = $documentPath;
		if (null !== ($pageTitle = $this->getPageTitle()) && !empty($pageTitle)) {
			$queryParams['utmdt'] = $pageTitle;
		}
		return $this->track($queryParams);
	}


	/**
	 * Creates a Google Analytics Event
	 *
	 * @param string $category [optional]
	 * @param string $action [optional]
	 * @param string $label [optional]
	 * @param string $value [optional]
	 * @return GoogleAnalyticsServerSide
	 * @access public
	 */
	public function createEvent($category = null, $action = null, $label = null, $value = null, $nonInteraction = false) {
		if (0 < func_num_args()) {
			$this->setEvent($category, $action, $label, $value, $nonInteraction);
		}
		$queryParams = array(	'utmt'	=> 'event'
							,	'utme'	=> $this->getEventString());
		if ($this->nonInteraction === true) {
			$queryParams['utmni'] = '1';
		}
		return $this->track($queryParams);
	}


	/**
	 * Track information.
	 * Updates all the cookies, makes a server side request to Google Analytics.
	 *
	 * Defenitions of the Analytics Parameters are stored at:
	 * http://code.google.com/apis/analytics/docs/tracking/gaTrackingTroubleshooting.html
	 *
	 * @param array $extraParams
	 * @return boolean|GoogleAnalyticsServerSide
	 * @access private
	 */
	private function track(array $extraParams = array()) {

		if ($this->botInfo !== null
				&& $this->botInfo->getIsBot()) {
			return false;
		}

		$domainName = $this->getServerName();
		if (empty($domainName)) {
			$domainName = '';
		}

		$documentReferer = $this->getDocumentReferer();
		$documentReferer = (empty($documentReferer) && $documentReferer !== "0")
							? '-'
							: urldecode($documentReferer);

		$this->setCookies();

		// Construct the gif hit url.
		$queryParams = array(	'utmwv'	=> $this->getVersion()
							,	'utmn'	=> rand(0, 0x7fffffff)
							,	'utmhn'	=> $domainName
							,	'utmr'	=> $documentReferer
							,	'utmac'	=> $this->getAccount()
							,	'utmcc'	=> $this->getCookiesString()
							,	'utmul' => $this->getAcceptLanguage()
							,	'utmcs' => $this->getCharset()
							,	'utmip'	=> $this->getIPToReport()
							,	'utmu'	=> 'q~');
		$queryParams = array_merge($queryParams, $extraParams);

		if (null !== ($customVarString = $this->getCustomVariableString())) {
			$queryParams['utme'] = ((isset($queryParams['utme']) && !empty($queryParams['utme']))
									? $queryParams['utme']
									: '') . $customVarString;
		}

		$utmUrl = self::GIF_URL.'?'.http_build_query($queryParams, null, '&');

		GASS_Http::getInstance()->request($utmUrl)->getResponse();
		return $this;
	}
}

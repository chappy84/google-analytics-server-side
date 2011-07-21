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
 * Requires curl PHP module to be installed
 *
 * @copyright	Copyright (c) 2011 Tom Chapman (http://tom-chapman.co.uk/)
 * @license		http://www.gnu.org/copyleft/gpl.html  GPL
 * @author 		Tom Chapman
 * @link		http://github.com/chappy84/google-analytics-server-side
 * @version		0.6.2 Beta
 * @example		$gass = new GoogleAnalyticsServerSide();
 *	    	$gass->setAccount('UA-XXXXXXX-X')
 *					 ->createPageView();
 */
class GoogleAnalyticsServerSide {


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
	const GIF_LOCATION = 'http://www.google-analytics.com/__utm.gif';


	/**
	 * Location of the current JS file
	 *
	 * @var string
	 */
	const JS_LOCATION = 'http://www.google-analytics.com/ga.js';



	/**
	 * Location of a list of all known bots to ignore from the
	 *
	 * @var string
	 */
	const BOT_CSV_LOCATION = 'http://user-agent-string.info/rpc/get_data.php?botIP-All=csv';


	/**
	 * Google Analytics Tracker Version
	 *
	 * @var string
	 * @access private
	 */
	private $version = '5.1.0';


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
	 * CharacterSet the displayed page is encoded in.
	 *
	 * @var string
	 * @access private
	 */
	private $charset = 'UTF-8';


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
	 * List of bots in use that the class should ignore
	 * array format: 'bot name' => 'bot user agent'
	 *
	 * @var array
	 * @access private
	 */
	private $bots = array();


	/**
	 * Options provided by the user to the class
	 *
	 * @var array
	 * @access private
	 */
	private $options = array(	'ignoreBots' 		=> false
							,	'cachePath'			=> null
							,	'cacheBotsFilename'	=> 'bots.csv'
							,	'cacheTimeout'		=> 2592000
							,	'curlOptions'		=> array());


	/**
	 * Last date the bots were cached
	 *
	 * @var integer
	 * @access private
	 */
	private $botsCacheDate = null;


	/**
	 * Class Level Constructor
	 * Sets all the variables it can from the request headers received from the Browser
	 *
	 * @param array $options
	 * @throws InvalidArgumentException
	 * @access public
	 */
	public function __construct(array $options = array()) {
		if (!extension_loaded('curl')) {
			throw new RuntimeException('cURL PHP extension is not loaded. This extension is required by GoogleAnalyticsServerSide.');
		}
		if (!is_array($options)) {
			throw new InvalidArgumentException('Argument $options must be an array.');
		}
		$this->setOptions($options);
		$this->setLatestVersionFromJs();
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
	}


	/**
	 * @return the $version
	 * @access public
	 */
	public function getVersion() {
		return $this->version;
	}


	/**
	 * @return the $userAgent
	 * @access public
	 */
	public function getUserAgent() {
		return $this->userAgent;
	}


	/**
	 * @return the $acceptLanguage
	 * @access public
	 */
	public function getAcceptLanguage() {
		return $this->acceptLanguage;
	}


	/**
	 * @return the $serverName
	 * @access public
	 */
	public function getServerName() {
		return $this->serverName;
	}

	/**
	 * @return the $remoteAddress
	 * @access public
	 */
	public function getRemoteAddress() {
		return $this->remoteAddress;
	}


	/**
	 * @return the $account
	 * @access public
	 */
	public function getAccount() {
		return $this->account;
	}


	/**
	 * @return the $documentReferer
	 * @access public
	 */
	public function getDocumentReferer() {
		return $this->documentReferer;
	}


	/**
	 * @return the $documentPath
	 * @access public
	 */
	public function getDocumentPath() {
		return $this->documentPath;
	}


	/**
	 * @return the $pageTitle
	 * @access public
	 */
	public function getPageTitle() {
		return $this->pageTitle;
	}

	/**
	 * @return the $event
	 * @access public
	 */
	public function getEvent() {
		return $this->event;
	}

	/**
	 * @return the $charset
	 * @access public
	 */
	public function getCharset() {
		return $this->charset;
	}


	/**
	 * @return the $bots
	 * @access public
	 */
	public function getBots() {
		return $this->bots;
	}


	/**
	 * @return the $options
	 */
	public function getOptions() {
		return $this->options;
	}


	/**
	 * Gets a specific option
	 *
	 * @param string $name
	 * @throws OutOfRangeException
	 * @return mixed
	 */
	public function getOption($name) {
		if (!array_key_exists($name, $this->options)) {
			$methodName = 'get'.ucfirst($name);
			if (method_exists($this, $methodName)) {
				$reflectionMethod = new ReflectionMethod($this, $methodName);
				if ($reflectionMethod->isPublic()) {
					return $this->$methodName();
				}
			}
			throw new OutOfRangeException('Option '.$name.' is not an available option.');
		}
		return $this->options[$name];
	}


	/**
	 * @return the $botsCacheDate
	 */
	private function getBotsCacheDate() {
		return $this->botsCacheDate;
	}


	/**
	 * @param field_type $version
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
	 * @param array $event
	 * @return GoogleAnalyticsServerSide
	 * @throws InvalidArgumentException
	 * @access public
	 */
	public function setEvent($category, $action, $label = null, $value = null) {
		if (($category === null && $action !== null) || ($category !== null && $action === null)) {
			throw new InvalidArgumentException('Category and Action must be set for an Event');
		}
		$this->event = array(	'category'	=> $category
							,	'action'	=> $action
							,	'label'		=> $label
							,	'value'		=> $value);
		return $this;
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
	 * @param array $bots
	 * @return GoogleAnalyticsServerSide
	 * @access public
	 */
	public function setBots(array $bots = array()) {
		if (!is_array($bots)) {
			throw new InvalidArgumentException(__FUNCTION__.' must be called with an array as an argument');
		}
		$this->bots = $bots;
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
		if (!array_key_exists($name, $this->options)) {
			$methodName = 'set'.ucfirst($name);
			if (method_exists($this, $methodName)) {
				$reflectionMethod = new ReflectionMethod($this, $methodName);
				if ($reflectionMethod->isPublic()) {
					return $this->$methodName($value);
				}
			}
		}
		switch ($name) {
			case 'ignoreBots':
				if (!is_bool($value)) {
					throw new InvalidArgumentException($name.' must be a boolean.');
				}
				break;
			case 'cachePath':
				if (null !== $value) {
					if (!is_string($value)) {
						throw new InvalidArgumentException($name.' must be a string.');
					}
					if (!is_dir($value) || !is_writable($value)) {
						throw new RuntimeException('Path '.$value.' is not writable, please check permissions on the folder and try again.');
					}
				}
				break;
			case 'cacheBotsFilename':
				if (!is_string($value)) {
					throw new InvalidArgumentException($name.' must be a string.');
				}
				break;
			case 'cacheTimeout':
				if (!is_numeric($value)) {
					throw new InvalidArgumentException($name.' must be numeric.');
				}
				if ($value < 10) {
					throw new InvalidArgumentException($name.' has a minimum value of 10.');
				}
				break;
			default:
		}
		$this->options[$name] = $value;
		return $this;
	}


	/**
	 * Sets the last bot cache date from the last cache file created
	 *
	 * @return GoogleAnalyticsServerSide
	 * @access private
	 */
	private function setBotsCacheDate($botsCacheDate = null) {
		if (0 == func_num_args()) {
			$fileRelPath = DIRECTORY_SEPARATOR.$this->getOption('cacheBotsFilename');
			$botsCacheDate = (null !== ($csvPathname = $this->getOption('cachePath'))
										&& file_exists($csvPathname.$fileRelPath)
										&& is_readable($csvPathname.$fileRelPath)
										&& false !== ($fileModifiedTime = filectime($csvPathname.$fileRelPath)))
									? $fileModifiedTime : null;
		} elseif (null !== $botsCacheDate && !is_numeric($botsCacheDate)) {
			throw new Exception('botsCacheDate must be numeric or null.');
		}
		$this->botsCacheDate = $botsCacheDate;
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
		$eventValues = array();
		foreach ($this->getEvent() as $key => $value) {
			if (!empty($value)) {
				$eventValues[] = $value;
			}
		}
		if (empty($eventValues)) {
			throw new DomainException('Event Cannot be Empty! setEvent must be called or parameters must be passed to createEvent.');
		}
		return '5('.implode($eventValues, '*').')';
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
	 * Sets the google analytics cookies with the relevant values. For the relevant sections see:
	 * http://www.analyticsevangelist.com/google-analytics/how-to-read-google-analytics-cookies/
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
		$this->setCookie('__utma', $domainId.'.'.$visitorId.'.'.$firstVisit.'.'.$lastVisit.'.'.$currentVisit.'.'.$session);
		$this->setCookie('__utmb', $domainId.'.'.$pageVisits.'.'.$session.'.'.$currentVisit);
		$this->setCookie('__utmc', $domainId);
		$this->setCookie('__utmz', $domainId.'.'.$firstVisit.'.'.$session.'.'.$sessionVisits.'.'.$trafficSourceString);

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
		return $this;
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
	 * Retreives the contents from the external csv source
	 * and then parses it into the class level variable bots
	 *
	 * @return GoogleAnalyticsServerSide
	 * @access private
	 */
	private function setBotsFromCsv() {
		if (null !== ($csvPathname = $this->getOption('cachePath'))) {
			$this->setBotsCacheDate();
			if (null !== ($lastCacheDate = $this->getBotsCacheDate())) {
				$csvPath = $csvPathname.DIRECTORY_SEPARATOR.$this->getOption('cacheBotsFilename');
				if ($lastCacheDate > (time() - $this->getOption('cacheTimeout')) && is_readable($csvPath)) {
					$botsCsv = file_get_contents($csvPath);
				} elseif (false === @unlink($csvPath)) {
					throw new RuntimeException('Cannot delete "'.$csvPath.'". Please check permissions.');
				}
			}
		}
		if (!isset($botsCsv)) {
			$this->setBotsCacheDate(null);
			$botsCsv = $this->retreiveBotsCsv();
		}
		$this->setBots($this->parseBotsCsv($botsCsv));
		return $this;
	}


	/**
	 * Retreives the bots csv from the default source
	 *
	 * @return string
	 * @access private
	 */
	private function retreiveBotsCsv() {
		return trim($this->getSource(self::BOT_CSV_LOCATION));
	}


	/**
	 * Parses the contents of the csv from the default source and
	 * returns an array of bots in the default format
	 *
	 * @param string $fileContexts
	 * @return array
	 */
	private function parseBotsCsv($fileContexts) {
		$botList = explode("\n", $fileContexts);
		$distinctBots = array();
		foreach ($botList as $line) {
			$csvLine = str_getcsv($line);
			if (!isset($distinctBots[$csvLine[0]])) {
				$distinctBots[$csvLine[0]] = (isset($csvLine[6])) ? $csvLine[6] : $csvLine[1];
			}
		}
		return $distinctBots;
	}


	/**
	 * Saves the current list of bots to the cache directory for use next time the script is run
	 *
	 * @return GoogleAnalyticsServerSide
	 * @access private
	 */
	private function saveBotsToCsv() {
		if (null === $this->getBotsCacheDate()
				&& null !== ($csvPath = $this->getOption('cachePath')) && is_writable($csvPath)) {
			$csvLines = array();
			foreach ($this->getBots() as $name => $value) {
				$csvLines[] = '"'.addslashes($name).'","'.addslashes($value).'"';
			}
			$csvString = implode("\n", $csvLines);
			if (false === file_put_contents($csvPath.DIRECTORY_SEPARATOR.$this->getOption('cacheBotsFilename'), $csvString, LOCK_EX)) {
				throw new RuntimeException('Unable to write to file '.$csvPath.DIRECTORY_SEPARATOR.$this->getOption('cacheBotsFilename'));
			}
		}
		return $this;
	}


	/**
	 * Returns whether or not the current user is a bot
	 *
	 * @return boolean
	 * @access public
	 */
	public function getIsBot() {
		$userAgent = $this->getUserAgent();
		$bots = $this->getBots();
		return (!empty($bots) && (in_array($userAgent, $bots) || array_key_exists($userAgent, $bots)));
	}


	/**
	 * Make a tracking request to Google Analytics from this server.
	 * Copies the headers from the original request to the new one.
	 *
	 * @param string $url
	 * @return mixed
	 * @throws UnexpectedValueException
	 * @throws RuntimeException
	 * @access public
	 */
	private function getSource($url) {

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->getUserAgent());
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(	'Accepts-Language: '.$this->getAcceptLanguage()
												,	'X-Forwarded-For: '.$this->getRemoteAddress()));

		$extraCurlOptions = $this->getOption('curlOptions');
		if (!empty($extraCurlOptions) && false === curl_setopt_array($ch, $extraCurlOptions)) {
			throw new UnexpectedValueException('One of the extra curl options specified is invalid.');
		}

		if (false === ($response = curl_exec($ch))) {
			throw new RuntimeException('Source could not be retreived');
		}

		$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		switch($statusCode) {
			case '400':
			case '411':
			case '412':
			case '413':
			case '414':
			case '415':
			case '416':
			case '417':
			case '422':
			case '423':
			case '424':
			case '425':
			case '426':
				$message = 'Bad Request';
				break;
			case '401':
			case '403':
			case '407':
				$message = 'Forbidden';
				break;
			case '402':
				$message = 'Payment Required';
				break;
			case '404':
			case '410':
				$message = 'Not Found';
				break;
			case '408':
				$message = 'Timeout';
				break;
			case '444':
				$message = 'No Response';
				break;
			case '418':
				$message = 'The server is a teapot, this code doesn\'t like tea!';
				break;
			default:
		}
		if (isset($message)) {
			throw new RuntimeException($message, $statusCode);
		}
		curl_close($ch);

		return $response;
	}


	/**
	 * Retreives the latest version of Google Analytics from the ga.js file
	 *
	 * @return GoogleAnalyticsServerSide
	 * @access public
	 */
	public function setLatestVersionFromJs() {
		$currentJs = $this->getSource(self::JS_LOCATION);
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
	public function createEvent($category = null, $action = null, $label = null, $value = null) {
		if (0 < func_num_args()) {
			$this->setEvent($category, $action, $label, $value);
		}
		$queryParams = array(	'utmt'	=> 'event'
							,	'utme'	=> $this->getEventString());
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

		$options = $this->getOptions();
		if (true === $options['ignoreBots'] && empty($options['cachePath'])) {
			throw new DomainException('You must set a cachePath if you wish to use ignoreBots.');
		}

		if (true === $options['ignoreBots']) {
			if (empty($this->bots)) {
				$this->setBotsFromCsv();
			}
			if ($this->getIsBot()) {
				return false;
			}
		}

		$domainName = $this->getServerName();
		if (empty($domainName)) {
			$domainName = '';
		}

		$documentReferer = $this->getDocumentReferer();
		$documentReferer = (empty($documentReferer) && $documentReferer !== "0")
							? '-'
							: urldecode($documentReferer);

		$userAgent = $this->getUserAgent();
		if (empty($userAgent)) {
			$userAgent = '';
		}

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
		$utmUrl = self::GIF_LOCATION.'?'.http_build_query($queryParams, null, '&');

		$this->getSource($utmUrl);
		return $this;
	}


	/**
	 * Class Level Destructor
	 *
	 * @access public
	 */
	public function __destruct() {
		if (null !== $this->getOptions('cachePath')) {
			$this->saveBotsToCsv();
		}
	}
}

if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50300) {
	function str_getcsv($input, $delimiter = ',', $enclosure = '"', $escape = '\\') {
		$input = trim($input, $enclosure." \n\t\r\0");
		$csvElements = preg_split('/['.addslashes($enclosure).']\s*?'.addslashes($delimiter).'\s*?['.addslashes($enclosure).']/', $input);
		$returnArray = array();
		foreach ($csvElements as $element) {
			$returnArray[] = trim($element);
		}
		return $returnArray;
	}
}
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
 * The GNU General Public License can be found at
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
 * @version		Beta 0.5.0
 * @example		$gass = new GoogleAnalyticsServerSide();
 *            	$gass->setAccount('UA-XXXXXXX-X')
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
	private $charset;


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
	 * Class Level Constructor
	 * Sets all the variables it can from the request headers received from the Browser
	 *
	 * @access public
	 */
	public function __construct() {
		if (isset($_SERVER['HTTP_USER_AGENT']) && !empty($_SERVER['HTTP_USER_AGENT'])) {
			$this->setUserAgent($_SERVER['HTTP_USER_AGENT']);
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
		if (isset($_SERVER['HTTP_ACCEPT_CHARSET']) && !empty($_SERVER['HTTP_ACCEPT_CHARSET'])) {
			$charset = $_SERVER['HTTP_ACCEPT_CHARSET'];
			if (false !== strpos(strtolower($charset), 'utf-8')) {
				$charset = 'utf-8';
			} else {
				if (false !== strpos($charset,';')) {
					list($charset, $other) = explode(';', $charset, 2);
				}
				if (false !== strpos($charset, ',')) {
					list($charset,$other) = explode(',', $charset, 2);
				}
				unset($other);
			}
			$this->setCharset($charset);
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
	 * @param field_type $version
	 * @return GoogleAnalyticsServerSide
	 * @access public
	 */
	public function setVersion($version) {
		if (1 !== preg_match('/^[\d\.]+$', $version)) {
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
	 * @access public
	 */
	public function setDocumentReferer($documentReferer) {
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
	 * Returns the last saved event as a string for the URL parameters
	 *
	 * @return string
	 * @access public
	 */
	public function getEventString() {
		$eventValues = array();
		foreach ($this->getEvent() as $key => $value) {
			if (!empty($value)) {
				$eventValues[] = $value;
			}
		}
		return '5('.implode($eventValues, '*').')';
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
			list($domainId, $visitorId, $firstVisit, $lastVisit, $currentVisit, $session) = explode('.', $cookies['__utma']);
		}
		if (isset($cookies['__utmb']) && null !== $cookies['__utmb']) {
			list($domainId, $pageVisits, $session, $currentVisit) = explode('.', $cookies['__utmb']);
		}
		if (isset($cookies['__utmc']) && null !== $cookies['__utmc']) {
			$domainId = $cookies['__utmc'];
		}
		if (isset($cookies['__utmz']) && null !== $cookies['__utmz']) {
			list($domainId, $firstVisit, $session, $sessionVisits) = explode('.', $cookies['__utmz']);
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
		 * Set the cookies tot he required values
		 */
		$this->setCookie('__utma', $domainId.'.'.$visitorId.'.'.$firstVisit.'.'.$lastVisit.'.'.$currentVisit.'.'.$session);
		$this->setCookie('__utmb', $domainId.'.'.$pageVisits.'.'.$session.'.'.$currentVisit);
		$this->setCookie('__utmc', $domainId);
		$this->setCookie('__utmz', $domainId.'.'.$firstVisit.'.'.$session.'.'.$sessionVisits.'.utmcsr=(direct)|utmccn=(direct)|utmcmd=(none)');

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
	 * Make a tracking request to Google Analytics from this server.
	 * Copies the headers from the original request to the new one.
	 *
	 * @param string $utmUrl
	 * @return GoogleAnalyticsServerSide
	 * @access public
	 */
	public function sendRequest($utmUrl) {

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $utmUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->getUserAgent());
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(	'Accepts-Language: '.$this->getAcceptLanguage()
												,	'X-Forwarded-For: '.$this->getRemoteAddress()));

		$gifResponse = curl_exec($ch);

		switch(curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
			case '400':
				throw new RuntimeException('Google Analytics data was not saved.');
				break;
			case '404':
				throw new RuntimeException('URI has no match in the display map.');
				break;
			case '406':
				throw new RuntimeException('Requested representation not available for this resource.');
				break;
			default:
		}
		curl_close($ch);

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
		return $this->trackPageView($queryParams);
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
		return $this->trackPageView($queryParams);
	}


	/**
	 * Track a page view, updates all the cookies and campaign tracker,
	 * makes a server side request to Google Analytics.
	 *
	 * Defenitions of the Analytics Parameters are stored at: http://code.google.com/apis/analytics/docs/tracking/gaTrackingTroubleshooting.html
	 *
	 * @param array $extraParams
	 * @return GoogleAnalyticsServerSide
	 * @access private
	 */
	private function trackPageView(array $extraParams = array()) {
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
							,	'utmu'	=> 'q~');
		$queryParams = array_merge($queryParams, $extraParams);
		$utmUrl = self::GIF_LOCATION.'?'.http_build_query($queryParams, null, '&');

		$this->sendRequest($utmUrl);
		return $this;
	}
}
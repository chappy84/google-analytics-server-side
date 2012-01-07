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
 * @version		0.7.6 Beta
 */
class GASS_BotInfo_BrowserCap
	extends GASS_BotInfo_Base
	implements GASS_BotInfo_Interface
{

	/**
	 * URL location of the current browsercap.ini file
	 *
	 * @var string
	 */
	const BROWSCAP_URL = 'http://browsers.garykeith.com/stream.asp?PHP_BrowsCapINI';


	/**
	 * URL location specifying the latest update date of the file
	 *
	 * @var string
	 */
	const VERSION_DATE_URL = 'http://browsers.garykeith.com/version-date';


	/**
	 * The last time the browsercap file was updated
	 *
	 * @var integer
	 * @access private
	 */
	private $latestVersionDate;


	/**
	 * The parsed contents of the browscap ini file
	 *
	 * @var array
	 * @access private
	 */
	private $browsers = array();


	/**
	 * Class options
	 *
	 * @var array
	 * @access private
	 */
	protected $options = array(	'browscap'	=> null);


	/**
	 * {@inheritdoc}
	 *
	 * @param array $options
	 * @throws RuntimeException
	 * @access public
	 */
	public function __construct(array $options = array()) {
		if (!isset($options['browscap'])
				&& false !== ($browsCapLocation = ini_get('browscap'))
				&& '' != trim($browsCapLocation)) {
			$options['browscap'] = trim($browsCapLocation);
		}
		parent::__construct($options);
	}


	/**
	 * Returns the last date the ini file was updated (on remote webiste)
	 *
	 * @return integer
	 * @access public
	 */
	public function getLatestVersionDate() {
		if ($this->latestVersionDate === null) {
			$this->setLatestVersionDate();
		}
		return $this->latestVersionDate;
	}


	/**
	 * Gets the latest version date from the web
	 *
	 * @access private
	 */
	private function setLatestVersionDate() {
		$latestDateString = trim(GASS_Http::getInstance()
												->request(self::VERSION_DATE_URL)
												->getResponse());
		if (false !== ($latestVersionDate = strtotime($latestDateString))) {
			$this->latestVersionDate = $latestVersionDate;
		}
	}


	/**
	 * Sets a specific option
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return GASS_Adapter_Base
	 * @access public
	 */
	public function setOption($name, $value) {
		parent::setOption($name, $value);
		switch ($name) {
			case 'browscap':
				$this->checkIniFile();
				break;
			default:
		}
		return $this;
	}


	/**
	 * Checks whether the browsercap file exists, is readable, and hasn't expired the cache lifetime
	 *
	 * @throws RuntimeException
	 * @access private
	 */
	private function checkIniFile() {
		if (null === ($browsCapLocation = $this->getOption('browscap'))) {
			throw new RuntimeException('The browscap option has not been specified, please set this and try again.');
		}
		if (!file_exists($browsCapLocation)) {
			$this->updateIniFile();
		}
		if (!is_readable($browsCapLocation)) {
			throw new RuntimeException('The browscap option points to a un-readable file, please ensure the permissions are correct and try again.');
		}
		if (false === ($fileSaveTime = filemtime($browsCapLocation))
				|| (null !== ($latestVersionDate = $this->getLatestVersionDate())
					&& $fileSaveTime < $latestVersionDate)) {
			$this->updateIniFile();
		}
		$this->loadIniFile();
	}


	/**
	 * Updates the browsercap ini file to the latest version
	 *
	 * @throws RuntimeException
	 * @access private
	 */
	private function updateIniFile() {
		$browsCapLocation = $this->getOption('browscap');
		$lastDirSep = strrpos($browsCapLocation, DIRECTORY_SEPARATOR);
		$directory = substr($browsCapLocation, 0, $lastDirSep);
		if ((!file_exists($directory) && !mkdir($directory, 0777, true)) || !is_writable($directory)) {
			throw new RuntimeException('The directory "'.$directory.'" is not writable, please ensure this file can be written to and try again.');
		}
		$currentHttpUserAgent = GASS_Http::getInstance()->getUserAgent();
		if ($currentHttpUserAgent === null || '' == trim($currentHttpUserAgent)) {
			throw new RuntimeException(__CLASS__.' cannot be initialised before a user-agent has been set in the GASS_Http adapter.'
												.' The remote server rejects requests without a user-agent.');
		}
		$browscapSource = GASS_Http::getInstance()->request(self::BROWSCAP_URL)->getResponse();
		$browscapContents = trim($browscapSource);
		if (empty($browscapContents)) {
			throw new RuntimeException(	 'BrowserCap ini file retrieved from external source seems to be empty. '
										.'Please either set botInfo to null or ensure the php_browsercap.ini file can be retreived.');
		}
		if (false == file_put_contents($browsCapLocation, $browscapContents)) {
			throw new RuntimeException('Could not write to "'.$browsCapLocation.'", please check the permissions and try again.');
		}
	}


	/**
	 * Loads the browscap ini file from the specified location
	 *
	 * @access private
	 */
	private function loadIniFile() {
		$browscapLocation = $this->getOption('browscap');
		if (defined('PHP_VERSION_ID') && PHP_VERSION_ID >= 50300) {
			$browsers = parse_ini_file($browscapLocation, true, INI_SCANNER_RAW);
		} else {
			$browsers = parse_ini_file($browscapLocation, true);
		}
		if ($browsers === false || empty($browsers)) {
			throw new RuntimeException('Browscap ini file could not be parsed.');
		}
		$this->browsers = $browsers;
	}


	/**
	 * Returns all the details related to a browser
	 *
	 * @param string $index
	 * @return array|boolean
	 */
	private function getBrowserDetails($index) {
		if (isset($this->browsers[$index])) {
			$browserDetails = $this->browsers[$index];
			if (isset($browserDetails['Parent'])) {
				if (false === ($extraDetails = $this->getBrowserDetails($browserDetails['Parent']))) {
					return false;
				}
				$browserDetails = array_merge($extraDetails, $browserDetails);
			}
			return $browserDetails;
		}
		return false;
	}


	/**
	 * Checks the parsed browscap ini file for the provided user-agent.
	 * Return value is compatible with php's get_browser return value.
	 *
	 * @param string $userAgent
	 * @return boolean|object|array
	 */
	public function getBrowser($userAgent = null, $returnArray = false) {
		if (0 < func_num_args()) {
			$this->setUserAgent($userAgent);
		}
		$userAgent = $this->getUserAgent();
		$browser = '*';
		foreach ($this->browsers as $browserKey => $details) {
			$regEx = $this->getBrowserRegex($browserKey);
			if (1 === preg_match($regEx, $userAgent)) {
				$browser = $browserKey;
				break;
			}
		}
		if (false !== ($browserDetails = $this->getBrowserDetails($browser))) {
			$returnBrowsDet = array();
			$browserRegex = $this->getBrowserRegex($browser);
			$returnBrowsDet['browser_name_regex'] = substr($browserRegex, 0, strlen($browserRegex) - 1);
			$returnBrowsDet['browser_name_pattern'] = $browser;
			foreach ($browserDetails as $key => $value) {
				if ($value == 'true') {
					$value = '1';
				} elseif ($value == 'false') {
					$value = '';
				}
				$returnBrowsDet[strtolower($key)] = $value;
			}
			return ($returnArray === true) ? $returnBrowsDet : (object)$returnBrowsDet;
		}
		return false;
	}


	/**
	 * Converts a browscap browser pattern into a regular expression
	 *
	 * @param string $browserPattern
	 * @return string
	 * @access private
	 */
	private function getBrowserRegex($browserPattern) {
		$regEx = preg_quote($browserPattern);
		$regEx = str_replace(array('\?', '\*'), array('.', '.*'), $regEx);
		return '?^'.strtolower($regEx).'$?i';
	}


	/**
	 * {@inheritdoc}
	 *
	 * @param string $userAgent
	 * @return boolean
	 * @access public
	 */
	public function getIsBot($userAgent = null) {
		if ($userAgent !== null) {
			$this->setUserAgent($userAgent);
		}
		$browserDetails = $this->getBrowser();
		return (false === $browserDetails
					|| (isset($browserDetails->crawler) && $browserDetails->crawler == 1)
					|| (isset($browserDetails->isbanned) && $browserDetails->isbanned == 1)
					|| !isset($browserDetails->javascript) || $browserDetails->javascript != 1
					|| !isset($browserDetails->cookies) || $browserDetails->cookies != 1);
	}
}
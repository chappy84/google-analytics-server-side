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
 * @version		0.7.0 Beta
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
	 * Options to use with the class
	 *
	 * @var array
	 * @access protected
	 */
	protected $options = array('cacheLifetime' => 2592000);


	/**
	 * {@inheritdoc}
	 *
	 * @param array $options
	 * @throws RuntimeException
	 * @access public
	 */
	public function __construct(array $options = array()) {
		parent::__construct($options);
		$this->checkIniFile();
	}


	/**
	 * Checks whether the browsercap file exists, is readable, and hasn't expired the cache lifetime
	 *
	 * @throws RuntimeException
	 * @access private
	 */
	private function checkIniFile() {
		if (false === ($browsCapLocation = ini_get('browscap')) || '' == trim($browsCapLocation)) {
			throw new RuntimeException('The browscap php ini setting has not been specified, please set this and try again.');
		}
		if (!file_exists($browsCapLocation)) {
			$this->updateIniFile();
		}
		if (!is_readable($browsCapLocation)) {
			throw new RuntimeException('The browscap php ini setting points to a un-readable file, please ensure the permissions are correct and try again.');
		}
		if (false === ($fileSaveTime = filemtime($browsCapLocation))
				|| null === ($cacheLifetime = $this->getOption('cacheLifetime'))
				|| $fileSaveTime < (time() - $cacheLifetime)) {
			$this->updateIniFile();
		}
	}


	/**
	 * Updates the browsercap ini file to the latest version
	 *
	 * @throws RuntimeException
	 * @access private
	 */
	private function updateIniFile() {
		$browsCapLocation = ini_get('browscap');
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
		$userAgent = $this->getUserAgent();
		$browserDetails = get_browser($userAgent);
		return ((isset($browserDetails->crawler) && $browserDetails->crawler == 1)
					|| !isset($browserDetails->javascript) || $browserDetails->javascript != 1
					|| !isset($browserDetails->cookies) || $browserDetails->cookies != 1);
	}
}
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
class GASS_BotInfo_UserAgentStringInfo
	extends GASS_BotInfo_Base
{

	/**
	 * Location of a list of all known bots to ignore from the
	 *
	 * @var string
	 */
	const CSV_URL = 'http://user-agent-string.info/rpc/get_data.php?botIP-All=csv';


	/**
	 * List of bots in use that the class should ignore
	 * array format: 'bot name' => 'bot user agent'
	 *
	 * @var array
	 * @access private
	 */
	private $bots = array();


	/**
	 * Last date the cache was saved
	 *
	 * @var number|null
	 * @access private
	 */
	private $cacheDate;


	/**
	 * Options to use with the class
	 *
	 * @var array
	 * @access protected
	 */
	protected $options = array(	'cachePath'		=> null
							,	'cacheFilename'	=> 'bots.csv'
							,	'cacheLifetime'	=> 2592000);


	/**
	 * Class level constructor
	 *
	 * @param array $cacheOptions
	 */
	public function __construct(array $options = array()) {
		parent::__construct($options);
		$this->set();
	}


	/**
	 * Sets the bots list
	 *
	 * @return GASS_Bots
	 * @access public
	 */
	public function set() {
		if (null === ($bots = $this->getFromCache())) {
			$bots = $this->getFromWeb();
		}
		$bots = $this->parseCsv($bots);
		$this->bots = (is_array($bots))
						? $bots
						: array();
		return $this;
	}


	/**
	 * Returns the current bots
	 *
	 * @return array
	 */
	public function get() {
		return $this->bots;
	}


	/**
	 * Retreives the contents from the external csv source
	 * and then parses it into the class level variable bots
	 *
	 * @return array
	 * @access private
	 */
	private function getFromCache() {
		if (null !== ($csvPathname = $this->getOption('cachePath'))) {
			$this->setCacheDate();
			if (null !== ($lastCacheDate = $this->getCacheDate())) {
				$csvPath = $csvPathname.DIRECTORY_SEPARATOR.$this->getOption('cacheFilename');
				if ($lastCacheDate > (time() - $this->getOption('cacheLifetime')) && is_readable($csvPath)
						&& false !== ($botsCsv = @file_get_contents($csvPath))) {
					return $botsCsv;
				} elseif (false === @unlink($csvPath)) {
					throw new RuntimeException('Cannot delete "'.$csvPath.'". Please check permissions.');
				}
			}
		}
		$this->setCacheDate(null);
		return null;
	}


	/**
	 * Retreives the bots csv from the default source
	 *
	 * @return string
	 * @access private
	 */
	private function getFromWeb() {
		$csvSource = GASS_Http::getInstance()->request(self::CSV_URL)->getResponse();
		$botsCsv = trim($csvSource);
		if (empty($botsCsv)) {
			throw new RuntimeException(	 'Bots CSV retrieved from external source seems to be empty. '
										.'Please either set botInfo to null or ensure the bots csv file can be retreived.');
		}
		return $botsCsv;
	}


	/**
	 * Parses the contents of the csv from the default source and
	 * returns an array of bots in the default format
	 *
	 * @param string $fileContexts
	 * @return array
	 */
	private function parseCsv($fileContexts) {
		$botList = explode("\n", $fileContexts);
		$distinctBots = array();
		foreach ($botList as $line) {
			$line = trim($line);
			if (!empty($line)) {
				$csvLine = str_getcsv($line);
				if (!isset($distinctBots[$csvLine[0]])) {
					$distinctBots[$csvLine[0]] = (isset($csvLine[6])) ? $csvLine[6] : $csvLine[1];
				}
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
	private function saveToCache() {
		if (null === $this->getCacheDate()
				&& null !== ($csvPath = $this->getOption('cachePath')) && is_writable($csvPath)) {
			$csvLines = array();
			foreach ($this->bots as $name => $value) {
				$csvLines[] = '"'.addslashes($name).'","'.addslashes($value).'"';
			}
			$csvString = implode("\n", $csvLines);
			if (false === @file_put_contents($csvPath.DIRECTORY_SEPARATOR.$this->getOption('cacheFilename'), $csvString, LOCK_EX)) {
				throw new RuntimeException('Unable to write to file '.$csvPath.DIRECTORY_SEPARATOR.$this->getOption('cacheFilename'));
			}
		}
		return $this;
	}


	/**
	 * Sets the last bot cache date from the last cache file created
	 *
	 * @return GoogleAnalyticsServerSide
	 * @access private
	 */
	private function setCacheDate($cacheDate = null) {
		if (0 == func_num_args()) {
			$fileRelPath = DIRECTORY_SEPARATOR.$this->getOption('cacheFilename');
			$cacheDate = (null !== ($csvPathname = $this->getOption('cachePath'))
										&& file_exists($csvPathname.$fileRelPath)
										&& is_readable($csvPathname.$fileRelPath)
										&& false !== ($fileModifiedTime = filemtime($csvPathname.$fileRelPath)))
									? $fileModifiedTime : null;
		} elseif (null !== $cacheDate && !is_numeric($cacheDate)) {
			throw new Exception('cacheDate must be numeric or null.');
		}
		$this->cacheDate = $cacheDate;
		return $this;
	}


	/**
	 *
	 *
	 * @return number|null
	 */
	public function getCacheDate() {
		return $this->cacheDate;
	}


	/**
	 * Returns whether or not the current user is a bot
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
		return (!empty($this->bots) && (in_array($userAgent, $this->bots) || array_key_exists($userAgent, $this->bots)));
	}


	/* (non-PHPdoc)
	 * @see GASS_Adapter_Interface::setOptions()
	 */
	public function setOptions(array $options) {
		foreach ($options as $name => $value) {
			$this->getOption($name);
		}
		return parent::setOptions($options);
	}


	/**
	 * Class Destructor
	 *
	 * @access public
	 */
	public function __destruct() {
		$this->saveToCache();
	}
}
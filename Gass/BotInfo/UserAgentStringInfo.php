<?php
/**
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * Google Analytics Server Side is free software; you can redistribute it and/or
 * modify it under the terms of the BSD 3-clause "New" or "Revised" License
 *
 * N/B: This code is nether written or endorsed by Google or any of it's
 *      employees. "Google" and "Google Analytics" are trademarks of
 *      Google Inc. and it's respective subsidiaries.
 *
 * @copyright   Copyright (c) 2011-2015 Tom Chapman (http://tom-chapman.uk/)
 * @license     BSD 3-clause "New" or "Revised" License
 * @link        http://github.com/chappy84/google-analytics-server-side
 */
namespace Gass\BotInfo;

use Gass\Exception;
use Gass\Http;

/**
 * BrowsCap adapter which uses browscap ini file to negatively identify search engine bots
 *
 * @see         Gass\Exception
 * @see         Gass\Http
 * @author      Tom Chapman
 * @package     Gass\BotInfo
 * @deprecated  until udger.com implements csvs to replace user agent string info's csv, as that's now shut down
 */
class UserAgentStringInfo extends Base
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
     */
    private $bots = array();

    /**
     * List of IPs in use by bots that the class should ignore
     * array_format: 'IP address' => 'bot name'
     *
     * @var array
     */
    private $botIps = array();

    /**
     * Last date the cache was saved
     *
     * @var number|null
     */
    private $cacheDate;

    /**
     * Options to use with the class
     *
     * @var array
     */
    protected $options = array(
        'cachePath'     => null,
        'cacheFilename' => 'bots.csv',
        'cacheLifetime' => 2592000
    );

    /**
     * Sets the bots list
     *
     * @return \Gass\BotInfo\UserAgentStringInfo
     */
    public function set()
    {
        if (null === ($bots = $this->getFromCache())) {
            $bots = $this->getFromWeb();
        }
        $botInfo = $this->parseCsv($bots);
        $this->bots = (is_array($botInfo->distinctBots))
            ? $botInfo->distinctBots
            : array();
        $this->botIps = (is_array($botInfo->distinctIPs))
            ? $botInfo->distinctIPs
            : array();
        return $this;
    }

    /**
     * Returns the current bots
     *
     * @return array
     */
    public function get()
    {
        return $this->bots;
    }

    /**
     * Retrieves the contents from the external csv source
     * and then parses it into the class level variable bots
     *
     * @return array|null
     * @throws \Gass\Exception\RuntimeException
     */
    private function getFromCache()
    {
        if (null !== ($csvPathname = $this->getOption('cachePath'))) {
            $this->setCacheDate();
            if (null !== ($lastCacheDate = $this->getCacheDate())) {
                $csvPath = $csvPathname . DIRECTORY_SEPARATOR . $this->getOption('cacheFilename');
                if ($lastCacheDate > (time() - $this->getOption('cacheLifetime')) && @is_readable($csvPath)
                        && false !== ($botsCsv = @file_get_contents($csvPath))) {
                    return $botsCsv;
                } elseif (false === @unlink($csvPath)) {
                    throw new Exception\RuntimeException('Cannot delete "' . $csvPath . '". Please check permissions.');
                }
            }
        }
        $this->setCacheDate(null);
        return null;
    }

    /**
     * Retrieves the bots csv from the default source
     *
     * @return string
     * @throws \Gass\Exception\RuntimeException
     */
    private function getFromWeb()
    {
        $csvSource = Http\Http::getInstance()->request(self::CSV_URL)->getResponse();
        $botsCsv = trim($csvSource);
        if (empty($botsCsv)) {
            throw new Exception\RuntimeException(
                'Bots CSV retrieved from external source seems to be empty. ' .
                'Please either set botInfo to null or ensure the bots csv file can be retrieved.'
            );
        }
        return $botsCsv;
    }

    /**
     * Parses the contents of the csv from the default source and
     * returns an array of bots in the default format
     *
     * @param string $fileContexts
     * @return stdClass
     */
    private function parseCsv($fileContexts)
    {
        $botList = explode("\n", $fileContexts);
        $botInfo = new \stdClass;
        $botInfo->distinctBots = array();
        $botInfo->distinctIPs = array();
        foreach ($botList as $line) {
            $line = trim($line);
            if (!empty($line)) {
                $csvLine = str_getcsv($line);
                if (!isset($botInfo->distinctBots[$csvLine[0]])) {
                    $botInfo->distinctBots[$csvLine[0]] = (isset($csvLine[6]))
                        ? $csvLine[6]
                        : $csvLine[2];
                }
                if (!isset($botInfo->distinctIPs[$csvLine[1]])) {
                    $botInfo->distinctIPs[$csvLine[1]] = $csvLine[0];
                }
            }
        }
        return $botInfo;
    }

    /**
     * Saves the current list of bots to the cache directory for use next time the script is run
     *
     * @return GoogleAnalyticsServerSide
     * @throws \Gass\Exception\RuntimeException
     */
    private function saveToCache()
    {
        if (null === $this->getCacheDate()
                && null !== ($csvPath = $this->getOption('cachePath')) && @is_writable($csvPath)) {
            $csvLines = array();
            foreach ($this->botIps as $ipAddress => $name) {
                $csvLines[] = '"' . addslashes($name) . '","' . addslashes($ipAddress) . '","' .
                    addslashes($this->bots[$name]) . '"';
            }
            $csvString = implode("\n", $csvLines);
            if (false === @file_put_contents(
                $csvPath . DIRECTORY_SEPARATOR . $this->getOption('cacheFilename'),
                $csvString,
                LOCK_EX
            )) {
                throw new Exception\RuntimeException(
                    'Unable to write to file ' . $csvPath . DIRECTORY_SEPARATOR . $this->getOption('cacheFilename')
                );
            }
        }
        return $this;
    }

    /**
     * Sets the last bot cache date from the last cache file created
     *
     * @param integer $cacheDate [optional]
     * @return GoogleAnalyticsServerSide
     * @throws \Gass\Exception\DomainException
     */
    private function setCacheDate($cacheDate = null)
    {
        if (0 == func_num_args()) {
            $fileRelPath = DIRECTORY_SEPARATOR . $this->getOption('cacheFilename');
            $cacheDate = (null !== ($csvPathname = $this->getOption('cachePath'))
                    && @is_readable($csvPathname . $fileRelPath)
                    && false !== ($fileModifiedTime = @filemtime($csvPathname . $fileRelPath)))
                ? $fileModifiedTime
                : null;
        } elseif (null !== $cacheDate && !is_numeric($cacheDate)) {
            throw new Exception\DomainException('cacheDate must be numeric or null.');
        }
        $this->cacheDate = $cacheDate;
        return $this;
    }

    /**
     * Returns the current cache date
     *
     * @return number|null
     */
    public function getCacheDate()
    {
        return $this->cacheDate;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $userAgent [optional]
     * @param string $remoteAddress [optional]
     * @return boolean
     */
    public function isBot($userAgent = null, $remoteAddress = null)
    {
        if (empty($this->bots)) {
            $this->set();
        }
        $noArgs = func_num_args();
        if ($noArgs >= 1) {
            $this->setUserAgent($userAgent);
        }
        if ($noArgs >= 2) {
            $this->setRemoteAddress($remoteAddress);
        }
        $userAgent = $this->getUserAgent();
        $remoteAddress = $this->getRemoteAddress();
        return ((!empty($this->bots)
                && (in_array($userAgent, $this->bots) || array_key_exists($userAgent, $this->bots)))
            || (!empty($this->botIps) && array_key_exists($remoteAddress, $this->botIps)));
    }

    /**
     * {@inheritdoc}
     *
     * @param array $options
     * @return \Gass\BotInfo\UserAgentStringInfo
     */
    public function setOptions(array $options)
    {
        foreach ($options as $name => $value) {
            $this->getOption($name);
        }
        return parent::setOptions($options);
    }

    /**
     * Class Destructor
     */
    public function __destruct()
    {
        $this->saveToCache();
    }
}

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
 * @copyright   Copyright (c) 2011-2016 Tom Chapman (http://tom-chapman.uk/)
 * @license     BSD 3-clause "New" or "Revised" License
 * @link        http://github.com/chappy84/google-analytics-server-side
 */

namespace Gass\BotInfo;

use Gass\Exception\DomainException;
use Gass\Exception\RuntimeException;
use Gass\Http\Http;

/**
 * BrowsCap adapter which uses browscap ini file to positively identify allowed browsers
 *
 * @see         Gass\Exception\RuntimeException
 * @see         Gass\Http\Http
 * @author      Tom Chapman
 */
class BrowsCap extends Base
{
    /**
     * URL location of the current browscap.ini file
     *
     * @var string
     */
    const BROWSCAP_URL = 'http://browscap.org/stream?q=Full_PHP_BrowsCapINI';

    /**
     * URL location specifying the latest update date of the file
     *
     * @var string
     */
    const VERSION_DATE_URL = 'http://browscap.org/version';

    /**
     * Browscap option index
     *
     * @var string
     */
    const OPT_BROWSCAP = 'browscap';

    /**
     * Ini file option index
     *
     * @var string
     */
    const OPT_INI_FILE = 'iniFilename';

    /**
     * Save path option index
     *
     * @var string
     */
    const OPT_SAVE_PATH = 'savePath';

    /**
     * Latest version date file option index
     *
     * @var string
     */
    const OPT_LATEST_VERSION_DATE_FILE = 'latestVersionDateFile';

    /**
     * The last time the browscap file was updated
     *
     * @var int
     */
    private $latestVersionDate;

    /**
     * The parsed contents of the browscap ini file
     *
     * @var array
     */
    private $browsers = array();

    /**
     * Class options
     *
     * @var array
     */
    protected $options = array(
        'iniFilename' => null,
        'savePath' => null,
        'latestVersionDateFile' => 'latestVersionDate.txt',
    );

    /**
     * {@inheritdoc}
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        if (!isset($options[static::OPT_INI_FILE])
            && !isset($options[static::OPT_SAVE_PATH])
            && !isset($options[static::OPT_BROWSCAP])
            && false !== ($browsCapLocation = ini_get(static::OPT_BROWSCAP))
            && '' != trim($browsCapLocation)
        ) {
            $options[static::OPT_BROWSCAP] = trim($browsCapLocation);
        }
        parent::__construct($options);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function setOption($name, $value)
    {
        if (static::OPT_BROWSCAP === $name) {
            $currentFileName = $this->getOption(static::OPT_INI_FILE);
            $currentDirectory = $this->getOption(static::OPT_SAVE_PATH);
            if (empty($currentDirectory) && empty($currentFileName)) {
                parent::setOption(static::OPT_INI_FILE, basename($value));
                parent::setOption(static::OPT_SAVE_PATH, dirname($value));
            }
            return $this;
        }
        return parent::setOption($name, $value);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $name
     * @return mixed
     */
    public function getOption($name)
    {
        if (static::OPT_BROWSCAP === $name) {
            return $this->getFilePath(static::OPT_INI_FILE);
        }
        return parent::getOption($name);
    }

    /**
     * Returns the last date the ini file was updated (on remote webiste)
     *
     * @return int
     */
    public function getLatestVersionDate()
    {
        if ($this->latestVersionDate === null) {
            $this->setLatestVersionDate();
        }
        return $this->latestVersionDate;
    }

    /**
     * Gets the latest version date from the web
     *
     * @throws DomainException
     * @throws RuntimeException
     */
    private function setLatestVersionDate()
    {
        if (null === ($latestVersionDateFile = $this->getFilePath(static::OPT_LATEST_VERSION_DATE_FILE))) {
            throw new DomainException(
                'Cannot deduce latest version date file location. Please set the required options.'
            );
        }
        if (!file_exists($latestVersionDateFile)
            || false === ($fileSaveTime = filemtime($latestVersionDateFile))
            || $fileSaveTime < time() - 86400
        ) {
            $latestDateString = trim(
                Http::getInstance()
                    ->request(static::VERSION_DATE_URL)
                    ->getResponse()
            );
            if (false === $this->checkValidDateTimeString($latestDateString)) {
                unset($latestDateString);
            } else {
                $this->saveToFile($latestVersionDateFile, trim($latestDateString));
            }
        } elseif (false === ($latestDateString = @file_get_contents($latestVersionDateFile))) {
            $errorMsg = isset($php_errormsg)
                ? $php_errormsg
                : 'error message not available, this could be because the ini ' .
                    'setting "track_errors" is set to "Off" or XDebug is running';
            throw new RuntimeException(
                'Couldn\'t read latest version date file: ' . $latestVersionDateFile . ' due to: ' . $errorMsg
            );
        }
        if (isset($latestDateString)
            && false !== ($latestVersionDate = $this->checkValidDateTimeString($latestDateString))
        ) {
            $this->latestVersionDate = $latestVersionDate;
        }
    }

    /**
     * Checks a string is a valid date format, and if so returns it's associated unix timestamp
     *
     * @param string $dateString
     * @return int|bool
     */
    private function checkValidDateTimeString($dateString)
    {
        if (false === ($timestamp = strtotime($dateString))) {
            return false;
        }
        return $timestamp;
    }

    /**
     * Checks whether the browscap file exists, is readable, and hasn't expired the cache lifetime
     *
     * @throws DomainException
     * @throws RuntimeException
     */
    private function checkIniFile()
    {
        if (null === ($iniFilePath = $this->getFilePath(static::OPT_INI_FILE))) {
            throw new DomainException(
                'Cannot deduce browscap ini file location. Please set the required options.'
            );
        }
        if (!file_exists($iniFilePath)) {
            $this->updateIniFile();
        }
        if (!is_readable($iniFilePath)) {
            throw new RuntimeException(
                'The browscap ini file ' .
                    $iniFilePath .
                    ' is un-readable, please ensure the permissions are correct and try again.'
            );
        }
        if (false === ($fileSaveTime = filemtime($iniFilePath))
            || (null !== ($latestVersionDate = $this->getLatestVersionDate())
                && $fileSaveTime < $latestVersionDate)
        ) {
            $this->updateIniFile();
        }
        $this->loadIniFile();
    }

    /**
     * Updates the browscap ini file to the latest version
     *
     * @throws DomainException
     * @throws RuntimeException
     */
    private function updateIniFile()
    {
        if (null === ($iniFilePath = $this->getFilePath(static::OPT_INI_FILE))) {
            throw new DomainException(
                'Cannot deduce browscap ini file location. Please set the required options.'
            );
        }

        $http = Http::getInstance();
        $currentHttpUserAgent = $http->getUserAgent();
        if ($currentHttpUserAgent === null || '' == trim($currentHttpUserAgent)) {
            throw new DomainException(
                'A user-agent has not beeen set in the Gass\Http adapter.' .
                    ' The remote server rejects requests without a user-agent.'
            );
        }
        $browscapSource = $http->request(static::BROWSCAP_URL)->getResponse();
        $browscapContents = trim($browscapSource);
        if (empty($browscapContents)) {
            throw new RuntimeException(
                'browscap ini file retrieved from external source seems to be empty. ' .
                    'Please ensure the ini file file can be retrieved.'
            );
        }

        $this->saveToFile($iniFilePath, $browscapContents);
    }

    /**
     * Loads the browscap ini file from the specified location
     *
     * @throws RuntimeException
     */
    private function loadIniFile()
    {
        $browsers = parse_ini_file($this->getFilePath(static::OPT_INI_FILE), true, INI_SCANNER_RAW);
        if (empty($browsers)) {
            throw new RuntimeException('Browscap ini file could not be parsed.');
        }
        $this->browsers = $browsers;
    }

    /**
     * Returns all the details related to a browser
     *
     * @param string $index
     * @return array|bool
     */
    private function getBrowserDetails($index)
    {
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
     * @param bool $returnArray
     * @return bool|object|array
     */
    public function getBrowser($userAgent = null, $returnArray = false)
    {
        if (empty($this->browsers)) {
            $this->checkIniFile();
        }

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
        if (false !== ($browserDetails = $this->getBrowserDetails($browser))
            && 'Default Browser' !== $browserDetails['Browser']
        ) {
            $browserRegex = $this->getBrowserRegex($browser);
            $returnBrowsDet = array(
                'browser_name_regex' => substr($browserRegex, 0, strlen($browserRegex) - 1),
                'browser_name_pattern' => $browser,
            );
            foreach ($browserDetails as $key => $value) {
                if ($value == 'true') {
                    $value = '1';
                } elseif ($value == 'false') {
                    $value = '';
                }
                $returnBrowsDet[strtolower($key)] = $value;
            }
            return ($returnArray === true) ? $returnBrowsDet : (object) $returnBrowsDet;
        }
        return false;
    }

    /**
     * Converts a browscap browser pattern into a regular expression
     *
     * @param string $browserPattern
     * @return string
     */
    private function getBrowserRegex($browserPattern)
    {
        return '?^' .
            strtolower(
                str_replace(
                    array('\?', '\*'),
                    array('.', '.*'),
                    preg_quote($browserPattern)
                )
            ) .
            '$?i';
    }

    /**
     * {@inheritdoc}
     *
     * @param string $userAgent [optional]
     * @param string $remoteAddress [optional]
     * @return bool
     */
    public function isBot($userAgent = null, $remoteAddress = null)
    {
        $noArgs = func_num_args();
        if ($noArgs >= 1) {
            $this->setUserAgent($userAgent);
        }
        if ($noArgs >= 2) {
            $this->setRemoteAddress($remoteAddress);
        }
        $browserDetails = $this->getBrowser();
        return (
            false === $browserDetails
            || (isset($browserDetails->crawler) && $browserDetails->crawler == 1)
            || (isset($browserDetails->isbanned) && $browserDetails->isbanned == 1)
            || !isset($browserDetails->javascript) || $browserDetails->javascript != 1
            || !isset($browserDetails->cookies) || $browserDetails->cookies != 1
        );
    }

    /**
     * Returns the specified option with static::OPT_SAVE_PATH prepended
     *
     * @param string $optionName
     * @return string|null
     */
    private function getFilePath($optionName)
    {
        $path = $this->getOption(static::OPT_SAVE_PATH);
        $filename = $this->getOption($optionName);
        if (!empty($path) && !empty($filename)) {
            return rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
        }
        return null;
    }

    /**
     * Save contents to a specific path
     *
     * @param string $filePath
     * @param mixed $fileContents
     * @throws RuntimeException
     * @return $this
     */
    private function saveToFile($filePath, $fileContents)
    {
        $parentDirPath = dirname($filePath);
        $folderNotWritable = (!file_exists($filePath) && !is_writable($parentDirPath));
        $fileNotWritable = (file_exists($filePath) && !is_writable($filePath));
        if ($folderNotWritable
            || $fileNotWritable
            || false === @file_put_contents($filePath, $fileContents, LOCK_EX)
        ) {
            if ($folderNotWritable) {
                $errorMsg = 'Folder ' . $parentDirPath . ' is not writable';
            } elseif ($fileNotWritable) {
                $errorMsg = 'File is not writable';
            } elseif (!isset($php_errormsg)) {
                $errorMsg = 'error message not available, this could be because the ini ' .
                    'setting "track_errors" is set to "Off" or XDebug is running';
            } else {
                $errorMsg = $php_errormsg;
            }
            throw new RuntimeException(
                'Cannot save file ' . $filePath . ' due to: ' . $errorMsg
            );
        }
    }
}

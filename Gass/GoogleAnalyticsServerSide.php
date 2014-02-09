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
 *      employees. "Google" and "Google Analytics" are trademarks of
 *      Google Inc. and it's respective subsidiaries.
 *
 * @copyright   Copyright (c) 2011-2013 Tom Chapman (http://tom-chapman.co.uk/)
 * @license     http://www.gnu.org/copyleft/gpl.html  GPL
 * @author      Tom Chapman
 * @link        http://github.com/chappy84/google-analytics-server-side
 * @version     0.9.0 Beta
 * @category    GoogleAnalyticsServerSide
 * @package     Gass
 * @example     $gass = new \Gass\GoogleAnalyticsServerSide;
 *              $gass->setAccount('UA-XXXXXXX-X')
 *                  ->trackPageView();
 */

namespace Gass;

use Gass\BotInfo;
use Gass\Exception;
use Gass\Http;
use Gass\Validate;

/**
 * Main Google Analytics server Side Class
 *
 * @copyright   Copyright (c) 2011-2013 Tom Chapman (http://tom-chapman.co.uk/)
 * @license     http://www.gnu.org/copyleft/gpl.html  GPL
 * @author      Tom Chapman
 * @category    GoogleAnalyticsServerSide
 * @package     Gass
 */
class GoogleAnalyticsServerSide implements GassInterface
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
     * Changelog: https://developers.google.com/analytics/community/gajs_changelog
     *
     * @var string
     */
    const JS_URL = 'http://www.google-analytics.com/ga.js';

    /**
     * Current contents of the ga.js file
     *
     * @var string
     */
    private $currentJsFile;

    /**
     * Google Analytics Tracker Version
     *
     * @var string
     */
    private $version = '5.4.6';

    /**
     * Whether or not setVersion has been called, used
     * to determine whther or ot to set from ga.js file
     *
     * @var boolean
     */
    private $setVersionCalled = false;

    /**
     * Browser User Agent
     *
     * @var string
     */
    private $userAgent;

    /**
     * Accept Language
     *
     * @var string
     */
    private $acceptLanguage = 'en';

    /**
     * Server Name
     *
     * @var string
     */
    private $serverName;

    /**
     * The User's IP Address
     *
     * @var string
     */
    private $remoteAddress;

    /**
     * Document Referer
     * value for utmr
     *
     * @var string
     */
    private $documentReferer;

    /**
     * Documment Path
     * value for utmp
     *
     * @var string
     */
    private $documentPath;

    /**
     * The value of the Do Not Track header
     *
     * @var integer|null
     */
    private $doNotTrack;

    /**
     * Google Analytics Account ID for the site
     * value for utmac
     *
     * @var string
     */
    private $account;

    /**
     * Title of the current page
     *
     * @var string
     */
    private $pageTitle;

    /**
     * Data for the custom variables
     *
     * @var array
     */
    private $customVariables = array();

    /**
     * CharacterSet the displayed page is encoded in.
     *
     * @var string
     */
    private $charset = 'UTF-8';

    /**
     * Whether or not to send the cookies when send
     *
     * @var boolean
     */
    private $sendCookieHeaders = true;

    /**
     * Timeout of the default user session cookie (default half hour)
     *
     * @var integer
     */
    private $sessionCookieTimeout = 1800;

    /**
     * Timout of the default visitor cookie (default two years)
     *
     * @var integer
     */
    private $visitorCookieTimeout = 63072000;

    /**
     * Contains all the details of the analytics cookies
     *
     * @var array
     */
    private $cookies = array(
        '__utma' => null,
        '__utmb' => null,
        '__utmc' => null,
        '__utmv' => null,
        '__utmz' => null
    );

    /**
     * Whether or not setCookies has been called
     *
     * @var boolean
     */
    private $setCookiesCalled = false;

    /**
     * Search engines and their query parameters
     * used to determine if referer is organic or not
     *
     * @var array
     */
    private $searchEngines = array(
        'daum'              => array('q'),
        'eniro'             => array('search_word'),
        'naver'             => array('query'),
        'pchome'            => array('q'),
        'images.google'     => array('q'),
        'google'            => array('q'),
        'yahoo'             => array('p', 'q'),
        'msn'               => array('q'),
        'bing'              => array('q'),
        'aol'               => array('query', 'q'),
        'lycos'             => array('q', 'query'),
        'ask'               => array('q'),
        'netscape'          => array('query'),
        'cnn'               => array('query'),
        'about'             => array('terms'),
        'mamma'             => array('q'),
        'voila'             => array('rdata'),
        'virgilio'          => array('qs'),
        'live'              => array('q'),
        'baidu'             => array('wd'),
        'alice'             => array('qs'),
        'yandex'            => array('text'),
        'najdi'             => array('q'),
        'seznam'            => array('q'),
        'rakuten'           => array('qt'),
        'biglobe'           => array('q'),
        'goo.ne'            => array('MT'),
        'wp'                => array('szukaj'),
        'onet'              => array('qt'),
        'yam'               => array('k'),
        'kvasir'            => array('q'),
        'ozu'               => array('q'),
        'terra'             => array('query'),
        'rambler'           => array('query'),
        'conduit'           => array('q'),
        'babylon'           => array('q'),
        'search-results'    => array('q'),
        'avg'               => array('q'),
        'comcast'           => array('q'),
        'incredimail'       => array('q'),
        'startsiden'        => array('q'),
        'go.mail.ru'        => array('q'),
        'search.centrum.cz' => array('q'),
        '360.cn'            => array('q')
    );

    /**
     * Whether or not setSearchEngines has been called, used
     * to determine whther or ot to set from ga.js file
     *
     * @var boolean
     */
    private $setSearchEnginesCalled = false;

    /**
     * Class to check if the current request is a bot or not
     *
     * @var null|Gass\BotInfo
     */
    private $botInfo;

    /**
     * Options to pass to Gass\Http
     *
     * @var null|array|Gass\Http\Interface
     */
    private $http;

    /**
     * Whether or not to ignore the Do Not Track header
     *
     * @var boolean
     */
    private $ignoreDoNotTrack = false;

    /**
     * Class Level Constructor
     * Sets all the variables it can from the request headers received from the Browser
     *
     * @param array $options
     * @throws Gass\Exception\InvalidArgumentException
     */
    public function __construct(array $options = array())
    {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'Bootstrap.php';
        if (!empty($_SERVER['SERVER_NAME'])) {
            $this->setServerName($_SERVER['SERVER_NAME']);
        }
        if (!empty($_SERVER['REMOTE_ADDR'])) {
            $this->setRemoteAddress($_SERVER['REMOTE_ADDR']);
        }
        if (!empty($_SERVER['REQUEST_URI'])) {
            $this->setDocumentPath($_SERVER['REQUEST_URI']);
        }
        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            $this->setUserAgent($_SERVER['HTTP_USER_AGENT']);
        }
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $this->setDocumentReferer($_SERVER['HTTP_REFERER']);
        }
        if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $this->setAcceptLanguage($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        }
        if (array_key_exists('HTTP_DNT', $_SERVER)) {
            $this->setDoNotTrack($_SERVER['HTTP_DNT']);
        }
        $this->setOptions($options);
    }

    /**
     * @return string
     */
    public function getCurrentJsFile()
    {
        if (empty($this->currentJsFile)) {
            $this->setCurrentJsFile();
        }
        return $this->currentJsFile;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        if (!$this->setVersionCalled) {
            $this->setVersionFromJs();
        }
        return $this->version;
    }

    /**
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * @return string
     */
    public function getAcceptLanguage()
    {
        return $this->acceptLanguage;
    }

    /**
     * @return string
     */
    public function getServerName()
    {
        return $this->serverName;
    }

    /**
     * @return string
     */
    public function getRemoteAddress()
    {
        return $this->remoteAddress;
    }

    /**
     * @return string
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @return string
     */
    public function getDocumentReferer()
    {
        return $this->documentReferer;
    }

    /**
     * @return string
     */
    public function getDocumentPath()
    {
        return $this->documentPath;
    }

    /**
     * @return integer|null
     */
    public function getDoNotTrack()
    {
        return $this->doNotTrack;
    }

    /**
     * @return string
     */
    public function getPageTitle()
    {
        return $this->pageTitle;
    }

    /**
     * @return string
     */
    public function getCustomVariables()
    {
        return $this->customVariables;
    }

    /**
     * Returns the value of the specified custom variable
     *
     * @param integer $index
     * @throws Gass\Exception\OutOfBoundsException
     * @return string
     */
    public function getVisitorCustomVar($index)
    {
        if (isset($this->customVariables['index'.$index])) {
            return $this->customVariables['index'.$index]['value'];
        }
        throw new Exception\OutOfBoundsException('The index: "'.$index.'" has not been set.');
    }

    /**
     * Returns all custom vars for a specific scope
     *
     * @param integer $scope
     * @return array
     */
    public function getCustomVarsByScope($scope = 3)
    {
        $customVars = $this->getCustomVariables();
        $returnArray = array();
        foreach ($customVars as $customVar) {
            if ($customVar['scope'] == $scope) {
                $returnArray[] = implode('=', $customVar);
            }
        }
        return $returnArray;
    }

    /**
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * @return array
     */
    public function getSearchEngines()
    {
        if (empty($this->searchEngines) && !$this->setSearchEnginesCalled) {
            $this->setSearchEnginesFromJs();
        }
        return $this->searchEngines;
    }

    /**
     * @return null|Gass\BotInfo
     */
    public function getBotInfo()
    {
        return $this->botInfo;
    }

    /**
     * @return null|array|Gass\Http\Interface
     */
    public function getHttp()
    {
        return $this->http;
    }

    /**
     * @return boolean
     */
    public function getIgnoreDoNotTrack()
    {
        return $this->ignoreDoNotTrack;
    }

    /**
     * Gets a specific option
     *
     * @param string $name
     * @throws Gass\Exception\OutOfRangeException
     * @return mixed
     */
    public function getOption($name)
    {
        $methodName = 'get'.ucfirst($name);
        if (method_exists($this, $methodName)) {
            $reflectionMethod = new \ReflectionMethod($this, $methodName);
            if ($reflectionMethod->isPublic()) {
                return $this->$methodName();
            }
        }
        throw new Exception\OutOfRangeException($name.' is not an available option.');
    }

    /**
     * Checks whether a variable can be cast to string
     * Returns the var cast as string if so
     * Throws an InvalidArgumentException if not
     *
     * @param string $var
     * @param string $description
     * @throws Gass\Exception\InvalidArgumentException
     * @return string
     */
    private function getAsString($var, $description)
    {
        if (!is_string($var)) {
            if (!is_scalar($var) && !is_null($var)
                    && (!is_object($var) || !method_exists($var, '__toString'))) {
                throw new Exception\InvalidArgumentException($description.' must be a string.');
            }
            $var = (string) $var;
        }
        return $var;
    }

    /**
     * Sets the file contents of the latest ga.js version
     *
     * @return Gass\GoogleAnalyticsServerSide
     */
    protected function setCurrentJsFile()
    {
        $this->currentJsFile = trim(Http\Http::request(self::JS_URL)->getResponse());
        return $this;
    }

    /**
     * @param string $version
     * @return Gass\GoogleAnalyticsServerSide
     * @throws Gass\Exception\InvalidArgumentException
     */
    public function setVersion($version)
    {
        $this->setVersionCalled = true;
        $version = $this->getAsString($version, 'Version');
        if (1 !== preg_match('/^(\d+\.){2}\d+$/', $version)) {
            throw new Exception\InvalidArgumentException('Invalid version number provided: '.$version);
        }
        $this->version = $version;
        return $this;
    }

    /**
     * @param string $userAgent
     * @return Gass\GoogleAnalyticsServerSide
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $this->getAsString($userAgent, 'User Agent');
        Http\Http::setUserAgent($this->userAgent);
        if ($this->botInfo instanceof BotInfo\BotInfo) {
            $this->botInfo->setUserAgent($this->userAgent);
        }
        return $this;
    }

    /**
     * @param string $acceptLanguage
     * @return Gass\GoogleAnalyticsServerSide
     */
    public function setAcceptLanguage($acceptLanguage)
    {
        $acceptLanguage = $this->getAsString($acceptLanguage, 'Accept Language');
        if (false !== strpos($acceptLanguage, ';')) {
            list($acceptLanguage, $other) = explode(';', $acceptLanguage, 2);
        }
        if (false !== strpos($acceptLanguage, ',')) {
            list($acceptLanguage, $other) = explode(',', $acceptLanguage, 2);
        }
        $acceptLanguage = strtolower($acceptLanguage);
        $langValidator = new Validate\LanguageCode;
        if (!$langValidator->isValid($acceptLanguage)) {
            throw new Exception\InvalidArgumentException(
                'Accept Language validation errors: '.implode(', ', $langValidator->getMessages())
            );
        }
        $this->acceptLanguage = $acceptLanguage;
        Http\Http::setAcceptLanguage($acceptLanguage);
        return $this;
    }

    /**
     * @param string $serverName
     * @return Gass\GoogleAnalyticsServerSide
     */
    public function setServerName($serverName)
    {
        $this->serverName = $this->getAsString($serverName, 'Server Name');
        return $this;
    }

    /**
     * @param string $remoteAddress
     * @return Gass\GoogleAnalyticsServerSide
     * @throws Gass\Exception\InvalidArgumentException
     */
    public function setRemoteAddress($remoteAddress)
    {
        $remoteAddress = $this->getAsString($remoteAddress, 'Remote Address');
        $ipValidator = new Validate\IpAddress;
        if (!$ipValidator->isValid($remoteAddress)) {
            throw new Exception\InvalidArgumentException(
                'Remote Address validation errors: '.implode(', ', $ipValidator->getMessages())
            );
        }
        $this->remoteAddress = $remoteAddress;
        Http\Http::setRemoteAddress($this->remoteAddress);
        if ($this->botInfo instanceof BotInfo\BotInfo) {
            $this->botInfo->setRemoteAddress($this->remoteAddress);
        }
        return $this;
    }

    /**
     * @param string $account
     * @return Gass\GoogleAnalyticsServerSide
     * @throws Gass\Exception\InvalidArgumentException
     */
    public function setAccount($account)
    {
        $account = $this->getAsString($account, 'Account');
        if (1 !== preg_match('/^(MO|UA)-\d{4,}-\d+$/', $account)) {
            throw new Exception\InvalidArgumentException(
                'Google Analytics user account must be in the format: UA-XXXXXXX-X or MO-XXXXXXX-X'
            );
        }
        $this->account = trim($account);
        return $this;
    }

    /**
     * @param string $documentReferer
     * @return Gass\GoogleAnalyticsServerSide
     * @throws Gass\Exception\InvalidArgumentException
     */
    public function setDocumentReferer($documentReferer)
    {
        $documentReferer = trim($this->getAsString($documentReferer, 'Document Referer'));
        if (!empty($documentReferer)
                && (!preg_match('#^([a-z0-9]{3,})://([a-z0-9\.-]+)(/\S*)??$#', $documentReferer)
                        || false === @parse_url($documentReferer))) {
            throw new Exception\InvalidArgumentException('Document Referer must be a valid URL.');
        }
        $this->documentReferer = $documentReferer;
        return $this;
    }

    /**
     * @param string $documentPath
     * @return Gass\GoogleAnalyticsServerSide
     */
    public function setDocumentPath($documentPath)
    {
        $documentPath = $this->getAsString($documentPath, 'Document Path');
        if (false !== ($queryPos = strpos($documentPath, '?'))) {
            $documentPath = substr($documentPath, 0, $queryPos);
        }
        $this->documentPath = $documentPath;
        return $this;
    }

    /**
     * @param integer|string|null $doNotTrack
     * @return Gass\GoogleAnalyticsServerSide
     */
    public function setDoNotTrack($doNotTrack)
    {
        if (!in_array($doNotTrack, array(1, 0, null, '1', '0', 'null', 'unset'))) {
            throw new Exception\InvalidArgumentException('$doNotTrack must have a value of 1, 0, \'unset\' or null');
        } elseif (is_string($doNotTrack)) {
            $doNotTrack = (is_numeric($doNotTrack)) ? (int) $doNotTrack : null;
        }
        $this->doNotTrack = $doNotTrack;
        return $this;
    }

    /**
     * @param string $pageTitle
     * @return Gass\GoogleAnalyticsServerSide
     */
    public function setPageTitle($pageTitle)
    {
        $this->pageTitle = $this->getAsString($pageTitle, 'Page Title');
        return $this;
    }

    /**
     * @param boolean $ignoreDoNotTrack
     * @return Gass\GoogleAnalyticsServerSide
     */
    public function setIgnoreDoNotTrack($ignoreDoNotTrack = true)
    {
        if (!is_bool($ignoreDoNotTrack)) {
            throw new Exception\InvalidArgumentException('$ignoreDoNotTrack must be a boolean.');
        }
        $this->ignoreDoNotTrack = $ignoreDoNotTrack;
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
     * @throws Gass\Exception\OutOfBoundsException
     * @throws Gass\Exception\InvalidArgumentException
     * @throws Gass\Exception\DomainException
     * @return Gass\GoogleAnalyticsServerSide
     */
    public function setCustomVar($name, $value, $scope = 3, $index = null)
    {
        if ($index === null) {
            $index = 0;
            do {
                $index++;
            } while (isset($this->customVariables['index'.$index]) && $index < 6);
            if ($index > 5) {
                throw new Exception\OutOfBoundsException('You cannot add more than 5 custom variables.');
            }
        } elseif (!is_int($index) || $index < 1 || $index > 5) {
            throw new Exception\OutOfBoundsException('The index must be an integer between 1 and 5.');
        }
        if (!is_int($scope) || $scope < 1 || $scope > 3) {
            throw new Exception\InvalidArgumentException('The Scope must be a value between 1 and 3');
        }
        $name = $this->getAsString($name, 'Custom Var Name');
        $value = $this->getAsString($value, 'Custom Var Value');
        if (128 < strlen($name.$value)) {
            throw new Exception\DomainException('The name / value combination exceeds the 128 byte custom var limit.');
        }
        $this->customVariables['index'.$index] = array(
            'index' => (int) $index,
            'name'  => (string) $this->removeSpecialCustomVarChars($name),
            'value' => (string) $this->removeSpecialCustomVarChars($value),
            'scope' => (int) $scope
        );
        return $this;
    }

    /**
     * Sets the custom vars from the cookie if not already set by developer
     *
     * @param string $customVarsString
     */
    private function setCustomVarsFromCookie($customVarsString)
    {
        if (!empty($customVarsString)) {
            if (false !== strpos($customVarsString, '^')) {
                $customVars = explode('^', $customVarsString);
            } else {
                $customVars = array($customVarsString);
            }
            $currentCustVars = $this->getCustomVariables();
            foreach ($customVars as $customVar) {
                list($custVarIndex, $custVarName, $custVarValue, $custVarScope) = explode('=', $customVar, 4);
                if (!isset($currentCustVars['index'.$custVarIndex])) {
                    $this->setCustomVar($custVarName, $custVarValue, $custVarScope, $custVarIndex);
                }
            }
        }
    }

    /**
     * Removes the special characters used when defining custom vars in the url
     *
     * @param string $value
     * @return string
     */
    private function removeSpecialCustomVarChars($value)
    {
        return str_replace(array('*', '(', ')', '^'), ' ', $value);
    }

    /**
     * Removes a previously set custom variable
     *
     * @param integer $index
     * @return Gass\GoogleAnalyticsServerSide
     */
    public function deleteCustomVar($index)
    {
        unset($this->customVariables['index'.$this->getAsString($index, 'Custom Var Index')]);
        return $this;
    }

    /**
     * @param string $charset
     * @return Gass\GoogleAnalyticsServerSide
     */
    public function setCharset($charset)
    {
        $this->charset = strtoupper($this->getAsString($charset, 'Charset'));
        return $this;
    }

    /**
     * @param array $searchEngines
     * @throws Gass\Exception\InvalidArgumentException
     * @throws Gass\Exception\DomainException
     * @throws Gass\Exception\OutOfBoundsException
     * @return Gass\GoogleAnalyticsServerSide
     */
    public function setSearchEngines(array $searchEngines)
    {
        $this->setSearchEnginesCalled = true;
        foreach ($searchEngines as $searchEngine => $queryParams) {
            if (!is_array($queryParams) || 1 > count($queryParams)) {
                throw new Exception\DomainException('searchEngines entry '.$searchEngine.' invalid');
            }
            if (!is_string($searchEngine)
                    || 1 !== preg_match('/^[a-z0-9\.-]+$/', $searchEngine)) {
                throw new Exception\OutOfBoundsException('search engine name "'.$searchEngine.'" is invalid');
            }
            foreach ($queryParams as $queryParameter) {
                if (!is_string($queryParameter)
                        || 1 !== preg_match('/^[a-z0-9_\-]+$/i', $queryParameter)) {
                    throw new Exception\DomainException(
                        'search engine query parameter "'.$queryParameter.'" is invalid'
                    );
                }
            }
        }
        $this->searchEngines = $searchEngines;
        return $this;
    }

    /**
     * Sets confguration options for the BotInfo adapter to use, or the class adapter to use itself
     *
     * @param array|boolean|Gass\BotInfo\Interface|null $botInfo
     * @throws Gass\Exception\InvalidArgumentException
     * @return Gass\GoogleAnalyticsServerSide
     */
    public function setBotInfo($botInfo)
    {
        if (!is_array($botInfo) && !is_bool($botInfo) && $botInfo !== null
                && !$botInfo instanceof BotInfo\BotInfoInterface) {
            throw new Exception\InvalidArgumentException(
                'botInfo must be an array, boolean, null'.
                ' or a class which implements Gass\BotInfo\Interface.'
            );
        } elseif ($botInfo !== null) {
            if ($botInfo instanceof BotInfo\BotInfoInterface) {
                $this->botInfo = new BotInfo\BotInfo(array(), $botInfo);
            } elseif (is_array($botInfo)) {
                $this->botInfo = new BotInfo\BotInfo($botInfo);
            } else {
                $this->botInfo = new BotInfo\BotInfo;
            }
            $this->botInfo->setUserAgent($this->getUserAgent())
                ->setRemoteAddress($this->getRemoteAddress());
        } else {
            $this->botInfo = null;
        }
        return $this;
    }

    /**
     * @param null|array|Gass\Http\Interface $http
     * @throws Gass\Exception\InvalidArgumentException
     * @return Gass\GoogleAnalyticsServerSide
     */
    public function setHttp($http = null)
    {
        if ($http !== null && !is_array($http)
                && !$http instanceof Http\HttpInterface) {
            throw new Exception\InvalidArgumentException(
                'http must be an array, null'.
                ' or a class which implements Gass\Http\Interface.'
            );
        }
        if ($http !== null) {
            if ($http instanceof Http\HttpInterface) {
                Http\Http::getInstance(array(), $http);
            } elseif (is_array($http)) {
                Http\Http::getInstance($http);
            }
        }
        Http\Http::setAcceptLanguage($this->getAcceptLanguage())
                 ->setRemoteAddress($this->getRemoteAddress())
                 ->setUserAgent($this->getUserAgent());
        $this->http = $http;
        return $this;
    }

    /**
     * @param array $options
     * @return Gass\GoogleAnalyticsServerSide
     */
    public function setOptions(array $options)
    {
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
     * @return Gass\GoogleAnalyticsServerSide
     */
    public function setOption($name, $value)
    {
        $this->getOption($this->getAsString($name, 'Option Name'));
        $methodName = 'set'.ucfirst($name);
        if (method_exists($this, $methodName)) {
            $reflectionMethod = new \ReflectionMethod($this, $methodName);
            if ($reflectionMethod->isPublic()) {
                return $this->$methodName($value);
            }
        }
        return $this;
    }

    /**
     * Returns the last saved event as a string for the URL parameters
     *
     * @param string $category
     * @param string $action
     * @param string $label [optional]
     * @param integer $value [optional]
     * @return string
     * @throws Gass\Exception\DomainException
     */
    public function getEventString($category, $action = null, $label = null, $value = null)
    {
        // Deal with BC
        if (is_array($category)) {
            if (isset($category['action'])) {
                $action = $category['action'];
            }
            if (isset($category['label'])) {
                $label = $category['label'];
            }
            if (isset($category['value'])) {
                $value = $category['value'];
            }
            $category = (isset($category['category'])) ? $category['category'] : null;
        }
        $category = $this->getAsString($category, 'Event Category');
        $action = $this->getAsString($action, 'Event Action');
        if (empty($category) || empty($action)) {
            throw new Exception\InvalidArgumentException('An event requires at least a category and action');
        }
        if ($label !== null) {
            $label = $this->getAsString($label, 'Event Label');
        }
        if ($value !== null && !is_int($value)) {
            throw new Exception\InvalidArgumentException('Value must be an integer.');
        }
        return '5('.$category.'*'.$action.(empty($label) ? '' : '*'.$label).')'.
            (($value !== null) ? '('.$value.')' : '');
    }

    /**
     * Returns the saved custom variables as a string for the URL parameters
     *
     * @return string|null
     */
    public function getCustomVariableString()
    {
        $customVars = $this->getCustomVariables();
        if (!empty($customVars)) {
            $names = array();
            $values = array();
            $scopes = array();
            foreach ($customVars as $value) {
                $names[] = $value['name'];
                $values[] = $value['value'];
                if (in_array($value['scope'], array(1,2))) {
                    $scopes[] = (($value['index'] > (count($scopes) + 1)) ? $value['index'].'!' : '' ) .
                        $value['scope'];
                }
            }
            return '8('.implode($names, '*').')9('.implode($values, '*').')11('.implode($scopes, '*').')';
        }
        return null;
    }

    /**
     * The last octect of the IP address is removed to anonymize the user.
     *
     * @param string $remoteAddress [optional]
     * @return string
     */
    public function getIPToReport($remoteAddress = null)
    {
        if ($remoteAddress !== null) {
            $this->setRemoteAddress($remoteAddress);
        }
        $remoteAddress = $this->getRemoteAddress();
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
     */
    public function getDomainHash($domain = null)
    {
        $domain = ($domain === null) ? $this->serverName
                                     : $this->getAsString($domain, 'Domain');
        $a = 1;
        $c = 0;
        if (!empty($domain)) {
            $a = 0;
            for ($h = strlen($domain)-1; $h>=0; $h--) {
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
     * see: https://developers.google.com/analytics/resources/concepts/gaConceptsCookies
     *      http://www.analyticsevangelist.com/google-analytics/how-to-read-google-analytics-cookies/
     *      http://www.cheatography.com/jay-taylor/cheat-sheets/google-analytics-cookies-v2/
     *      http://www.tutkiun.com/2011/04/a-google-analytics-cookie-explained.html
     *
     * @param array $cookies [optional]
     * @return Gass\GoogleAnalyticsServerSide
     */
    public function setCookies(array $cookies = array())
    {
        $doNotTrack = (1 === $this->getDoNotTrack() && !$this->getIgnoreDoNotTrack()) ? true : false;
        if (!$this->setCookiesCalled && !$doNotTrack && empty($cookies)) {
            $this->setCookiesFromRequestHeaders();
        }
        $this->setCookiesCalled = true;
        $cookies = (empty($cookies)) ? $this->getCookies() : $cookies;

        // Check the cookies provided are valid for this class,
        // getCookie will throw the exception if the name isn't valid
        foreach ($cookies as $name => $value) {
            $this->getCookie($name);
        }

        /**
         * Get the correct values out of the google analytics cookies
         */
        if (!empty($cookies['__utma'])) {
            list($domainId, $visitorId, $firstVisit, $lastVisit, $currentVisit, $session)
                = explode('.', $cookies['__utma'], 6);
        }
        if (!empty($cookies['__utmb'])) {
            list($domainId, $pageVisits, $session, $currentVisit) = explode('.', $cookies['__utmb'], 4);
        }
        if (!empty($cookies['__utmc'])) {
            $domainId = $cookies['__utmc'];
        }
        if (!empty($cookies['__utmv']) && false !== strpos($cookies['__utmv'], '.|')) {
            list($domainId, $customVars) = explode('.|', $cookies['__utmv'], 2);
            $this->setCustomVarsFromCookie($customVars);
        }
        if (!empty($cookies['__utmz'])) {
            list($domainId, $firstVisit, $session, $campaignNumber, $campaignParameters)
                = explode('.', $cookies['__utmz'], 5);
        }

        /**
         * Set the new section values for the cookies
         */
        if (!isset($domainId) || !is_numeric($domainId)) {
            $domainId = $this->getDomainHash();
        }
        if (!isset($visitorId) || !is_numeric($visitorId)) {
            $visitorId = rand(0, 999999999);
        }
        if (!isset($firstVisit) || !is_numeric($firstVisit)) {
            $firstVisit = time();
        }
        if (!isset($session) || !is_numeric($session)) {
            $session = 1;
        } elseif (!isset($cookies['__utmz'],$cookies['__utmb'])) {
            $session++;
        }
        $pageVisits = (!isset($pageVisits) || !is_numeric($pageVisits)) ? 1 : ++$pageVisits;
        $lastVisit = (!isset($currentVisit) || !is_numeric($currentVisit)) ? time() : $currentVisit;
        $currentVisit = time();

        /**
         * Works out where the traffic came from and sets the end part of the utmz cookie accordingly
         */
        $previousCampaignParameters = (!isset($campaignParameters) || false === strpos($campaignParameters, 'utmcsr='))
            ? ''
            : $campaignParameters;
        $referer = $this->getDocumentReferer();
        $serverName = $this->getServerName();
        if (!empty($referer) && !empty($serverName) && false === strpos($referer, $serverName)
                && preg_match('#^([a-z0-9]{3,})://([a-z0-9\.-]+)(/\S*)??$#', $referer)
                && false !== ($refererParts = @parse_url($referer))
                && isset($refererParts['host'], $refererParts['path'])
        ) {
            $refererSearchEngine = false;
            $searchEngines = $this->getSearchEngines();
            foreach ($searchEngines as $searchEngine => $queryParams) {
                $refererDomainParts = explode('.', $refererParts['host']);
                array_pop($refererDomainParts);
                if (!empty($refererParts['query']) && ((false !== strpos($searchEngine, '.')
                        && false !== strpos($refererParts['host'], $searchEngine))
                            || in_array($searchEngine, $refererDomainParts))) {
                    $refererSearchEngine = $searchEngine;
                    break;
                }
            }
            if (false === $refererSearchEngine) {
                $campaignParameters = 'utmcsr='.$refererParts['host'].
                    '|utmccn=(referral)|utmcmd=referral|utmcct='.$refererParts['path'];
            } else {
                $queryParameters = $searchEngines[$searchEngine];
                parse_str($refererParts['query'], $refererQueryParams);
                $queryParamValue = '(not provided)';
                foreach ($queryParameters as $queryParameter) {
                    if (array_key_exists($queryParameter, $refererQueryParams)
                            && !empty($refererQueryParams[$queryParameter])) {
                        $queryParamValue = $refererQueryParams[$queryParameter];
                        break;
                    }
                }
                $campaignParameters = 'utmcsr='.$searchEngine.
                    '|utmccn=(organic)|utmcmd=organic|utmctr='.$queryParamValue;
            }
        }
        if (!isset($campaignParameters) || false === strpos($campaignParameters, 'utmcsr=')) {
            $campaignParameters = 'utmcsr=(direct)|utmccn=(direct)|utmcmd=(none)';
        }

        if (!isset($campaignNumber) || !is_numeric($campaignNumber)) {
            $campaignNumber = 1;
        } elseif ($previousCampaignParameters != $campaignParameters) {
            $campaignNumber++;
        }

        $sendCookieHeaders = ($this->sendCookieHeaders && !$doNotTrack) ? true : false;
        /**
         * Set the cookies to the required values
         */
        $this->setCookie(
            '__utma',
            $domainId.'.'.$visitorId.'.'.$firstVisit.'.'.$lastVisit.'.'.$currentVisit.'.'.$session,
            $sendCookieHeaders
        );
        $this->setCookie('__utmb', $domainId.'.'.$pageVisits.'.'.$session.'.'.$currentVisit, $sendCookieHeaders);
        $this->setCookie('__utmc', $domainId, $sendCookieHeaders);
        $this->setCookie(
            '__utmz',
            $domainId.'.'.$firstVisit.'.'.$session.'.'.$campaignNumber.'.'.$campaignParameters,
            $sendCookieHeaders
        );

        $scope1Vars = $this->getCustomVarsByScope(1);
        if (!empty($scope1Vars)) {
            $this->setCookie('__utmv', $domainId.'.|'.implode('^', $scope1Vars), $sendCookieHeaders);
        }

        if ($sendCookieHeaders) {
            $this->disableCookieHeaders();
        }
        return $this;
    }

    /**
     * Returns all the google analytics cookies as an array
     *
     * @return array
     */
    public function getCookies()
    {
        return $this->cookies;
    }

    /**
     * Returns the google analytics cookies as a string ready to be set to google analytics
     *
     * @return string
     */
    public function getCookiesString()
    {
        $cookieParts = array();
        $currentCookies = $this->getCookies();
        unset($currentCookies['__utmv']);
        foreach ($currentCookies as $name => $value) {
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
     * @param boolean $setHeader
     * @throws Gass\Exception\LengthException
     * @throws Gass\Exception\OutOfBoundsException
     * @return Gass\GoogleAnalyticsServerSide
     */
    private function setCookie($name, $value, $setHeader = true)
    {
        $name = trim($this->getAsString($name, 'Cookie Name'));
        $value = trim($this->getAsString($value, 'Cookie Value'));
        if (empty($value)) {
            throw new Exception\LengthException('Cookie cannot have an empty value');
        }
        if (array_key_exists($name, $this->cookies) && !empty($value)) {
            $this->cookies[$name] = $value;
            switch ($name) {
                case '__utmb':
                    $cookieLife = time() + $this->sessionCookieTimeout;
                    break;
                case '__utmc':
                    $cookieLife = 0; // Session Cookie
                    break;
                case '__utmz':
                    $cookieLife = time() + (((60*60)*24)*90); // 3-Month Cookie
                    break;
                default:
                    $cookieLife = time() + $this->visitorCookieTimeout;
            }
            if ($setHeader) {
                setcookie($name, $value, $cookieLife, self::COOKIE_PATH, '.'.$this->getServerName());
            }
            return $this;
        }
        throw new Exception\OutOfBoundsException('Cookie by name: '.$name.' is not related to Google Analytics.');
    }

    /**
     * Sets the session cookie timeout
     *
     * @param integer $sessionCookieTimeout (milliseconds)
     * @return Gass\GoogleAnalyticsServerSide
     */
    public function setSessionCookieTimeout($sessionCookieTimeout)
    {
        if (!is_int($sessionCookieTimeout)) {
            throw new Exception\InvalidArgumentException('Session Cookie Timeout must be an integer.');
        }
        $this->sessionCookieTimeout = round($sessionCookieTimeout / 1000);
        return $this;
    }

    /**
     * Sets the visitor cookie timeout
     *
     * @param integer $visitorCookieTimeout (milliseconds)
     * @return Gass\GoogleAnalyticsServerSide
     */
    public function setVisitorCookieTimeout($visitorCookieTimeout)
    {
        if (!is_int($visitorCookieTimeout)) {
            throw new Exception\InvalidArgumentException('Visitor Cookie Timeout must be an integer.');
        }
        $this->visitorCookieTimeout = round($visitorCookieTimeout / 1000);
        return $this;
    }

    /**
     * Disables whether or not the cookie headers are sent when setCookies is called
     *
     * @return Gass\GoogleAnalyticsServerSide
     */
    public function disableCookieHeaders()
    {
        $this->sendCookieHeaders = false;
        return $this;
    }

    /**
     * Returns the current value of a google analytics cookie
     *
     * @param string $name
     * @throws Gass\Exception\OutOfBoundsException
     * @return string
     */
    private function getCookie($name)
    {
        $name = $this->getAsString($name, 'Cookie Name');
        if (array_key_exists($name, $this->cookies)) {
            return $this->cookies[$name];
        }
        throw new Exception\OutOfBoundsException('Cookie by name: '.$name.' is not related to Google Analytics.');
    }

    /**
     * Sets the cookies values inside the class from
     * the cookies sent with the request headers
     *
     * @return Gass\GoogleAnalyticsServerSide
     */
    public function setCookiesFromRequestHeaders()
    {
        foreach ($this->getCookies() as $name => $value) {
            if (!empty($_COOKIE[$name])) {
                $this->setCookie($name, $_COOKIE[$name], false);
            }
        }
        return $this;
    }

    /**
     * Retrieves the current version of Google Analytics from the ga.js file
     *
     * @return Gass\GoogleAnalyticsServerSide
     */
    public function setVersionFromJs()
    {
        $currentJs = $this->getCurrentJsFile();
        if (!empty($currentJs)) {
            $regEx = '((\d+\.){2}\d+)';
            $version = preg_replace('/^[\s\S]+\=function\(\)\{return[\'"]'.$regEx.'[\'"][\s\S]+$/i', '$1', $currentJs);
            if (preg_match('/^'.$regEx.'$/', $version)) {
                $this->setVersion($version);
            }
        }
        return $this;
    }

    /**
     * Retrieves the current list of search engines and query parameters from the ga.js file
     *
     * @return Gass\GoogleAnalyticsServerSide
     */
    public function setSearchEnginesFromJs()
    {
        $currentJs = $this->getCurrentJsFile();
        if (!empty($currentJs)) {
            $regEx = '([a-z0-9:\s-_\.]+)';
            $searchEngineString = preg_replace(
                '/^[\s\S]+\=[\'"]'.$regEx.'[\'"]\.split\([\'"]\s+[\'"]\)[\s\S]+$/i',
                '$1',
                $currentJs
            );
            if (preg_match('/^'.$regEx.'$/i', $searchEngineString)) {
                $searchEngineArray = preg_split('#\s+#', $searchEngineString);
                $searchEngines = array();
                foreach ($searchEngineArray as $searchEngine) {
                    $searchEngineParts = explode(':', $searchEngine);
                    if (2 == count($searchEngineParts)) {
                        if (isset($searchEngines[$searchEngineParts[0]])
                                && is_array($searchEngines[$searchEngineParts[0]])) {
                            $searchEngines[$searchEngineParts[0]][] = $searchEngineParts[1];
                        } else {
                            $searchEngines[$searchEngineParts[0]] = array($searchEngineParts[1]);
                        }
                    }
                }
                $this->setSearchEngines($searchEngines);
            }
        }
        return $this;
    }

    /**
     * Tracks a Page View in Google Analytics
     *
     * @param string $url
     * @return Gass\GoogleAnalyticsServerSide
     */
    public function trackPageview($url = null)
    {
        if ($url !== null) {
            $url = $this->getAsString($url, 'Page View URL');
            if (0 != strpos($url, '/')) {
                if (!preg_match('#^([a-z0-9]{3,})://([a-z0-9\.-]+)(/\S*)??$#', $url)
                        || false === ($urlParts = @parse_url($url))) {
                    throw new Exception\DomainException('Url is invalid: '.$url);
                }
                $url = $urlParts['path'];
                $this->setServerName($urlParts['host']);
            }
            $this->setDocumentPath($url);
        }
        $queryParams = array('utmp' => urldecode((string) $this->getDocumentPath()));
        if (null !== ($pageTitle = $this->getPageTitle()) && !empty($pageTitle)) {
            $queryParams['utmdt'] = $pageTitle;
        }
        return $this->track($queryParams);
    }

    /**
     * Tracks an Event in Google Analytics
     *
     * @param string $category
     * @param string $action
     * @param string $label [optional]
     * @param integer $value [optional]
     * @param boolean $nonInteraction [optional]
     * @throws Gass\Exception\InvalidArgumentException
     * @return Gass\GoogleAnalyticsServerSide
     */
    public function trackEvent($category, $action, $label = null, $value = null, $nonInteraction = false)
    {
        if (!is_bool($nonInteraction)) {
            throw new Exception\InvalidArgumentException('NonInteraction must be a boolean.');
        }
        $queryParams = array(
            'utmt' => 'event',
            'utme' => $this->getEventString($category, $action, $label, $value)
        );
        if ($nonInteraction === true) {
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
     * http://www.cheatography.com/jay-taylor/cheat-sheets/google-analytics-utm-parameters-v2/
     *
     * @param array $extraParams
     * @return boolean|GoogleAnalyticsServerSide
     */
    private function track(array $extraParams = array())
    {
        if ($this->botInfo !== null && $this->botInfo->getIsBot()) {
            return false;
        }

        $account = (string) $this->getAccount();
        if (empty($account)) {
            throw new Exception\DomainException('The account number must be set before any tracking can take place.');
        }
        $domainName = (string) $this->getServerName();
        $documentReferer = (string) $this->getDocumentReferer();
        $documentReferer = (empty($documentReferer) && $documentReferer !== '0')
                            ? '-'
                            : urldecode($documentReferer);
        $this->setCookies();

        // Construct the gif hit url.
        $queryParams = array(
            'utmwv' => $this->getVersion(),
            'utmn'  => rand(0, 0x7fffffff),
            'utmhn' => $domainName,
            'utmr'  => $documentReferer,
            'utmac' => $account,
            'utmcc' => $this->getCookiesString(),
            'utmul' => $this->getAcceptLanguage(),
            'utmcs' => $this->getCharset(),
            'utmu'  => 'q~'
        );
        if (0 === strpos($account, 'MO-')) {
            $queryParams['utmip'] = $this->getIPToReport();
        }
        $queryParams = array_merge($queryParams, $extraParams);

        if (null !== ($customVarString = $this->getCustomVariableString())) {
            $queryParams['utme'] = ((isset($queryParams['utme']) && !empty($queryParams['utme']))
                                    ? $queryParams['utme']
                                    : '') . $customVarString;
        }

        $utmUrl = self::GIF_URL.'?'.http_build_query($queryParams, null, '&');

        Http\Http::request($utmUrl);
        return $this;
    }
}

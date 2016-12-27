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
 * @version     0.10.0 Beta
 * @example     $gass = new \Gass\GoogleAnalyticsServerSide;
 *              $gass->setAccount('UA-XXXXXXX-X')
 *                  ->trackPageView();
 */

namespace Gass;

use Gass\BotInfo\BotInfo;
use Gass\BotInfo\BotInfoInterface;
use Gass\Exception\DomainException;
use Gass\Exception\InvalidArgumentException;
use Gass\Exception\LengthException;
use Gass\Exception\OutOfBoundsException;
use Gass\Exception\OutOfRangeException;
use Gass\Http\Http;
use Gass\Http\HttpInterface;
use Gass\Validate\IpAddress as ValidateIpAddress;
use Gass\Validate\LanguageCode as ValidateLanguageCode;
use Gass\Validate\Url as ValidateUrl;

/**
 * Main Google Analytics server Side Class
 *
 * @author      Tom Chapman
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
    const GIF_URL = 'https://www.google-analytics.com/__utm.gif';

    /**
     * Location of the current JS file
     * Changelog: https://developers.google.com/analytics/community/gajs_changelog
     *
     * @var string
     */
    const JS_URL = 'https://www.google-analytics.com/ga.js';

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
    private $version = '5.6.7';

    /**
     * Whether or not setVersion has been called, used
     * to determine whther or ot to set from ga.js file
     *
     * @var bool
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
     * @var int|null
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
     * Title of the current page.
     *
     * @var string
     */
    private $pageTitle;

    /**
     * Data for the custom variables.
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
     * @var bool
     */
    private $sendCookieHeaders = true;

    /**
     * Timeout of the default user session cookie (default half hour)
     *
     * @var int
     */
    private $sessionCookieTimeout = 1800;

    /**
     * Timout of the default visitor cookie (default two years)
     *
     * @var int
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
        '__utmz' => null,
    );

    /**
     * Whether or not setCookies has been called
     *
     * @var bool
     */
    private $setCookiesCalled = false;

    /**
     * Search engines and their query parameters
     * used to determine if referer is organic or not
     *
     * @var array
     */
    private $searchEngines = array(
        '360.cn' => array('q'),
        'alice' => array('qs'),
        'aol' => array('query', 'q'),
        'ask' => array('q'),
        'auone' => array('q'),
        'avg' => array('q'),
        'babylon' => array('q'),
        'baidu' => array('wd', 'word'),
        'biglobe' => array('q'),
        'bing' => array('q'),
        'centrum.cz' => array('q'),
        'cnn' => array('query'),
        'comcast' => array('q'),
        'conduit' => array('q'),
        'daum' => array('q'),
        'eniro' => array('search_word'),
        'globo' => array('q'),
        'go.mail.ru' => array('q'),
        'goo.ne' => array('MT'),
        'google' => array('q'),
        'haosou.com' => array('q'),
        'images.google' => array('q'),
        'incredimail' => array('q'),
        'kvasir' => array('q'),
        'lycos' => array('q', 'query'),
        'msn' => array('q'),
        'najdi' => array('q'),
        'naver' => array('query'),
        'onet' => array('qt', 'q'),
        'pchome' => array('q'),
        'rakuten' => array('qt'),
        'rambler' => array('query'),
        'search-results' => array('q'),
        'search.smt.docomo' => array('MT'),
        'seznam' => array('q'),
        'so.com' => array('q'),
        'sogou' => array('query'),
        'startsiden' => array('q'),
        'terra' => array('query'),
        'tut.by' => array('query'),
        'ukr' => array('q'),
        'virgilio' => array('qs'),
        'yahoo' => array('p', 'q'),
        'yandex' => array('text'),
    );

    /**
     * Whether or not setSearchEngines has been called, used
     * to determine whther or ot to set from ga.js file
     *
     * @var bool
     */
    private $setSearchEnginesCalled = false;

    /**
     * Class to check if the current request is a bot or not
     *
     * @var null|BotInfo
     */
    private $botInfo;

    /**
     * Options to pass to Gass\Http
     *
     * @var null|array|\Gass\Http\Interface
     */
    private $http;

    /**
     * Whether or not to ignore the Do Not Track header
     *
     * @var bool
     */
    private $ignoreDoNotTrack = false;

    /**
     * URL Validator
     *
     * @var Gass\Validate\Url
     */
    private $urlValidator;

    /**
     * Class Level Constructor
     * Sets all the variables it can from the request headers received from the Browser
     *
     * @param array $options
     * @throws InvalidArgumentException
     */
    public function __construct(array $options = array())
    {
        $this->urlValidator = new ValidateUrl;
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
     * Returns the current JS File Contents
     *
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
     * Returns the current JS Version number
     *
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
     * Returns the User Agent
     *
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * Returns the User's Accept Language
     *
     * @return string
     */
    public function getAcceptLanguage()
    {
        return $this->acceptLanguage;
    }

    /**
     * Returns the Server Name
     *
     * @return string
     */
    public function getServerName()
    {
        return $this->serverName;
    }

    /**
     * Returns the Remote Address
     *
     * @return string
     */
    public function getRemoteAddress()
    {
        return $this->remoteAddress;
    }

    /**
     * Returns the GA Account Number
     *
     * @return string
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Returns the HTTP Referer
     *
     * @return string
     */
    public function getDocumentReferer()
    {
        return $this->documentReferer;
    }

    /**
     * Returns the Document Path
     *
     * @return string
     */
    public function getDocumentPath()
    {
        return $this->documentPath;
    }

    /**
     * Returns whether or not Do Not Track has been enabled
     *
     * @return int|null
     */
    public function getDoNotTrack()
    {
        return $this->doNotTrack;
    }

    /**
     * Returns the Page Title
     *
     * @return string
     */
    public function getPageTitle()
    {
        return $this->pageTitle;
    }

    /**
     * Returns any Custom Variables
     *
     * @return string
     */
    public function getCustomVariables()
    {
        return $this->customVariables;
    }

    /**
     * Returns the value of the specified custom variable
     *
     * @param int $index
     *
     * @throws OutOfBoundsException
     * @return string
     */
    public function getVisitorCustomVar($index)
    {
        if (isset($this->customVariables['index' . $index])) {
            return $this->customVariables['index' . $index]['value'];
        }
        throw new OutOfBoundsException('The index: "' . $index . '" has not been set.');
    }

    /**
     * Returns all custom vars for a specific scope
     *
     * @param int $scope
     *
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
     * Returns the current Character Set
     *
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * Returns the current set of search engines supported
     *
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
     * Returns the current instance of BotInfo
     *
     * @return null|BotInfo
     */
    public function getBotInfo()
    {
        return $this->botInfo;
    }

    /**
     * Returns the current HTTP client in use
     *
     * @return null|array|\Gass\Http\Interface
     */
    public function getHttp()
    {
        return $this->http;
    }

    /**
     * Returns whether or not Do Not Track is being ignored
     *
     * @return bool
     */
    public function getIgnoreDoNotTrack()
    {
        return $this->ignoreDoNotTrack;
    }

    /**
     * Gets a specific option
     *
     * @param string $name
     * @throws OutOfRangeException
     * @return mixed
     */
    public function getOption($name)
    {
        $methodName = 'get' . ucfirst($name);
        if (method_exists($this, $methodName)) {
            $reflectionMethod = new \ReflectionMethod($this, $methodName);
            if ($reflectionMethod->isPublic()) {
                return $this->$methodName();
            }
        }
        throw new OutOfRangeException($name . ' is not an available option.');
    }

    /**
     * Checks whether a variable can be cast to string
     * Returns the var cast as string if so
     * Throws an InvalidArgumentException if not
     *
     * @param string $var
     * @param string $description
     * @throws InvalidArgumentException
     * @return string
     */
    private function getAsString($var, $description)
    {
        if (!is_string($var)) {
            if (!is_scalar($var) && !is_null($var)
                && (!is_object($var) || !method_exists($var, '__toString'))
            ) {
                throw new InvalidArgumentException($description . ' must be a string.');
            }
            $var = (string) $var;
        }
        return $var;
    }

    /**
     * Sets the file contents of the latest ga.js version
     *
     * @return $this
     */
    protected function setCurrentJsFile()
    {
        $this->currentJsFile = trim(Http::request(static::JS_URL)->getResponse());
        return $this;
    }

    /**
     * Set the Current JS Version
     *
     * @param string $version
     * @throws InvalidArgumentException
     * @return $this
     */
    public function setVersion($version)
    {
        $this->setVersionCalled = true;
        $version = $this->getAsString($version, 'Version');
        if (1 !== preg_match('/^(\d+\.){2}\d+$/', $version)) {
            throw new InvalidArgumentException('Invalid version number provided: ' . $version);
        }
        $this->version = $version;
        return $this;
    }

    /**
     * Set the User Agent
     *
     * @param string $userAgent
     * @return $this
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $this->getAsString($userAgent, 'User Agent');
        Http::setUserAgent($this->userAgent);
        if ($this->botInfo instanceof BotInfo) {
            $this->botInfo->setUserAgent($this->userAgent);
        }
        return $this;
    }

    /**
     * Set the Accept Language
     *
     * @param string $acceptLanguage
     * @throws InvalidArgumentException
     * @return $this
     */
    public function setAcceptLanguage($acceptLanguage)
    {
        $acceptLanguage = $this->getAsString($acceptLanguage, 'Accept Language');
        if (false !== strpos($acceptLanguage, ';')) {
            list($acceptLanguage) = explode(';', $acceptLanguage, 2);
        }
        if (false !== strpos($acceptLanguage, ',')) {
            list($acceptLanguage) = explode(',', $acceptLanguage, 2);
        }
        $acceptLanguage = strtolower($acceptLanguage);
        $langValidator = new ValidateLanguageCode;
        if (!$langValidator->isValid($acceptLanguage)) {
            throw new InvalidArgumentException(
                'Accept Language validation errors: ' . implode(', ', $langValidator->getMessages())
            );
        }
        $this->acceptLanguage = $acceptLanguage;
        Http::setAcceptLanguage($acceptLanguage);
        return $this;
    }

    /**
     * Set the Server Name
     *
     * @param string $serverName
     * @return $this
     */
    public function setServerName($serverName)
    {
        $this->serverName = $this->getAsString($serverName, 'Server Name');
        return $this;
    }

    /**
     * Set the Remote Address
     *
     * @param string $remoteAddress
     * @throws InvalidArgumentException
     * @return $this
     */
    public function setRemoteAddress($remoteAddress)
    {
        $remoteAddress = $this->getAsString($remoteAddress, 'Remote Address');
        $ipValidator = new ValidateIpAddress;
        if (!$ipValidator->isValid($remoteAddress)) {
            throw new InvalidArgumentException(
                'Remote Address validation errors: ' . implode(', ', $ipValidator->getMessages())
            );
        }
        $this->remoteAddress = $remoteAddress;
        Http::setRemoteAddress($this->remoteAddress);
        if ($this->botInfo instanceof BotInfo) {
            $this->botInfo->setRemoteAddress($this->remoteAddress);
        }
        return $this;
    }

    /**
     * Set tht GA Account Number
     *
     * @param string $account
     * @throws InvalidArgumentException
     * @return $this
     */
    public function setAccount($account)
    {
        $account = $this->getAsString($account, 'Account');
        if (1 !== preg_match('/^(MO|UA)-\d{4,}-\d+$/', $account)) {
            throw new InvalidArgumentException(
                'Google Analytics user account must be in the format: UA-XXXXXXX-X or MO-XXXXXXX-X'
            );
        }
        $this->account = trim($account);
        return $this;
    }

    /**
     * Set the HTTP Document Referrer
     *
     * @param string $documentReferer
     * @throws InvalidArgumentException
     * @return $this
     */
    public function setDocumentReferer($documentReferer)
    {
        $documentReferer = trim($this->getAsString($documentReferer, 'Document Referer'));
        if (!empty($documentReferer)
            && !$this->urlValidator->isValid($documentReferer)
        ) {
            throw new InvalidArgumentException('Document Referer must be a valid URL.');
        }
        $this->documentReferer = $documentReferer;
        return $this;
    }

    /**
     * Set Document Path
     *
     * @param string $documentPath
     * @return $this
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
     * Set Do Not Track
     *
     * @param int|string|null $doNotTrack
     * @throws InvalidArgumentException
     * @return $this
     */
    public function setDoNotTrack($doNotTrack)
    {
        if (!in_array($doNotTrack, array(1, 0, null, '1', '0', 'null', 'unset'))) {
            throw new InvalidArgumentException('$doNotTrack must have a value of 1, 0, \'unset\' or null');
        }
        if (is_string($doNotTrack)) {
            $doNotTrack = (is_numeric($doNotTrack)) ? (int) $doNotTrack : null;
        }
        $this->doNotTrack = $doNotTrack;
        return $this;
    }

    /**
     * Set Page Title
     *
     * @param string $pageTitle
     * @return $this
     */
    public function setPageTitle($pageTitle)
    {
        $this->pageTitle = $this->getAsString($pageTitle, 'Page Title');
        return $this;
    }

    /**
     * Set whether or not to ignore do not track
     *
     * @param bool $ignoreDoNotTrack
     * @throws InvalidArgumentException
     * @return $this
     */
    public function setIgnoreDoNotTrack($ignoreDoNotTrack = true)
    {
        if (!is_bool($ignoreDoNotTrack)) {
            throw new InvalidArgumentException('$ignoreDoNotTrack must be a boolean.');
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
     * @param int $scope [optional]
     * @param int $index [optional]
     * @throws DomainException
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     * @return $this
     */
    public function setCustomVar($name, $value, $scope = 3, $index = null)
    {
        if ($index === null) {
            $index = 0;
            do {
                ++$index;
            } while (isset($this->customVariables['index' . $index]) && $index < 6);
            if ($index > 5) {
                throw new OutOfBoundsException('You cannot add more than 5 custom variables.');
            }
        } elseif (!is_int($index) || $index < 1 || $index > 5) {
            throw new OutOfBoundsException('The index must be an integer between 1 and 5.');
        }
        if (!is_int($scope) || $scope < 1 || $scope > 3) {
            throw new InvalidArgumentException('The Scope must be a value between 1 and 3');
        }
        $name = $this->getAsString($name, 'Custom Var Name');
        $value = $this->getAsString($value, 'Custom Var Value');
        if (128 < strlen($name . $value)) {
            throw new DomainException('The name / value combination exceeds the 128 byte custom var limit.');
        }
        $this->customVariables['index' . $index] = array(
            'index' => (int) $index,
            'name' => (string) $this->removeSpecialCustomVarChars($name),
            'value' => (string) $this->removeSpecialCustomVarChars($value),
            'scope' => (int) $scope,
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
                if (!isset($currentCustVars['index' . $custVarIndex])) {
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
     * @param int $index
     *
     * @return $this
     */
    public function deleteCustomVar($index)
    {
        unset($this->customVariables['index' . $this->getAsString($index, 'Custom Var Index')]);
        return $this;
    }

    /**
     * Set Character Set
     *
     * @param string $charset
     * @return $this
     */
    public function setCharset($charset)
    {
        $this->charset = strtoupper($this->getAsString($charset, 'Charset'));
        return $this;
    }

    /**
     * Sets the current Search Engines supported
     *
     * @param array $searchEngines
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws OutOfBoundsException
     * @return $this
     */
    public function setSearchEngines(array $searchEngines)
    {
        $this->setSearchEnginesCalled = true;
        foreach ($searchEngines as $searchEngine => $queryParams) {
            if (!is_array($queryParams) || 1 > count($queryParams)) {
                throw new DomainException('searchEngines entry ' . $searchEngine . ' invalid');
            }
            if (!is_string($searchEngine)
                || 1 !== preg_match('/^[a-z0-9\.-]+$/', $searchEngine)
            ) {
                throw new OutOfBoundsException('search engine name "' . $searchEngine . '" is invalid');
            }
            foreach ($queryParams as $queryParameter) {
                if (!is_string($queryParameter)
                    || 1 !== preg_match('/^[a-z0-9_\-]+$/i', $queryParameter)
                ) {
                    throw new DomainException(
                        'search engine query parameter "' . $queryParameter . '" is invalid'
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
     * @param array|bool|BotInfoInterface|null $botInfo
     * @throws InvalidArgumentException
     * @return $this
     */
    public function setBotInfo($botInfo)
    {
        if (!is_array($botInfo) && !is_bool($botInfo) && $botInfo !== null
            && !$botInfo instanceof BotInfoInterface
        ) {
            throw new InvalidArgumentException(
                'botInfo must be an array, boolean, null' .
                ' or a class which implements Gass\BotInfo\BotInfoInterface.'
            );
        } elseif ($botInfo !== null) {
            if ($botInfo instanceof BotInfoInterface) {
                $this->botInfo = new BotInfo(array(), $botInfo);
            } elseif (is_array($botInfo)) {
                $this->botInfo = new BotInfo($botInfo);
            } else {
                $this->botInfo = new BotInfo;
            }
            $this->botInfo->setUserAgent($this->getUserAgent());
            $this->botInfo->setRemoteAddress($this->getRemoteAddress());
        } else {
            $this->botInfo = null;
        }
        return $this;
    }

    /**
     * Set the HTTP Client
     *
     * @param null|array|\Gass\Http\Interface $http
     * @throws InvalidArgumentException
     * @return $this
     */
    public function setHttp($http = null)
    {
        if ($http !== null && !is_array($http)
            && !$http instanceof HttpInterface
        ) {
            throw new InvalidArgumentException(
                'http must be an array, null' .
                ' or a class which implements Gass\Http\Interface.'
            );
        }
        if ($http !== null) {
            if ($http instanceof HttpInterface) {
                Http::getInstance(array(), $http);
            } elseif (is_array($http)) {
                Http::getInstance($http);
            }
        }
        Http::setAcceptLanguage($this->getAcceptLanguage())
            ->setRemoteAddress($this->getRemoteAddress())
            ->setUserAgent($this->getUserAgent());
        $this->http = $http;
        return $this;
    }

    /**
     * Set Options
     *
     * @param array $options
     * @return $this
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
     * @return $this
     */
    public function setOption($name, $value)
    {
        $this->getOption($this->getAsString($name, 'Option Name'));
        $methodName = 'set' . ucfirst($name);
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
     * @param int $value [optional]
     * @throws InvalidArgumentException
     * @return string
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
            throw new InvalidArgumentException('An event requires at least a category and action');
        }
        if ($label !== null) {
            $label = $this->getAsString($label, 'Event Label');
        }
        if ($value !== null && !is_int($value)) {
            throw new InvalidArgumentException('Value must be an integer.');
        }
        return '5(' . $category . '*' . $action . (empty($label) ? '' : '*' . $label) . ')' .
            (($value !== null) ? '(' . $value . ')' : '');
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
                if (in_array($value['scope'], array(1, 2))) {
                    $scopes[] = (($value['index'] > (count($scopes) + 1)) ? $value['index'] . '!' : '') .
                        $value['scope'];
                }
            }
            return '8(' . implode($names, '*') . ')9(' . implode($values, '*') . ')11(' . implode($scopes, '*') . ')';
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
     *
     * @return int
     */
    public function getDomainHash($domain = null)
    {
        $domain = ($domain === null)
            ? $this->serverName
            : $this->getAsString($domain, 'Domain');
        $a = 1;
        $c = 0;
        if (!empty($domain)) {
            $a = 0;
            for ($h = strlen($domain) - 1; $h >= 0; --$h) {
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
     *
     * @see https://developers.google.com/analytics/resources/concepts/gaConceptsCookies
     * @see http://www.analyticsevangelist.com/google-analytics/how-to-read-google-analytics-cookies/
     * @see http://www.cheatography.com/jay-taylor/cheat-sheets/google-analytics-cookies-v2/
     * @see http://www.tutkiun.com/2011/04/a-google-analytics-cookie-explained.html
     *
     * @param array $cookies [optional]
     * @return $this
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

        /*
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

        /*
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
        } elseif (!isset($cookies['__utmz'], $cookies['__utmb'])) {
            ++$session;
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
            && $this->urlValidator->isValid($referer)
            && false !== ($refererParts = parse_url($referer))
            && isset($refererParts['host'], $refererParts['path'])
        ) {
            $refererSearchEngine = false;
            $searchEngines = $this->getSearchEngines();
            foreach ($searchEngines as $searchEngine => $queryParams) {
                $refererDomainParts = explode('.', $refererParts['host']);
                array_pop($refererDomainParts);
                if (!empty($refererParts['query'])
                    && ((false !== strpos($searchEngine, '.')
                            && false !== strpos($refererParts['host'], $searchEngine))
                        || in_array($searchEngine, $refererDomainParts)
                    )
                ) {
                    $refererSearchEngine = $searchEngine;
                    break;
                }
            }
            if (false === $refererSearchEngine) {
                $campaignParameters = 'utmcsr=' .
                    $refererParts['host'] .
                    '|utmccn=(referral)|utmcmd=referral|utmcct=' .
                    $refererParts['path'];
            } else {
                $queryParameters = $searchEngines[$searchEngine];
                parse_str($refererParts['query'], $refererQueryParams);
                $queryParamValue = '(not provided)';
                foreach ($queryParameters as $queryParameter) {
                    if (array_key_exists($queryParameter, $refererQueryParams)
                        && !empty($refererQueryParams[$queryParameter])
                    ) {
                        $queryParamValue = $refererQueryParams[$queryParameter];
                        break;
                    }
                }
                $campaignParameters = 'utmcsr=' . $searchEngine .
                    '|utmccn=(organic)|utmcmd=organic|utmctr=' . $queryParamValue;
            }
        }
        if (!isset($campaignParameters) || false === strpos($campaignParameters, 'utmcsr=')) {
            $campaignParameters = 'utmcsr=(direct)|utmccn=(direct)|utmcmd=(none)';
        }

        if (!isset($campaignNumber) || !is_numeric($campaignNumber)) {
            $campaignNumber = 1;
        } elseif ($previousCampaignParameters != $campaignParameters) {
            ++$campaignNumber;
        }

        $sendCookieHeaders = ($this->sendCookieHeaders && !$doNotTrack) ? true : false;
        /*
         * Set the cookies to the required values
         */
        $this->setCookie(
            '__utma',
            $domainId . '.' . $visitorId . '.' . $firstVisit . '.' . $lastVisit . '.' . $currentVisit . '.' . $session,
            $sendCookieHeaders
        );
        $this->setCookie(
            '__utmb',
            $domainId . '.' . $pageVisits . '.' . $session . '.' . $currentVisit,
            $sendCookieHeaders
        );
        $this->setCookie('__utmc', $domainId, $sendCookieHeaders);
        $this->setCookie(
            '__utmz',
            $domainId . '.' . $firstVisit . '.' . $session . '.' . $campaignNumber . '.' . $campaignParameters,
            $sendCookieHeaders
        );

        $scope1Vars = $this->getCustomVarsByScope(1);
        if (!empty($scope1Vars)) {
            $this->setCookie('__utmv', $domainId . '.|' . implode('^', $scope1Vars), $sendCookieHeaders);
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
                $cookieParts[] = $name . '=' . $value . ';';
            }
        }
        return implode($cookieParts, ' ');
    }

    /**
     * Sets a cookie for the user for the name and value provided
     *
     * @param string $name
     * @param string $value
     * @param bool $setHeader
     * @throws LengthException
     * @throws OutOfBoundsException
     * @return $this
     */
    private function setCookie($name, $value, $setHeader = true)
    {
        $name = trim($this->getAsString($name, 'Cookie Name'));
        $value = trim($this->getAsString($value, 'Cookie Value'));
        if (empty($value)) {
            throw new LengthException('Cookie cannot have an empty value');
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
                    $cookieLife = time() + (((60 * 60) * 24) * 90); // 3-Month Cookie
                    break;
                default:
                    $cookieLife = time() + $this->visitorCookieTimeout;
            }
            if ($setHeader) {
                setcookie($name, $value, $cookieLife, static::COOKIE_PATH, '.' . $this->getServerName());
            }
            return $this;
        }
        throw new OutOfBoundsException('Cookie by name: ' . $name . ' is not related to Google Analytics.');
    }

    /**
     * Sets the session cookie timeout
     *
     * @param int $sessionCookieTimeout (milliseconds)
     * @throws InvalidArgumentException
     * @return $this
     */
    public function setSessionCookieTimeout($sessionCookieTimeout)
    {
        if (!is_int($sessionCookieTimeout)) {
            throw new InvalidArgumentException('Session Cookie Timeout must be an integer.');
        }
        $this->sessionCookieTimeout = round($sessionCookieTimeout / 1000);
        return $this;
    }

    /**
     * Sets the visitor cookie timeout
     *
     * @param int $visitorCookieTimeout (milliseconds)
     * @throws InvalidArgumentException
     * @return $this
     */
    public function setVisitorCookieTimeout($visitorCookieTimeout)
    {
        if (!is_int($visitorCookieTimeout)) {
            throw new InvalidArgumentException('Visitor Cookie Timeout must be an integer.');
        }
        $this->visitorCookieTimeout = round($visitorCookieTimeout / 1000);
        return $this;
    }

    /**
     * Disables whether or not the cookie headers are sent when setCookies is called
     *
     * @return $this
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
     * @throws OutOfBoundsException
     * @return string
     */
    private function getCookie($name)
    {
        $name = $this->getAsString($name, 'Cookie Name');
        if (array_key_exists($name, $this->cookies)) {
            return $this->cookies[$name];
        }
        throw new OutOfBoundsException('Cookie by name: ' . $name . ' is not related to Google Analytics.');
    }

    /**
     * Sets the cookies values inside the class from
     * the cookies sent with the request headers
     *
     * @return $this
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
     * @return $this
     */
    public function setVersionFromJs()
    {
        $currentJs = $this->getCurrentJsFile();
        if (!empty($currentJs)) {
            $regEx = '((\d+\.){2}\d+)';
            $version = preg_replace(
                '/^[\s\S]+\=function\(\)\{return[\'"]' . $regEx . '[\'"][\s\S]+$/i',
                '$1',
                $currentJs
            );
            if (preg_match('/^' . $regEx . '$/', $version)) {
                $this->setVersion($version);
            }
        }
        return $this;
    }

    /**
     * Retrieves the current list of search engines and query parameters from the ga.js file
     *
     * @return $this
     */
    public function setSearchEnginesFromJs()
    {
        $currentJs = $this->getCurrentJsFile();
        if (!empty($currentJs)) {
            $regEx = '([a-z0-9:\s-_\.]+)';
            $searchEngineString = preg_replace(
                '/^[\s\S]+\=[\'"]' . $regEx . '[\'"]\.split\([\'"]\s+[\'"]\)[\s\S]+$/i',
                '$1',
                $currentJs
            );
            if (preg_match('/^' . $regEx . '$/i', $searchEngineString)) {
                $searchEngineArray = preg_split('#\s+#', $searchEngineString);
                $searchEngines = array();
                foreach ($searchEngineArray as $searchEngine) {
                    $searchEngineParts = explode(':', $searchEngine);
                    if (2 == count($searchEngineParts)) {
                        if (isset($searchEngines[$searchEngineParts[0]])
                            && is_array($searchEngines[$searchEngineParts[0]])
                        ) {
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
     * @throws DomainException
     * @return $this
     */
    public function trackPageview($url = null)
    {
        if ($url !== null) {
            $url = $this->getAsString($url, 'Page View URL');
            if (0 != strpos($url, '/')) {
                if (!$this->urlValidator->isValid($url)) {
                    throw new DomainException('Url is invalid: ' . $url);
                }
                $urlParts = parse_url($url);
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
     * @param int $value [optional]
     * @param bool $nonInteraction [optional]
     * @throws InvalidArgumentException
     * @return $this
     */
    public function trackEvent($category, $action, $label = null, $value = null, $nonInteraction = false)
    {
        if (!is_bool($nonInteraction)) {
            throw new InvalidArgumentException('NonInteraction must be a boolean.');
        }
        $queryParams = array(
            'utmt' => 'event',
            'utme' => $this->getEventString($category, $action, $label, $value),
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
     * @throws DomainException
     * @return bool|GoogleAnalyticsServerSide
     */
    private function track(array $extraParams = array())
    {
        if ($this->botInfo !== null && $this->botInfo->isBot()) {
            return false;
        }

        $account = (string) $this->getAccount();
        if (empty($account)) {
            throw new DomainException('The account number must be set before any tracking can take place.');
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
            'utmn' => rand(0, 0x7fffffff),
            'utmhn' => $domainName,
            'utmr' => $documentReferer,
            'utmac' => $account,
            'utmcc' => $this->getCookiesString(),
            'utmul' => $this->getAcceptLanguage(),
            'utmcs' => $this->getCharset(),
            'utmu' => 'q~',
        );
        if (0 === strpos($account, 'MO-')) {
            $queryParams['utmip'] = $this->getIPToReport();
        }
        $queryParams = array_merge($queryParams, $extraParams);

        if (null !== ($customVarString = $this->getCustomVariableString())) {
            $queryParams['utme'] = (
                    (isset($queryParams['utme']) && !empty($queryParams['utme']))
                        ? $queryParams['utme']
                        : ''
                ) .
                $customVarString;
        }

        $utmUrl = static::GIF_URL . '?' . http_build_query($queryParams, null, '&');

        Http::request($utmUrl);
        return $this;
    }
}

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
 * @copyright   Copyright (c) 2011-2017 Tom Chapman (http://tom-chapman.uk/)
 * @license     BSD 3-clause "New" or "Revised" License
 * @link        http://github.com/chappy84/google-analytics-server-side
 */

namespace Gass\Http;

use Gass\Exception\DomainException;
use Gass\Exception\RuntimeException;
use Gass\Exception\UnexpectedValueException;

/**
 * cURL adapter for Http
 *
 * @see         Gass\Exception
 * @see         Gass\Exception\DomainException
 * @see         Gass\Exception\RuntimeException
 * @see         Gass\Exception\UnexpectedValueException
 * @author      Tom Chapman
 */
class Curl extends Base
{
    /**
     * Curl instance
     *
     * @var resource
     */
    protected $curl;

    /**
     * Class options
     *
     * @see https://secure.php.net/manual/en/function.curl-setopt.php
     * @var array
     */
    protected $options = array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_FOLLOWLOCATION => true,
    );

    /**
     * Class Constructor
     *
     * @param array $options
     * @throws RuntimeException
     * @codeCoverageIgnore
     */
    public function __construct(array $options = array())
    {
        if (!extension_loaded('curl')) {
            throw new RuntimeException('cURL PHP extension is not loaded.');
        }
        parent::__construct($options);
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $index [optional]
     * @throws DomainException
     * @return mixed
     */
    public function getInfo($index = null)
    {
        if (is_resource($this->curl)) {
            if ($index === null) {
                return curl_getinfo($this->curl);
            }
            return curl_getinfo($this->curl, $index);
        }
        throw new DomainException('A cURL request has not been made yet.');
    }

    /**
     * {@inheritdoc}
     *
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        return $this->setOption(CURLOPT_URL, $url);
    }

    /**
     * Closes the curl connection if one is present
     *
     * @return $this
     */
    protected function close()
    {
        if (is_resource($this->curl)) {
            curl_close($this->curl);
        }
        $this->curl = null;
        $this->setResponse(null);
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $url
     * @param array $options
     * @throws RuntimeException
     * @throws UnexpectedValueException
     * @return $this
     */
    public function request($url = null, array $options = array())
    {
        parent::request($url, $options);

        $this->close();
        $this->curl = curl_init();

        if (null !== ($userAgent = $this->getUserAgent())) {
            $this->setOption(CURLOPT_USERAGENT, $userAgent);
        }

        $currentHeaders = $this->getOption(CURLOPT_HTTPHEADER);
        $extraHeaders = (is_array($currentHeaders)) ? $currentHeaders : array();
        if (null !== ($acceptedLanguage = $this->getAcceptLanguage())) {
            $extraHeaders[] = 'Accepts-Language: ' . $acceptedLanguage;
        }
        if (null !== ($remoteAddress = $this->getRemoteAddress())) {
            $extraHeaders[] = 'X-Forwarded-For: ' . $remoteAddress;
        }
        if (!empty($extraHeaders)) {
            $this->setOption(CURLOPT_HTTPHEADER, $extraHeaders);
        }

        $extraCurlOptions = $this->getOptions();
        if (!empty($extraCurlOptions) && false === @curl_setopt_array($this->curl, $extraCurlOptions)) {
            throw new UnexpectedValueException(
                'One of the extra curl options specified is invalid. Error: ' . curl_error($this->curl)
            );
        }

        if (false === ($response = curl_exec($this->curl))) {
            throw new RuntimeException('Source could not be retrieved. Error: ' . curl_error($this->curl));
        }

        $this->checkResponseCode(
            $this->getInfo(CURLINFO_HTTP_CODE)
        );

        $this->setResponse($response);

        return $this;
    }

    /**
     * Class Destructor
     */
    public function __destruct()
    {
        $this->close();
    }
}

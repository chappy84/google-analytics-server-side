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
 * @copyright   Copyright (c) 2011-2020 Tom Chapman (http://tom-chapman.uk/)
 * @license     BSD 3-clause "New" or "Revised" License
 * @link        http://github.com/chappy84/google-analytics-server-side
 */

namespace Gass\Http;

use Gass\Exception\DomainException;
use Gass\Exception\InvalidArgumentException;
use Gass\Exception\RuntimeException;

/**
 * Stream adapter for Http
 *
 * @see         Gass\Exception\DomainException
 * @see         Gass\Exception\InvalidArgumentException
 * @see         Gass\Exception\RuntimeException
 * @author      Tom Chapman
 */
class Stream extends Base
{
    /**
     * Class options
     *
     * @see https://secure.php.net/manual/en/context.http.php
     * @var array
     */
    protected $options = array(
        'context' => array('http' => array('method' => 'GET')),
    );

    /**
     * The headers returned in response to the previous request
     *
     * @var array
     */
    protected $responseHeaders = array();

    /**
     * {@inheritdoc}
     *
     * @param mixed $index [optional]
     * @throws DomainException
     * @return mixed
     */
    public function getInfo($index = null)
    {
        if (!empty($this->responseHeaders)) {
            if ($index !== null) {
                return (isset($this->responseHeaders[$index])) ? $this->responseHeaders[$index] : null;
            }
            return $this->responseHeaders;
        }
        throw new DomainException('A Http Request has not been made yet.');
    }

    /**
     * Returns all options set
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options['context']['http'];
    }

    /**
     * Sets a specific option
     *
     * @see https://secure.php.net/manual/en/context.http.php
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function setOption($name, $value)
    {
        $this->options['context']['http'][$name] = $value;
        return $this;
    }

    /**
     * Returns a specific option
     *
     * @see https://secure.php.net/manual/en/context.http.php
     * @param string $name
     * @return mixed
     */
    public function getOption($name)
    {
        return (isset($this->options['context']['http'][$name]))
            ? $this->options['context']['http'][$name]
            : null;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        return parent::setOption('url', $url);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $url [optional]
     * @param array $options [optional]
     * @throws RuntimeException
     * @return $this
     */
    public function request($url = null, array $options = array())
    {
        parent::request($url, $options);

        $currentHeaders = $this->getOption('header');
        $headersArray = ($currentHeaders === null) ? array() : $this->parseHeaders($currentHeaders);
        if (null !== ($userAgent = $this->getUserAgent())) {
            $headersArray['User-Agent'] = $userAgent;
        }
        if (null !== ($acceptedLanguage = $this->getAcceptLanguage())) {
            $headersArray['Accepts-Language'] = $acceptedLanguage;
        }
        if (null !== ($remoteAddress = $this->getRemoteAddress())) {
            $headersArray['X-Forwarded-For'] = $remoteAddress;
        }
        $newHeaders = array();
        foreach ($headersArray as $key => $header) {
            if (!is_array($header)) {
                $header = array($header);
            }
            foreach ($header as $value) {
                $newHeaders[] = $key . ': ' . $value;
            }
        }
        $headerString = implode("\r\n", $newHeaders);

        if (!empty($headerString)) {
            $this->setOption('header', $headerString);
        }

        $context = stream_context_create(parent::getOption('context'));

        if (false === ($response = @file_get_contents(parent::getOption('url'), false, $context))) {
            $errorMsg = 'error message not available. You may have a custom error handler in place.';
            $errorDet = error_get_last();
            if (!empty($errorDet['message'])) {
                $errorMsg = $errorDet['message'];
            }
            throw new RuntimeException('Source could not be retrieved. Error: ' . $errorMsg);
        }
        $this->setResponseHeaders($http_response_header);
        if (null !== ($statusCode = $this->getInfo('Http-Code'))) {
            $this->checkResponseCode($statusCode);
        }

        $this->setResponse($response);

        return $this;
    }

    /**
     * Sets the response headers to the class level variable
     *
     * @param array $responseHeaders
     */
    private function setResponseHeaders($responseHeaders)
    {
        $this->responseHeaders = $this->parseHeaders($responseHeaders);
    }

    /**
     * Parses HTTP headers into an associative array whether in
     * the $http_response_header or stream_context_create format
     *
     * @param string|array $headers
     * @throws InvalidArgumentException
     * @return array
     */
    private function parseHeaders($headers)
    {
        if (is_string($headers)) {
            $headers = explode("\n", $headers);
        }
        if (!is_array($headers)) {
            throw new InvalidArgumentException(
                'Headers must be provided in either string or numerically indexed array format.'
            );
        }
        $returnHeaders = array();
        foreach ($headers as $header) {
            $header = trim($header);
            if (1 === preg_match('/^([^:]+?)\s*?:([\s\S]+?)$/', $header, $matches)) {
                $headerVal = trim($matches[2]);
                if (in_array(strtolower($matches[1]), array('set-cookie'))) {
                    if (!isset($returnHeaders[$matches[1]])) {
                        $returnHeaders[$matches[1]] = array();
                    }
                    $returnHeaders[$matches[1]][] = $headerVal;
                } else {
                    $returnHeaders[$matches[1]] = $headerVal;
                }
            } elseif (1 === preg_match('/^HTTP\/\d+?\.\d+?\s+?(\d+)[\s\S]+?$/i', $header, $matches)) {
                $returnHeaders['Http-Code'] = $matches[1];
            }
        }
        return $returnHeaders;
    }
}

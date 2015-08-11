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
 * @copyright   Copyright (c) 2011-2015 Tom Chapman (http://tom-chapman.uk/)
 * @license     http://www.gnu.org/copyleft/gpl.html  GPL
 * @link        http://github.com/chappy84/google-analytics-server-side
 */
namespace Gass\Http;

use Gass\Exception;

/**
 * Stream adapter for Http
 *
 * @see         Gass\Exception
 * @author      Tom Chapman
 * @package     Gass\Http
 */
class Test extends Base implements HttpInterface
{
    /**
     * Class options
     *
     * @var array
     */
    protected $options = array();

    /**
     * The headers returned in response to the previous request
     *
     * @var array
     */
    protected $responseHeaders = array();

    /**
     * Different http requests that may be requested from the Test Http Adapter
     *
     * @var array
     */
    protected $requestQueue = array();

    /**
     * {@inheritdoc}
     *
     * @param mixed $index [optional]
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
        throw new Exception\DomainException('A Http Request has not been made yet.');
    }

    /**
     * Returns all options set
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Sets a specific option
     *
     * @param string $name
     * @param mixed $value
     * @return Gass\Adapter\Base
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
        return $this;
    }

    /**
     * Returns a specific option
     *
     * @param string $name
     * @return mixed
     */
    public function getOption($name)
    {
        return (isset($this->options[$name]))
                ? $this->options[$name]
                : null;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $url
     * @return Gass\Http\Stream
     */
    public function setUrl($url)
    {
        return parent::setOption('url', $url);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $url
     * @param array $options
     * @return Gass\Http\Stream
     */
    public function request($url = null, array $options = array())
    {
        parent::request($url, $options);
        $url = $this->getBaseUrl(
            $this->getOption('url')
        );
        $this->responseHeaders = array();
        if (!isset($this->requestQueue[$url])) {
            throw new Exception\OutOfBoundsException(
                'No Test Http request info for requested url: ' . $this->getOption('url')
            );
        }
        $requestQueueItem = $this->requestQueue[$url];
        $this->setResponseHeaders($requestQueueItem['responseHeaders']);
        $this->setResponse($requestQueueItem['responseBody']);
        if (null !== ($statusCode = $this->getInfo('Http-Code'))) {
            $this->checkResponseCode($statusCode);
        }
        return $this;
    }

    /**
     * Adds a response for the specified request url
     *
     * @param string $url
     * @param string|array $responseHeaders
     * @param string $responseBody
     * @return \Gass\Http\Test
     */
    public function addRequestQueueItem($url, $responseHeaders, $responseBody)
    {
        $url = $this->getBaseUrl($url);
        $this->requestQueue[$url] = array(
            'responseHeaders' => $responseHeaders,
            'responseBody' => $responseBody
        );
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
     * @throws Gass\Exception\InvalidArgumentException
     * @return array
     */
    private function parseHeaders($headers)
    {
        if (is_string($headers)) {
            $headers = explode("\n", $headers);
        }
        if (!is_array($headers)) {
            throw new Exception\InvalidArgumentException(
                'Headers must be provided in either string or numerically indexed array format.'
            );
        }
        $returnHeaders = array();
        foreach ($headers as $header) {
            $header = trim($header);
            if (1 === preg_match('/^([^:]+?)\s*?:([\s\S]+?)$/', $header, $matches)) {
                $returnHeaders[$matches[1]] = trim($matches[2]);
            } elseif (1 === preg_match('/^HTTP\/\d+?\.\d+?\s+?(\d+)[\s\S]+?$/i', $header, $matches)) {
                $returnHeaders['Http-Code'] = $matches[1];
            }
        }
        return $returnHeaders;
    }

    /**
     * Returns the url without the query and fragment
     *
     * @param string $url
     * @return string
     */
    private function getBaseUrl($url)
    {
        $urlParts = parse_url($url);
        $pre = $urlParts['scheme'] . '://';
        if (!empty($urlParts['user'])) {
            $pre .= $urlParts['user'];
            if (!empty($urlParts['pass'])) {
                $pre .= ':' . $urlParts['pass'];
            }
            $pre .= '@';
        }
        return $pre . $urlParts['host'] . $urlParts['path'];
    }
}

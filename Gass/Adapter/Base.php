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
 * @copyright   Copyright (c) 2011-2014 Tom Chapman (http://tom-chapman.uk/)
 * @license     http://www.gnu.org/copyleft/gpl.html  GPL
 * @author      Tom Chapman
 * @link        http://github.com/chappy84/google-analytics-server-side
 * @category    GoogleAnalyticsServerSide
 * @package     Gass
 * @subpackage  Adapter
 */

/**
 * @namespace
 */
namespace Gass\Adapter;

/**
 * Base class for use with adapters
 *
 * @copyright   Copyright (c) 2011-2014 Tom Chapman (http://tom-chapman.uk/)
 * @license     http://www.gnu.org/copyleft/gpl.html  GPL
 * @author      Tom Chapman
 * @category    GoogleAnalyticsServerSide
 * @package     Gass
 * @subpackage  Adapter
 */
abstract class Base implements AdapterInterface
{
    /**
     * Adapter options passed in as part of construct or setOption/s
     *
     * @var array
     */
    protected $options = array();

    /**
     * Class constructor
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->setOptions($options);
    }

    /**
     * {@inheritdoc}
     *
     * @param array $options
     * @return Gass\Adapter\Base
     */
    public function setOptions(array $options)
    {
        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }
        return $this;
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
     * Returns all options set
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
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
}

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
 * @author      Tom Chapman
 * @link        http://github.com/chappy84/google-analytics-server-side
 * @category    GoogleAnalyticsServerSide
 * @package     Gass
 * @subpackage  Validate
 */

/**
 * @namespace
 */
namespace Gass\Validate;

/**
 * URL Validator
 *
 * @copyright   Copyright (c) 2011-2015 Tom Chapman (http://tom-chapman.uk/)
 * @license     http://www.gnu.org/copyleft/gpl.html  GPL
 * @author      Tom Chapman
 * @category    GoogleAnalyticsServerSide
 * @package     Gass
 * @subpackage  Validate
 */
class Url extends Base
{
    /**
     * Returns whether or not the value is valid
     *
     * @param mixed $value
     * @return boolean
     */
    public function isValid($value)
    {
        $value = $this->setValue($value)->getValue();
        if (!is_string($value)) {
            $this->addMessage('The provided IP address must be a string.');
            return false;
        }
        if (false === filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addMessage('"%value%" is an invalid URL');
            return false;
        }
        if (!preg_match('#^([a-z0-9]{3,})://([a-z0-9\.-]+)(/\S*)??$#', $value)) {
            $this->addMessage('"%value%" does not have a protocol');
            return false;
        }
        return true;
    }
}

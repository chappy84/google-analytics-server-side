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

/**
 * Add the current path to the include path if it's not already in there
 */
$gassCurrentDir = dirname(__FILE__);
$gassCurrentIncludePath = get_include_path();
if (false === strpos($gassCurrentIncludePath, $gassCurrentDir)) {
	set_include_path($gassCurrentIncludePath.PATH_SEPARATOR.$gassCurrentDir);
}

/**
 * Autoload function for all classes and interfaces in the
 * GoogleAnalyticsServerSide virtual namespace
 *
 * @param string $name
 * @throws RuntimeException
 */
function GASSAutoload($name) {
	if (0 === strpos($name, 'GASS_')) {
		$filePath = str_replace('_', DIRECTORY_SEPARATOR, $name);
		$includePaths = explode(PATH_SEPARATOR, get_include_path());
		$fileFound = false;
		$classFound = false;
		foreach ($includePaths as $includePath) {
			$proposedPath = $includePath . DIRECTORY_SEPARATOR . $filePath . '.php';
			if (@file_exists($proposedPath) && @is_readable($proposedPath)) {
				$fileFound = true;
				require_once $proposedPath;
				if (class_exists($name) || interface_exists($name)) {
					$classFound = true;
				}
			}
		}
		if (!$fileFound) {
			throw new RuntimeException('File could not be found for '.$name);
		} elseif (!$classFound) {
			throw new RuntimeException('Class or Interface could not be found for '.$name);
		}
	}
}


/**
 * Adds the autoloader to the php auto-load stack
 */
spl_autoload_register('GASSAutoload');

/**
 * If lower than PHP 5.3 add in functionality
 * to ensure all functions work correctly
 */
if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50300) {
	require_once 'php-5.2-extras.php';
}
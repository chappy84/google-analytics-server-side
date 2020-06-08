Google Analytics Server Side Changelog
======================================

Version 0.14.5 Beta
-------------------

- Add in support for IPv6 addresses
- Remove test support for DNT header removed in 0.14.0
- Fix UnitTest broken in PHP 7.4

Version 0.14.4 Beta
-------------------

- Correct minor non-functional issues with new Browscap ini file update functionality
- Remove the need for gh-pages branch

Version 0.14.3 Beta
-------------------

- Add in documentation for new Browscap ini file update functionality in previous release
- Correct update script help docs

Version 0.14.2 Beta
-------------------

 - Add in ability to update browscap ini file via cron job
 - Add in ability to disable auto browscap ini file update per request
 - Add in unofficial support for PHP 7.2 - 7.4
 - Switch from using $php-errormsg to the error_get_last function, in part for compatibility with more recent PHP versions
 - Correct ordering of parameters to implode function in line with requirements for more recent PHP versions
 - Switch to using constant keys for default option setup

Version 0.14.1 Beta
-------------------

 - Remove deprecated FILTER_VALIDATE options [depricated in PHP 7.3](https://www.php.net/manual/en/migration73.deprecated.php#migration73.deprecated.filter)

Version 0.14.0 Beta
-------------------

 - Remove DNT header support since the technology has been abandoned (https://github.com/w3c/dnt/commit/5d85d6c3d116b5eb29fddc69352a77d87dfd2310)

Version 0.12.2 Beta
-------------------

 - Finalise unit test coverage
 - Fixed a few remaining bugs / issues found as part of unit testing
 - Remove HHVM CI support

Version 0.12.1 Beta
-------------------

 - Increase unit test coverage
 - Slight re-write of BrowsCap BotInfo Adapter, abstracting common functionality
 - Increase configuration options of BrowsCap BotInfo Adapter
 - Add in constants for config option names with BrowsCap and UserAgentStringInfo BotInfo Adapters

Version 0.12.0 Beta
-------------------

 - Remove Test HTTP Adapter. This was added for unit testing purposes but has been replaced by mocking
 - Increase unit test coverage
 - Fix various bugs / issues, including bugs in the Multi BotInfo adapter

Version 0.11.0 Beta
-------------------

 - Change to BSD License
 - Stop using parent namespaces in use statements
 - Ensure works with PHP 7+
 - Various code style & Docblock updates

Version 0.10.0 Beta
------------------

 - New Multi Adapter to allow multiple adapter checks per call
 - BotInfo interface method altered from getIsBot to isBot
 - New BotInfo Multi Apadter
 - BrowsCap updated to use full file rather than standard file due to required attributes
 - User Agent String Info marked as deprecated until udger.com implements csvs to replace user agent string info's csv, as user-agent-string.info has now shut down
 - Rename Validator interface to ValidatorInterface
 - New Url Validator
 - Update PHP CodeSniffer to latest version for testing
 - Increase PHPUnit memory limit for new larger BrowsCap file size
 - Temporarily limit phpunit/phpunit-mock-objects to version 2.3.0 due to PHPUnit/HHVM issue (https://github.com/sebastianbergmann/phpunit-mock-objects/issues/223)
 - PSR-2 & coding style updates

Version 0.9.3 Beta
------------------

- Update to PSR-4 autoloading
- Add in extra CI checks for PHP Coding Standards Fixer
- Add in Coveralls support
- Add PHP 5.6 support

Version 0.9.2 Beta
------------------

- Added PHP CodeSniffer & PHP CS Fixer PSR-2 Check to Travis CI
- Added provisional Travis CI testing for PHP 5.6 & HipHop
- Install test suite via composer for Travis CI tests
- Fix issue with Gass\Http\Curl library in relation to headers
- Start transition to using PHPUnit Mock for class dependency testing
- Add in vfsStream to composer for future unit testing of filesystem interaction

Version 0.9.1 Beta
------------------

- Update BrowsCap URLs as current ones used will stop working on 30th March 2014
- Add in PHP 5.5 Support
- Update Composer support
- Docblock Corrections
- PSR-2 Corrections
- Update default version of ga.js used
- Deal with multiple Set-Cookie headers received

Version 0.9.0 Beta
------------------

- Rename namespace from GASS to Gass inline with PSR-2 standard
- Move main GoogleAnalyticsServerSide class inside Gass namespace
- Implement SplClassLoader rather than proprietary one
- Bring the code-base fully inline with the PSR-2 standard using PHP_CodeSniffer
- Add in PHPUnit configuration values to test where superglobal values are used
- Update Test HTTP adapter to store the response for one or more HTTP requests

Version 0.8.6 Beta
------------------

- Ensure the autoloader function is requested first so any other implemented autoloaders
don't throw Exceptions when trying to load files from this framework
- Add Do Not Track header functionality enabled by default. This can be disabled by calling the setIgnoreDoNotTrack method.
- Update to deal with new search providers containing numerical characters in their names
- Ensure ga.js file only retrieved if the user hasn't manually set the version and search engines
- Ensure HTTP curl adapter doesn't overwrite any user defined headers

Version 0.8.5 Beta
------------------

- Code to PSR-2 compliant
    ( using @fabpot's php coding standards fixer: https://github.com/fabpot/PHP-CS-Fixer )
- Update URLs for the browscap project now Gary Keith has transfered ownership to a new project leader

Version 0.8.4 Beta
------------------

- Extract re-used validation into re-usable validator classes
- Convert readme to github flavoured markdown
- Added in PHPUnit tests for a large majority of the code, remainder to come
- Search Engine information used for organic campaign info changed format in 5.3.0, update to deal with that
- Altered parameters to getEventString. Includes code to deal with Backwards Compatibility.
- Default GASS\Http adapter is now GASS\Http\Curl, falls back to GASS\Http\Stream if php cURL extension is not available

Version 0.8.3 Beta
------------------

- Fix a few issues existing in the code with un-defined / un-used variables

Version 0.8.2 Beta
------------------

- Add in support for organic campaign parameters with __utmz cookies.
- Only send utmip gif query parameter when using a mobile account, pointless otherwise.

Version 0.8.1 Beta
------------------

- Add a __callStatic magic method to GASS\Http so adapter methods can be called statically
- Ensure $php_errormsg has a value in GASS\Http\Stream so can be used when http request fails
- Correct spelling mistakes (retreive -> retrieve)

Version 0.8.0 Beta
------------------

- Converted to PHP 5.3 with namespaces instead of PHP 5.2 virtual namespaces
    ( using @ralphschindler's php-namespacer: https://github.com/ralphschindler/PHPTools )
- Removed deprecated methods from main class
- Removed get/setEvent, not in GA code and not needed, set is done directly in trackEvent
- Removed support for old csv cache in UserAgentStringInfo which just stored user agents without IPs
- BrowserCap renamed BrowsCap inline with PHP

Version 0.7.13 Beta
------------------

- Fix a few issues existing in the code with un-defined / un-used variables

Version 0.7.12 Beta
-------------------

- Ensure $php_errormsg has a value in GASS_Http_Stream so can be used when http request fails
- Correct spelling mistakes (retreive -> retrieve)

Version 0.7.11 Beta
-------------------

- Remove all non-PHPDoc doc blocks and replace with PHPDoc blocks so all works when PHPDoc run on project
- Add in package and subpackage tags to all files for use in PHPDoc
- GASS_BotInfo_UserAgentStringInfo now blocks per IP address as well as user agent string to ensure is blocking all bots in list

Version 0.7.10 Beta
-------------------

- Sets the __utmv custom var cookie which stores scope 1 (visitor-level) variables
- Correct the custom var string passed to GA, scope 3 (page-level) shouldn't be passed.
- PHPDoc completion.
- Only load data files in BotInfo adapters when data actually needed
- setCustomVar uses the first available index when one not provided and returns $this for chaining

Version 0.7.9 Beta
------------------

- 4: Ensure user can use the Mobile GA accounts starting in "MO-" aswell. Lets utmip provide the user's location to GA.
  (https://github.com/chappy84/google-analytics-server-side/issues/4)
- Remove un-required method call.
- Silence parse_url when an issue occurs, return value is checked.

Version 0.7.8 Beta
------------------

- Silence all is_readable, is_writable, file_exists and filemtime calls as the result is being checked anyway
so we don't really want a lot of E_WARNING php errors for no reason.
- Minor code speed improvements
- Some extra verification on event / custom variable strings
- Remove issue where cookie contains one of the invalid raw cookie characters
- convert createPageView / createEvent / setCustomVariable to trackPageView / trackEvent / setCustomVar as they
are in the Google Analytics ECMAScript, left alias functions for old method names
- Add getVisitorCustomVar, deleteCustomVar, setSessionCookieTimeout & setVisitorCookieTimeout methods

Version 0.7.7 Beta
------------------

- Ensure the BrowserCap latest version date isn't retrieved more than once a day form the server.
Store the version date in a one day cache file in same dir as php_browscap.ini.
- Make createPageView compatible with Google's trackPageView.
- 6: Fix issue with the event value being passed wrongly to Google.
  (https://github.com/chappy84/google-analytics-server-side/issues/6)

Version 0.7.6 Beta
------------------

- BrowserCap ini file is now parsed and dealt with by the code rather than using php's built in get_browser.
- BrowserCap ini file now updated whenever update available on server.

Version 0.7.5 Beta
------------------

- Fix wrong name variable used in BrowserCap.
- Ensure UserAgent has been sent to BotInfo.
- Ensure AcceptedLanguage / RemoteAddress / UserAgent has been sent to Http.

Version 0.7.4 Beta
------------------

- Fix autoloading issue in PHP 5.2 where GASS_Adapter_Base::__construct didn't match GASS_Adapter_Interface::__construct.

Version 0.7.3 Beta
------------------

- Add in setting of Custom Variables.
- Add extra nonInteraction parameter to setEvent / createEvent.
- Correct issue with getEventString where value set in wrong place.

Version 0.7.2 Beta
------------------

- Check event value is integer.
- 5: Readme updates from @skl
  (https://github.com/chappy84/google-analytics-server-side/issues/5)

Version 0.7.1 Beta
------------------

- PHPDoc and Readme updates.

Version 0.7.0 Beta
------------------

- Convert into a small framework under GASS PHP 5.2 virtual namespace (for backwards compatability).
- Add in autoloader for new GASS virtual namespace.
- GASS_Http singleton so can be used anywhere in framework without passing options around.
- GASS_Http uses adapters, defaults to Stream, can also use cURL, ability for developer to write own adapters.
- GASS_BotInfo which detects if user-agent is a bot or not separated out as not required.
- GASS_BotInfo uses adapters, php's BrowserCap is default, but original UserAgentString.info is also available, developer can write own adapters.

Version 0.6.4 Beta
------------------

- 3: Ensure bots.csv is not empty when received from url and ensure lines in csv aren't empty before dealing with them.
  (https://github.com/chappy84/google-analytics-server-side/pull/3)

Version 0.6.3 Beta
------------------

- 1: Ensure set cookie headers are only sent out once.
  (https://github.com/chappy84/google-analytics-server-side/issues/1)
- 2: Readme Updates from @skl
  (https://github.com/chappy84/google-analytics-server-side/issues/2)

Version 0.6.2 Beta
------------------

- Add in check to ensure cURL extension has been installed when the class is instantiated.

Version 0.6.1 Beta
------------------

- Allow setting / getting of any class level variable with a publicly available get / set method via getOption / setOption methods.

Version 0.6.0 Beta
------------------

- Ability to ignore logging statistics for web trawling bots and spiders and caching of the bots list.
- Auto-setting of latest GA version number from ga.js.
- Auto-setting of accepted language from headers.
- Ability to pass options to cURL.
- Remove auto-setting of charset from headers, default to UTF-8 (response should define this, not request headers).
- Try manually reporting IP address to GA as done in the GA mobile code ( http://www.google.com/analytics/googleanalyticsformobile.zip ).
- str_getcsv is not available in PHP before PHP 5.3 so added in a crude implementation so it works in lower than 5.3.

Version 0.5.5 Beta
------------------

- Set Document-Referer by default and check url format.
- Add extra Exceptions thrown in required circumstances.
- Ensure explode on cookies only returns no of parts required.
- Add Traffic source string with utmz cookie.
- Update PHPDoc in code.
- Update Readme with extra info & to correct markdown format.


Version 0.5.0 Beta
------------------

- Initial Release.
- Provides basic PageView and Event functionality, setting cookies in correct format.

[Google Analytics Server Side][1]
============================

Archived Repo
-------------

This is an archived project, simply here for posterity. It is no longer supported or updated, and should be considered End of Life.  
If you wish to continue to develop this code yourself, we recommend you fork it.

The project was reverse engineered from the ga.js Universal Analytics code, which [Google has now marked as "End Of Life"](https://support.google.com/analytics/answer/11583528?hl=en). This package **WILL NOT** work with the latest version of Google Analytics (GA4 at time or writing).  
We recommend you follow Google's recommendation, and [migrate to GA4](https://support.google.com/analytics/answer/10759417?sjid=5599945154580947509-EU), at which point you can then use the [Google Analytics 4 Measurement Protocol](https://developers.google.com/analytics/devguides/collection/protocol/ga4) to record analytics from the server side.  
There are various existing PHP implementations of this protocol, which we won't recommend one. If switching, do your research, and pick a good one, or be bold, and create a new one.

About
-----

Google Analytics Server Side is an implementation of the [Google Analytics web tracking ECMAScript][2] in [PHP][3].  
It provides server side Google Analytics tracking with a small easy to use PHP 5.3+ package.  
Implemented are the parts of the interface that would be available without [ECMAScript][6] in a 
browser to detect certain features such as screen resolution / colour, flash / java plugin version etc.

CODE: `git clone git://github.com/chappy84/google-analytics-server-side.git`  
HOME: <http://github.com/chappy84/google-analytics-server-side>  
BUGS: <http://github.com/chappy84/google-analytics-server-side/issues>  

Google Analytics was developed by [Google][4].  
This PHP adaptation is maintained by [Tom Chapman][5].  

[1]: http://git.io/gass
[2]: https://developers.google.com/analytics/devguides/collection/gajs/
[3]: http://www.php.net/
[4]: http://www.google.com/analytics
[5]: http://tom-chapman.uk/
[6]: http://en.wikipedia.org/wiki/ECMAScript

[![Build Status](https://secure.travis-ci.org/chappy84/google-analytics-server-side.png?branch=master)](http://travis-ci.org/chappy84/google-analytics-server-side)
[![Master Code Coverage Status](https://coveralls.io/repos/chappy84/google-analytics-server-side/badge.png?branch=master)](https://coveralls.io/r/chappy84/google-analytics-server-side)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/655a47b5-a324-487f-9b14-67da007b24d1/mini.png)](https://insight.sensiolabs.com/projects/655a47b5-a324-487f-9b14-67da007b24d1)
[![Latest Stable Version](https://poser.pugx.org/chappy84/google-analytics-server-side/v/stable)](https://packagist.org/packages/chappy84/google-analytics-server-side)
[![Total Downloads](https://poser.pugx.org/chappy84/google-analytics-server-side/downloads)](https://packagist.org/packages/chappy84/google-analytics-server-side)
[![License](https://poser.pugx.org/chappy84/google-analytics-server-side/license)](https://packagist.org/packages/chappy84/google-analytics-server-side)

Installation
------------

The package is available to install using [composer][7] from the [packagist][8] repository since
v0.8.6-beta. Simply require [chappy84/google-analytics-server-side][9] and it'll be installed,
checking the requirements.

Alternatively if you don't want to use composer, the package, without tests, can be included by 
using the following:

```php
require_once '<base_dir>' . DIRECTORY_SEPARATOR . 'Gass' . DIRECTORY_SEPARATOR . 'Bootstrap.php';
```

where `<base_dir>` is the base directory of Google Analytics Server Side on your filesystem.

[7]: http://getcomposer.org/
[8]: https://packagist.org/
[9]: https://packagist.org/packages/chappy84/google-analytics-server-side

Usage
-----

Google Analytics Server Side can be used simply in the following manner:

```php
$gass = new \Gass\GoogleAnalyticsServerSide;
$gass->setAccount('UA-XXXXXXX-X')
    ->trackPageView();
```

The class constructor accepts an optional associative array parameter of available
configuration options. Basically if there's a public method to set the variable
then it can be passed as part of the array to the class.

e.g.

```php
$gass = new \Gass\GoogleAnalyticsServerSide;
$gass->setAccount('UA-XXXXXXX-X')
    ->setBotInfo(true);
```

could also be done like this:

```php
$gass = new \Gass\GoogleAnalyticsServerSide(
    array(
        'account' => 'UA-XXXXXXX-X',
        'botInfo' => true
    )
);
```

These options can also be set individually by the method setOption,
or in one go with the method `setOptions`

Most of the [current basic methods][10] available in the `ga.js` tracking code have been
implemented.  
The methods implemented are:

- `deleteCustomVar`
- `getAccount`
- `getVersion`
- `getVisitorCustomVar`
- `setAccount`
- `setCustomVar`
- `setSessionCookieTimeout`
- `setVisitorCookieTimeout`
- `trackPageview`

The methods not implemented yet are:

- `getName`
- `setSampleRate`
- `setSiteSpeedSampleRate`

Extra methods are also available for the information which would normally be
pre-determined in the javascript or http request object from the browser. The User Agent,
Server Name, Remote Address, Document Path, Document Referer, Google Analytics Version,
Accepted Language, Cookies and Search Engines are all set automatically without any method
calls being required by the developer. However, the following methods are available to set
these variables and should be called before the `trackPageView` / `trackEvent` method to save
the tracking information:

- `setVersion`
- `setAcceptLanguage`
- `setUserAgent`
- `setServerName`
- `setRemoteAddress`
- `setDocumentPath`
- `setDocumentReferer`
- `setCookies`

On top of this there are also set methods to alter the default values for the the page
title and document character set.
These are available via the following methods:

- `setPageTitle`
- `setCharset`

Get methods are also provided for all of the above.  
All methods but `get` methods allow chaining for ease of use.

### Event Tracking

Event tracking is implemented using the [same functionality as in the ga.js tracking code][11]

```php
\Gass\GoogleAnalyticsServerSide::trackEvent(
     string $category, 
     string $action, 
    [string $label = null, 
    [int    $value = null, 
    [bool   $nonInteraction = false]]] 
);
```

N.B. `trackEvent()` does not require `trackPageView()` to be called first.  
However if you do not call `trackPageView` first or set `nonInteraction` to `true` then your 
pages/visit metric may become less than 1.

[10]: https://developers.google.com/analytics/devguides/collection/gajs/methods/gaJSApiBasicConfiguration
[11]: https://developers.google.com/analytics/devguides/collection/gajs/methods/gaJSApiEventTracking

BotInfo
-------

You must enable `BotInfo` for it to ignore any search/trawler bots.  
To do this you need to pass one of `true`, and associative array, or an instance of the 
adapter you want to use into the class.  The code will default to the `BrowsCap` adapter. 
Setting this to true will use the default. If you pass an associative array, this will be 
passed to `BotInfo` and through to the Adapter. When providing an associative array you can 
also pass the element `'adapter'` which will tell `BotInfo` which class to use as the adapter. 
You can also pass an instance of a `Gass\BotInfo\BotInfoInterface` adapter which will be used by the 
`Gass\BotInfo` class.

### Adapters

There are two adapters available in the GASS package

#### BrowsCap
There are five options as part of the array configuration parameter:

- `\Gass\BotInfo\BrowsCap::OPT_SAVE_PATH`: The Path where the ini file and latest version file are stored.
- `\Gass\BotInfo\BrowsCap::OPT_INI_FILE`: The name of the ini file to store the browscap ini data in.
- `\Gass\BotInfo\BrowsCap::OPT_LATEST_VERSION_DATE_FILE`: The name of the text file to store the latest version timestamp in.
- `\Gass\BotInfo\BrowsCap::OPT_BROWSCAP`: This is the same as the php ini setting [browscap][12], a file-system location where the [full_php_browscap.ini file][13] is located / can be downloaded to. 
- `\Gass\BotInfo\BrowsCap::OPT_DISABLE_AUTO_UPDATE`: This disables the auto-update feature for the browscap.ini file. See the [update](#updating-the-ini-file) section below for further information.

N/B: `OPT_BROWSCAP` will be ignored if you have set either `OPT_SAVE_PATH` or `OPT_INI_FILE`. `OPT_SAVE_PATH` or `OPT_INI_FILE` will also override any value derived from `OPT_BROWSCAP`. This is as `OPT_BROWSCAP` is intended as a fallback for the browscap ini setting, and backwards compatibility with previous versions of this package.  

e.g.

```php
$gass = new \Gass\GoogleAnalyticsServerSide(
    array(
        'botInfo' => true,
        'account' => 'UA-XXXXXXX-X',
    )
);
```

or

```php
$gass = new \Gass\GoogleAnalyticsServerSide(
    array(
        'botInfo' => array(
            'adapter' => 'BrowsCap',
            \Gass\BotInfo\BrowsCap::OPT_SAVE_PATH => '/var/lib/browscap',
            \Gass\BotInfo\BrowsCap::OPT_INI_FILE => 'full_php_browscap.ini',
        ),
        'account' => 'UA-XXXXXXX-X'
    )
);
```

or

```php
$gass = new \Gass\GoogleAnalyticsServerSide(array('account' => 'UA-XXXXXXX-X'));
$browsCapAdapter = new \Gass\BotInfo\BrowsCap;
$gass->setBotInfo($browsCapAdapter);
```
##### Updating the ini file

When an update for the browscap ini file is available [on the server][13] the code will 
automatically download the file into the location provided.  
This functionality can be disabled by setting the `\Gass\BotInfo\BrowsCap::OPT_DISABLE_AUTO_UPDATE` configuration option to `true`.  

With this disabled, this package provides another method to allow you to update the ini file automatically.  
The script `bin/gass-browscap-updater` can be setup to run with a scheduler. 
This uses the same code from the auto-update feature, checking the cache file, and the latest update date stored on [browscap.org][13], then downloads a new version if one is available.

Ths script has the following command line options:

- `-s` / `--save-path` The path to save the ini file and latest update cache file in
- `-f` / `--ini-filename` The filename given to the browscap file downloaded
- `-c` / `--cache-filename`: The filename given to the latest version date cache file
- `-v` / `--version`: Output's the current version
- `-h` / `--help`: Displays the help

See the help provided by the script for further information on usage.

##### Notes

* You MUST either provide location info for the browscap ini file or have the browscap ini setting 
set in php.ini, otherwise this adapter will not work.
* Due to an issue with the browscap ini file only being loaded when PHP starts up 
(which is with the web-server apache, PHP-FPM etc.) the code deals with the ini file 
itself, rather than using the built in get_browser function. This ensures the auto-update 
functionality will work without the need to restart the web-server.

#### UserAgentStringInfo

***DEPRECATED*** - until udger.com (or a comparable service) implements csvs (or another data source) 
to replace user agent string info's csv, as user-agent-string.info has now shut down

This downloaded a csv list of search engine crawlers from [user-agent-string.info][14].  
There are three options as part of the array configuration parameter:

- `\Gass\BotInfo\UserAgentStringInfo::OPT_CACHE_PATH`: where to save the list of bots downloaded from user-agent-string.info (required)
- `\Gass\BotInfo\UserAgentStringInfo::OPT_CACHE_FILENAME`: the filename to save the list of bots to (optional, defaults to bots.csv)
- `\Gass\BotInfo\UserAgentStringInfo::OPT_CACHE_LIFETIME`: number of secods before the cache expires (optional, defaults to 2592000 (30 days))

This can be implemented in the same way as the BrowsCap adapter.

[12]: http://www.php.net/manual/en/misc.configuration.php#ini.browscap
[13]: http://browscap.org/ 
[14]: http://user-agent-string.info/download

Http
----

This is a singleton class which provides http functionality across all sections of the
GASS package.  
This will default to using the `Curl` adapter if the php curl extension is available, otherwise it'll 
fall back to the `Stream` adapter. It requires no options. All options should be passed as a 
configuration option to `GoogleAnalyticsServerSide`, either via the configuration parameter 
in the `'http'` element or via the `setHttp` method's parameter. This can either be an associative 
array or an instance of the required adapter.

e.g.

```php
$gass = new \Gass\GoogleAnalyticsServerSide(
    array(
        'account' => 'UA-XXXXXXX-X',
        'http' => array(
            'adapter' => 'Curl',
            CURLOPT_PROXY => 'http://exampleproxy.local:8080'
        )
    )
);
```

or

```php
$gass = new \Gass\GoogleAnalyticsServerSide(array('account' => 'UA-XXXXXXX-X'));
$httpAdapter = new \Gass\Http\Stream;
$gass->setHttp($httpAdapter);
```

### Adapters

There are two Adapters available in `Gass\Http`, these are:

#### Stream
`Stream` creates a stream context and utilises this stream with `file_get_contents`. See 
[php's example][15]. Any [available options][16] provided to this class will go into the 
`'http'` array for the stream context, thus you may pass any headers or proxy information etc. 
into this to use in the connection when made.

#### Curl
This utilises the php extension cURL. cURL is recommended, however as it's not always 
available the code falls back to stream to allow all servers make http requests in the 
correct way.  
Any options provided to this class must be passed using the [curl constants][17] as 
identifiers (associative array keys or option names).

[15]: http://www.php.net/manual/en/function.file-get-contents.php#refsect1-function.file-get-contents-examples
[16]: https://secure.php.net/manual/en/context.http.php
[17]: http://www.php.net/manual/en/function.curl-setopt.php#refsect1-function.curl-setopt-parameters

End User Location
-----------------

The End User's Location will be reported as the location of the server if you use the GA Account 
number in the format `UA-XXXXXXX-X` as provided by Google. If you alter this to the format 
`MO-XXXXXXX-X` then the location will be tracked correctly and appear on the location map as 
it does with the normal ECMAScript tracking.

Cookies
-------

Cookies are automatically set when either `trackPageView` or `trackEvent` are called.  
They are however only sent as headers to the browser once, thus if you call either 
function more than once, or call both functions, then they will only be included in the 
headers when the first call is made.

You do have the option to turn off the sending of the cookie headers to the browser which 
can be done by calling `disableCookieHeaders` before calling trackPageView / trackEvent for 
the first time.

Test Suite & CI
---------------

This package uses [PHPUnit][20], along with [TravisCI][21], to test functionality on the
supported PHP minor versions 5.3, 5.4, 5.5, 5.6, 7.0, and 7.1, with unofficial support for 7.2, 7.3, and 7.4.
This is done by default on the latest bug fix point release of that minor point version to ensure it works.

If you're submitting a pull request, please ensure you've run the test suite with PHPUnit, installed via 
[composer][7]. Please see the instructions [here][18] on how to install it. After which you can [install][19] 
phpunit, and the  other required dev dependencies using `composer install`.

[18]: https://getcomposer.org/doc/00-intro.md#downloading-the-composer-executable
[19]: https://getcomposer.org/doc/03-cli.md#install
[20]: https://github.com/sebastianbergmann/phpunit
[21]: https://travis-ci.org/

PHP Version
-----------

The minimum supported version is PHP 5.3.23

#### Un-supported versions of PHP

I've left the following branches of versions which worked with the now un-supported versions of PHP:

- [PHP 5.2 Branch][22] 

Please feel free to use, fork etc. any of these branches. Any issues which arise in them won't 
have fixes attempted I'm afraid. However if you've attempted a fix yourself, please lodge a 
pull-request and It'll be considered. 

[22]: https://github.com/chappy84/google-analytics-server-side/tree/php-5.2

LICENSE
-------

This software uses the BSD 3-Clause license:

Copyright (c) 2011-2020, Tom Chapman (http://tom-chapman.uk)
All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are
permitted provided that the following conditions are met:

1. Redistributions of source code must retain the above copyright notice, this list of conditions
and the following disclaimer.

2. Redistributions in binary form must reproduce the above copyright notice, this list of
conditions and the following disclaimer in the documentation and/or other materials provided with
the distribution.

3. Neither the name of the copyright holder nor the names of its contributors may be used to
endorse or promote products derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR
IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR
CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY
WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE

N/B: This code is nether written or endorsed by Google or any of it's employees.  
"Google" and "Google Analytics" are trademarks of Google Inc. and it's respective subsidiaries.

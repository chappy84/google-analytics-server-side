Google Analytics Server Side
============================

Google Analytics Server Side is an implementation of the 
[Google Analytics Web Tracking ECMAScript][1] in [PHP][2].  
It implements parts of the interface that would be available without ECMAScript in a 
Browser.

CODE: `git clone git://github.com/chappy84/google-analytics-server-side.git`  
HOME: <http://github.com/chappy84/google-analytics-server-side>  
BUGS: <http://github.com/chappy84/google-analytics-server-side/issues>  

Google Analytics was developed by [Google][3].  
This PHP adaptation is maintained by [Tom Chapman][4].  

[1]: https://developers.google.com/analytics/devguides/collection/gajs/
[2]: http://www.php.net/
[3]: http://www.google.com/analytics
[4]: http://tom-chapman.co.uk/

Build Status
------------

Master: [![Build Status](https://secure.travis-ci.org/chappy84/google-analytics-server-side.png?branch=master)](http://travis-ci.org/chappy84/google-analytics-server-side)  
Development: [![Build Status](https://secure.travis-ci.org/chappy84/google-analytics-server-side.png?branch=development)](http://travis-ci.org/chappy84/google-analytics-server-side)

Usage
-----

Google Analytics Server Side can be used simply in the following manner:

```php
$gass = new GoogleAnalyticsServerSide();
$gass->setAccount('UA-XXXXXXX-X')
     ->trackPageView();
```

The class constructor accepts an optional associative array parameter of available
configuration options. Basically if there's a public method to set the variable
then it can be passed as part of the array to the class.

e.g.

```php
$gass = new GoogleAnalyticsServerSide();
$gass->setAccount('UA-XXXXXXX-X')
     ->setBotInfo(true);
```

could also be done like this:

```php
$gass = new GoogleAnalyticsServerSide(array('account'	=> 'UA-XXXXXXX-X',
    										'botInfo'	=> true));
```

These options can also be set individually by the method setOption,
or in one go with the method setOptions

Most of the [current basic methods][5] available in ga.js tracking code have been 
implemented.  
The methods implemented are:

- deleteCustomVar
- getAccount
- getVersion
- getVisitorCustomVar
- setAccount
- setCustomVar
- setSessionCookieTimeout
- setVisitorCookieTimeout
- trackPageview

The methods not implemented yet are:

- getName
- setSampleRate
- setSiteSpeedSampleRate

Extra methods are also available for the information which would normally be 
pre-determined in the javascript or http request object from the browser. The User Agent, 
Server Name, Remote Address, Document Path, Document Referer, Google Analytics Version, 
Accepted Language, Cookies and Search Engines are all set automatically without any method 
calls being required by the developer. However, the following methods are available to set 
these variables and should be called before the trackPageView / trackEvent method to save 
the tracking information:

- setVersion
- setAcceptLanguage
- setUserAgent
- setServerName
- setRemoteAddress
- setDocumentPath
- setDocumentReferer
- setCookies

On top of this there are also set methods to alter the default values for the the page 
title and document character set.  
These are available via the following methods:

- setPageTitle
- setCharset

Get methods are also provided for all of the above.  
All methods but get methods allow chaining for ease of use.

### Event Tracking

Event tracking is implemented using the [same functionality as in the ga.js tracking code][6]

```php
$gass->trackEvent('Category',                             // string
                  'Action',                               // string
                  'Label',                                // string [optional]
                  Value,                                  // integer [optional]
                  nonInteraction);                        // boolean [optional]
```

N.B. trackEvent() does not require trackPageView() to be called first.  
However if you do not call trackPageView first or set nonInteraction to true then your 
pages/visit metric may become less than 1.

[5]: https://developers.google.com/analytics/devguides/collection/gajs/methods/gaJSApiBasicConfiguration
[6]: https://developers.google.com/analytics/devguides/collection/gajs/methods/gaJSApiEventTracking

BotInfo
-------

You must enable botInfo for it to ignore any search/trawler bots.  
To do this you need to pass one of true, and associative array or an instance of the 
adapter you want to use into the class.  The code will default to the BrowsCap adapter. 
Setting this to true will use the default. If you pass an associative array, this will be 
passed to BotInfo and through to the Adapter. When providing an associative array you can 
also pass the element 'adapter' which will tell BotInfo which class to use as the Adapter. 
You can also pass an instance of a GASS\BotInfo Adapter which will be used by the 
GASS\BotInfo Class.

### Adapters

There are two adapters available in the GASS framework

#### BrowsCap
There is one optional option as part of the array configuration parameter. 

- browscap: This is the same as the php ini setting [browscap][7], a file-system location 
where the [php_browscap.ini file][8] is located / can be downloaded to.

e.g.

```php
$gass = new GoogleAnalyticsServerSide(array('botInfo' 	=> true,
    										'account'	=> 'UA-XXXXXXX-X'));
```

or

```php
$gass = new GoogleAnalyticsServerSide(array('botInfo' 	=> array(	'adapter' => 'BrowsCap',
    																'browscap'=> '/tmp/php_browscap.ini'),
    										'account'	=> 'UA-XXXXXXX-X'));
```

or

```php
$gass = new GoogleAnalyticsServerSide(array('account'	=> 'UA-XXXXXXX-X'))
$browsCapAdapter = new \GASS\BotInfo\BrowsCap;
$gass->setBotInfo($browsCapAdapter);
```

When an update for the browscap ini file is available [on the server][8] the code will 
automatically download the file into the location provided.

N/B: You MUST either provide the browscap setting or have it set in php.ini, otherwise 
this adapter will not work.

N/B2: Due to an issue with the browscap ini file only being loaded when PHP starts up 
(which is with the web-server apache, PHP-FPM etc.) the code deals with the ini file 
itself, rather than using the built in get_browser function. This ensures the auto-update 
functionality will work without the need to restart the web-server.

#### UserAgentStringInfo
This was the previous default for Google Analytics Server Side which downloads a csv list 
of search engine crawlers from [user-agent-string.info][9].  
There are three options as part of the array configuration parameter:

- cachePath: where to save the list of bots downloaded from user-agent-string.info (required)
- cacheFilename: the filename to save the list of bots to (optional, defaults to bots.csv)
- cacheLifetime: number of secods before the cache expires (optional, defaults to 2592000 (30 days))

This can be implemented in the same way as the BrowsCap adapter.

[7]: http://www.php.net/manual/en/misc.configuration.php#ini.browscap
[8]: http://tempdownloads.browserscap.com/ 
[9]: http://user-agent-string.info/download

Http
----

This is a singleton class which provides http functionality across all sections of the 
GASS framework.  
This will default to using the Curl adapter if it's available otherwise it'll fall back 
to the Stream adapter. It requires no options. All options should be passed as a 
configuration option to GoogleAnalyticsServerSide either via the configuration parameter 
in the 'http' element or via the setHttp parameter. This can either be an associative 
array or an instance of the required adapter.

e.g.

```php
$gass = new GoogleAnalyticsServerSide(array('account'	=> 'UA-XXXXXXX-X',
    										'http'		=> array(	'adapter'		=> 'Curl',
    																CURLOPT_PROXY	=> 'http://exampleproxy.local:8080'));
```

or

```php
$httpAdapter = new \GASS\Http\Stream();
$gass = new GoogleAnalyticsServerSide(array('account'	=> 'UA-XXXXXXX-X'));
$gass->setHttp($httpAdapter);
```

### Adapters

There are two Adapters available to GASS\Http, these are:

#### Stream
Stream creates a stream context and utilises this stream with file_get_contents. See 
[php's example][10]. Any options provided to this class will go into the 'http' array for 
the stream context, thus you may pass any headers or proxy information etc. into this to 
use in the connection when made.

#### Curl
This utilises the php extension cURL. cURL is recommended, however as it's not always 
available the code defaults to stream to allow all servers make http requests in the 
correct way.  
Any options provided to this class must be passed using the [curl constants][11] as 
identifiers (associative array keys or option names).

[10]: http://www.php.net/file_get_contents#example-2118
[11]: http://www.php.net/manual/en/function.curl-setopt.php#refsect1-function.curl-setopt-parameters

Location
--------

The Location will be reported as the location of the server if you use the GA Account 
number in the format UA-XXXXXXX-X as provided by Google. If you alter this to the format 
MO-XXXXXXX-X then the location will be tracked correctly and appear on the location map as 
it does with the normal ECMAScript tracking.

Cookies
-------

Cookies are automatically set when either trackPageView or trackEvent are called.  
They are however only sent as headers to the browser once, thus if you call either 
function more than once, or call both functions, then they will only be included in the 
headers when the first call is made.

You do have the option to turn off the sending of the cookie headers to the browser which 
can be done by calling disableCookieHeaders before calling trackPageView / trackEvent for 
the first time.

PHP Version
-----------

The Bad News: This project is now a PHP 5.3+ project. PHP themselves no longer support 
PHP 5.2 as of August 2011 and with PHP 5.4 now available it's time we left 5.2 behind 
(sorry to all those on shared hosting stuck with it).  
   
The Good News: I have however left a [PHP 5.2 Branch][12] which you can feel free to use, 
fork etc.. I will try to fix any issues which arise in this branch. Please notify me of 
any issues via the bugs link at the top of this readme, or via a pull request from your 
fork if you've attempted a fix yourself.

[12]: https://github.com/chappy84/google-analytics-server-side/tree/php-5.2

Quick Note on External Frameworks
---------------------------------

You may be wondering why this framework doesn't use an external framework (such as Zend 
Framework, Symfony etc.) for certain sections of this (Http, Validators etc.).  It was 
decided not to rely on any external sources, mainly for maintenance reasons (there's 
enough for the GA code), but also so that developers could use this code with minimal 
setup and without having to download any other code from other locations.

LICENSE
-------

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
"AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

Google Analytics Server Side is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or any later
version.

The GNU General Public License can be found at  
http://www.gnu.org/copyleft/gpl.html.

N/B: This code is nether written or endorsed by Google or any of it's employees.  
"Google" and "Google Analytics" are trademarks of Google Inc. and it's respective subsidiaries.

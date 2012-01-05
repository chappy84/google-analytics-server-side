Google Analytics Server Side
============================

Google Analytics Server Side is an implementation of [Google Analytics ECMAScript][1] code in [PHP][2]
It implements parts of the interface that would be available without ECMAScript in a Browser

CODE: `git clone git://github.com/chappy84/google-analytics-server-side.git`
HOME: <http://github.com/chappy84/google-analytics-server-side>
BUGS: <http://github.com/chappy84/google-analytics-server-side/issues>

Google Analytics was developed by [Google][3].
This PHP adaptation is maintained by [Tom Chapman][4].

[1]: http://code.google.com/apis/analytics/docs/tracking/home.html
[2]: http://www.php.net/
[3]: http://www.google.com/analytics
[4]: http://tom-chapman.co.uk/

USAGE
-----

Google Analytics Server Side can be used simply in the following manner:

	$gass = new GoogleAnalyticsServerSide();
	$gass->setAccount('UA-XXXXXXX-X')
		->createPageView();

The class constructor accepts an optional associative array parameter of available
configuration options. Basically if there's a public method to set the variable
then it can be passed as part of the array to the class.

e.g.

	$gass = new GoogleAnalyticsServerSide();
	$gass->setAccount('UA-XXXXXXX-X')
		->setBotInfo(true);

could also be done like this:

	$gass = new GoogleAnalyticsServerSide(array('account'	=> 'UA-XXXXXXX-X'
											,	'botInfo'	=> true));

These options can also be set individually by the method setOption,
or in one go with the method setOptions

The User Agent, Server Name, Remote Address, Document Path, Document Referer, Google
Analytics Version, Accepted Language and Cookies are all set automatically without
any method calls being required by the developer. However, the following methods are
available to set these variables and should be called before the createPageView/createEvent
method to save the tracking information:

- setVersion
- setAcceptLanguage
- setUserAgent
- setServerName
- setRemoteAddress
- setDocumentPath
- setDocumentReferer
- setCookies

On top of this there are also set methods to alter the default values for
the Google Analytics account, the page title, document charset and the event.
These are available via the following methods:

- setAccount
- setPageTitle
- setCharset
- setEvent**

**setEvent is not required if arguments are provided to createEvent.
get methods are also provided for all of the above.
All methods but get methods allow chaining for ease of use.

### Event Tracking

	$gass->createEvent('Category', 'Action', 'Label (optional)', 'Value [optional - integer]');

N.B. createEvent() does not require createPageView() to be called first, however your pages/visit
metric may become < 1 if you do not call createPageView() first.

BotInfo
-------

You must enable botInfo for it to ignore any search/trawler bots.
To do this you need to pass one of true, and associative array or an instance of the adapter you want to use
into the class.  The code will default to the BrowserCap adapter. Setting this to true will use the default.
If you pass an associative array, this will be passed to BotInfo and through to the Adapter. When providing
an associative array you can also pass the element 'adapter' which will tell BotInfo which class to use as the
Adapter. You can also pass an instance of a GASS_BotInfo Adapter which will be used by the GASS_BotInfo Class.

### Adapters

There are two adapters available in the GASS framework

#### BrowserCap
To use this adapter you must have the php ini setting [browsercap][5] set.
The code will automatically update/download the file in/to the location provided.

There is one optional option as part of the array configuration parameter.  This is 'cacheLifetime'.
cacheLifetime is the number of seconds before the ini file set in browscap is regarded as
expired and the code will try to download a replacement file. This defaults to 2592000 (30 days).

e.g.

	$gass = new GoogleAnalyticsServerSide(array('botInfo' 	=> true
											,	'account'	=> 'UA-XXXXXXX-X'));

or

	$gass = new GoogleAnalyticsServerSide(array('botInfo' 	=> array(	'adapter' 		=> 'BrowserCap'
																	,	'cacheLifetime'	=> 86400)
											,	'account'	=> 'UA-XXXXXXX-X'));

or

	$gass = new GoogleAnalyticsServerSide(array('account'	=> 'UA-XXXXXXX-X'))
	$browserCapAdapter = new GASS_BotInfo_BrowserCap;
	$gass->setBotInfo($browserCapAdapter);

#### UserAgentStringInfo
This was the previous default for Google Analytics Server Side which downloads a csv list of search engine
crawlers from [user-agent-string.info][6].
There are three options as part of the array configuration parameter:

- cachePath: where to save the list of bots downloaded from user-agent-string.info (required)
- cacheFilename: the filename to save the list of bots to (optional, defaults to bots.csv)
- cacheLifetime: number of secods before the cache expires (optional, defaults to 2592000 (30 days))

This can be implemented in the same way as the BrowserCap adapter.

[5]: http://www.php.net/manual/en/misc.configuration.php#ini.browscap
[6]: http://user-agent-string.info/download

Http
----

This is a singleton class which provides http functionality across all sections of the GASS framework.
This will default to using the Stream adapter and requires no options. All options should be passed as a
configuration option to GoogleAnalyticsServerSide either via the configuration parameter int he 'http' element
or via the setHttp parameter. This can either be an associative array or an instance of the required adapter

e.g.

	$gass = new GoogleAnalyticsServerSide(array('account'	=> 'UA-XXXXXXX-X'
											,	'http'		=> array(	'adapter'		=> 'Curl'
																	,	CURLOPT_PROXY	=> 'http://exampleproxy.local:8080'));

or

	$httpAdapter = new GASS_Http_Stream();
	$gass = new GoogleAnalyticsServerSide(array('account'	=> 'UA-XXXXXXX-X'));
	$gass->setHttp($httpAdapter);

### Adapters

There are two Adapters available to GASS_Http, these are:

#### Stream
Stream creates a stream context and utilises this stream with file_get_contents. See [php's example][7].
Any options provided to this class will go into the 'http' array for the stream context, thus you may pass
any headers or proxy information etc. into this to use in the connection when made.


#### Curl
This utilises the php extension cURL. cURL is recommended, however as it's not always available the code defaults
to stream to allow all servers make http requests in the correct way.
Any options provided to this class must be passed using the [curl constants] as identifiers (associative array
keys or option names).

[7]: http://www.php.net/file_get_contents#example-2118
[8]: http://www.php.net/manual/en/function.curl-setopt.php#refsect1-function.curl-setopt-parameters

COOKIES
-------

Cookies are automatically set when either createPageView or createEvent are called.
They are however only sent as headers to the browser once, thus if you call either function more than
once, or call both functions, then they will only be included in the headers when the first call is made.

You do have the option to turn off the sending of the cookie headers to the browser which can be done
by calling disableCookieHeaders before calling createPageView/createEvent for the first time.

Quick Note on External Frameworks
---------------------------------

You may be wondering why this framework doesn't use an external framework (such as Zend Framework, Symfony etc.)
for certain sections of this (Http etc.).  It was decided not to rely on any external sources, thus developers
could use this code regardless of any framework they already had.

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

N/B: This code is nether written or endorsed by Google or any of it's
employees. "Google" and "Google Analytics" are trademarks of
Google Inc. and it's respective subsidiaries.

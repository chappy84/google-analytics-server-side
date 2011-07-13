Google Analytics Server Side
============================

Google Analytics Server Side is an implementation of [Google Analytics ECMAScript][1] code in [PHP][2]
It implements parts of the interface that would be available without ECMAScript in a Browser

CODE: `git clone git://github.com/chappy84/google-analytics-server-side.git`
HOME: <http://github.com/chappy84/google-analytics-server-side>
BUGS: <http://github.com/chappy84/google-analytics-server-side/issues>

Google Analytics was developed by [Google][3]. 
The PHP version is maintained by [Tom Chapman][4].

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
		 
The User Agent, Server Name, Remove Address, Document Path, Charset and Cookies
are all set automatically without any method calls being required by the developer
However, the following methods are available to set these variables and should be 
called before the createPageView/createEvent method to save the Page View / Event :

	setUserAgent
	setServerName
	setRemoteAddress
	setDocumentPath
	setCharset
	setCookies
	
On top of this there are also set methods to alter the default values for 
the Google Analytics tracker version, the accepted language, the Google Analytics
account, the referer, the document path, the page title and the event. These are
available via the following methods:

	setVersion
	setAcceptLanguage
	setAccount
	setDocumentReferer
	setDocumentPath
	setPageTitle
	setEvent*
	
*setEvent is not required if arguments are provided to createEvent.

get methods are also provided for all of the above.
	
All methods but get methods allow chaining for ease of use.

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

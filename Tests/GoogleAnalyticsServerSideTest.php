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
 * @copyright	Copyright (c) 2011-2012 Tom Chapman (http://tom-chapman.co.uk/)
 * @license		http://www.gnu.org/copyleft/gpl.html  GPL
 * @author 		Tom Chapman
 * @link		http://github.com/chappy84/google-analytics-server-side
 * @category	GoogleAnalyticsServerSide
 * @package		GASSTests
 * @subpackage	GoogleAnalyticsServerSide
 */

namespace GASSTests;

class GoogleAnalyticsServerSideTest
	extends \PHPUnit_Framework_TestCase {


	protected $gass;


	public function setUp() {
		parent::setUp();
		require_once __DIR__.DIRECTORY_SEPARATOR.'../GoogleAnalyticsServerSide.php';
		$this->gass = new \GoogleAnalyticsServerSide();
	}


	public function tearDown() {
		parent::tearDown();
	}


	public function testSetVersionValid() {
		$this->assertInstanceOf('GoogleAnalyticsServerSide', $this->gass->setVersion('1.2.3'));
		$this->assertEquals('1.2.3', $this->gass->getVersion());
		$this->gass->setVersion('5.20.71');
		$this->assertEquals('5.20.71', $this->gass->getVersion());
	}


	public function testSetVersionExceptionDecimal() {
		$this->setExpectedException('InvalidArgumentException', 'Invalid version number provided: 5.23');
		$this->gass->setVersion('5.23');
	}


	public function testSetVersionExceptionInteger() {
		$this->setExpectedException('InvalidArgumentException', 'Invalid version number provided: 523');
		$this->gass->setVersion('523');
	}


	public function testSetVersionExceptionString() {
		$this->setExpectedException('InvalidArgumentException', 'Invalid version number provided: abc');
		$this->gass->setVersion('abc');
	}


	public function testSetUserAgent() {
		$userAgent = 'Mozilla/5.0 (compatible; Konqueror/2.2.2)';
		$this->gass->setUserAgent($userAgent);
		$this->assertEquals($userAgent, $this->gass->getUserAgent());
		$this->assertEquals($userAgent, \GASS\Http\Http::getUserAgent());
		if (null !== $this->gass->getBotInfo()) {
			$this->assertEquals('en-gb', $this->gass->getBotInfo()->getUserAgent());
		}
	}


	public function testSetAcceptLanguageValid() {
		$this->assertInstanceOf('GoogleAnalyticsServerSide', $this->gass->setAcceptLanguage('en-GB,en;q=0.8'));
		$this->assertEquals('en-gb', $this->gass->getAcceptLanguage());
		$this->assertEquals('en-gb', \GASS\Http\Http::getAcceptLanguage());
	}


	public function testSetAcceptLanguageExceptionTooLong() {
		$this->setExpectedException('InvalidArgumentException');
		$this->gass->setAcceptLanguage('abc,def;q=0.8');
	}


	public function testSetAcceptLanguageExceptionTooLong2() {
		$this->setExpectedException('InvalidArgumentException');
		$this->gass->setAcceptLanguage('AbCDefg');
	}


	public function testSetAcceptLanguageExceptionInvalidCountry() {
		$this->setExpectedException('InvalidArgumentException');
		$this->gass->setAcceptLanguage('ab-cde');
	}


	public function testSetAcceptLanguageExceptionInvalidLanguage() {
		$this->setExpectedException('InvalidArgumentException');
		$this->gass->setAcceptLanguage('abc-de');
	}


	public function testSetServerName() {
		$serverName = 'www.example.com';
		$this->assertInstanceOf('GoogleAnalyticsServerSide', $this->gass->setServerName($serverName));
		$this->assertEquals($serverName, $this->gass->getServerName());
		$serverName = 'localhost';
		$this->gass->setServerName($serverName);
		$this->assertEquals($serverName, $this->gass->getServerName());
	}


	public function testSetRemoteAddressValid() {
		$remoteAddress = '192.168.0.1';
		$this->assertInstanceOf('GoogleAnalyticsServerSide', $this->gass->setRemoteAddress($remoteAddress));
		$this->assertEquals($remoteAddress, $this->gass->getRemoteAddress());
		$remoteAddress = '255.255.255.0';
		$this->gass->setRemoteAddress($remoteAddress);
		$this->assertEquals($remoteAddress, $this->gass->getRemoteAddress());
		$this->assertEquals($remoteAddress, \GASS\Http\Http::getRemoteAddress());
		if (null !== $this->gass->getBotInfo()) {
			$this->assertEquals($remoteAddress, $this->gass->getBotInfo()->getRemoteAddress());
		}
	}


	public function testSetRemoteAddressExceptionLetters() {
		$this->setExpectedException('InvalidArgumentException');
		$this->gass->setAcceptLanguage('abc.def.ghi.jkl');
	}


	public function testSetRemoteAddressExceptionTooHighSegments() {
		$this->setExpectedException('InvalidArgumentException');
		$this->gass->setAcceptLanguage('500.500.500.500');
	}


	public function testSetRemoteAddressExceptionMissingSegments() {
		$this->setExpectedException('InvalidArgumentException');
		$this->gass->setAcceptLanguage('255.255');
	}


	public function testSetRemoteAddressExceptionInteger() {
		$this->setExpectedException('InvalidArgumentException');
		$this->gass->setAcceptLanguage('192');
	}


	public function testSetAccountValid() {
		$account = 'UA-1234-5';
		$this->assertInstanceOf('GoogleAnalyticsServerSide', $this->gass->setAccount($account));
		$this->assertEquals($account, $this->gass->getAccount());
		$account = 'MO-1234567-89';
		$this->gass->setAccount($account);
		$this->assertEquals($account, $this->gass->getAccount());
	}


	public function testSetAccountExceptionInvalidFirstSegment() {
		$this->setExpectedException('InvalidArgumentException');
		$this->gass->setAccount('AB-1234567-0');
	}


	public function testSetAccountExceptionMissingFirstHyphen() {
		$this->setExpectedException('InvalidArgumentException');
		$this->gass->setAccount('UA1234567-0');
	}


	public function testSetAccountExceptionMissingSecondHyphen() {
		$this->setExpectedException('InvalidArgumentException');
		$this->gass->setAccount('UA-12345670');
	}


	public function testSetAccountExceptionLowerCaseFirstSegment() {
		$this->setExpectedException('InvalidArgumentException');
		$this->gass->setAccount('mo-1234567-0');
	}


	public function testSetDocumentRefererValid() {
		$documentReferer = 'http://www.example.com/random.html?a=b';
		$this->assertInstanceOf('GoogleAnalyticsServerSide', $this->gass->setDocumentReferer($documentReferer));
		$this->assertEquals($documentReferer, $this->gass->getDocumentReferer());
		$documentReferer = 'http://localhost/random';
		$this->gass->setDocumentReferer($documentReferer);
		$this->assertEquals($documentReferer, $this->gass->getDocumentReferer());
	}


	public function testSetDocumentRefererExceptionMissingHostname() {
		$this->setExpectedException('InvalidArgumentException');
		$this->gass->setDocumentReferer('http:///random.html?a=b');
	}


	public function testSetDocumentRefererExceptionOnlyProtocol() {
		$this->setExpectedException('InvalidArgumentException');
		$this->gass->setDocumentReferer('http://');
	}
}
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
 * @subpackage	GoogleAnalyticsServerSideTest
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


	public function testSetVersionExceptionWrongDataType() {
		$this->setExpectedException('InvalidArgumentException', 'Version must be a string.');
		$this->gass->setVersion(array('5.2.3'));
	}


	public function testSetUserAgentValid() {
		$userAgent = 'Mozilla/5.0 (compatible; Konqueror/2.2.2)';
		$this->assertInstanceOf('GoogleAnalyticsServerSide', $this->gass->setUserAgent($userAgent));
		$this->assertEquals($userAgent, $this->gass->getUserAgent());
		$this->assertEquals($userAgent, \GASS\Http\Http::getUserAgent());
		if (null !== $this->gass->getBotInfo()) {
			$this->assertEquals($userAgent, $this->gass->getBotInfo()->getUserAgent());
		}
	}


	public function testSetUserAgentExceptionWrongDataType() {
		$this->setExpectedException('InvalidArgumentException', 'User Agent must be a string.');
		$this->gass->setUserAgent(array('Mozilla/5.0 (compatible; Konqueror/2.2.2)'));
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


	public function testSetAcceptLanguageExceptionWrongDataType() {
		$this->setExpectedException('InvalidArgumentException', 'Accept Language must be a string.');
		$this->gass->setAcceptLanguage(array('en-gb'));
	}


	public function testSetServerNameValid() {
		$serverName = 'www.example.com';
		$this->assertInstanceOf('GoogleAnalyticsServerSide', $this->gass->setServerName($serverName));
		$this->assertEquals($serverName, $this->gass->getServerName());
		$serverName = 'localhost';
		$this->gass->setServerName($serverName);
		$this->assertEquals($serverName, $this->gass->getServerName());
	}


	public function testSetServerNameExceptionWrongDataType() {
		$this->setExpectedException('InvalidArgumentException', 'Server Name must be a string.');
		$this->gass->setServerName(array('www.example.com'));
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
		$this->gass->setRemoteAddress('abc.def.ghi.jkl');
	}


	public function testSetRemoteAddressExceptionTooHighSegments() {
		$this->setExpectedException('InvalidArgumentException');
		$this->gass->setRemoteAddress('500.500.500.500');
	}


	public function testSetRemoteAddressExceptionMissingSegments() {
		$this->setExpectedException('InvalidArgumentException');
		$this->gass->setRemoteAddress('255.255');
	}


	public function testSetRemoteAddressExceptionInteger() {
		$this->setExpectedException('InvalidArgumentException');
		$this->gass->setRemoteAddress('192');
	}


	public function testSetRemoteAddressExceptionWrongDataType() {
		$this->setExpectedException('InvalidArgumentException', 'Remote Address must be a string.');
		$this->gass->setRemoteAddress(array('255.255.255.0'));
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


	public function testSetAccountExceptionWrongDataType() {
		$this->setExpectedException('InvalidArgumentException', 'Account must be a string.');
		$this->gass->setAccount(array('UA-1234-5'));
	}


	public function testSetDocumentRefererValid() {
		$documentReferer = 'http://www.example.com/random.html?a=b';
		$this->assertInstanceOf('GoogleAnalyticsServerSide', $this->gass->setDocumentReferer($documentReferer));
		$this->assertEquals($documentReferer, $this->gass->getDocumentReferer());
		$documentReferer = 'http://localhost/random';
		$this->gass->setDocumentReferer($documentReferer);
		$this->assertEquals($documentReferer, $this->gass->getDocumentReferer());
	}


	public function testSetDocumentRefererExceptionMissingProtocol() {
		$this->setExpectedException('InvalidArgumentException');
		$this->gass->setDocumentReferer('www.example.com/random.html?a=b');
	}


	public function testSetDocumentRefererExceptionMissingHostname() {
		$this->setExpectedException('InvalidArgumentException');
		$this->gass->setDocumentReferer('http:///random.html?a=b');
	}


	public function testSetDocumentRefererExceptionOnlyProtocol() {
		$this->setExpectedException('InvalidArgumentException');
		$this->gass->setDocumentReferer('http://');
	}


	public function testSetDocumentRefererExceptionInvalidHostname() {
		$this->setExpectedException('InvalidArgumentException');
		$this->gass->setDocumentReferer('http://www.example_1.com/');
	}


	public function testSetDocumentRefererExceptionInvalidProtocol() {
		$this->setExpectedException('InvalidArgumentException');
		$this->gass->setDocumentReferer('a%b://www.example.com/');
	}


	public function testSetDocumentRefererExceptionWrongDataType() {
		$this->setExpectedException('InvalidArgumentException', 'Document Referer must be a string.');
		$this->gass->setDocumentReferer(array('http://localhost/random'));
	}


	public function testSetDocumentPathValid() {
		$documentPath = '/abcdefg.html';
		$this->assertInstanceOf('GoogleAnalyticsServerSide', $this->gass->setDocumentPath($documentPath));
		$this->assertEquals($documentPath, $this->gass->getDocumentPath());
		$this->assertInstanceOf('GoogleAnalyticsServerSide', $this->gass->setDocumentPath('/abcdefg.html?a=b&c=d'));
		$this->assertEquals($documentPath, $this->gass->getDocumentPath());
	}


	public function testSetDocumentPathExceptionWrongDataType() {
		$this->setExpectedException('InvalidArgumentException', 'Document Path must be a string.');
		$this->gass->setDocumentPath(array('/abcdefg.html'));
	}


	public function testSetPageTitleValid() {
		$pageTitle = 'Abcdef Ghijk Lmnop';
		$this->assertInstanceOf('GoogleAnalyticsServerSide', $this->gass->setPageTitle($pageTitle));
		$this->assertEquals($pageTitle, $this->gass->getPageTitle());
	}


	public function testSetPageTitleExceptionWrongDataType() {
		$this->setExpectedException('InvalidArgumentException', 'Page Title must be a string.');
		$this->gass->setPageTitle(array('Abcdef Ghijk Lmnop'));
	}


	public function testSetCustomVarValid() {
		$customVar1 = array('index' => 1
						,	'name'	=> 'Custom Var 1'
						,	'value'	=> 'Custom Value 1'
						,	'scope'	=> 3);
		$customVar2 = array('index' => 5
						,	'name'	=> 'Custom Var 5'
						,	'value'	=> 'Custom Value 5'
						,	'scope'	=> 2);
		$this->assertInstanceOf('GoogleAnalyticsServerSide', $this->gass->setCustomVar($customVar1['name'], $customVar1['value']));
		$this->gass->setCustomVar($customVar2['name'], $customVar2['value'], $customVar2['scope'], $customVar2['index']);
		$customVars = $this->gass->getCustomVariables();
		$this->assertArrayHasKey('index1', $customVars);
		$this->assertEquals($customVar1, $customVars['index1']);
		$this->assertEquals($customVar1['value'], $this->gass->getVisitorCustomVar(1));
		$this->assertEquals(array(implode('=',$customVar1)), $this->gass->getCustomVarsByScope(3));
		$this->assertArrayHasKey('index5', $customVars);
		$this->assertEquals($customVar2, $customVars['index5']);
		$this->assertEquals($customVar2['value'], $this->gass->getVisitorCustomVar(5));
		$this->assertEquals(array(implode('=',$customVar2)), $this->gass->getCustomVarsByScope(2));
	}


	public function testSetCustomVarExceptionInvalidIndexTooHigh() {
		$this->setExpectedException('OutOfBoundsException', 'The index must be an integer between 1 and 5.');
		$this->gass->setCustomVar('Custom Var 1', 'Custom Value 1', 3, 6);
	}


	public function testSetCustomVarExceptionInvalidIndexTooLow() {
		$this->setExpectedException('OutOfBoundsException', 'The index must be an integer between 1 and 5.');
		$this->gass->setCustomVar('Custom Var 1', 'Custom Value 1', 3, 0);
	}


	public function testSetCustomVarExceptionInvalidScopeTooHigh() {
		$this->setExpectedException('InvalidArgumentException', 'The Scope must be a value between 1 and 3');
		$this->gass->setCustomVar('Custom Var 1', 'Custom Value 1', 4, 1);
	}


	public function testSetCustomVarExceptionInvalidScopeTooLow() {
		$this->setExpectedException('InvalidArgumentException', 'The Scope must be a value between 1 and 3');
		$this->gass->setCustomVar('Custom Var 1', 'Custom Value 1', 0, 1);
	}


	public function testSetCustomVarExceptionNameWrongDataType() {
		$this->setExpectedException('InvalidArgumentException', 'Custom Var Name must be a string.');
		$this->gass->setCustomVar(array('Custom Var 1'), 'Custom Value 1');
	}


	public function testSetCustomVarExceptionValueWrongDataType() {
		$this->setExpectedException('InvalidArgumentException', 'Custom Var Value must be a string.');
		$this->gass->setCustomVar('Custom Var 1', array('Custom Value 1'));
	}


	public function testSetCustomVarExceptionExceedsByteVarLimit() {
		$this->setExpectedException('DomainException', 'The name / value combination exceeds the 64 byte custom var limit.');
		$this->gass->setCustomVar('abcdefghijklmnopqrstuvwxyz123456', 'abcdefghijklmnopqrstuvwxyz1234567');
	}


	public function testSetCustomVarExceptionExceededVarCountLimit() {
		$this->setExpectedException('OutOfBoundsException', 'You cannot add more than 5 custom variables.');
		$this->gass->setCustomVar('Custom Var 1', 'Custom Value 1');
		$this->gass->setCustomVar('Custom Var 2', 'Custom Value 2');
		$this->gass->setCustomVar('Custom Var 3', 'Custom Value 3');
		$this->gass->setCustomVar('Custom Var 4', 'Custom Value 4');
		$this->gass->setCustomVar('Custom Var 5', 'Custom Value 5');
		$this->gass->setCustomVar('Custom Var 6', 'Custom Value 6');
	}


	public function testDeleteCustomVarValid() {
		$this->gass->setCustomVar('Custom Var 1', 'Custom Value 1');
		$this->gass->setCustomVar('Custom Var 2', 'Custom Value 2');
		$this->gass->setCustomVar('Custom Var 3', 'Custom Value 3');
		$this->assertInstanceOf('GoogleAnalyticsServerSide', $this->gass->deleteCustomVar(2));
		$this->assertArrayNotHasKey('index2', $this->gass->getCustomVariables());
	}


	public function testDeleteCustomVarExceptionWrongDataType() {
		$this->setExpectedException('InvalidArgumentException', 'Custom Var Index must be a string.');
		$this->gass->deleteCustomVar(array(1));
	}


	public function testSetCharsetValid() {
		$charset = 'UTF-8';
		$this->assertInstanceOf('GoogleAnalyticsServerSide', $this->gass->setCharset($charset));
		$this->assertEquals($charset, $this->gass->getCharset());
		$this->gass->setCharset(strtolower($charset));
		$this->assertEquals($charset, $this->gass->getCharset());
	}


	public function testSetCharsetExceptionWrongDataType() {
		$this->setExpectedException('InvalidArgumentException', 'Charset must be a string.');
		$this->gass->setCharset(array('UTF-8'));
	}
}
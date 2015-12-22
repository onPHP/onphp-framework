<?php
	final class CurlHttpClientTest extends TestCase
	{
		private static $failTestMsg = null;
		private static $emptyMsg = null;
		
		public static function setUpBeforeClass() {
			parent::setUpBeforeClass();
			if (!defined('ONPHP_CURL_TEST_URL'))
				self::$failTestMsg = 'not defined test constant ONPHP_CURL_TEST_URL';
			
			self::$emptyMsg = file_get_contents(ONPHP_CURL_TEST_URL);
		}
		
		public function setUp() {
			parent::setUp();
			if (self::$failTestMsg)
				$this->fail (self::$failTestMsg);
			
			$this->assertEquals(
				$this->generateString(array(), array(), array(), ''),
				self::$emptyMsg,
				'wrong server empty response'
			);
		}

		public function testGetWithAdditionalGet()
		{
			$get = array(
				'a' => array('b@&=' => array('c' => '@d@[]')),
				'e' => array('f' => array('&1&', '3', '5')),
			);
			
			$request = $this->spawnRequest(HttpMethod::get(), 'urlGet=really')->
				setGet($get)->
				setPost(array('post' => 'value'));
			
			$response = $this->spawnClient()->send($request);
			
			$this->assertEquals(
				$this->generateString(array('urlGet' => 'really') + $get, array(), array(), ''),
				$response->getBody()
			);
		}

		public function testPostAndFilesWithMultiCurl()
		{
			$get = array(
				'get' => 'value',
			);
			$post1 = array(
				'c' => array(
					'd&=@' => '@',
					'e' => array(
						'f' => array('1' => '2'),
						'g' => array('4' => '3'),
					),
					'k' => $this->getFile1Path(),
				)
			);
			$post2 = array('post' => 'value');
			$files = array(
				'file1' => $this->getFile1Path(),
				'file2' => $this->getFile2Path(),
			);
			$body = file_get_contents($this->getFile1Path());
			
			$request1 = $this->spawnRequest(HttpMethod::post(), 'urlGet=super')->
				setGet($get)->
				setPost($post1);
			$request2 = $this->spawnRequest(HttpMethod::post())->
				setPost($post2)->
				setFiles($files);
			$request3 = $this->spawnRequest(HttpMethod::post())->
				setBody($body);
			
			$client = $this->spawnClient()->
				addRequest($request1)->
				addRequest($request2)->
				addRequest($request3);
			$client->multiSend();
			
			//check response 1st request
			$this->assertEquals(
				$this->generateString(array('urlGet' => 'super') + $get, $post1, array(), UrlParamsUtils::toString($post1)),
				$client->getResponse($request1)->getBody()
			);
			
			//check response 2nd request
			$filesExpectation = array(
				'file1' => file_get_contents($this->getFile1Path()),
				'file2' => file_get_contents($this->getFile2Path()),
			);
			$this->assertEquals(
				$this->generateString(array(), $post2, $filesExpectation, ''),
				$client->getResponse($request2)->getBody()
			);
			
			//check response 3rd request
			$this->assertEquals(
				$this->generateString(array(), array(), array(), $body),
				$client->getResponse($request3)->getBody()
			);
		}

		public function testSecurityExceptionWithSendingFileAndAtInPostPhp5_4()
		{
			if (version_compare(PHP_VERSION, '5.5.0', '>=')) {
				$this->markTestSkipped('Test only for php versions lower 5.5');
			}

			$post = array(
				'a' => array(
					array('b' => '@foobar')
				)
			);

			$files = array('file' => $this->getFile1Path());

			$request = $this->spawnRequest(HttpMethod::post())->
				setPost($post)->
				setFiles($files);

			try {
				$this->spawnClient()->send($request);
				$this->fail('expected NetworkException about security');
			} catch (NetworkException $e) {
				$this->assertStringStartsWith('Security excepion:', $e->getMessage());
			}
		}

		public function testSecurityExceptionWithSendingFileAndAtInPostPhp5_5()
		{
			if (!version_compare(PHP_VERSION, '5.5.0', '>=')) {
				$this->markTestSkipped('Test only for php versions 5.5+');
			}

			$post = array(
				'a' => array(
					array('b' => '@foobar')
				)
			);

			$files = array('file' => $this->getFile1Path());

			$request = $this->spawnRequest(HttpMethod::post())->
			setPost($post)->
			setFiles($files);

			$filesExpectation = array('file' => file_get_contents($this->getFile1Path()));
			$this->assertEquals(
				$this->generateString(array(), $post, $filesExpectation, ''),
				$this->spawnClient()->send($request)->getBody()
			);
		}
		
		public function testSendingNotExistsFile()
		{	
			$files = array('file' => $this->getFileNotExists());
			
			$request = $this->spawnRequest(HttpMethod::post())->
				setFiles($files);
			
			try {
				$this->spawnClient()->send($request);
				$this->fail('expected exception about not exists file');
			} catch (WrongArgumentException $e) {
				$this->assertStringStartsWith('couldn\'t access to file with path:', $e->getMessage());
			}
		}
		
		/**
		 * @param HttpMethod $method
		 * @return HttpRequest
		 */
		private function spawnRequest(HttpMethod $method, $urlPostfix = '')
		{
			$url = HttpUrl::create()->parse(ONPHP_CURL_TEST_URL);
			$glue = $url->getQuery() ? '&' : '?';
			
			return HttpRequest::create()->
				setUrl($url->parse(ONPHP_CURL_TEST_URL.$glue.$urlPostfix))->
				setMethod($method);
		}
		
		/**
		 * @return CurlHttpClient
		 */
		private function spawnClient()
		{
			return CurlHttpClient::create()->
				setOldUrlConstructor(false)->
				setTimeout(5);
		}
		
		private function generateString($get, $post, $files, $inputString)
		{
			return print_r(array($get, $post, $files, $inputString), 1);
		}
		
		private function getFile1Path()
		{
			return $this->getFileDirPath().'contents';
		}
		
		private function getFile2Path()
		{
			return $this->getFileDirPath().'contents';
		}
		
		private function getFileNotExists()
		{
			return $this->getFileDirPath().'notexists';
		}
		
		private function getFileDirPath()
		{
			return ONPHP_TEST_PATH.'main/data/directory/';
		}
	}
?>
<?php
	/* $Id$ */
	
	final class HttpRewriterTest extends TestCase
	{
		private $pathTest = array(
			'/example/' => array(
				'' => '',
				'/' => '/',
				'/example/' => '',
				'login?user=boo' => 'login?user=boo',
				'/login?user=boo' => '/login?user=boo',
				'/example/login/?user=boo' => 'login/?user=boo',
				'/example/script.php' => 'script.php',
				'http://example.org/example/login/?user=boo' => 'login/?user=boo',
			),
			
			'http://example.org/example/' => array(
				'' => '',
				'/' => '/',
				'http://example.org/example/' => '',
				'login?user=boo' => 'login?user=boo',
				'/login?user=boo' => '/login?user=boo',
				'/example/login/?user=boo' => 'login/?user=boo',
				'/example/script.php' => 'script.php',
				'http://example.org/example/login/?user=boo' => 'login/?user=boo',
				'http://example.com/example/login/?user=boo' => 'http://example.com/example/login/?user=boo',
			),
			
			'http://example.org/' => array(
				'' => '',
				'/' => '',
				'http://example.org/' => '',
				'login?user=boo' => 'login?user=boo',
				'/login?user=boo' => 'login?user=boo',
				'/example/login/?user=boo' => 'example/login/?user=boo',
				'/example/script.php' => 'example/script.php',
				'http://example.org/' => '',
				'http://example.org' => '',
				'http://example.com' => 'http://example.com',
			),
			
			'http://example.org' => array(
				'' => '',
				'/' => '/',
				'http://example.org' => '',
				'login?user=boo' => 'login?user=boo',
				'/login?user=boo' => '/login?user=boo',
				'/example/login/?user=boo' => '/example/login/?user=boo',
				'/example/script.php' => '/example/script.php',
				'http://example.org/?user=boo' => '/?user=boo',
				'http://example.org?user=boo' => '?user=boo',
				'http://example.com?user=boo' => 'http://example.com?user=boo',
			),
			
			'/' => array(
				'' => '',
				'/' => '',
				'login?user=boo' => 'login?user=boo',
				'/login?user=boo' => 'login?user=boo',
				'/example/login/?user=boo' => 'example/login/?user=boo',
				'/example/script.php' => 'example/script.php',
				'http://example.org/?user=boo' => '?user=boo',
				'http://example.org?user=boo' => '?user=boo',
			),
			
			'/example/index.php' => array(
				'' => '',
				'/' => '/',
				'/example/index.php' => 'index.php',
				'login?user=boo' => 'login?user=boo',
				'/login?user=boo' => '/login?user=boo',
				'/example/login/?user=boo' => 'login/?user=boo',
				'/example/script.php' => 'script.php',
				'http://example.org/example/login/' => 'login/',
			),
			
			'index.php' => array(
				'' => '',
				'/' => '/',
				'index.php' => 'index.php',
				'login?user=boo' => 'login?user=boo',
				'/login?user=boo' => '/login?user=boo',
				'/example/login/?user=boo' => '/example/login/?user=boo',
				'http://example.org/example/login/?user=boo' => '/example/login/?user=boo',
				'http://example.org/index.php?user=boo' => '/index.php?user=boo',
				'script.php' => 'script.php',
				'index.php/boo' => 'index.php/boo',
			),
			
			'' => array(
				'' => '',
				'/' => '/',
				'login?user=boo' => 'login?user=boo',
				'/login?user=boo' => '/login?user=boo',
				'/example/login/?user=boo' => '/example/login/?user=boo',
				'/example/script.php' => '/example/script.php',
				'http://example.org/?user=boo' => '/?user=boo',
				'http://example.org?user=boo' => '?user=boo',
			),
		);
		
		public function testGetPath()
		{
			foreach ($this->pathTest as $base => $cases) {
				$rewriter =
					HttpRewriter::create(
						HttpUrl::create()->parse($base)
					);
				
				foreach ($cases as $arg => $href) {
					$actualResult =
						$rewriter->getPath(HttpUrl::create()->parse($arg))->
							toString();
					
					$pos = strpos($href, '?');
					
					$expectedResult =
						$pos !== false
						? substr($href, 0, $pos)
						: $href;
					
					$this->assertEquals(
						$actualResult,
						$expectedResult,
						"$base - $arg"
					);
				}
			}
		}
		
		public function testGetScope()
		{
			foreach ($this->pathTest as $base => $cases) {
				$rewriter =
					HttpRewriter::create(
						HttpUrl::create()->parse($base)
					);
				
				foreach ($cases as $arg => $href) {
					$actualResult =
						$rewriter->getUrl(
							$rewriter->getScope(
								HttpUrl::create()->parse($arg)
							)
						)->
						toString();
					
					$expectedResult =
						$rewriter->getBase()->transform(
							HttpUrl::create()->parse($href)
						)->
						toString();
					
					$this->assertEquals(
						$actualResult,
						$expectedResult,
						"$base - $arg"
					);
				}
			}
		}
	}
?>
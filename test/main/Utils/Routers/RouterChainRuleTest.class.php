<?php
	/* $Id$ */
	
	class RouterChainRuleTest extends TestCase
	{
		public function setUp()
		{
			ServerVarUtils::unsetVars($_SERVER);
			RouterRewrite::me()->
				setBaseUrl(
					new HttpUrl()
				);
		}
		
		public function testChainingMatch()
		{
			$chain = new RouterChainRule();
			
			$foo =
				RouterHostnameRule::create(
					'www.example.com'
				)->
				setDefaults(
					array(
						'foo' => 1
					)
				);
			
			$bar =
				RouterStaticRule::create(
					'bar'
				)->
				setDefaults(
					array(
						'bar' => 2
					)
				);
			
			$chain->
				chain($foo)->
				chain($bar);
			
			$request = $this->buildRequest('http://www.example.com/blin');
			
			$res = $chain->match($request);
			
			$this->assertEquals(array(), $res);
			
			$request = $this->buildRequest('http://www.example.com/bar');
			
			$res = $chain->match($request);
			
			$this->assertEquals(1, $res['foo']);
			$this->assertEquals(2, $res['bar']);
		}
		
		public function testChainingShortcutMatch()
		{
			$foo =
				RouterHostnameRule::create(
					'www.example.com'
				)->
				setDefaults(
					array(
						'foo' => 1
					)
				);
			
			$bar =
				RouterStaticRule::create(
					'bar'
				)->
				setDefaults(
					array(
						'bar' => 2,
						'area' => 'foo',
						'action' => 'bar'
					)
				);
			
			$chain = $foo->chain($bar);
			
			$request = $this->buildRequest('http://www.example.com/bar');
			
			$res = $chain->match($request);
			
			$this->assertEquals(1, $res['foo']);
			$this->assertEquals(2, $res['bar']);
		}
		
		public function testChainingMatchFailure()
		{
			$foo =
				RouterHostnameRule::create(
					'www.example.com'
				)->
				setDefaults(
					array(
						'foo' => 1
					)
				);
			
			$bar =
				RouterStaticRule::create(
					'bar'
				)->
				setDefaults(
					array(
						'bar' => 2,
						'area' => 'foo',
						'action' => 'bar'
					)
				);
			
			$chain = $foo->chain($bar);
			
			$request = $this->buildRequest('http://nope.example.com/bar');
			
			$res = $chain->match($request);
			
			$this->assertEquals(array(), $res);
		}
		
		public function testChainingVariableOverriding()
		{
			$foo =
				RouterHostnameRule::create(
					'www.example.com'
				)->
				setDefaults(
					array(
						'foo' => 1,
						'area' => 'foo',
						'module' => 'foo'
					)
				);
			
			$bar =
				RouterTransparentRule::create(
					'bar'
				)->
				setDefaults(
					array(
						'bar' => 2,
						'area' => 'bar',
						'action' => 'bar'
					)
				);
			
			$chain = $foo->chain($bar);
			
			$request = $this->buildRequest('http://www.example.com/bar');
			$res = $chain->match($request);
			
			$this->assertEquals('foo', $res['module']);
			$this->assertEquals('bar', $res['area']);
			$this->assertEquals('bar', $res['action']);
		}
		
		public function testChainingSeparatorOverriding()
		{
			$this->markTestSkipped('Route features not ready');
			
			$foo = RouterTransparentRule::create('foo')->
				setDefaults(array('foo' => 1));
				
			$bar = RouterTransparentRule::create('bar')->
				setDefaults(array('bar' => 2));
				
			$baz = RouterTransparentRule::create('baz')->
				setDefaults(array('baz' => 3));
			
			$chain = $foo->chain($bar, '.');
			
			$res = $chain->match('foo.bar');
			$this->assertInternalType('array', $res);
			
			$res = $chain->match('foo/bar');
			$this->assertEquals(array(), $res);
			
			$chain->chain($baz, ':');
			
			$res = $chain->match('foo.bar:baz');
			$this->assertInternalType('array', $res);
		}
		
		public function testI18nChaining()
		{
			$this->markTestSkipped('Route features not ready');
			
			$lang =
				RouterTransparentRule::create(
					':lang'
				)->
				setDefaults(
					array(
						'lang' => 'en'
					)
				);
			
			$profile =
				RouterTransparentRule::create(
					'user/:id'
				)->
				setDefaults(
					array(
						'area' => 'foo',
						'action' => 'bar'
					)
				);
			
			$chain = $lang->chain($profile);
			
			$res = $chain->match('en/user/1');
			
			$this->assertEquals('en', $res['lang']);
			$this->assertEquals('1', $res['id']);
		}
		
		public function testChainingAssemblyWithStatic()
		{
			$chain = new RouterChainRule();
			
			$foo =
				RouterHostnameRule::create(
					'www.example.com'
				)->
				setDefaults(
					array(
						'foo' => 'foo'
					)
				);
			
			$bar =
				RouterStaticRule::create(
					'bar'
				)->
				setDefaults(
					array(
						'bar' => 'bar'
					)
				);
			
			$chain->
				chain($foo)->
				chain($bar);
			
			$request = $this->buildRequest('http://www.example.com/bar');
			$res = $chain->match($request);
			
			$this->assertInternalType('array', $res);
			$this->assertRegexp(
				'#[^a-z0-9]?www\.example\.com/bar$#i',
				$chain->assembly()
			);
		}
		
		public function testChainingAssemblyWithRegex()
		{
			$chain = new RouterChainRule();
			
			$foo =
				RouterHostnameRule::create(
					'www.example.com'
				)->
				setDefaults(
					array(
						'foo' => 'foo'
					)
				);
			
			$bar =
				RouterRegexpRule::create(
					'bar'
				)->
				setDefaults(
					array(
						'bar' => 'bar'
					)
				)->
				setReverse('bar');
			
			$chain->
				chain($foo)->
				chain($bar);
			
			$request = $this->buildRequest('http://www.example.com/bar');
			$res = $chain->match($request);
			
			$this->assertInternalType('array', $res);
			$this->assertRegexp(
				'#[^a-z0-9]?www\.example\.com/bar$#i',
				$chain->assembly()
			);
		}
		
		public function testChainingReuse()
		{
			$foo =
				RouterHostnameRule::create(
					'www.example.com'
				)->
				setDefaults(
					array(
						'foo' => 'foo'
					)
				);
			
			$profile =
				RouterTransparentRule::create(
					'user/:id'
				)->
				setDefaults(
					array(
						'area' => 'prof'
					)
				);
			
			$article =
				RouterTransparentRule::create(
					'article/:id'
				)->
				setDefaults(
					array(
						'area' => 'art',
						'action' => 'art'
					)
				);
			
			$profileChain = $foo->chain($profile);
			$articleChain = $foo->chain($article);
			
			$request = $this->buildRequest('http://www.example.com/user/1');
			$res = $profileChain->match($request);
			
			$this->assertInternalType('array', $res);
			$this->assertEquals('prof', $res['area']);
			
			$request = $this->buildRequest('http://www.example.com/article/1');
			$res = $articleChain->match($request);
			
			$this->assertInternalType('array', $res);
			$this->assertEquals('art', $res['area']);
			$this->assertEquals('art', $res['action']);
		}
		
		public function testAssemblyWithHostnameAndTransparent()
		{
			$chain = new RouterChainRule();
			
			$host =
				RouterHostnameRule::create(
					':subdomain.example.com'
				)->
				setDefaults(
					array(
						'subdomain' => 'www'
					)
				);
			
			$transparent =
				RouterTransparentRule::create(
					':bar/:area/:action'
				)->
				setDefaults(
					array(
						'bar' => 'barvalue',
						'area' => 'controller',
						'action' => 'create',
					)
				);
			
			$chain->
				chain($host)->
				chain($transparent);
						
			$this->assertEquals(
				'http://www.example.com/',
				$chain->assembly()
			);
			
			$this->assertEquals(
				'http://www.example.com/barvalue/controller/misc',
				$chain->assembly(
					array(
						'action' => 'misc'
					)
				)
			);
						
			$this->assertEquals(
				'http://www.example.com/barvalue/misc',
				$chain->assembly(
					array(
						'area' => 'misc'
					)
				)
			);
			
			$this->assertEquals(
				'http://www.example.com/misc',
				$chain->assembly(
					array(
						'bar' => 'misc'
					)
				)
			);
		}
		
		/**
		 * @param string $url
		 * @return HttpRequest
		**/
		protected function buildRequest($url)
		{
			ServerVarUtils::build($_SERVER, $url);
			
			return
				HttpRequest::create()->
				setServer($_SERVER);
		}
	}
?>
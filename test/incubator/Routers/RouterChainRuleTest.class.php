<?php
	/** $Id$ **/

	class RouterChainRuleTest extends PHPUnit_Framework_TestCase
	{
		public function setUp()
		{
			ServerVarUtils::unsetVars($_SERVER);
		}

		public function testChainingMatch()
		{
			$chain = new RouterChainRule();

			$foo = new RouterHostnameRule(
				'www.example.com',
				array(
					'foo' => 1
				)
			);

			$bar = new RouterStaticRule(
				'bar',
				array(
					'bar' => 2
				)
			);

			$chain->
				chain($foo)->
				chain($bar);

			$request = $this->buildRequest('http://www.example.com/blin');

			$res = $chain->match($request);

			$this->assertFalse($res);

			$request = $this->buildRequest('http://www.example.com/bar');

			$res = $chain->match($request);

			$this->assertEquals(1, $res['foo']);
			$this->assertEquals(2, $res['bar']);
		}

		public function testChainingShortcutMatch()
		{
			$foo = new RouterHostnameRule(
				'www.example.com',
				array(
					'foo' => 1
				)
			);

			$bar = new RouterStaticRule(
				'bar',
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
			$foo = new RouterHostnameRule(
				'www.example.com',
				array(
					'foo' => 1
				)
			);

			$bar = new RouterStaticRule(
				'bar',
				array(
					'bar' => 2,
					'area' => 'foo',
					'action' => 'bar'
				)
			);

			$chain = $foo->chain($bar);

			$request = $this->buildRequest('http://nope.example.com/bar');

			$res = $chain->match($request);

			$this->assertFalse($res);
		}

		public function testChainingVariableOverriding()
		{
			$foo = new RouterHostnameRule(
				'www.example.com',
				array(
					'foo' => 1,
					'area' => 'foo',
					'module' => 'foo'
				)
			);

			$bar = new RouterTransparentRule(
				'bar',
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

			$foo = new RouterTransparentRule('foo', array('foo' => 1));
			$bar = new RouterTransparentRule('bar', array('bar' => 2));
			$baz = new RouterTransparentRule('baz', array('baz' => 3));

			$chain = $foo->chain($bar, '.');

			$res = $chain->match('foo.bar');
			$this->assertType('array', $res);

			$res = $chain->match('foo/bar');
			$this->assertEquals(false, $res);

			$chain->chain($baz, ':');

			$res = $chain->match('foo.bar:baz');
			$this->assertType('array', $res);
		}

		public function testI18nChaining()
		{
			$this->markTestSkipped('Route features not ready');

			$lang = new RouterTransparentRule(':lang', array('lang' => 'en'));
			$profile = new RouterTransparentRule('user/:id', array('area' => 'foo', 'action' => 'bar'));

			$chain = $lang->chain($profile);

			$res = $chain->match('en/user/1');

			$this->assertEquals('en', $res['lang']);
			$this->assertEquals('1', $res['id']);
		}

		public function testChainingAssembleWithStatic()
		{
			$chain = new RouterChainRule();

			$foo = new RouterHostnameRule(
				'www.example.com',
				array(
					'foo' => 'foo'
				)
			);

			$bar = new RouterStaticRule(
				'bar',
				array(
					'bar' => 'bar'
				)
			);

			$chain->
				chain($foo)->
				chain($bar);

			$request = $this->buildRequest('http://www.example.com/bar');
			$res = $chain->match($request);

			$this->assertType('array', $res);
			$this->assertRegexp('#[^a-z0-9]?www\.example\.com/bar$#i', $chain->assemble());
		}

		public function testChainingAssembleWithRegex()
		{
			$chain = new RouterChainRule();

			$foo = new RouterHostnameRule(
				'www.example.com',
				array(
					'foo' => 'foo'
				)
			);

			$bar = new RouterRegexpRule(
				'bar',
				array(
					'bar' => 'bar'
				),
				array(),
				'bar'
			);

			$chain->
				chain($foo)->
				chain($bar);

			$request = $this->buildRequest('http://www.example.com/bar');
			$res = $chain->match($request);

			$this->assertType('array', $res);
			$this->assertRegexp('#[^a-z0-9]?www\.example\.com/bar$#i', $chain->assemble());
		}

		public function testChainingReuse()
		{
			$foo = new RouterHostnameRule(
				'www.example.com',
				array(
					'foo' => 'foo'
				)
			);

			$profile = new RouterTransparentRule(
				'user/:id',
				array(
					'area' => 'prof'
				)
			);

			$article = new RouterTransparentRule(
				'article/:id',
				array(
					'area' => 'art',
					'action' => 'art'
				)
			);

			$profileChain = $foo->chain($profile);
			$articleChain = $foo->chain($article);

			$request = $this->buildRequest('http://www.example.com/user/1');
			$res = $profileChain->match($request);

			$this->assertType('array', $res);
			$this->assertEquals('prof', $res['area']);

			$request = $this->buildRequest('http://www.example.com/article/1');
			$res = $articleChain->match($request);

			$this->assertType('array', $res);
			$this->assertEquals('art', $res['area']);
			$this->assertEquals('art', $res['action']);
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
<?php
	/** $Id$ **/

	class RouterHostnameRuleTest extends PHPUnit_Framework_TestCase
	{
		public function setUp()
		{
			ServerVarUtils::unsetVars($_SERVER);
		}

		public function testCorrectStaticHostMatch()
		{
			$route = $this->buildStaticHostnameRule();

			$values = $route->match($this->buildRequest('www.example.com'));
			$this->assertEquals('ctrl', $values['area']);
		}

		public function testHostMatchWithPort()
		{
			$route = $this->buildStaticHostnameRule();

			$values = $route->match($this->buildRequest('www.example.com:666'));

			$this->assertEquals('ctrl', $values['area']);
		}

		public function testWrongStaticHostMatch()
		{
			$route = $this->buildStaticHostnameRule();

			$values = $route->match($this->buildRequest('foo.example.com'));
			$this->assertFalse($values);
		}

		public function testCorrectHostMatch()
		{
			$route = $this->buildHostnameRule();

			$values = $route->match($this->buildRequest('foo.example.com'));
			$this->assertEquals('ctrl', $values['area']);
		}

		public function testWrongHostMatch()
		{
			$route = $this->buildHostnameRule();

			$values = $route->match($this->buildRequest('www.example.com'));
			$this->assertFalse($values);
		}

		public function testAssembleStaticHost()
		{
			$route = $this->buildStaticHostnameRule();

			$this->assertRegexp(
				'/[^a-z0-9]?www\.example\.com$/i',
				$route->assemble()
			);
		}

		public function testAssembleHost()
		{
			$route = $this->buildHostnameRule();

			$this->assertRegexp(
				'/[^a-z0-9]?foo\.example\.com$/i',
				$route->assemble(
					array(
						'subdomain' => 'foo'
					)
				)
			);
		}

		public function testAssembleHostWithMissingParam()
		{
			$route = $this->buildHostnameRule();

			try {
				$route->assemble();
				$this->fail('An expected RouterException has not been raised');
			} catch (RouterException $expected) {
				$this->assertContains('subdomain is not specified', $expected->getMessage());
			}
		}

		public function testAssembleHostWithDefaultParam()
		{
			$route = $this->buildHostnameRuleWithDefault();

			$this->assertRegexp(
				'/[^a-z0-9]?bar\.example\.com$/i',
				$route->assemble()
			);
		}

		public function testHostGetDefault()
		{
			$route = $this->buildHostnameRuleWithDefault();

			$this->assertEquals('bar', $route->getDefault('subdomain'));
		}

		public function testHostGetNonExistentDefault()
		{
			$route = $this->buildHostnameRuleWithDefault();

			$this->assertEquals(null, $route->getDefault('blah'));
		}

		public function testHostGetDefaults()
		{
			$route    = $this->buildHostnameRuleWithDefault();
			$defaults = $route->getDefaults();

			$this->assertEquals('bar', $defaults['subdomain']);
		}

		public function testRouteWithHostname()
		{
			$_SERVER['HOST_NAME'] = 'www.example.com';

			$request = $this->buildRequest('www.example.com');

			$route = new RouterHostnameRule(
				'www.example.com',
				array(
					'area' => 'host-foo',
					'action' => 'host-bar'
				)
			);

			$values = $route->match($request);

			$this->assertEquals('host-foo', $values['area']);
			$this->assertEquals('host-bar', $values['action']);
		}

		/**
		* @return RouterHostnameRule
		**/
		protected function buildStaticHostnameRule()
		{
			$route = new RouterHostnameRule(
				'www.example.com',
				array(
					'area' => 'ctrl',
					'action' => 'act'
				)
			);

			return $route;
		}

		/**
		* @return RouterHostnameRule
		**/
		protected function buildHostnameRule()
		{
			$route = new RouterHostnameRule(
				':subdomain.example.com',
				array(
					'area' => 'ctrl',
					'action' => 'act'
				),
				array(
					'subdomain' => '(foo|bar)'
				)
			);

			return $route;
		}

		/**
		 * @return RouterHostnameRule
		**/
		protected function buildHostnameRuleWithDefault()
		{
			$route = new RouterHostnameRule(
				':subdomain.example.com',
				array(
					'area' => 'ctrl',
					'action' => 'act',
					'subdomain' => 'bar'
				),
				array(
					'subdomain' => '(foo|bar)'
				)
			);

			return $route;
		}

		/**
		 * @param string $host
		 * @return HttpRequest
		**/
		protected function buildRequest($host, $schema = 'http')
		{
			ServerVarUtils::build($_SERVER, $schema.'://'.$host);

			return
				HttpRequest::create()->
				setServer($_SERVER);
		}
	}
?>
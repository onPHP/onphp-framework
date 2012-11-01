<?php
	/* $Id$ */
	
	namespace Onphp\Test;

	class RouterHostnameRuleTest extends TestCase
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
			$this->assertEquals(array(), $values);
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
			$this->assertEquals(array(), $values);
		}
		
		public function testAssemblyStaticHost()
		{
			$route = $this->buildStaticHostnameRule();
			
			$this->assertRegexp(
				'/[^a-z0-9]?www\.example\.com$/i',
				$route->assembly()
			);
		}
		
		public function testAssemblyHost()
		{
			$route = $this->buildHostnameRule();
			
			$this->assertRegexp(
				'/[^a-z0-9]?foo\.example\.com$/i',
				
				$route->assembly(
					array(
						'subdomain' => 'foo'
					)
				)
			);
		}
		
		public function testAssemblyHostWithMissingParam()
		{
			$route = $this->buildHostnameRule();
			
			try {
				$route->assembly();
				$this->fail('An expected RouterException has not been raised');
			} catch (\Onphp\RouterException $expected) {
				$this->assertContains('subdomain is not specified', $expected->getMessage());
			}
		}
		
		public function testAssemblyHostWithDefaultParam()
		{
			$route = $this->buildHostnameRuleWithDefault();
			
			$this->assertRegexp(
				'/[^a-z0-9]?bar\.example\.com$/i',
				$route->assembly()
			);
		}
		
		public function testAssemblyHostWithDefaultParamInSecureScheme()
		{
			$route =
				$this->buildHostnameRuleWithDefault()->
				setScheme(\Onphp\RouterHostnameRule::SCHEME_HTTPS);
			
			$this->assertRegexp(
				'/^https\:\/\/bar\.example\.com$/i',
				$route->assembly()
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
			$route		= $this->buildHostnameRuleWithDefault();
			$defaults	= $route->getDefaults();
			
			$this->assertEquals('bar', $defaults['subdomain']);
		}
		
		public function testRouteWithHostname()
		{
			$_SERVER['HOST_NAME'] = 'www.example.com';
			
			$request = $this->buildRequest('www.example.com');
			
			$route =
				\Onphp\RouterHostnameRule::create(
					'www.example.com'
				)->
				setDefaults(
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
		* @return \Onphp\RouterHostnameRule
		**/
		protected function buildStaticHostnameRule()
		{
			$route =
				\Onphp\RouterHostnameRule::create(
					'www.example.com'
				)->
				setDefaults(
					array(
						'area' => 'ctrl',
						'action' => 'act'
					)
				);
			
			return $route;
		}
		
		/**
		* @return \Onphp\RouterHostnameRule
		**/
		protected function buildHostnameRule()
		{
			$route =
				\Onphp\RouterHostnameRule::create(
					':subdomain.example.com'
				)->
				setDefaults(
					array(
						'area' => 'ctrl',
						'action' => 'act'
					)
				)->
				setRequirements(
					array(
						'subdomain' => '(foo|bar)'
					)
				);
			
			return $route;
		}
		
		/**
		 * @return \Onphp\RouterHostnameRule
		**/
		protected function buildHostnameRuleWithDefault()
		{
			$route =
				\Onphp\RouterHostnameRule::create(
					':subdomain.example.com'
				)->
				setDefaults(
					array(
						'area' => 'ctrl',
						'action' => 'act',
						'subdomain' => 'bar'
					)
				)->
				setRequirements(
					array(
						'subdomain' => '(foo|bar)'
					)
				);
			
			return $route;
		}
		
		/**
		 * @param string $host
		 * @return \Onphp\HttpRequest
		**/
		protected function buildRequest($host, $schema = 'http')
		{
			ServerVarUtils::build($_SERVER, $schema.'://'.$host);
			
			return
				\Onphp\HttpRequest::create()->
				setServer($_SERVER);
		}
	}
?>
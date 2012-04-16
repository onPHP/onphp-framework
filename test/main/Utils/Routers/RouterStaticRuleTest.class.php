<?php
	/* $Id$ */
	
	class RouterStaticRuleTest extends TestCase
	{
		public function setUp()
		{
			ServerVarUtils::unsetVars($_SERVER);
		}
		
		public function testStaticMatch()
		{
			$route = new RouterStaticRule('users/all');
			$values = $route->match(
				$this->buildRequest('http://localhost/users/all')
			);
			
			$this->assertInternalType('array', $values);
		}
		
		public function testStaticMatchFailure()
		{
			$route = new RouterStaticRule('archive/2006');
			$values = $route->match(
				$this->buildRequest('http://localhost/users/all')
			);
			
			$this->assertSame(false, $values);
		}
		
		public function testStaticMatchWithDefaults()
		{
			$route =
				RouterStaticRule::create(
					'users/all'
				)->
				setDefaults(
					array(
						'area' => 'ctrl',
						'action' => 'act'
					)
				);
			
			$values = $route->match(
				$this->buildRequest('http://localhost/users/all')
			);
			
			$this->assertInternalType('array', $values);
			$this->assertSame('ctrl', $values['area']);
			$this->assertSame('act', $values['action']);
		}
		
		public function testStaticUTFMatch()
		{
			$route = new RouterStaticRule('żółć');
			$values = $route->match(
				$this->buildRequest('http://localhost/'.urlencode('żółć'))
			);
			
			$this->assertInternalType('array', $values);
		}
		
		public function testRootRoute()
		{
			$route = new RouterStaticRule('/');
			$values = $route->match($this->buildRequest('http://localhost/'));
			
			$this->assertSame(array(), $values);
		}
		
		public function testAssembly()
		{
			$route = new RouterStaticRule('/about');
			$url = $route->assembly();
			
			$this->assertSame('about', $url);
		}
		
		public function testGetDefaults()
		{
			$route =
				RouterStaticRule::create(
					'users/all'
				)->
				setDefaults(
					array(
						'area' => 'ctrl',
						'action' => 'act'
					)
				);
			
			$values = $route->getDefaults();
			
			$this->assertInternalType('array', $values);
			$this->assertSame('ctrl', $values['area']);
			$this->assertSame('act', $values['action']);
		}
		
		public function testGetDefault()
		{
			$route =
				RouterStaticRule::create(
					'users/all'
				)->
				setDefaults(
					array(
						'area' => 'ctrl',
						'action' => 'act'
					)
				);
			
			$this->assertSame('ctrl', $route->getDefault('area'));
			$this->assertSame(null, $route->getDefault('bogus'));
		}
		
		public function testGetInstance()
		{
			$route =
				RouterStaticRule::create(
					'users/all'
				)->
				setDefaults(
					array(
						'area' => 'ctrl'
					)
				);
			
			$this->assertInstanceOf('RouterStaticRule', $route);
			
			$values = $route->match(
				$this->buildRequest('http://localhost/users/all')
			);
			
			$this->assertSame('ctrl', $values['area']);
		}
		
		protected function buildRequest($url)
		{
			ServerVarUtils::build($_SERVER, $url);
			
			return
				HttpRequest::create()->
				setServer($_SERVER);
		}
	}
?>
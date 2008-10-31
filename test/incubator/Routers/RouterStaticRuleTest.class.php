<?php
	/** $Id$ **/

	class RouterStaticRuleTest extends PHPUnit_Framework_TestCase
	{
		public function setUp()
		{
			ServerVarUtils::unsetVars($_SERVER);
		}

		public function testStaticMatch()
		{
			$route = new RouterStaticRule('users/all');
			$values = $route->match($this->buildRequest('http://localhost/users/all'));

			$this->assertType('array', $values);
		}

		public function testStaticMatchFailure()
		{
			$route = new RouterStaticRule('archive/2006');
			$values = $route->match($this->buildRequest('http://localhost/users/all'));

			$this->assertSame(false, $values);
		}

		public function testStaticMatchWithDefaults()
		{
			$route = new RouterStaticRule(
				'users/all',
				array(
					'area' => 'ctrl',
					'action' => 'act'
				)
			);

			$values = $route->match($this->buildRequest('http://localhost/users/all'));

			$this->assertType('array', $values);
			$this->assertSame('ctrl', $values['area']);
			$this->assertSame('act', $values['action']);
		}

		public function testStaticUTFMatch()
		{
			$route = new RouterStaticRule('żółć');
			$values = $route->match($this->buildRequest('http://localhost/'.urlencode('żółć')));

			$this->assertType('array', $values);
		}

		public function testRootRoute()
		{
			$route = new RouterStaticRule('/');
			$values = $route->match($this->buildRequest('http://localhost/'));

			$this->assertSame(array(), $values);
		}

		public function testAssemble()
		{
			$route = new RouterStaticRule('/about');
			$url = $route->assemble();

			$this->assertSame('about', $url);
		}

		public function testGetDefaults()
		{
			$route = new RouterStaticRule(
				'users/all',
				array(
					'area' => 'ctrl',
					'action' => 'act'
				)
			);

			$values = $route->getDefaults();

			$this->assertType('array', $values);
			$this->assertSame('ctrl', $values['area']);
			$this->assertSame('act', $values['action']);
		}

		public function testGetDefault()
		{
			$route = new RouterStaticRule(
				'users/all',
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
			$route = RouterStaticRule::create(
				'users/all',
				array(
					'area' => 'ctrl'
				)
			);

			$this->assertType('RouterStaticRule', $route);

			$values = $route->match($this->buildRequest('http://localhost/users/all'));

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
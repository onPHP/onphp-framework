<?php
	/** $Id$ **/

	class RouterRegexpRuleTest extends PHPUnit_Framework_TestCase
	{
		public function setUp()
		{
			ServerVarUtils::unsetVars($_SERVER);
		}

		public function testStaticMatch()
		{
			$route = new RouterRegexpRule('users/all');
			$values = $route->match($this->buildRequest('http://localhost/users/all'));

			$this->assertSame(array(), $values);
		}

		public function testURLDecode()
		{
			$route = new RouterRegexpRule('żółć');
			$values = $route->match($this->buildRequest('http://localhost/'.urlencode('żółć')));

			$this->assertSame(array(), $values);
		}

		public function testStaticNoMatch()
		{
			$route = new RouterRegexpRule('users/a/martel');
			$values = $route->match($this->buildRequest('http://localhost/users/a'));

			$this->assertSame(false, $values);
		}

		public function testStaticMatchWithDefaults()
		{
			$route = new RouterRegexpRule(
				'users/all',
				array(
					'area' => 'ctrl'
				)
			);

			$values = $route->match($this->buildRequest('http://localhost/users/all'));

			$this->assertSame(1, count($values));
			$this->assertSame('ctrl', $values['area']);
		}

		public function testRootRoute()
		{
			$route = new RouterRegexpRule('');
			$values = $route->match($this->buildRequest('http://localhost/'));

			$this->assertSame(array(), $values);
		}

		public function testVariableMatch()
		{
			$route = new RouterRegexpRule('users/(.+)');
			$values = $route->match($this->buildRequest('http://localhost/users/martel'));

			$this->assertSame(1, count($values));
			$this->assertSame('martel', $values[1]);
		}

		public function testDoubleMatch()
		{
			$route = new RouterRegexpRule('users/(user_(\d+).html)');
			$values = $route->match($this->buildRequest('http://localhost/users/user_1354.html'));

			$this->assertSame(2, count($values));
			$this->assertSame('user_1354.html', $values[1]);
			$this->assertSame('1354', $values[2]);
		}

		public function testNegativeMatch()
		{
			$route = new RouterRegexpRule(
				'((?!admin|moderator).+)',
				array(
					'module' => 'index',
					'area' => 'index'
				),
				array(1 => 'action')
			);

			$values = $route->match($this->buildRequest('http://localhost/users'));

			$this->assertSame(3, count($values));
			$this->assertSame('index', $values['module']);
			$this->assertSame('index', $values['area']);
			$this->assertSame('users', $values['action']);
		}

		public function testNumericDefault()
		{
			$route = new RouterRegexpRule(
				'users/?(.+)?',
				array(
					1 => 'martel'
				)
			);

			$values = $route->match($this->buildRequest('http://localhost/users'));

			$this->assertSame(1, count($values));
			$this->assertSame('martel', $values[1]);
		}

		public function testVariableMatchWithNumericDefault()
		{
			$route = new RouterRegexpRule(
				'users/?(.+)?',
				array(
					1 => 'martel'
				)
			);

			$values = $route->match($this->buildRequest('http://localhost/users/vicki'));

			$this->assertSame(1, count($values));
			$this->assertSame('vicki', $values[1]);
		}

		public function testNamedVariableMatch()
		{
			$route = new RouterRegexpRule('users/(?P<username>.+)');
			$values = $route->match($this->buildRequest('http://localhost/users/martel'));

			$this->assertSame(1, count($values));
			$this->assertSame('martel', $values[1]);
		}

		public function testMappedVariableMatch()
		{
			$route = new RouterRegexpRule(
				'users/(.+)',
				null,
				array(1 => 'username')
			);

			$values = $route->match($this->buildRequest('http://localhost/users/martel'));

			$this->assertSame(1, count($values));
			$this->assertSame('martel', $values['username']);
		}

		public function testMappedVariableWithDefault()
		{
			$route = new RouterRegexpRule(
				'users(?:/(.+))?',
				array(
					'username' => 'martel'
				),
				array(
					1 => 'username'
				)
			);

			$values = $route->match($this->buildRequest('http://localhost/users'));

			$this->assertSame(1, count($values));
			$this->assertSame('martel', $values['username']);
		}

		public function testMappedVariableWithNamedSubpattern()
		{
			$route = new RouterRegexpRule(
				'users/(?P<name>.+)',
				null,
				array(
					1 => 'username'
				)
			);

			$values = $route->match($this->buildRequest('http://localhost/users/martel'));

			$this->assertSame(1, count($values));
			$this->assertSame('martel', $values['username']);
		}

		public function testOptionalVar()
		{
			$route = new RouterRegexpRule(
				'users/(\w+)/?(?:p/(\d+))?',
				null,
				array(
					1 => 'username',
					2 => 'page'
				)
			);

			$values = $route->match($this->buildRequest('http://localhost/users/martel/p/1'));

			$this->assertSame(2, count($values));
			$this->assertSame('martel', $values['username']);
			$this->assertSame('1', $values['page']);
		}

		public function testEmptyOptionalVar()
		{
			$route = new RouterRegexpRule(
				'users/(\w+)/?(?:p/(\d+))?',
				null,
				array(
					1 => 'username',
					2 => 'page'
				)
			);

			$values = $route->match($this->buildRequest('http://localhost/users/martel'));

			$this->assertSame(1, count($values));
			$this->assertSame('martel', $values['username']);
		}

		public function testMixedMap()
		{
			$route = new RouterRegexpRule(
				'users/(\w+)/?(?:p/(\d+))?',
				null,
				array(
					1 => 'username'
				)
			);

			$values = $route->match($this->buildRequest('http://localhost/users/martel/p/1'));

			$this->assertSame(2, count($values));
			$this->assertSame('martel', $values['username']);
			$this->assertSame('1', $values[2]);
		}

		public function testNumericDefaultWithMap()
		{
			$route = new RouterRegexpRule(
				'users/?(.+)?',
				array(
					1 => 'martel'
				),
				array(
					1 => 'username'
				)
			);

			$values = $route->match($this->buildRequest('http://localhost/users'));

			$this->assertSame(1, count($values));
			$this->assertSame('martel', $values['username']);
		}

		public function testMixedMapWithDefault()
		{
			$route = new RouterRegexpRule(
				'users/(\w+)/?(?:p/(\d+))?',
				array(
					2 => '1'
				),
				array(
					1 => 'username'
				)
			);

			$values = $route->match($this->buildRequest('http://localhost/users/martel/p/10'));

			$this->assertSame(2, count($values));
			$this->assertSame('martel', $values['username']);
			$this->assertSame('10', $values[2]);
		}

		public function testMixedMapWithDefaults2()
		{
			$route = new RouterRegexpRule(
				'users/?(\w+)?/?(?:p/(\d+))?',
				array(
					2 => '1',
					'username' => 'martel'
				),
				array(
					1 => 'username'
				)
			);

			$values = $route->match($this->buildRequest('http://localhost/users'));

			$this->assertSame(2, count($values));
			$this->assertSame('martel', $values['username']);
			$this->assertSame('1', $values[2]);
		}

		public function testOptionalVarWithMapAndDefault()
		{
			$route = new RouterRegexpRule(
				'users/(\w+)/?(?:p/(\d+))?',
				array(
					'page' => '1',
					'username' => 'martel'
				),
				array(
					1 => 'username',
					2 => 'page'
				)
			);

			$values = $route->match($this->buildRequest('http://localhost/users/martel'));

			$this->assertSame(2, count($values));
			$this->assertSame('martel', $values['username']);
			$this->assertSame('1', $values['page']);
		}

		public function testOptionalVarWithMapAndNumericDefault()
		{
			$route = new RouterRegexpRule(
				'users/(\w+)/?(?:p/(\d+))?',
				array(
					2 => '1'
				),
				array(
					2 => 'page'
				)
			);

			$values = $route->match($this->buildRequest('http://localhost/users/martel'));

			$this->assertSame(2, count($values));
			$this->assertSame('martel', $values[1]);
			$this->assertSame('1', $values['page']);
		}

		public function testMappedAndNumericDefault()
		{
			$route = new RouterRegexpRule(
				'users/?(\w+)?',
				array(
					1 => 'martel',
					'username' => 'vicki'
				),
				array(
					1 => 'username'
				)
			);

			$values = $route->match($this->buildRequest('http://localhost/users'));

			$this->assertSame(1, count($values));
			$this->assertSame('vicki', $values['username']);
		}

		public function testAssemble()
		{
			$route = new RouterRegexpRule(
				'users/(.+)',
				null,
				array(
					1 => 'username'
				),
				'users/%s'
			);

			$values = $route->match($this->buildRequest('http://localhost/users/martel'));

			$url = $route->assemble();
			$this->assertSame('users/martel', $url);
		}

		public function testAssembleWithDefault()
		{
			$route = new RouterRegexpRule(
				'users/?(.+)?',
				array(
					1 => 'martel'
				),
				null,
				'users/%s'
			);

			$values = $route->match($this->buildRequest('http://localhost/users'));

			$url = $route->assemble();
			$this->assertSame('users/martel', $url);
		}

		public function testAssembleWithMappedDefault()
		{
			$route = new RouterRegexpRule(
				'users/?(.+)?',
				array(
					'username' => 'martel'
				),
				array(
					1 => 'username'
				),
				'users/%s'
			);

			$values = $route->match($this->buildRequest('http://localhost/users'));

			$url = $route->assemble();
			$this->assertSame('users/martel', $url);
		}

		public function testAssembleWithData()
		{
			$route = new RouterRegexpRule(
				'users/(.+)',
				null,
				null,
				'users/%s'
			);

			$values = $route->match($this->buildRequest('http://localhost/users/martel'));

			$url = $route->assemble(array(1 => 'vicki'));
			$this->assertSame('users/vicki', $url);
		}

		public function testAssembleWithMappedVariable()
		{
			$route = new RouterRegexpRule(
				'users/(.+)',
				null,
				array(
					1 => 'username'
				),
				'users/%s'
			);

			$values = $route->match($this->buildRequest('http://localhost/users/martel'));

			$url = $route->assemble(
				array('username' => 'vicki')
			);

			$this->assertSame('users/vicki', $url);
		}

		public function testAssembleWithMappedVariableAndNumericKey()
		{
			$route = new RouterRegexpRule(
				'users/(.+)',
				null,
				array(
					1 => 'username'
				),
				'users/%s'
			);

			$values = $route->match($this->buildRequest('http://localhost/users/martel'));

			$url = $route->assemble(array(1 => 'vicki'));
			$this->assertSame('users/vicki', $url);
		}

		public function testAssembleWithoutMatch()
		{
			$route = new RouterRegexpRule(
				'users/(.+)',
				null,
				null,
				'users/%s'
			);

			try {
				$url = $route->assemble();
				$this->fail();
			} catch (Exception $e) {}
		}

		public function testAssembleWithDefaultWithoutMatch()
		{
			$route = new RouterRegexpRule(
				'users/?(.+)?',
				array(
					1 => 'martel'
				),
				null,
				'users/%s'
			);

			$url = $route->assemble();
			$this->assertSame('users/martel', $url);
		}

		public function testAssembleWithMappedDefaultWithoutMatch()
		{
			$route = new RouterRegexpRule(
				'users/?(.+)?',
				array(
					'username' => 'martel'
				),
				array(
					1 => 'username'
				),
				'users/%s'
			);

			$url = $route->assemble();
			$this->assertSame('users/martel', $url);
		}

		public function testAssembleWithDataWithoutMatch()
		{
			$route = new RouterRegexpRule(
				'users/(.+)',
				null,
				null,
				'users/%s'
			);

			$url = $route->assemble(array(1 => 'vicki'));
			$this->assertSame('users/vicki', $url);
		}

		public function testAssembleWithMappedVariableWithoutMatch()
		{
			$route = new RouterRegexpRule(
				'users/(.+)',
				null,
				array(
					1 => 'username'
				),
				'users/%s'
			);

			$url = $route->assemble(array('username' => 'vicki'));
			$this->assertSame('users/vicki', $url);
		}

		public function testAssemble2()
		{
			$route = new RouterRegexpRule(
				'(.+)\.([0-9]+)-([0-9]+)\.html',
				array(
					'module' => 'default',
					'area' => 'content.item',
					'action' => 'forward'
				),
				array(
					1 => 'name',
					2 => 'id',
					3 => 'class'
				),
				'%s.%s-%s.html'
			);

			$route->match($this->buildRequest('http://localhost/uml-explained-composition.72-3.html'));

			$url = $route->assemble();

			$this->assertSame('uml-explained-composition.72-3.html', $url);

			$url = $route->assemble(array('name' => 'post_name', 'id' => '12', 'class' => 5));

			$this->assertSame('post_name.12-5.html', $url);
		}

		public function testCreateInstance()
		{
			$route = RouterRegexpRule::create(
				'forum/(\d+)',
				array(
					'area' => 'ctrl'
				),
				'forum/%d'
			);

			$this->assertType('RouterRegexpRule', $route);

			$values = $route->match($this->buildRequest('http://localhost/forum/1'));

			$this->assertSame('ctrl', $values['area']);
		}

		public function testAssemblyOfRouteWithMergedMatchedParts()
		{
			$route = new RouterRegexpRule(
				'itemlist(?:/(\d+))?',
				array(
					'page' => 1
				),
				array(
					1 => 'page'
				),
				'itemlist/%d'
			);

			$this->assertEquals(
				array('page' => 1),
				$route->match($this->buildRequest('http://localhost/itemlist/'))
			);

			$this->assertEquals(
				'itemlist/1',
				$route->assemble()
			);

			$this->assertEquals(
				array('page' => 2),
				$route->match($this->buildRequest('http://localhost/itemlist/2'))
			);

			$this->assertEquals(
				'itemlist/2',
				$route->assemble()
			);

			$this->assertEquals(
				'itemlist/3',
				$route->assemble(
					array('page' => 3)
				)
			);

			$this->assertEquals(
				'itemlist/1',
				$route->assemble(
					array('page' => null)
				)
			);
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
<?php
	/* $Id$ */
	
	class RouterRegexpRuleTest extends TestCase
	{
		public function setUp()
		{
			ServerVarUtils::unsetVars($_SERVER);
		}
		
		public function testStaticMatch()
		{
			$route = new RouterRegexpRule('users/all');
			$values = $route->match(
				$this->buildRequest('http://localhost/users/all')
			);
			
			$this->assertSame(array(), $values);
		}
		
		public function testURLDecode()
		{
			$route = new RouterRegexpRule('żółć');
			$values = $route->match(
				$this->buildRequest('http://localhost/'.urlencode('żółć'))
			);
			
			$this->assertSame(array(), $values);
		}
		
		public function testStaticNoMatch()
		{
			$route = new RouterRegexpRule('users/a/martel');
			$values = $route->match(
				$this->buildRequest('http://localhost/users/a')
			);
			
			$this->assertSame(array(), $values);
		}
		
		public function testStaticMatchWithDefaults()
		{
			$route =
				RouterRegexpRule::create(
					'users/all'
				)->
				setDefaults(
					array(
						'area' => 'ctrl'
					)
				);
			
			$values = $route->match(
				$this->buildRequest('http://localhost/users/all')
			);
			
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
			$values = $route->match(
				$this->buildRequest('http://localhost/users/martel')
			);
			
			$this->assertSame(1, count($values));
			$this->assertSame('martel', $values[1]);
		}
		
		public function testDoubleMatch()
		{
			$route = new RouterRegexpRule('users/(user_(\d+).html)');
			$values = $route->match(
				$this->buildRequest('http://localhost/users/user_1354.html')
			);
			
			$this->assertSame(2, count($values));
			$this->assertSame('user_1354.html', $values[1]);
			$this->assertSame('1354', $values[2]);
		}
		
		public function testNegativeMatch()
		{
			$route =
				RouterRegexpRule::create(
					'((?!admin|moderator).+)'
				)->
				setDefaults(
					array(
						'module' => 'index',
						'area' => 'index'
					)
				)->
				setMap(
					array(
						1 => 'action'
					)
				);
			
			$values = $route->match(
				$this->buildRequest('http://localhost/users')
			);
			
			$this->assertSame(3, count($values));
			$this->assertSame('index', $values['module']);
			$this->assertSame('index', $values['area']);
			$this->assertSame('users', $values['action']);
		}
		
		public function testNumericDefault()
		{
			$route =
				RouterRegexpRule::create(
					'users/?(.+)?'
				)->
				setDefaults(
					array(
						1 => 'martel'
					)
				);
			
			$values = $route->match(
				$this->buildRequest('http://localhost/users')
			);
			
			$this->assertSame(1, count($values));
			$this->assertSame('martel', $values[1]);
		}
		
		public function testVariableMatchWithNumericDefault()
		{
			$route =
				RouterRegexpRule::create(
					'users/?(.+)?'
				)->
				setDefaults(
					array(
						1 => 'martel'
					)
				);
			
			$values = $route->match(
				$this->buildRequest('http://localhost/users/vicki')
			);
			
			$this->assertSame(1, count($values));
			$this->assertSame('vicki', $values[1]);
		}
		
		public function testNamedVariableMatch()
		{
			$route = new RouterRegexpRule('users/(?P<username>.+)');
			$values = $route->match(
				$this->buildRequest('http://localhost/users/martel')
			);
			
			$this->assertSame(1, count($values));
			$this->assertSame('martel', $values[1]);
		}
		
		public function testMappedVariableMatch()
		{
			$route =
				RouterRegexpRule::create(
					'users/(.+)'
				)->
				setMap(
					array(
						1 => 'username'
					)
				);
			
			$values = $route->match(
				$this->buildRequest('http://localhost/users/martel')
			);
			
			$this->assertSame(1, count($values));
			$this->assertSame('martel', $values['username']);
		}
		
		public function testMappedVariableWithDefault()
		{
			$route =
				RouterRegexpRule::create(
					'users(?:/(.+))?'
				)->
				setDefaults(
					array(
						'username' => 'martel'
					)
				)->
				setMap(
					array(
						1 => 'username'
					)
				);
			
			$values = $route->match(
				$this->buildRequest('http://localhost/users')
			);
			
			$this->assertSame(1, count($values));
			$this->assertSame('martel', $values['username']);
		}
		
		public function testMappedVariableWithNamedSubpattern()
		{
			$route =
				RouterRegexpRule::create(
					'users/(?P<name>.+)'
				)->
				setMap(
					array(
						1 => 'username'
					)
				);
			
			$values = $route->match(
				$this->buildRequest('http://localhost/users/martel')
			);
			
			$this->assertSame(1, count($values));
			$this->assertSame('martel', $values['username']);
		}
		
		public function testOptionalVar()
		{
			$route =
				RouterRegexpRule::create(
					'users/(\w+)/?(?:p/(\d+))?'
				)->
				setMap(
					array(
						1 => 'username',
						2 => 'page'
					)
				);
			
			$values = $route->match(
				$this->buildRequest('http://localhost/users/martel/p/1')
			);
			
			$this->assertSame(2, count($values));
			$this->assertSame('martel', $values['username']);
			$this->assertSame('1', $values['page']);
		}
		
		public function testEmptyOptionalVar()
		{
			$route =
				RouterRegexpRule::create(
					'users/(\w+)/?(?:p/(\d+))?'
				)->
				setMap(
					array(
						1 => 'username',
						2 => 'page'
					)
				);
			
			$values = $route->match(
				$this->buildRequest('http://localhost/users/martel')
			);
			
			$this->assertSame(1, count($values));
			$this->assertSame('martel', $values['username']);
		}
		
		public function testMixedMap()
		{
			$route =
				RouterRegexpRule::create(
					'users/(\w+)/?(?:p/(\d+))?'
				)->
				setMap(
					array(
						1 => 'username'
					)
				);
			
			$values = $route->match(
				$this->buildRequest('http://localhost/users/martel/p/1')
			);
			
			$this->assertSame(2, count($values));
			$this->assertSame('martel', $values['username']);
			$this->assertSame('1', $values[2]);
		}
		
		public function testNumericDefaultWithMap()
		{
			$route =
				RouterRegexpRule::create(
					'users/?(.+)?'
				)->
				setDefaults(
					array(
						1 => 'martel'
					)
				)->
				setMap(
					array(
						1 => 'username'
					)
				);
			
			$values = $route->match(
				$this->buildRequest('http://localhost/users')
			);
			
			$this->assertSame(1, count($values));
			$this->assertSame('martel', $values['username']);
		}
		
		public function testMixedMapWithDefault()
		{
			$route =
				RouterRegexpRule::create(
					'users/(\w+)/?(?:p/(\d+))?'
				)->
				setDefaults(
					array(
						2 => '1'
					)
				)->
				setMap(
					array(
						1 => 'username'
					)
				);
			
			$values = $route->match(
				$this->buildRequest('http://localhost/users/martel/p/10')
			);
			
			$this->assertSame(2, count($values));
			$this->assertSame('martel', $values['username']);
			$this->assertSame('10', $values[2]);
		}
		
		public function testMixedMapWithDefaults2()
		{
			$route =
				RouterRegexpRule::create(
					'users/?(\w+)?/?(?:p/(\d+))?'
				)->
				setDefaults(
					array(
						2 => '1',
						'username' => 'martel'
					)
				)->
				setMap(
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
			$route =
				RouterRegexpRule::create(
					'users/(\w+)/?(?:p/(\d+))?'
				)->
				setDefaults(
					array(
						'page' => '1',
						'username' => 'martel'
					)
				)->
				setMap(
					array(
						1 => 'username',
						2 => 'page'
					)
				);
			
			$values = $route->match(
				$this->buildRequest('http://localhost/users/martel')
			);
			
			$this->assertSame(2, count($values));
			$this->assertSame('martel', $values['username']);
			$this->assertSame('1', $values['page']);
		}
		
		public function testOptionalVarWithMapAndNumericDefault()
		{
			$route =
				RouterRegexpRule::create(
					'users/(\w+)/?(?:p/(\d+))?'
				)->
				setDefaults(
					array(
						2 => '1'
					)
				)->
				setMap(
					array(
						2 => 'page'
					)
				);
			
			$values = $route->match(
				$this->buildRequest('http://localhost/users/martel')
			);
			
			$this->assertSame(2, count($values));
			$this->assertSame('martel', $values[1]);
			$this->assertSame('1', $values['page']);
		}
		
		public function testMappedAndNumericDefault()
		{
			$route =
				RouterRegexpRule::create(
					'users/?(\w+)?'
				)->
				setDefaults(
					array(
						1 => 'martel',
						'username' => 'vicki'
					)
				)->
				setMap(
					array(
						1 => 'username'
					)
				);
			
			$values = $route->match(
				$this->buildRequest('http://localhost/users')
			);
			
			$this->assertSame(1, count($values));
			$this->assertSame('vicki', $values['username']);
		}
		
		public function testAssembly()
		{
			$route =
				RouterRegexpRule::create(
					'users/(.+)'
				)->
				setMap(
					array(
						1 => 'username'
					)
				)->
				setReverse(
					'users/%s'
				);
			
			$values = $route->match(
				$this->buildRequest('http://localhost/users/martel')
			);
			
			$url = $route->assembly();
			$this->assertSame('users/martel', $url);
		}
		
		public function testAssemblyWithDefault()
		{
			$route =
				RouterRegexpRule::create(
					'users/?(.+)?'
				)->
				setDefaults(
					array(
						1 => 'martel'
					)
				)->
				setReverse(
					'users/%s'
				);
			
			$values = $route->match(
				$this->buildRequest('http://localhost/users')
			);
			
			$url = $route->assembly();
			$this->assertSame('users/martel', $url);
		}
		
		public function testAssemblyWithMappedDefault()
		{
			$route =
				RouterRegexpRule::create(
					'users/?(.+)?'
				)->
				setDefaults(
					array(
						'username' => 'martel'
					)
				)->
				setMap(
					array(
						1 => 'username'
					)
				)->
				setReverse(
					'users/%s'
				);
			
			$values = $route->match(
				$this->buildRequest('http://localhost/users')
			);
			
			$url = $route->assembly();
			$this->assertSame('users/martel', $url);
		}
		
		public function testAssemblyWithData()
		{
			$route =
				RouterRegexpRule::create(
					'users/(.+)'
				)->
				setReverse(
					'users/%s'
				);
			
			$values = $route->match(
				$this->buildRequest('http://localhost/users/martel')
			);
			
			$url = $route->assembly(array(1 => 'vicki'));
			$this->assertSame('users/vicki', $url);
		}
		
		public function testAssemblyWithMappedVariable()
		{
			$route =
				RouterRegexpRule::create(
					'users/(.+)'
				)->
				setMap(
					array(
						1 => 'username'
					)
				)->
				setReverse(
					'users/%s'
				);
			
			$values = $route->match(
				$this->buildRequest('http://localhost/users/martel')
			);
			
			$url = $route->assembly(
				array('username' => 'vicki')
			);
			
			$this->assertSame('users/vicki', $url);
		}
		
		public function testAssemblyWithMappedVariableAndNumericKey()
		{
			$route =
				RouterRegexpRule::create(
					'users/(.+)'
				)->
				setMap(
					array(
						1 => 'username'
					)
				)->
				setReverse(
					'users/%s'
				);
			
			$values = $route->match(
				$this->buildRequest('http://localhost/users/martel')
			);
			
			$url = $route->assembly(array(1 => 'vicki'));
			$this->assertSame('users/vicki', $url);
		}
		
		public function testAssemblyWithoutMatch()
		{
			$route =
				RouterRegexpRule::create(
					'users/(.+)'
				)->
				setReverse(
					'users/%s'
				);
			
			try {
				$url = $route->assembly();
				$this->fail();
			} catch (BaseException $e) {/*_*/}
		}
		
		public function testAssemblyWithDefaultWithoutMatch()
		{
			$route =
				RouterRegexpRule::create(
					'users/?(.+)?'
				)->
				setDefaults(
					array(
						1 => 'martel'
					)
				)->
				setReverse(
					'users/%s'
				);
			
			$url = $route->assembly();
			$this->assertSame('users/martel', $url);
		}
		
		public function testAssemblyWithMappedDefaultWithoutMatch()
		{
			$route =
				RouterRegexpRule::create(
					'users/?(.+)?'
				)->
				setDefaults(
					array(
						'username' => 'martel'
					)
				)->
				setMap(
					array(
						1 => 'username'
					)
				)->
				setReverse(
					'users/%s'
				);
			
			$url = $route->assembly();
			$this->assertSame('users/martel', $url);
		}
		
		public function testAssemblyWithDataWithoutMatch()
		{
			$route =
				RouterRegexpRule::create(
					'users/(.+)'
				)->
				setReverse(
					'users/%s'
				);
			
			$url = $route->assembly(array(1 => 'vicki'));
			$this->assertSame('users/vicki', $url);
		}
		
		public function testAssemblyWithMappedVariableWithoutMatch()
		{
			$route =
				RouterRegexpRule::create(
					'users/(.+)'
				)->
				setMap(
					array(
						1 => 'username'
					)
				)->
				setReverse(
					'users/%s'
				);
			
			$url = $route->assembly(array('username' => 'vicki'));
			$this->assertSame('users/vicki', $url);
		}
		
		public function testAssembly2()
		{
			$route =
				RouterRegexpRule::create(
					'(.+)\.([0-9]+)-([0-9]+)\.html'
				)->
				setDefaults(
					array(
						'module' => 'default',
						'area' => 'content.item',
						'action' => 'forward'
					)
				)->
				setMap(
					array(
						1 => 'name',
						2 => 'id',
						3 => 'class'
					)
				)->
				setReverse(
					'%s.%s-%s.html'
				);
			
			$route->match(
				$this->buildRequest(
					'http://localhost/uml-explained-composition.72-3.html'
				)
			);
			
			$url = $route->assembly();
			
			$this->assertSame('uml-explained-composition.72-3.html', $url);
			
			$url = $route->assembly(
				array('name' => 'post_name', 'id' => '12', 'class' => 5)
			);
			
			$this->assertSame('post_name.12-5.html', $url);
		}
		
		public function testCreateInstance()
		{
			$route =
				RouterRegexpRule::create(
					'forum/(\d+)'
				)->
				setDefaults(
					array(
						'area' => 'ctrl'
					)
				)->
				setReverse(
					'forum/%d'
				);
			
			$this->assertType('RouterRegexpRule', $route);
			
			$values = $route->match($this->buildRequest('http://localhost/forum/1'));
			
			$this->assertSame('ctrl', $values['area']);
		}
		
		public function testAssemblyOfRouteWithMergedMatchedParts()
		{
			$route =
				RouterRegexpRule::create(
					'itemlist(?:/(\d+))?'
				)->
				setDefaults(
					array(
						'page' => 1
					)
				)->
				setMap(
					array(
						1 => 'page'
					)
				)->
				setReverse(
					'itemlist/%d'
				);
			
			$this->assertEquals(
				array('page' => 1),
				$route->match($this->buildRequest('http://localhost/itemlist/'))
			);
			
			$this->assertEquals(
				'itemlist/1',
				$route->assembly()
			);
			
			$this->assertEquals(
				array('page' => 2),
				$route->match($this->buildRequest('http://localhost/itemlist/2'))
			);
			
			$this->assertEquals(
				'itemlist/2',
				$route->assembly()
			);
			
			$this->assertEquals(
				'itemlist/3',
				$route->assembly(
					array('page' => 3)
				)
			);
			
			$this->assertEquals(
				'itemlist/1',
				$route->assembly(
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
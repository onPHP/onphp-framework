<?php
	/* $Id$ */
	
	class RouterTransparentRuleTest extends TestCase
	{
		public function setUp()
		{
			ServerVarUtils::unsetVars($_SERVER);
		}
		
		public function testStaticMatch()
		{
			$route = new RouterTransparentRule('users/all');
			$values = $route->match(
				$this->buildRequest('http://localhost/users/all')
			);
			
			$this->assertSame(array(), $values);
		}
		
		public function testURLDecode()
		{
			$route = new RouterTransparentRule('żółć');
			$values = $route->match(
				$this->buildRequest('http://localhost/'.urlencode('żółć'))
			);
			
			$this->assertSame(array(), $values);
		}
		
		public function testStaticPathShorterThanParts()
		{
			$route = new RouterTransparentRule('users/a/martel');
			$values = $route->match(
				$this->buildRequest('http://localhost/users/a')
			);
			
			$this->assertSame(false, $values);
		}
		
		public function testStaticPathLongerThanParts()
		{
			$route = new RouterTransparentRule('users/a');
			$values = $route->match(
				$this->buildRequest('http://localhost/users/a/martel')
			);
			
			$this->assertEquals(false, $values);
		}
		
		public function testStaticMatchWithDefaults()
		{
			$route = 
				RouterTransparentRule::create(
					'users/all'
				)->
				setDefaults(				
					array(
						'area' => 'ctrl',
					)
				);
			
			$values = $route->match(
				$this->buildRequest('http://localhost/users/all')
			);
			
			$this->assertEquals('ctrl', $values['area']);
		}
		
		public function testNotMatched()
		{
			$route = new RouterTransparentRule('users/all');
			$values = $route->match(
				$this->buildRequest('http://localhost/users/martel')
			);
			
			$this->assertEquals(false, $values);
		}
		
		public function testNotMatchedWithVariablesAndDefaults()
		{
			$route = 
				RouterTransparentRule::create(
					':area/:action'
				)->
				setDefaults(
					array(
						'area' => 'index',
						'action' => 'index',
					)
				);
			
			$values = $route->match(
				$this->buildRequest('http://localhost/archive/action/bogus')
			);
			
			$this->assertEquals(false, $values);
		}
		
		public function testNotMatchedWithVariablesAndStatic()
		{
			$route = new RouterTransparentRule('archive/:year/:month');
			$values = $route->match(
				$this->buildRequest('http://localhost/ctrl/act/2000')
			);
			
			$this->assertEquals(false, $values);
		}
		
		public function testStaticMatchWithWildcard()
		{
			$route = 
				RouterTransparentRule::create(
					'news/view/*'
				)->
				setDefaults(				
					array(
						'area' => 'news',
						'action' => 'view',
					)
				);
			
			$values = $route->match(
				$this->buildRequest(
					'http://localhost/news/view/show/all/year/2000/empty'
				)
			);
			
			$this->assertEquals('news', $values['area']);
			$this->assertEquals('view', $values['action']);
			$this->assertEquals('all', $values['show']);
			$this->assertEquals('2000', $values['year']);
			$this->assertEquals(null, $values['empty']);
		}
		
		public function testWildcardWithUTF()
		{
			$route = 
				RouterTransparentRule::create(
					'news/*'
				)->
				setDefaults(				
					array(
						'area' => 'news',
						'action' => 'view',
					)
				);
			
			$values = $route->match(
				$this->buildRequest(
					'http://localhost/news/klucz/'
					.urlencode('wartość')
					.'/'.urlencode('wskaźnik')
					.'/'.urlencode('wartość')
				)
			);
			
			$this->assertEquals('news', $values['area']);
			$this->assertEquals('view', $values['action']);
			$this->assertEquals('wartość', $values['klucz']);
			$this->assertEquals('wartość', $values['wskaźnik']);
		}
		
		public function testWildcardURLDecode()
		{
			$route = 
				RouterTransparentRule::create(
					'news/*'
				)->
				setDefaults(		
					array(
						'area' => 'news',
						'action' => 'view',
					)
				);
			
			$values = $route->match(
				$this->buildRequest(
					'http://localhost/news/wska%C5%BAnik/warto%C5%9B%C4%87'
				)
			);
			
			$this->assertEquals('news', $values['area']);
			$this->assertEquals('view', $values['action']);
			$this->assertEquals('wartość', $values['wskaźnik']);
		}
		
		public function testVariableValues()
		{
			$route = new RouterTransparentRule(':area/:action/:year');
			
			$values = $route->match(
				$this->buildRequest('http://localhost/ctrl/act/2000')
			);
			
			$this->assertEquals('ctrl', $values['area']);
			$this->assertEquals('act', $values['action']);
			$this->assertEquals('2000', $values['year']);
		}
		
		public function testVariableUTFValues()
		{
			$route = new RouterTransparentRule('test/:param');
			$values = $route->match(
				$this->buildRequest(
					'http://localhost/test/'.urlencode('aä')
				)
			);
			
			$this->assertEquals('aä', $values['param']);
		}
		
		public function testOneVariableValue()
		{
			$route = 
				RouterTransparentRule::create(
					':action'
				)->
				setDefaults(
					array(
						'area' => 'ctrl',
						'action' => 'action'
					)
				);
			
			$values = $route->match(
				$this->buildRequest('http://localhost/act')
			);
			
			$this->assertEquals('ctrl', $values['area']);
			$this->assertEquals('act', $values['action']);
		}
		
		public function testVariablesWithDefault()
		{
			$route = 
				RouterTransparentRule::create(
					':area/:action/:year'
				)->
				setDefaults(				
					array(
						'year' => '2006'
					)
				);
			
			$values = $route->match(
				$this->buildRequest('http://localhost/ctrl/act')
			);
			
			$this->assertEquals('ctrl', $values['area']);
			$this->assertEquals('act', $values['action']);
			$this->assertEquals('2006', $values['year']);
		}
		
		public function testVariablesWithNullDefault()
		{
			$route = 
				RouterTransparentRule::create(
					':area/:action/:year'
				)->
				setDefaults(			
					array(
						'year' => null
					)
				);
			
			$values = $route->match(
				$this->buildRequest('http://localhost/ctrl/act')
			);
			
			$this->assertEquals('ctrl', $values['area']);
			$this->assertEquals('act', $values['action']);
			$this->assertNull($values['year']);
		}
		
		public function testVariablesWithDefaultAndValue()
		{
			$route = 
				RouterTransparentRule::create(
					':area/:action/:year'
				)->
				setDefaults(
					array(
						'year' => '2006'
					)
				);
			
			$values = $route->match(
				$this->buildRequest('http://localhost/ctrl/act/2000')
			);
			
			$this->assertEquals('ctrl', $values['area']);
			$this->assertEquals('act', $values['action']);
			$this->assertEquals('2000', $values['year']);
		}
		
		public function testVariablesWithRequirementAndValue()
		{
			$route = 
				RouterTransparentRule::create(
					':area/:action/:year'
				)->
				setRequirements(
					array(
						'year' => '\d+'
					)
				);
			
			$values = $route->match(
				$this->buildRequest('http://localhost/ctrl/act/2000')
			);
			
			$this->assertEquals('ctrl', $values['area']);
			$this->assertEquals('act', $values['action']);
			$this->assertEquals('2000', $values['year']);
		}
		
		public function testVariablesWithRequirementAndIncorrectValue()
		{
			$route = 
				RouterTransparentRule::create(
					':area/:action/:year'
				)->
				setRequirements(				
					array(
						'year' => '\d+'
					)
				);
			
			$values = $route->match(
				$this->buildRequest('http://localhost/ctrl/act/2000t')
			);
			
			$this->assertEquals(false, $values);
		}
		
		public function testVariablesWithDefaultAndRequirement()
		{
			$route = 
				RouterTransparentRule::create(
					':area/:action/:year'
				)->
				setDefaults(				
					array(
						'year' => '2006'
					)
				)->
				setRequirements(				
					array(
						'year' => '\d+'
					)
				);
			
			$values = $route->match(
				$this->buildRequest('http://localhost/ctrl/act/2000')
			);
			
			$this->assertEquals('ctrl', $values['area']);
			$this->assertEquals('act', $values['action']);
			$this->assertEquals('2000', $values['year']);
		}
		
		public function testVariablesWithDefaultAndRequirementAndIncorrectValue()
		{
			$route = 
				RouterTransparentRule::create(
					':area/:action/:year'	
				)->
				setDefaults(
					array(
						'year' => '2006'
					)
				)->
				setRequirements(				
					array(
						'year' => '\d+'
					)
				);
			
			$values = $route->match(
				$this->buildRequest('http://localhost/ctrl/act/2000t')
			);
			
			$this->assertEquals(false, $values);
		}
		
		public function testVariablesWithDefaultAndRequirementAndWithoutValue()
		{
			$route = 
				RouterTransparentRule::create(
					':area/:action/:year'
				)->
				setDefaults(				
					array(
						'year' => '2006'
					)
				)->
				setRequirements(		
					array(
						'year' => '\d+'
					)
				);
			
			$values = $route->match(
				$this->buildRequest('http://localhost/ctrl/act')
			);
			
			$this->assertEquals('ctrl', $values['area']);
			$this->assertEquals('act', $values['action']);
			$this->assertEquals('2006', $values['year']);
		}
		
		public function testVariablesWithWildcardAndNumericKey()
		{
			$route = new RouterTransparentRule(':area/:action/:next/*');
			$values = $route->match(
				$this->buildRequest(
					'http://localhost/c/a/next/2000/show/all/sort/name'
				)
			);
			
			$this->assertEquals('c', $values['area']);
			$this->assertEquals('a', $values['action']);
			$this->assertEquals('next', $values['next']);
			$this->assertTrue(array_key_exists('2000', $values));
		}
		
		public function testRootRoute()
		{
			$route = new RouterTransparentRule('/');
			$values = $route->match($this->buildRequest('http://localhost/'));
			
			$this->assertEquals(array(), $values);
		}
		
		public function testAssemble()
		{
			$route = new RouterTransparentRule('authors/:name');
			$url = $route->assemble(array('name' => 'martel'));
			
			$this->assertEquals('authors/martel', $url);
		}
		
		public function testAssembleWithoutValue()
		{
			$route = new RouterTransparentRule('authors/:name');
			
			try {
				$url = $route->assemble();
			} catch (BaseException $e) {
				return true;
			}
			
			$this->fail();
		}
		
		public function testAssembleWithDefault()
		{
			$route = 
				RouterTransparentRule::create(
					'authors/:name'
				)->
				setDefaults(				
					array(
						'name' => 'martel'
					)					
				);
			
			$url = $route->assemble();
			
			$this->assertEquals('authors', $url);
		}
		
		public function testAssembleWithDefaultAndValue()
		{
			$route = 
				RouterTransparentRule::create(
					'authors/:name'
				)->
				setDefaults(				
					array(
						'name' => 'martel'
					)
				);
			
			$url = $route->assemble(array('name' => 'mike'));
			
			$this->assertEquals('authors/mike', $url);
		}
		
		public function testAssembleWithWildcardMap()
		{
			$route = new RouterTransparentRule('authors/:name/*');
			$url = $route->assemble(array('name' => 'martel'));
			
			$this->assertEquals('authors/martel', $url);
		}
		
		public function testAssembleWithReset()
		{
			$route = 
				RouterTransparentRule::create(
					'archive/:year/*'
				)->
				setDefaults(
					array(
						'area' => 'archive',
						'action' => 'show'
					)
				);
			
			$values = $route->match(
				$this->buildRequest(
					'http://localhost/archive/2006/show/all/sort/name'
				)
			);
			
			$url = $route->assemble(array('year' => '2005'), true);
			
			$this->assertEquals('archive/2005', $url);
		}
		
		public function testAssembleWithReset2()
		{
			$route = 
				RouterTransparentRule::create(
					':area/:action/*'
				)->
				setDefaults(				
					array(
						'area' => 'archive',
						'action' => 'show'
					)				
				);
			
			$values = $route->match(
				$this->buildRequest('http://localhost/users/list')
			);
			
			$url = $route->assemble(array(), true);
			
			$this->assertEquals('', $url);
		}
		
		public function testAssembleWithReset3()
		{
			$route = 
				RouterTransparentRule::create(
					'archive/:year/*'
				)->
				setDefaults(
					array(
						'area' => 'archive',
						'action' => 'show',
						'year' => 2005
					)
				);
			
			$values = $route->match(
				$this->buildRequest(
					'http://localhost/archive/2006/show/all/sort/name'
				)
			);
			
			$url = $route->assemble(array(), true);
			
			$this->assertEquals('archive', $url);
		}
		
		public function testAssembleWithReset4()
		{
			$route = 
				RouterTransparentRule::create(
					':area/:action/*'
				)->
				setDefaults(				
					array(
						'area' => 'archive',
						'action' => 'show'
					)
				);
			
			$values = $route->match(
				$this->buildRequest('http://localhost/users/list')
			);
			
			$url = $route->assemble(array('action' => 'display'), true);
			
			$this->assertEquals('archive/display', $url);
		}
		
		public function testAssembleWithReset5()
		{
			$route = 
				RouterTransparentRule::create(
					'*'
				)->
				setDefaults(				
					array(
						'area' => 'index',
						'action' => 'index'
					)
				);
			
			$values = $route->match(
				$this->buildRequest(
					'http://localhost/key1/value1/key2/value2'
				)
			);
			
			$url = $route->assemble(array('key1' => 'newvalue'), true);
			
			$this->assertEquals('key1/newvalue', $url);
		}
		
		public function testAssembleWithWildcardAndAdditionalParameters()
		{
			$route = new RouterTransparentRule('authors/:name/*');
			
			$url = $route->assemble(
				array(
					'name' => 'martel',
					'var' => 'value'
				)
			);
			
			$this->assertEquals('authors/martel/var/value', $url);
		}
		
		public function testAssembleWithUrlVariablesReuse()
		{
			$route = new RouterTransparentRule('archives/:year/:month');
			
			$values = $route->match(
				$this->buildRequest('http://localhost/archives/2006/07')
			);
			
			$this->assertType('array', $values);
			
			$url = $route->assemble(array('month' => '03'));
			
			$this->assertEquals('archives/2006/03', $url);
		}
		
		public function testWildcardUrlVariablesOverwriting()
		{
			$route = 
				RouterTransparentRule::create(
					'archives/:year/:month/*'
				)->
				setDefaults(				
					array(
						'area' => 'archive'
					)
				);
			
			$values = $route->match(
				$this->buildRequest(
					'http://localhost/archives/2006/07/controller/test/year/10000/sort/author'
				)
			);
			
			$this->assertType('array', $values);
			
			$this->assertEquals('archive', $values['area']);
			$this->assertEquals('2006', $values['year']);
			$this->assertEquals('07', $values['month']);
			$this->assertEquals('author', $values['sort']);
		}
		
		public function testGetDefaults()
		{
			$route = 
				RouterTransparentRule::create(
					'users/all'
				)->
				setDefaults(
					array(
						'area' => 'ctrl',
						'action' => 'act'
					)
				);
			
			$values = $route->getDefaults();
			
			$this->assertType('array', $values);
			$this->assertEquals('ctrl', $values['area']);
			$this->assertEquals('act', $values['action']);
		}
		
		public function testGetDefault()
		{
			$route = 
				RouterTransparentRule::create(
					'users/all'
				)->
				setDefaults(				
					array(
						'area' => 'ctrl',
						'action' => 'act'
					)
				);
			
			$this->assertEquals('ctrl', $route->getDefault('area'));
			$this->assertEquals(null, $route->getDefault('bogus'));
		}
		
		public function testCreateInstance()
		{
			$routeConf = array(
				'route' => 'users/all',
				
				'defaults' => array(
					'area' => 'ctrl'
				)
			);
			
			$route = 
				RouterTransparentRule::create(
					$routeConf['route']
				)->
				setDefaults(
					$routeConf['defaults']
				);
			
			$this->assertType('RouterTransparentRule', $route);
			
			$values = $route->match(
				$this->buildRequest('http://localhost/users/all')
			);
			
			$this->assertEquals('ctrl', $values['area']);
		}
		
		public function testAssembleResetDefaults()
		{
			$route = 
				RouterTransparentRule::create(
					':area/:action/*'
				)->
				setDefaults(				
					array(
						'area' => 'index',
						'action' => 'index'
					)
				);
			
			$values = $route->match(
				$this->buildRequest('http://localhost/news/view/id/3')
			);
			
			$url = $route->assemble(array('area' => null));
			$this->assertEquals('index/view/id/3', $url);
			
			$url = $route->assemble(array('action' => null));
			$this->assertEquals('news/index/id/3', $url);
			
			$url = $route->assemble(array('action' => null, 'id' => null));
			$this->assertEquals('news', $url);
		}
		
		public function testAssembleWithRemovedDefaults()
		{
			$route = 
				RouterTransparentRule::create(
					':area/:action/*'
				)->
				setDefaults(
					array(
						'area' => 'index',
						'action' => 'index'
					)
				);			
			
			$url = $route->assemble(array('id' => 3));
			$this->assertEquals('index/index/id/3', $url);
			
			$url = $route->assemble(array('action' => 'test'));
			$this->assertEquals('index/test', $url);
			
			$url = $route->assemble(array('action' => 'test', 'id' => 3));
			$this->assertEquals('index/test/id/3', $url);
			
			$url = $route->assemble(array('area' => 'test'));
			$this->assertEquals('test', $url);
			
			$url = $route->assemble(array('area' => 'test', 'action' => 'test'));
			$this->assertEquals('test/test', $url);
			
			$url = $route->assemble(array('area' => 'test', 'id' => 3));
			$this->assertEquals('test/index/id/3', $url);
			
			$url = $route->assemble(array());
			$this->assertEquals('', $url);
			
			$route->match($this->buildRequest('http://localhost/ctrl'));
			
			$url = $route->assemble(array('id' => 3));
			$this->assertEquals('ctrl/index/id/3', $url);
			
			$url = $route->assemble(array('action' => 'test'));
			$this->assertEquals('ctrl/test', $url);
			
			$url = $route->assemble();
			$this->assertEquals('ctrl', $url);
			
			$route->match($this->buildRequest('http://localhost/index'));
			
			$url = $route->assemble();
			$this->assertEquals('', $url);
		}
		
		public function testAssembleWithAction()
		{
			$route = 
				RouterTransparentRule::create(
					'families/:action/*'
				)->
				setDefaults(				
					array(
						'module' => 'default',
						'area' => 'categories',
						'action' => 'index'
					)
				);
			
			$this->assertEquals('families', $route->assemble());
			
			$values = $route->match(
				$this->buildRequest('http://localhost/families/edit/id/4')
			);
			
			$this->assertType('array', $values);
			
			$this->assertEquals('families/edit/id/4', $route->assemble());
		}
		
		public function testAssemlingWithDefaultValueAndParams()
		{
			$router = 
				RouterTransparentRule::create(
					'contest/:contest/*'
				)->
				setDefaults(
					array(
						'area' => 'contestController'
					)
				)->
				setRequirements(				
					array(
						'contest' => '\d+'
					)
				);
			
			$this->assertEquals(
				'contest/123/param/435',
				
				$router->assemble(
					array(
						'area' => 'contestController',
						'contest' => 123,
						'param' => 435,
					),
					
					'contest'
				)
			);
		}
		
		public function testEncode()
		{
			$route = 
				RouterTransparentRule::create(
					':area/:action/*'
				)->
				setDefaults(				
					array(
						'area' => 'index',
						'action' => 'index'
					)
				);
			
			$url = $route->assemble(
				array('area' => 'My Controller'), false, true
			);
			$this->assertEquals('My+Controller', $url);
			
			$url = $route->assemble(
				array('area' => 'My Controller'), false, false
			);
			$this->assertEquals('My Controller', $url);
			
			$token = $route->match(
				$this->buildRequest(
					'http://localhost/en/foo/id/'.urlencode('My Value')
				)
			);
			
			$url = $route->assemble(array(), false, true);
			$this->assertEquals('en/foo/id/My+Value', $url);
			
			$url = $route->assemble(
				array('id' => 'My Other Value'), false, true
			);
			$this->assertEquals('en/foo/id/My+Other+Value', $url);
			
			$route = 
				RouterTransparentRule::create(
					':area/*'
				)->
				setDefaults(
					array(
						'area' => 'My Controller'
					)
				);
			
			$url = $route->assemble(array('id' => 1), false, true);
			$this->assertEquals('My+Controller/id/1', $url);
		}

		public function testPartialMatch()
		{
			$this->markTestSkipped('Route features not ready');
			
			$route = 
				RouterTransparentRule::create(
					':lang/:temp'
				)->
				setDefaults(
					array(
						'lang' => 'pl'
					)
				)->
				setRequirements(				
					array(
						'temp' => '\d+'
					)
				);
			
			$values = $route->match(
				$this->buildRequest(
					'http://localhost/en/tmp/ctrl/action/id/1'
				)
			);
			
			$this->assertFalse($values);
			
			$route = 
				RouterTransparentRule::create(
					':lang/:temp'
				)->
				setDefaults(				
					array(
						'lang' => 'pl'
					)
				);
			
			$values = $route->match(
				$this->buildRequest(
					'http://localhost/en/tmp/ctrl/action/id/1'
				)
			);
			
			$this->assertType('array', $values);
			$this->assertEquals('en', $values['lang']);
			$this->assertEquals('tmp', $values['temp']);
			$this->assertEquals(6, $values[null]);
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
<?php
	/* $Id$ */
	
	namespace Onphp\Test;

	class RouterTransparentRuleTest extends TestCase
	{
		public function setUp()
		{
			ServerVarUtils::unsetVars($_SERVER);
		}
		
		public function testStaticMatch()
		{
			$route = new \Onphp\RouterTransparentRule('users/all');
			$values = $route->match(
				$this->buildRequest('http://localhost/users/all')
			);
			
			$this->assertSame(array(), $values);
		}
		
		public function testURLDecode()
		{
			$route = new \Onphp\RouterTransparentRule('żółć');
			$values = $route->match(
				$this->buildRequest('http://localhost/'.urlencode('żółć'))
			);
			
			$this->assertSame(array(), $values);
		}
		
		public function testStaticPathShorterThanParts()
		{
			$route = new \Onphp\RouterTransparentRule('users/a/martel');
			$values = $route->match(
				$this->buildRequest('http://localhost/users/a')
			);
			
			$this->assertSame(array(), $values);
		}
		
		public function testStaticPathLongerThanParts()
		{
			$route = new \Onphp\RouterTransparentRule('users/a');
			$values = $route->match(
				$this->buildRequest('http://localhost/users/a/martel')
			);
			
			$this->assertEquals(array(), $values);
		}
		
		public function testStaticMatchWithDefaults()
		{
			$route =
				\Onphp\RouterTransparentRule::create(
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
			$route = new \Onphp\RouterTransparentRule('users/all');
			$values = $route->match(
				$this->buildRequest('http://localhost/users/martel')
			);
			
			$this->assertEquals(array(), $values);
		}
		
		public function testNotMatchedWithVariablesAndDefaults()
		{
			$route =
				\Onphp\RouterTransparentRule::create(
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
			
			$this->assertEquals(array(), $values);
		}
		
		public function testNotMatchedWithVariablesAndStatic()
		{
			$route = new \Onphp\RouterTransparentRule('archive/:year/:month');
			$values = $route->match(
				$this->buildRequest('http://localhost/ctrl/act/2000')
			);
			
			$this->assertEquals(array(), $values);
		}
		
		public function testStaticMatchWithWildcard()
		{
			$route =
				\Onphp\RouterTransparentRule::create(
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
				\Onphp\RouterTransparentRule::create(
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
				\Onphp\RouterTransparentRule::create(
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
			$route = new \Onphp\RouterTransparentRule(':area/:action/:year');
			
			$values = $route->match(
				$this->buildRequest('http://localhost/ctrl/act/2000')
			);
			
			$this->assertEquals('ctrl', $values['area']);
			$this->assertEquals('act', $values['action']);
			$this->assertEquals('2000', $values['year']);
		}
		
		public function testVariableUTFValues()
		{
			$route = new \Onphp\RouterTransparentRule('test/:param');
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
				\Onphp\RouterTransparentRule::create(
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
				\Onphp\RouterTransparentRule::create(
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
				\Onphp\RouterTransparentRule::create(
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
				\Onphp\RouterTransparentRule::create(
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
				\Onphp\RouterTransparentRule::create(
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
				\Onphp\RouterTransparentRule::create(
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
			
			$this->assertEquals(array(), $values);
		}
		
		public function testVariablesWithDefaultAndRequirement()
		{
			$route =
				\Onphp\RouterTransparentRule::create(
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
				\Onphp\RouterTransparentRule::create(
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
			
			$this->assertEquals(array(), $values);
		}
		
		public function testVariablesWithDefaultAndRequirementAndWithoutValue()
		{
			$route =
				\Onphp\RouterTransparentRule::create(
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
			$route = new \Onphp\RouterTransparentRule(':area/:action/:next/*');
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
			$route = new \Onphp\RouterTransparentRule('/');
			$values = $route->match($this->buildRequest('http://localhost/'));
			
			$this->assertEquals(array(), $values);
		}
		
		public function testAssembly()
		{
			$route = new \Onphp\RouterTransparentRule('authors/:name');
			$url = $route->assembly(array('name' => 'martel'));
			
			$this->assertEquals('authors/martel', $url);
		}
		
		public function testAssemblyWithoutValue()
		{
			$route = new \Onphp\RouterTransparentRule('authors/:name');
			
			try {
				$url = $route->assembly();
			} catch (\Onphp\BaseException $e) {
				return true;
			}
			
			$this->fail();
		}
		
		public function testAssemblyWithDefault()
		{
			$route =
				\Onphp\RouterTransparentRule::create(
					'authors/:name'
				)->
				setDefaults(
					array(
						'name' => 'martel'
					)
				);
			
			$url = $route->assembly();
			
			$this->assertEquals('authors', $url);
		}
		
		public function testAssemblyWithDefaultAndValue()
		{
			$route =
				\Onphp\RouterTransparentRule::create(
					'authors/:name'
				)->
				setDefaults(
					array(
						'name' => 'martel'
					)
				);
			
			$url = $route->assembly(array('name' => 'mike'));
			
			$this->assertEquals('authors/mike', $url);
		}
		
		public function testAssemblyWithWildcardMap()
		{
			$route = new \Onphp\RouterTransparentRule('authors/:name/*');
			$url = $route->assembly(array('name' => 'martel'));
			
			$this->assertEquals('authors/martel', $url);
		}
		
		public function testAssemblyWithReset()
		{
			$route =
				\Onphp\RouterTransparentRule::create(
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
			
			$url = $route->assembly(array('year' => '2005'), true);
			
			$this->assertEquals('archive/2005', $url);
		}
		
		public function testAssemblyWithReset2()
		{
			$route =
				\Onphp\RouterTransparentRule::create(
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
			
			$url = $route->assembly(array(), true);
			
			$this->assertEquals('', $url);
		}
		
		public function testAssemblyWithReset3()
		{
			$route =
				\Onphp\RouterTransparentRule::create(
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
			
			$url = $route->assembly(array(), true);
			
			$this->assertEquals('archive', $url);
		}
		
		public function testAssemblyWithReset4()
		{
			$route =
				\Onphp\RouterTransparentRule::create(
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
			
			$url = $route->assembly(array('action' => 'display'), true);
			
			$this->assertEquals('archive/display', $url);
		}
		
		public function testAssemblyWithReset5()
		{
			$route =
				\Onphp\RouterTransparentRule::create(
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
			
			$url = $route->assembly(array('key1' => 'newvalue'), true);
			
			$this->assertEquals('key1/newvalue', $url);
		}
		
		public function testAssemblyWithWildcardAndAdditionalParameters()
		{
			$route = new \Onphp\RouterTransparentRule('authors/:name/*');
			
			$url = $route->assembly(
				array(
					'name' => 'martel',
					'var' => 'value'
				)
			);
			
			$this->assertEquals('authors/martel/var/value', $url);
		}
		
		public function testAssemblyWithUrlVariablesReuse()
		{
			$route = new \Onphp\RouterTransparentRule('archives/:year/:month');
			
			$values = $route->match(
				$this->buildRequest('http://localhost/archives/2006/07')
			);
			
			$this->assertInternalType('array', $values);
			
			$url = $route->assembly(array('month' => '03'));
			
			$this->assertEquals('archives/2006/03', $url);
		}
		
		public function testWildcardUrlVariablesOverwriting()
		{
			$route =
				\Onphp\RouterTransparentRule::create(
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
			
			$this->assertInternalType('array', $values);
			
			$this->assertEquals('archive', $values['area']);
			$this->assertEquals('2006', $values['year']);
			$this->assertEquals('07', $values['month']);
			$this->assertEquals('author', $values['sort']);
		}
		
		public function testGetDefaults()
		{
			$route =
				\Onphp\RouterTransparentRule::create(
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
			$this->assertEquals('ctrl', $values['area']);
			$this->assertEquals('act', $values['action']);
		}
		
		public function testGetDefault()
		{
			$route =
				\Onphp\RouterTransparentRule::create(
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
				\Onphp\RouterTransparentRule::create(
					$routeConf['route']
				)->
				setDefaults(
					$routeConf['defaults']
				);
			
			$this->assertInstanceOf('\Onphp\RouterTransparentRule', $route);
			
			$values = $route->match(
				$this->buildRequest('http://localhost/users/all')
			);
			
			$this->assertEquals('ctrl', $values['area']);
		}
		
		public function testAssemblyResetDefaults()
		{
			$route =
				\Onphp\RouterTransparentRule::create(
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
			
			$url = $route->assembly(array('area' => null));
			$this->assertEquals('index/view/id/3', $url);
			
			$url = $route->assembly(array('action' => null));
			$this->assertEquals('news/index/id/3', $url);
			
			$url = $route->assembly(array('action' => null, 'id' => null));
			$this->assertEquals('news', $url);
		}
		
		public function testAssemblyWithRemovedDefaults()
		{
			$route =
				\Onphp\RouterTransparentRule::create(
					':area/:action/*'
				)->
				setDefaults(
					array(
						'area' => 'index',
						'action' => 'index'
					)
				);
			
			$url = $route->assembly(array('id' => 3));
			$this->assertEquals('index/index/id/3', $url);
			
			$url = $route->assembly(array('action' => 'test'));
			$this->assertEquals('index/test', $url);
			
			$url = $route->assembly(array('action' => 'test', 'id' => 3));
			$this->assertEquals('index/test/id/3', $url);
			
			$url = $route->assembly(array('area' => 'test'));
			$this->assertEquals('test', $url);
			
			$url = $route->assembly(array('area' => 'test', 'action' => 'test'));
			$this->assertEquals('test/test', $url);
			
			$url = $route->assembly(array('area' => 'test', 'id' => 3));
			$this->assertEquals('test/index/id/3', $url);
			
			$url = $route->assembly(array());
			$this->assertEquals('', $url);
			
			$route->match($this->buildRequest('http://localhost/ctrl'));
			
			$url = $route->assembly(array('id' => 3));
			$this->assertEquals('ctrl/index/id/3', $url);
			
			$url = $route->assembly(array('action' => 'test'));
			$this->assertEquals('ctrl/test', $url);
			
			$url = $route->assembly();
			$this->assertEquals('ctrl', $url);
			
			$route->match($this->buildRequest('http://localhost/index'));
			
			$url = $route->assembly();
			$this->assertEquals('', $url);
		}
		
		public function testAssemblyWithAction()
		{
			$route =
				\Onphp\RouterTransparentRule::create(
					'families/:action/*'
				)->
				setDefaults(
					array(
						'module' => 'default',
						'area' => 'categories',
						'action' => 'index'
					)
				);
			
			$this->assertEquals('families', $route->assembly());
			
			$values = $route->match(
				$this->buildRequest('http://localhost/families/edit/id/4')
			);
			
			$this->assertInternalType('array', $values);
			
			$this->assertEquals('families/edit/id/4', $route->assembly());
		}
		
		public function testAssemlingWithDefaultValueAndParams()
		{
			$router =
				\Onphp\RouterTransparentRule::create(
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
				
				$router->assembly(
					array(
						'area' => 'contestController',
						'contest' => 123,
						'param' => 435,
					),
					
					'contest'
				)
			);
		}
		
		public function testAssemblingTransparentDefaultLogic()
		{
			$transparent =
				\Onphp\RouterTransparentRule::create(
					':bar/:area/:action'
				)->
				setDefaults(
					array(
						'bar' => 'barvalue',
						'area' => 'controller',
						'action' => 'create',
					)
				);
				
			$this->assertEquals(
				'barvalue/controller/misc',
				$transparent->assembly(
					array(
						'action' => 'misc'
					)
				)
			);
			
			$this->assertEquals(
				'barvalue/misc',
				$transparent->assembly(
					array(
						'area' => 'misc'
					)
				)
			);
			
			$this->assertEquals(
				'misc',
				$transparent->assembly(
					array(
						'bar' => 'misc'
					)
				)
			);
		}
		
		public function testEncode()
		{
			$route =
				\Onphp\RouterTransparentRule::create(
					':area/:action/*'
				)->
				setDefaults(
					array(
						'area' => 'index',
						'action' => 'index'
					)
				);
			
			$url = $route->assembly(
				array('area' => 'My Controller'), false, true
			);
			$this->assertEquals('My+Controller', $url);
			
			$url = $route->assembly(
				array('area' => 'My Controller'), false, false
			);
			$this->assertEquals('My Controller', $url);
			
			$token = $route->match(
				$this->buildRequest(
					'http://localhost/en/foo/id/'.urlencode('My Value')
				)
			);
			
			$url = $route->assembly(array(), false, true);
			$this->assertEquals('en/foo/id/My+Value', $url);
			
			$url = $route->assembly(
				array('id' => 'My Other Value'), false, true
			);
			$this->assertEquals('en/foo/id/My+Other+Value', $url);
			
			$route =
				\Onphp\RouterTransparentRule::create(
					':area/*'
				)->
				setDefaults(
					array(
						'area' => 'My Controller'
					)
				);
			
			$url = $route->assembly(array('id' => 1), false, true);
			$this->assertEquals('My+Controller/id/1', $url);
		}

		public function testPartialMatch()
		{
			$this->markTestSkipped('Route features not ready');
			
			$route =
				\Onphp\RouterTransparentRule::create(
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
				\Onphp\RouterTransparentRule::create(
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
			
			$this->assertInternalType('array', $values);
			$this->assertEquals('en', $values['lang']);
			$this->assertEquals('tmp', $values['temp']);
			$this->assertEquals(6, $values[null]);
		}
		
		protected function buildRequest($url)
		{
			ServerVarUtils::build($_SERVER, $url);
			
			return
				\Onphp\HttpRequest::create()->
				setServer($_SERVER);
		}
	}
?>
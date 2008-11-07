<?php
	/* $Id$ */
	
	class RouterRewriteTest extends TestCase
	{
		/**
		 * @var RouterRewrite
		**/
		protected $router = null;
		
		public function setUp()
		{
			$this->router =
				RouterRewrite::me()->
					resetAll()->
					setBaseUrl(HttpUrl::create());
			
			ServerVarUtils::unsetVars($_SERVER);
		}
		
		public function tearDown()
		{
			// $this->router->resetAll();
		}
		
		public function testAddRoute()
		{
			$this->router->addRoute(
				'archive',
				
				RouterTransparentRule::create(
					'archive/:year'
				)->
				setDefaults(
					array(
						'year' => '2006',
						'area' => 'archive',
						'action' => 'show'
					)
				)->
				setRequirements(
					array(
						'year' => '\d+'
					)
				)
			);
			
			$routes = $this->router->getRoutes();
			
			$this->assertEquals(1, count($routes));
			$this->assertType('RouterTransparentRule', $routes['archive']);
			
			$this->router->addRoute(
				'register',
				
				RouterTransparentRule::create(
					'register/:action'
				)->
				setDefaults(
					array(
						'area' => 'profile',
						'action' => 'register'
					)
				)
			);
			
			$routes = $this->router->getRoutes();
			
			$this->assertEquals(2, count($routes));
			$this->assertType('RouterTransparentRule', $routes['register']);
		}
		
		public function testAddRoutes()
		{
			$routes = array(
				'archive' =>
					RouterTransparentRule::create(
						'archive/:year'
					)->
					setDefaults(
						array(
							'year' => '2006',
							'area' => 'archive',
							'action' => 'show'
						)
					)->
					setRequirements(
						array(
							'year' => '\d+'
						)
					),
				'register' =>
					RouterTransparentRule::create(
						'register/:action'
					)->
					setDefaults(
						array(
							'area' => 'profile',
							'action' => 'register'
						)
					)
			);
			
			$this->router->addRoutes($routes);
			
			$values = $this->router->getRoutes();
			
			$this->assertEquals(2, count($values));
			$this->assertType('RouterTransparentRule', $values['archive']);
			$this->assertType('RouterTransparentRule', $values['register']);
		}
		
		public function testHasRoute()
		{
			$this->router->addRoute(
				'archive',
				
				RouterTransparentRule::create(
					'archive/:year'
				)->
				setDefaults(
					array(
						'year' => '2006',
						'area' => 'archive',
						'action' => 'show'
					)
				)->
				setRequirements(
					array(
						'year' => '\d+'
					)
				)
			);
			
			$this->assertEquals(true, $this->router->hasRoute('archive'));
			$this->assertEquals(false, $this->router->hasRoute('bogus'));
		}
		
		public function testGetRoute()
		{
			$archive =
				RouterTransparentRule::create(
					'archive/:year'
				)->
				setDefaults(
					array(
						'year' => '2006',
						'area' => 'archive',
						'action' => 'show'
					)
				)->
				setRequirements(
					array(
						'year' => '\d+'
					)
				);
			
			$this->router->addRoute('archive', $archive);
			
			$route = $this->router->getRoute('archive');
			
			$this->assertType('RouterTransparentRule', $route);
			$this->assertSame($route, $archive);
		}
		
		public function testRemoveRoute()
		{
			$this->router->addRoute(
				'archive',
				
				RouterTransparentRule::create(
					'archive/:year'
				)->
				setDefaults(
					array(
						'year' => '2006',
						'area' => 'archive',
						'action' => 'show'
					)
				)->
				setRequirements(
					array(
						'year' => '\d+'
					)
				)
			);
			
			$route = $this->router->getRoute('archive');
			
			$this->router->removeRoute('archive');
			
			$routes = $this->router->getRoutes();
			
			$this->assertEquals(0, count($routes));
			
			try {
				$route = $this->router->removeRoute('archive');
			} catch (RouterException $e) {
				$this->assertType('RouterException', $e);
				return true;
			}
			
			$this->fail();
		}
		
		public function testGetNonExistentRoute()
		{
			try {
				$route = $this->router->getRoute('bogus');
			} catch (RouterException $e) {
				$this->assertType('RouterException', $e);
				return true;
			}
			
			$this->fail();
		}
		
		public function testRoute()
		{
			$request = $this->buildRequest();
			
			$token = $this->router->route($request);
			
			$this->assertType('HttpRequest', $token);
		}
		
		public function testRouteWithIncorrectRequest()
		{
			$this->markTestSkipped('Route features not ready');
			
			$request = $this->buildIncorrectRequest();
			
			try {
				$token = $this->router->route($request);
				$this->fail('Should throw an Exception');
			} catch (Exception $e) {
				$this->assertType('RouterException', $e);
			}
		}
		
		public function testDefaultRoute()
		{
			$this->markTestSkipped('Route features not ready');
			
			$request = $this->buildRequest();
			
			$token = $this->router->route($request);
			
			$routes = $this->router->getRoutes();
			
			$this->assertType(
				// FIXME: huh?
				'RouterTransparentRule_Module',
				$routes['default']
			);
		}
		
		public function testDefaultRouteWithEmptyAction()
		{
			$this->markTestSkipped('Route features not ready');
			
			$request = $this->buildRequest('http://localhost/ctrl');
			
			$token = $this->router->route($request);
			
			$this->assertEquals('ctrl', $token->getAttachedVar('area'));
			$this->assertEquals('defact', $token->getAttachedVar('action'));
		}
		
		public function testEmptyRoute()
		{
			$request = $this->buildRequest('http://localhost/');
			
			$this->router->addRoute(
				'empty',
				
				RouterTransparentRule::create('')->
				setDefaults(
					array(
						'area' => 'ctrl',
						'action' => 'act'
					)
				)
			);
			
			$token = $this->router->route($request);
			
			$this->assertEquals('ctrl', $token->getAttachedVar('area'));
			$this->assertEquals('act', $token->getAttachedVar('action'));
		}
		
		public function testEmptyPath()
		{
			$request = $this->buildRequest('http://localhost/');
			
			$this->router->addRoute(
				'catch-all',
				
				RouterTransparentRule::create(
					':area/:action/*'
				)->
				setDefaults(
					array(
						'area' => 'ctrl',
						'action' => 'act'
					)
				)
			);
			
			$token = $this->router->route($request);
			
			$this->assertEquals('ctrl', $token->getAttachedVar('area'));
			$this->assertEquals('act', $token->getAttachedVar('action'));
		}
		
		public function testEmptyPathWithWildcardRoute()
		{
			$request = $this->buildRequest('http://localhost/');
			
			$this->router->addRoute(
				'catch-all',
				
				RouterTransparentRule::create(
					'*'
				)->
				setDefaults(
					array(
						'area' => 'ctrl',
						'action' => 'act'
					)
				)
			);
			
			$token = $this->router->route($request);
			
			$this->assertEquals('ctrl', $token->getAttachedVar('area'));
			$this->assertEquals('act', $token->getAttachedVar('action'));
		}
		
		public function testRouteNotMatched()
		{
			$request = $this->buildRequest(
				'http://localhost/archive/action/bogus'
			);
			
			$this->router->addRoute(
				'default',
				new RouterTransparentRule(':area/:action')
			);
			
			$token = $this->router->route($request);
			
			$this->assertFalse($token->hasAttachedVar('area'));
			$this->assertFalse($token->hasAttachedVar('action'));
		}
		
		public function testFirstRouteMatched()
		{
			$request = $this->buildRequest('http://localhost/archive/2006');
			
			$this->router->addRoute(
				'archive',
				
				RouterTransparentRule::create(
					'archive/:year'
				)->
				setDefaults(
					array(
						'year' => '2006',
						'area' => 'archive',
						'action' => 'show'
					)
				)->
				setRequirements(
					array(
						'year' => '\d+'
					)
				)
			);
			
			$this->router->addRoute(
				'register',
				
				RouterTransparentRule::create(
					'register/:action'
				)->
				setDefaults(
					array(
						'area' => 'profile',
						'action' => 'register'
					)
				)
			);
			
			$token = $this->router->route($request);
			
			$this->assertEquals('archive', $token->getAttachedVar('area'));
			$this->assertEquals('show', $token->getAttachedVar('action'));
			
			try {
				$this->assertEquals(
					'archive',
					$this->router->getCurrentRouteName()
				);
				
				$this->assertType(
					'RouterTransparentRule',
					$this->router->getCurrentRoute()
				);
			} catch (BaseException $e) {
				$this->fail('Current route is not set');
			}
		}
		
		public function testExceptionGetCurrentRoute()
		{
			$request = $this->buildRequest('http://localhost/ctrl/act');
			
			try {
				$route = $this->router->getCurrentRouteName();
				$this->fail();
			} catch (BaseException $e) {
				$this->assertType('RouterException', $e);
			}
		}
		
		public function testAttachedVars()
		{
			$request = $this->buildRequest(
				'http://localhost/archive/2006/param/07'
			);
			
			$this->router->addRoute(
				'test',
				
				new RouterTransparentRule(
					':area/:action/*'
				)
			);
			
			$token = $this->router->route($request);
			
			$this->assertEquals('archive', $token->getAttachedVar('area'));
			$this->assertEquals('2006', $token->getAttachedVar('action'));
			$this->assertEquals('archive', $token->getAttachedVar('area'));
			$this->assertEquals('2006', $token->getAttachedVar('action'));
			$this->assertEquals('07', $token->getAttachedVar('param'));
		}
		
		public function testUrlValuesHandling1()
		{
			$this->router->addRoute(
				'foo',
				
				RouterTransparentRule::create(
					':lang/foo'
				)->
				setDefaults(
					array(
						'lang' => 'nl',
						'area' => 'index',
						'action' => 'index'
					)
				)
			);
			
			$this->router->addRoute(
				'bar',
				
				RouterTransparentRule::create(
					':lang/bar'
				)->
				setDefaults(
					array(
						'lang' => 'nl',
						'area' => 'index',
						'action' => 'index'
					)
				)
			);
			
			$request = $this->buildRequest('http://localhost/nl/bar');
			
			$token = $this->router->route($request);
			
			$this->assertEquals(
				'nl/foo',
				$this->router->getRoute('foo')->assemble()
			);
			$this->assertEquals(
				'nl/bar',
				$this->router->getRoute('bar')->assemble()
			);
		}
		
		public function testUrlValuesHandling2()
		{
			$this->router->addRoute(
				'foo',
				
				RouterTransparentRule::create(
					':lang/foo'
				)->
				setDefaults(
					array(
						'lang' => 'nl',
						'area' => 'index',
						'action' => 'index'
					)
				)
			);
			
			$this->router->addRoute(
				'bar',
				
				RouterTransparentRule::create(
					':lang/bar'
				)->
				setDefaults(
					array(
						'lang' => 'nl',
						'area' => 'index',
						'action' => 'index'
					)
				)
			);
			
			$request = $this->buildRequest('http://localhost/en/foo');
			$token = $this->router->route($request);
			
			$this->assertEquals(
				'en/foo',
				$this->router->getRoute('foo')->assemble()
			);
			$this->assertEquals(
				'nl/bar',
				$this->router->getRoute('bar')->assemble()
			);
		}
		
		public function testUrlValuesHandling3()
		{
			$this->router->addRoute(
				'foo',
				
				RouterTransparentRule::create(
					':lang/foo'
				)->
				setDefaults(
					array(
						'lang' => 'nl',
						'area' => 'index',
						'action' => 'index'
					)
				)
			);
			
			$this->router->addRoute(
				'bar',
				
				RouterTransparentRule::create(
					':lang/bar'
				)->
				setDefaults(
					array(
						'lang' => 'nl',
						'area' => 'index',
						'action' => 'index'
					)
				)
			);
			
			$request = $this->buildRequest('http://localhost/en/bar');
			$token = $this->router->route($request);
			
			$this->assertEquals(
				'nl/foo',
				$this->router->getRoute('foo')->assemble()
			);
			$this->assertEquals(
				'en/bar',
				$this->router->getRoute('bar')->assemble()
			);
		}
		
		public function testRoutingChainedRoutes()
		{
			$this->markTestSkipped('Route features not ready');
			
			$request = $this->buildRequest('http://localhost/foo/bar');
			
			$foo =
				RouterTransparentRule::create(
					'foo'
				)->
				setDefaults(
					array(
						'foo' => true
					)
				);
			
			$bar =
				RouterTransparentRule::create(
					'bar'
				)->
				setDefaults(
					array(
						'bar' => true,
						'area' => 'foo',
						'action' => 'bar'
					)
				);
			
			$chain = new RouterChainRule();
			
			$chain->
				chain($foo)->
				chain($bar);
			
			$this->router->addRoute('foo-bar', $chain);
			
			$token = $this->router->route($request);
			
			$this->assertEquals('foo', $token->getAttachedVar('area'));
			$this->assertEquals('bar', $token->getAttachedVar('action'));
			$this->assertEquals(true, $token->getParam('foo'));
			$this->assertEquals(true, $token->getParam('bar'));
		}
		
		public function testRouteWithHostnameChain()
		{
			$request = $this->buildRequest('http://www.example.com/bar');
			
			$foo =
				RouterHostnameRule::create(
					'nope.example.com'
				)->
				setDefaults(
					array(
						'module' => 'nope-bla',
						'bogus' => 'bogus'
					)
				);
			
			$bar =
				RouterHostnameRule::create(
					'www.example.com'
				)->
				setDefaults(
					array(
						'module' => 'www-bla'
					)
				);
			
			$bla =
				RouterStaticRule::create(
					'bar'
				)->
				setDefaults(
					array(
						'area' => 'foo',
						'action' => 'bar'
					)
				);
			
			$chainMatch = new RouterChainRule();
			
			$chainMatch->
				chain($bar)->
				chain($bla);
			
			$chainNoMatch = new RouterChainRule();
			
			$chainNoMatch->
				chain($foo)->
				chain($bla);
			
			$this->router->addRoute('match', $chainMatch);
			$this->router->addRoute('no-match', $chainNoMatch);
			
			$token = $this->router->route($request);
			
			$this->assertEquals('foo', $token->getAttachedVar('area'));
			$this->assertEquals('bar', $token->getAttachedVar('action'));
			$this->assertFalse($token->hasAttachedVar('bogus'));
		}
		
		public function testAssemlingWithTransparentRule()
		{
			$this->router->addRoute(
				'contest',
				
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
				)
			);
			
			$this->assertEquals(
				'/contest/123',
				
				$this->router->assemble(
					array(
						'area' => 'contestController',
						'contest' => 123,
					),
					
					'contest'
				)
			);
		}
		
		public function testAssemblingWithHostnameHttp()
		{
			$this->markTestSkipped('Router features not ready');
			
			$route = new RouterHostnameRule('www.example.com');
			
			$this->router->addRoute('hostname-route', $route);
			
			$this->assertEquals(
				'http://www.example.com',
				$this->router->assemble(array(), 'hostname-route')
			);
		}
		
		public function testAssemblingWithHostnameHttps()
		{
			$this->markTestSkipped('Router features not ready');
			
			$backupServer = $_SERVER;
			$_SERVER['HTTPS'] = 'on';
			
			$route = new RouterHostnameRule('www.example.com');
			
			$this->router->addRoute('hostname-route', $route);
			
			$this->assertEquals(
				'https://www.example.com',
				$this->router->assemble(array(), 'hostname-route')
			);
			
			$_SERVER = $backupServer;
		}
		
		public function testAssemblingWithHostnameThroughChainHttp()
		{
			$this->markTestSkipped('Router features not ready');
			
			$foo = new RouterHostnameRule('www.example.com');
			$bar = new RouterStaticRule('bar');
			
			$chain = new RouterChainRule();
			$chain->chain($foo)->chain($bar);
			
			$this->router->addRoute('foo-bar', $chain);
			
			$this->assertEquals(
				'http://www.example.com/bar',
				$this->router->assemble(array(),
				'foo-bar')
			);
		}
		
		public function testAssemblingWithHostnameWithChainHttp()
		{
			$this->markTestSkipped('Router features not ready');
			
			$foo = new RouterHostnameRule('www.example.com');
			$bar = new RouterStaticRule('bar');
			
			$chain = $foo->chain($bar);
			
			$this->router->addRoute('foo-bar', $chain);
			
			$this->assertEquals(
				'http://www.example.com/bar',
				$this->router->assemble(array(), 'foo-bar')
			);
		}
		
		public function testAssemblingWithNonFirstHostname()
		{
			$this->markTestSkipped('Router features not ready');
			
			$foo = new RouterStaticRule('bar');
			$bar = new RouterHostnameRule('www.example.com');
			
			$foo->chain($bar);
			
			$this->router->addRoute('foo-bar', $foo);
			
			$this->assertEquals(
				'bar/www.example.com',
				$this->router->assemble(array(), 'foo-bar')
			);
		}
		
		public function testRouteShouldMatchEvenWithTrailingSlash()
		{
			$route =
				RouterTransparentRule::create(
					'blog/articles/:id'
				)->
				setDefaults(
					array(
						'area' => 'blog',
						'action' => 'articles',
						'id' => 0,
					)
				)->
				setRequirements(
					array(
						'id' => '[0-9]+',
					)
				);
			
			$this->router->addRoute('article-id', $route);
			
			$request = $this->buildRequest('http://localhost/blog/articles/2006/');
			$token   = $this->router->route($request);
			
			$this->assertSame('article-id', $this->router->getCurrentRouteName());
			
			$this->assertEquals('2006', $token->getAttachedVar('id'));
		}
		
		/**
		 * @param string $url
		 * @return HttpRequest
		**/
		protected function buildRequest($url = null)
		{
			ServerVarUtils::build($_SERVER, $url);
			
			return
				HttpRequest::create()->
				setServer($_SERVER);
		}
		
		protected function buildIncorrectRequest()
		{
			return HttpRequest::create();
		}
	}
?>
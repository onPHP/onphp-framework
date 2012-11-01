<?php
	/* $Id$ */
	
	namespace Onphp\Test;

	class RouterRewriteTest extends TestCase
	{
		/**
		 * @var \Onphp\RouterRewrite
		**/
		protected $router = null;
		
		public function setUp()
		{
			$this->router =
				\Onphp\RouterRewrite::me()->
					resetAll()->
					setBaseUrl(\Onphp\HttpUrl::create());
			
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
				
				\Onphp\RouterTransparentRule::create(
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
			$this->assertInstanceOf('\Onphp\RouterTransparentRule', $routes['archive']);
			
			$this->router->addRoute(
				'register',
				
				\Onphp\RouterTransparentRule::create(
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
			$this->assertInstanceOf('\Onphp\RouterTransparentRule', $routes['register']);
		}
		
		public function testAddRoutes()
		{
			$routes = array(
				'archive' =>
					\Onphp\RouterTransparentRule::create(
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
					\Onphp\RouterTransparentRule::create(
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
			$this->assertInstanceOf('\Onphp\RouterTransparentRule', $values['archive']);
			$this->assertInstanceOf('\Onphp\RouterTransparentRule', $values['register']);
		}
		
		public function testHasRoute()
		{
			$this->router->addRoute(
				'archive',
				
				\Onphp\RouterTransparentRule::create(
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
				\Onphp\RouterTransparentRule::create(
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
			
			$this->assertInstanceOf('\Onphp\RouterTransparentRule', $route);
			$this->assertSame($route, $archive);
		}
		
		public function testRemoveRoute()
		{
			$this->router->addRoute(
				'archive',
				
				\Onphp\RouterTransparentRule::create(
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
			} catch (\Onphp\RouterException $e) {
				$this->assertInstanceOf('\Onphp\RouterException', $e);
				return true;
			}
			
			$this->fail();
		}
		
		public function testGetNonExistentRoute()
		{
			try {
				$route = $this->router->getRoute('bogus');
			} catch (\Onphp\RouterException $e) {
				$this->assertInstanceOf('\Onphp\RouterException', $e);
				return true;
			}
			
			$this->fail();
		}
		
		public function testRoute()
		{
			$request = $this->buildRequest();
			
			$token = $this->router->route($request);
			
			$this->assertInstanceOf('\Onphp\HttpRequest', $token);
		}
				
		public function testEmptyRoute()
		{
			$request = $this->buildRequest('http://localhost/');
			
			$this->router->addRoute(
				'empty',
				
				\Onphp\RouterTransparentRule::create('')->
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
				
				\Onphp\RouterTransparentRule::create(
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
				
				\Onphp\RouterTransparentRule::create(
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
				new \Onphp\RouterTransparentRule(':area/:action')
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
				
				\Onphp\RouterTransparentRule::create(
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
				
				\Onphp\RouterTransparentRule::create(
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
				
				$this->assertInstanceOf(
					'\Onphp\RouterTransparentRule',
					$this->router->getCurrentRoute()
				);
			} catch (\Onphp\BaseException $e) {
				$this->fail('Current route is not set');
			}
		}
		
		public function testExceptionGetCurrentRoute()
		{
			$request = $this->buildRequest('http://localhost/ctrl/act');
			
			try {
				$route = $this->router->getCurrentRouteName();
				$this->fail();
			} catch (\Onphp\BaseException $e) {
				$this->assertInstanceOf('\Onphp\RouterException', $e);
			}
		}
		
		public function testAttachedVars()
		{
			$request = $this->buildRequest(
				'http://localhost/archive/2006/param/07'
			);
			
			$this->router->addRoute(
				'test',
				
				new \Onphp\RouterTransparentRule(
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
				
				\Onphp\RouterTransparentRule::create(
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
				
				\Onphp\RouterTransparentRule::create(
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
				$this->router->getRoute('foo')->assembly()
			);
			$this->assertEquals(
				'nl/bar',
				$this->router->getRoute('bar')->assembly()
			);
		}
		
		public function testUrlValuesHandling2()
		{
			$this->router->addRoute(
				'foo',
				
				\Onphp\RouterTransparentRule::create(
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
				
				\Onphp\RouterTransparentRule::create(
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
				$this->router->getRoute('foo')->assembly()
			);
			$this->assertEquals(
				'nl/bar',
				$this->router->getRoute('bar')->assembly()
			);
		}
		
		public function testUrlValuesHandling3()
		{
			$this->router->addRoute(
				'foo',
				
				\Onphp\RouterTransparentRule::create(
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
				
				\Onphp\RouterTransparentRule::create(
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
				$this->router->getRoute('foo')->assembly()
			);
			$this->assertEquals(
				'en/bar',
				$this->router->getRoute('bar')->assembly()
			);
		}
		
		public function testRoutingChainedRoutes()
		{
			$this->markTestSkipped('Route features not ready');
			
			$request = $this->buildRequest('http://localhost/foo/bar');
			
			$foo =
				\Onphp\RouterTransparentRule::create(
					'foo'
				)->
				setDefaults(
					array(
						'foo' => true
					)
				);
			
			$bar =
				\Onphp\RouterTransparentRule::create(
					'bar'
				)->
				setDefaults(
					array(
						'bar' => true,
						'area' => 'foo',
						'action' => 'bar'
					)
				);
			
			$chain = new \Onphp\RouterChainRule();
			
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
				\Onphp\RouterHostnameRule::create(
					'nope.example.com'
				)->
				setDefaults(
					array(
						'module' => 'nope-bla',
						'bogus' => 'bogus'
					)
				);
			
			$bar =
				\Onphp\RouterHostnameRule::create(
					'www.example.com'
				)->
				setDefaults(
					array(
						'module' => 'www-bla'
					)
				);
			
			$bla =
				\Onphp\RouterStaticRule::create(
					'bar'
				)->
				setDefaults(
					array(
						'area' => 'foo',
						'action' => 'bar'
					)
				);
			
			$chainMatch = new \Onphp\RouterChainRule();
			
			$chainMatch->
				chain($bar)->
				chain($bla);
			
			$chainNoMatch = new \Onphp\RouterChainRule();
			
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
		
		public function testRouteWithHostnameAndTransparentRuleChain()
		{
			$request = $this->buildRequest('http://www.example.com/test/123');
			
			$host =
				\Onphp\RouterHostnameRule::create(
					'www.example.com'
				)->
				setDefaults(
					array(
						'module' => 'nope-bla',
						'bogus' => 'bogus'
					)
				);
			
			$transparent =
				\Onphp\RouterTransparentRule::create(
					':area/:contest'
				)->
				setRequirements(
					array(
						'contest' => '\d+',
						'area' => '\w+'
					)
				);
			
			$chain = new \Onphp\RouterChainRule();
			
			$chain->
				chain($host)->
				chain($transparent);
			
			$this->router->addRoute('HostnameAndTransparent', $chain);
			
			$token = $this->router->route($request);
			
			$this->assertEquals('test', $token->getAttachedVar('area'));
			$this->assertEquals(123, $token->getAttachedVar('contest'));
			$this->assertEquals('nope-bla', $token->getAttachedVar('module'));
			$this->assertEquals('bogus', $token->getAttachedVar('bogus'));
		}
		
		public function testRouteWithHostnameAndTransparentAndBaseUrlRuleChain()
		{
			$base = 'http://www.example.com/~user/public_html/www/';
			
			$request = $this->buildRequest($base.'test/123');
			
			$host =
				\Onphp\RouterHostnameRule::create(
					'www.example.com'
				)->
				setDefaults(
					array(
						'module' => 'nope-bla',
						'domain' => 'www'
					)
				);
			
			$transparent =
				\Onphp\RouterTransparentRule::create(
					':area/:contest'
				)->
				setRequirements(
					array(
						'contest' => '\d+',
						'area' => '\w+'
					)
				);
			
			$chain = new \Onphp\RouterChainRule();
			
			$chain->
				chain($host)->
				chain($transparent);
			
			$this->router->setBaseUrl(
				\Onphp\HttpUrl::create()->parse($base)
			);
			
			$this->router->addRoute('HostnameAndTransparentWithBaseUrl', $chain);
			
			$token = $this->router->route($request);
			
			$this->assertEquals(4, count($token->getAttached()));
			$this->assertEquals('test', $token->getAttachedVar('area'));
			$this->assertEquals(123, $token->getAttachedVar('contest'));
			$this->assertEquals('nope-bla', $token->getAttachedVar('module'));
			$this->assertEquals('www', $token->getAttachedVar('domain'));
		}
		
		public function testRouteWithHostnameMaskAndRegexpAndBaseUrlRuleChain()
		{
			$base = 'http://www.example.com/~user/public_html/www/';
			
			$request = $this->buildRequest('http://test123d.example.com/~user/public_html/www/test/123.html');
			
			$host =
				\Onphp\RouterHostnameRule::create(
					':subdomain.example.com'
				)->
				setDefaults(
					array(
						'module' => 'nope-bla',
						'area' => 'test',
					)
				)->
				setRequirements(
					array(
						'subdomain' => '[\da-z][\da-z\_]*[\da-z]'
					)
				);
			
			$transparent =
				\Onphp\RouterRegexpRule::create(
					'test/(\d+)\.html'
				)->
				setDefaults(
					array(
						1 => 345
					)
				)->
				setMap(
					array(
						1 => 'testId'
					)
				);
			
			$chain = new \Onphp\RouterChainRule();
			
			$chain->
				chain($host)->
				chain($transparent);
			
			$this->router->setBaseUrl(
				\Onphp\HttpUrl::create()->parse($base)
			);
			
			$this->router->addRoute('HostnameMaskWithRegexp', $chain);
			
			$token = $this->router->route($request);
			
			$this->assertEquals(4, count($token->getAttached()));
			$this->assertEquals('test', $token->getAttachedVar('area'));
			$this->assertEquals(123, $token->getAttachedVar('testId'));
			$this->assertEquals('nope-bla', $token->getAttachedVar('module'));
			$this->assertEquals('test123d', $token->getAttachedVar('subdomain'));
		}
		
		public function testAssemlingWithTransparentRule()
		{
			$this->router->addRoute(
				'contest',
				
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
				)
			);
			
			$this->assertEquals(
				'/contest/123',
				
				$this->router->assembly(
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
			$route = new \Onphp\RouterHostnameRule('www.example.com');
			
			$this->router->addRoute('hostname-route', $route);
			
			$this->assertEquals(
				'http://www.example.com',
				$this->router->assembly(array(), 'hostname-route')
			);
		}
		
		public function testAssemblingWithHostnameHttps()
		{
			$route =
				\Onphp\RouterHostnameRule::create('www.example.com')->
				setSecure();
			
			$this->router->addRoute('hostname-route', $route);
			
			$this->assertEquals(
				'https://www.example.com',
				$this->router->assembly(array(), 'hostname-route')
			);
		}
		
		public function testAssemblingWithHostnameThroughChainHttp()
		{
			$foo = new \Onphp\RouterHostnameRule('www.example.com');
			$bar = new \Onphp\RouterStaticRule('bar');
			
			$chain =
				\Onphp\RouterChainRule::create()->
				chain($foo)->
				chain($bar);
			
			$this->router->addRoute('foo-bar', $chain);
			
			$this->assertEquals(
				'http://www.example.com/bar',
				$this->router->assembly(array(), 'foo-bar')
			);
		}
		
		public function testAssemblingWithHostnameWithChainHttp()
		{
			$foo = new \Onphp\RouterHostnameRule('www.example.com');
			$bar = new \Onphp\RouterStaticRule('bar');
			
			$chain = $foo->chain($bar);
			
			$this->router->addRoute('foo-bar', $chain);
			
			$this->assertEquals(
				'http://www.example.com/bar',
				$this->router->assembly(array(), 'foo-bar')
			);
		}
		
		public function testAssemblingWithHostnameThroughChainHttpAndBaseUrl()
		{
			$foo = new \Onphp\RouterHostnameRule('www.example.com');
			$bar = new \Onphp\RouterStaticRule('bar');
			
			$chain =
				\Onphp\RouterChainRule::create()->
				chain($foo)->
				chain($bar);
			
			$this->router->
				setBaseUrl(
					\Onphp\HttpUrl::create()->
					parse('http://www.example.com/~user/public/')
				)->
				addRoute('foo-bar', $chain);
			
			$this->assertEquals(
				'http://www.example.com/~user/public/bar',
				$this->router->assembly(array(), 'foo-bar')
			);
		}
		
		public function testAssemblingWithHostnameThroughChainHttpAndBaseUrlAndDiffHost()
		{
			$foo = new \Onphp\RouterHostnameRule('www.example.com');
			$bar = new \Onphp\RouterStaticRule('bar');
			
			$chain =
				\Onphp\RouterChainRule::create()->
				chain($foo)->
				chain($bar);
			
			$this->router->
				setBaseUrl(
					\Onphp\HttpUrl::create()->
					parse('http://qwerty.example.com/~user/public/')
				)->
				addRoute('foo-bar', $chain);
			
			$this->assertEquals(
				'http://www.example.com/~user/public/bar',
				$this->router->assembly(array(), 'foo-bar')
			);
		}
		
		public function testAssemblingWithHostnameThroughChainHttpAndBaseUrlAndDiffScheme()
		{
			$foo = new \Onphp\RouterHostnameRule('http.example.com');
			$bar = new \Onphp\RouterStaticRule('bar');
			
			$chain =
				\Onphp\RouterChainRule::create()->
				chain($foo)->
				chain($bar);
			
			$this->router->
				setBaseUrl(
					\Onphp\HttpUrl::create()->
					parse('https://www.example.com/~user/public/')
				)->
				addRoute('foo-bar', $chain);
						
			$this->assertEquals(
				'http://http.example.com/bar',
				$this->router->assembly(array(), 'foo-bar')
			);
		}
		
		public function testAssemblingWithSettingHostnameThroughChainHttpAndBaseUrlAndDiffScheme()
		{
			$foo =
				\Onphp\RouterHostnameRule::create('https.example.com')->
				setSecure();
				
			$bar = new \Onphp\RouterStaticRule('bar');
			
			$chain =
				\Onphp\RouterChainRule::create()->
				chain($foo)->
				chain($bar);
			
			$this->router->
				setBaseUrl(
					\Onphp\HttpUrl::create()->
					parse('https://www.example.com/~user/public/')
				)->
				addRoute('foo-bar', $chain);
						
			$this->assertEquals(
				'https://https.example.com/~user/public/bar',
				$this->router->assembly(array(), 'foo-bar')
			);
		}
					
		public function testAssemblingWrongChain()
		{
			$foo = new \Onphp\RouterStaticRule('bar');
			$bar = new \Onphp\RouterHostnameRule('mega.example.com');
			
			$chain = $foo->chain($bar);
			
			$this->router->addRoute('foobar', $chain);
		
			$this->assertEquals(
				2,
				$chain->getCount()
			);
			
			try {
				$s = $this->router->assembly(array(), 'foobar');
			} catch (\Onphp\BaseException $e) {
				$this->assertInstanceOf('\Onphp\RouterException', $e);
				return true;
			}
			
			$this->fail();
		}
		
		public function testRouteShouldMatchEvenWithTrailingSlash()
		{
			$route =
				\Onphp\RouterTransparentRule::create(
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
		
	public function testAssemblingWithHostnameAndBaseUrl()
		{
			$base = 'http://www.example.com/~users/public/www/';
			
			$hostname =
				\Onphp\RouterHostnameRule::create(':subdomain.example.com')->
					setDefaults(
						array(
							'subdomain' => 'mega'
						)
					);
			
			$this->router->setBaseUrl(
				\Onphp\HttpUrl::create()->parse($base)
			);
			
			$this->router->addRoute('host', $hostname);
			
			$this->assertEquals(
				'http://test.example.com/~users/public/www',
				$this->router->assembly(
					array(
						'subdomain' => 'test'
					),
					'host'
				)
			);
		}
		
		public function testAssemblingWithHostnameAndTransparentWithBaseUrl()
		{
			$base = 'http://www.example.com/~users/public/www/';
			
			$hostname =
				\Onphp\RouterHostnameRule::create(':subdomain.example.com')->
					setDefaults(
						array(
							'subdomain' => 'www'
						)
					);
			
			$transparent =
				\Onphp\RouterTransparentRule::create('/company/:id')->
					setRequirements(
						array(
							'id' => '\d+'
						)
					);
			
			$chain =
				\Onphp\RouterChainRule::create()->
				chain($hostname)->
				chain($transparent);
			
			$this->router->setBaseUrl(
				\Onphp\HttpUrl::create()->parse($base)
			);
			
			$this->router->addRoute('chain', $chain);
			
			$this->assertEquals(
				'http://test.example.com/~users/public/www/company/123',
				$this->router->assembly(
					array(
						'subdomain' => 'test',
						'id' => 123
					),
					'chain'
				)
			);
		}
		
		/**
		 * @param string $url
		 * @return \Onphp\HttpRequest
		**/
		protected function buildRequest($url = null)
		{
			ServerVarUtils::build($_SERVER, $url);
			
			return
				\Onphp\HttpRequest::create()->
				setServer($_SERVER);
		}
		
		protected function buildIncorrectRequest()
		{
			return \Onphp\HttpRequest::create();
		}
	}
?>
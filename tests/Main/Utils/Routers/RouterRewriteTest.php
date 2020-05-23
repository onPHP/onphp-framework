<?php

namespace OnPHP\Tests\Main\Utils\Router;

use OnPHP\Core\Exception\BaseException;
use OnPHP\Main\Flow\HttpRequest;
use OnPHP\Main\Net\HttpUrl;
use OnPHP\Main\Util\Router\RouterChainRule;
use OnPHP\Main\Util\Router\RouterException;
use OnPHP\Main\Util\Router\RouterHostnameRule;
use OnPHP\Main\Util\Router\RouterRegexpRule;
use OnPHP\Main\Util\Router\RouterRewrite;
use OnPHP\Main\Util\Router\RouterStaticRule;
use OnPHP\Main\Util\Router\RouterTransparentRule;
use OnPHP\Tests\TestEnvironment\ServerVarUtils;
use OnPHP\Tests\TestEnvironment\TestCase;

/**
 * @group utils
 * @group router
 */
class RouterRewriteTest extends TestCase
{
	/**
	 * @var RouterRewrite
	**/
	protected $router = null;

	public function setUp(): void
	{
		$this->router =
			RouterRewrite::me()->
				resetAll()->
				setBaseUrl(HttpUrl::create());

		ServerVarUtils::unsetVars($_SERVER);
	}

	public function tearDown(): void
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
		$this->assertInstanceOf(RouterTransparentRule::class, $routes['archive']);

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
		$this->assertInstanceOf(RouterTransparentRule::class, $routes['register']);
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
		$this->assertInstanceOf(RouterTransparentRule::class, $values['archive']);
		$this->assertInstanceOf(RouterTransparentRule::class, $values['register']);
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

		$this->assertInstanceOf(RouterTransparentRule::class, $route);
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
			$this->assertInstanceOf(RouterException::class, $e);
			return true;
		}

		$this->fail();
	}

	public function testGetNonExistentRoute()
	{
		try {
			$route = $this->router->getRoute('bogus');
		} catch (RouterException $e) {
			$this->assertInstanceOf(RouterException::class, $e);
			return true;
		}

		$this->fail();
	}

	public function testRoute()
	{
		$request = $this->buildRequest();

		$token = $this->router->route($request);

		$this->assertInstanceOf(HttpRequest::class, $token);
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

			$this->assertInstanceOf(
				RouterTransparentRule::class,
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
			$this->assertInstanceOf(RouterException::class, $e);
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

	public function testRouteWithHostnameAndTransparentRuleChain()
	{
		$request = $this->buildRequest('http://www.example.com/test/123');

		$host =
			RouterHostnameRule::create(
				'www.example.com'
			)->
			setDefaults(
				array(
					'module' => 'nope-bla',
					'bogus' => 'bogus'
				)
			);

		$transparent =
			RouterTransparentRule::create(
				':area/:contest'
			)->
			setRequirements(
				array(
					'contest' => '\d+',
					'area' => '\w+'
				)
			);

		$chain = new RouterChainRule();

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
			RouterHostnameRule::create(
				'www.example.com'
			)->
			setDefaults(
				array(
					'module' => 'nope-bla',
					'domain' => 'www'
				)
			);

		$transparent =
			RouterTransparentRule::create(
				':area/:contest'
			)->
			setRequirements(
				array(
					'contest' => '\d+',
					'area' => '\w+'
				)
			);

		$chain = new RouterChainRule();

		$chain->
			chain($host)->
			chain($transparent);

		$this->router->setBaseUrl(
			HttpUrl::create()->parse($base)
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
			RouterHostnameRule::create(
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
			RouterRegexpRule::create(
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

		$chain = new RouterChainRule();

		$chain->
			chain($host)->
			chain($transparent);

		$this->router->setBaseUrl(
			HttpUrl::create()->parse($base)
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
		$route = new RouterHostnameRule('www.example.com');

		$this->router->addRoute('hostname-route', $route);

		$this->assertEquals(
			'http://www.example.com',
			$this->router->assembly(array(), 'hostname-route')
		);
	}

	public function testAssemblingWithHostnameHttps()
	{
		$route =
			RouterHostnameRule::create('www.example.com')->
			setSecure();

		$this->router->addRoute('hostname-route', $route);

		$this->assertEquals(
			'https://www.example.com',
			$this->router->assembly(array(), 'hostname-route')
		);
	}

	public function testAssemblingWithHostnameThroughChainHttp()
	{
		$foo = new RouterHostnameRule('www.example.com');
		$bar = new RouterStaticRule('bar');

		$chain =
			RouterChainRule::create()->
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
		$foo = new RouterHostnameRule('www.example.com');
		$bar = new RouterStaticRule('bar');

		$chain = $foo->chain($bar);

		$this->router->addRoute('foo-bar', $chain);

		$this->assertEquals(
			'http://www.example.com/bar',
			$this->router->assembly(array(), 'foo-bar')
		);
	}

	public function testAssemblingWithHostnameThroughChainHttpAndBaseUrl()
	{
		$foo = new RouterHostnameRule('www.example.com');
		$bar = new RouterStaticRule('bar');

		$chain =
			RouterChainRule::create()->
			chain($foo)->
			chain($bar);

		$this->router->
			setBaseUrl(
				HttpUrl::create()->
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
		$foo = new RouterHostnameRule('www.example.com');
		$bar = new RouterStaticRule('bar');

		$chain =
			RouterChainRule::create()->
			chain($foo)->
			chain($bar);

		$this->router->
			setBaseUrl(
				HttpUrl::create()->
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
		$foo = new RouterHostnameRule('http.example.com');
		$bar = new RouterStaticRule('bar');

		$chain =
			RouterChainRule::create()->
			chain($foo)->
			chain($bar);

		$this->router->
			setBaseUrl(
				HttpUrl::create()->
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
			RouterHostnameRule::create('https.example.com')->
			setSecure();

		$bar = new RouterStaticRule('bar');

		$chain =
			RouterChainRule::create()->
			chain($foo)->
			chain($bar);

		$this->router->
			setBaseUrl(
				HttpUrl::create()->
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
		$foo = new RouterStaticRule('bar');
		$bar = new RouterHostnameRule('mega.example.com');

		$chain = $foo->chain($bar);

		$this->router->addRoute('foobar', $chain);

		$this->assertEquals(
			2,
			$chain->getCount()
		);

		try {
			$s = $this->router->assembly(array(), 'foobar');
		} catch (BaseException $e) {
			$this->assertInstanceOf(RouterException::class, $e);
			return true;
		}

		$this->fail();
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

public function testAssemblingWithHostnameAndBaseUrl()
	{
		$base = 'http://www.example.com/~users/public/www/';

		$hostname =
			RouterHostnameRule::create(':subdomain.example.com')->
				setDefaults(
					array(
						'subdomain' => 'mega'
					)
				);

		$this->router->setBaseUrl(
			HttpUrl::create()->parse($base)
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
			RouterHostnameRule::create(':subdomain.example.com')->
				setDefaults(
					array(
						'subdomain' => 'www'
					)
				);

		$transparent =
			RouterTransparentRule::create('/company/:id')->
				setRequirements(
					array(
						'id' => '\d+'
					)
				);

		$chain =
			RouterChainRule::create()->
			chain($hostname)->
			chain($transparent);

		$this->router->setBaseUrl(
			HttpUrl::create()->parse($base)
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
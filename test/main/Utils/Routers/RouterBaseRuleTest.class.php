<?php

/** $Id$ **/
class RouterBaseRuleStub extends RouterBaseRule
{

    public function getPath(HttpUrl $url)
    {
        return parent::getPath($url);
    }

    public function match(HttpRequest $request)
    {/**/
    }

    public function assembly(array $data = [], $reset = false, $encode = false)
    {/**/
    }
}

class RouterBaseRuleTest extends TestCase
{
    /**
     * array(
     *    '<base_url>' =>
     *        array(
     *            '<request_uri>' => '<expected_result>',
     *            ...
     *        ),
     *    ...
     * )
     *
     * @var array
     **/
    protected static $fixtures = [
        '/example/' => [
            '' => '',
            '/' => '/',
            '/example/' => '',
            'login?user=boo' => 'login',
            '/login?user=boo' => '/login',
            '/example/login/?user=boo' => 'login/',
            '/example/script.php' => 'script.php',
            'http://example.org/example/login/?user=boo' => 'login/'
        ],

        'http://example.org/example/' => [
            '' => '',
            '/' => '/',
            'http://example.org/example/' => '',
            'login?user=boo' => 'login',
            '/login?user=boo' => '/login',
            '/example/login/?user=boo' => 'login/',
            '/example/script.php' => 'script.php',
            'http://example.org/example/login/?user=boo' => 'login/',
            'http://example.com/example/login/?user=boo' => 'http://example.com/example/login/'
        ],

        'http://example.org/' => [
            '' => '',
            '/' => '',
            'http://example.org/' => '',
            'login?user=boo' => 'login',
            '/login?user=boo' => 'login',
            '/example/login/?user=boo' => 'example/login/',
            '/example/script.php' => 'example/script.php',
            'http://example.org/' => '',
            'http://example.org' => '',
            'http://example.com' => 'http://example.com'
        ],

        'http://example.org' => [
            '' => '',
            '/' => '/',
            'http://example.org' => '',
            'login?user=boo' => 'login',
            '/login?user=boo' => '/login',
            '/example/login/?user=boo' => '/example/login/',
            '/example/script.php' => '/example/script.php',
            'http://example.org/?user=boo' => '/',
            'http://example.org?user=boo' => '',
            'http://example.com?user=boo' => 'http://example.com'
        ],

        '/' => [
            '' => '',
            '/' => '',
            'login?user=boo' => 'login',
            '/login?user=boo' => 'login',
            '/example/login/?user=boo' => 'example/login/',
            '/example/script.php' => 'example/script.php',
            'http://example.org/?user=boo' => '',
            'http://example.org?user=boo' => ''
        ],

        '/example/index.php' => [
            '' => '',
            '/' => '/',
            '/example/index.php' => 'index.php',
            'login?user=boo' => 'login',
            '/login?user=boo' => '/login',
            '/example/login/?user=boo' => 'login/',
            '/example/script.php' => 'script.php',
            'http://example.org/example/login/' => 'login/'
        ],

        'index.php' => [
            '' => '',
            '/' => '/',
            'index.php' => 'index.php',
            'login?user=boo' => 'login',
            '/login?user=boo' => '/login',
            '/example/login/?user=boo' => '/example/login/',
            'http://example.org/example/login/?user=boo' => '/example/login/',
            'http://example.org/index.php?user=boo' => '/index.php',
            'script.php' => 'script.php',
            'index.php/boo' => 'index.php/boo'
        ],

        '' => [
            '' => '',
            '/' => '/',
            'login?user=boo' => 'login',
            '/login?user=boo' => '/login',
            '/example/login/?user=boo' => '/example/login/',
            '/example/script.php' => '/example/script.php',
            'http://example.org/?user=boo' => '/',
            'http://example.org?user=boo' => ''
        ]
    ];

    public function testGetPath()
    {
        foreach (self::$fixtures as $base => $cases) {
            $rewriter =
                RouterRewrite::me()->setBaseUrl(
                    HttpUrl::create()->parse($base)
                );

            foreach ($cases as $requestUri => $pathResult) {
                $actualResult =
                    RouterBaseRuleStub::create()->
                    getPath(
                        HttpUrl::create()->
                        parse($requestUri)
                    )->
                    toString();

                $this->assertEquals(
                    $pathResult,
                    $actualResult,
                    "base url: {$base}\nrequest uri: {$requestUri}"
                );
            }
        }
    }
}

?>
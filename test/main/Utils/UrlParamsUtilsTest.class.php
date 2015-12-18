<?php
	final class UrlParamsUtilsTest extends TestCase
	{
		public function testOneDeepLvl()
		{
			$scope = array(
				'a' => '1',
				'c' => '@3',
				'g' => array('1' => '1', '0' => '[0]'),
			);
			
			$this->assertEquals(
				'a=1&c=%403&g[1]=1&g[0]=%5B0%5D',
				UrlParamsUtils::toStringOneDeepLvl($scope)
			);
			
			$scope['z'] = array('2' => array('8' => '8'));
			
			try {
				UrlParamsUtils::toStringOneDeepLvl($scope);
				$this->fail('expected exception');
			} catch (BaseException $e) {
				$this->assertEquals(
					'urlencode() expects parameter 1 to be string, array given',
					$e->getMessage()
				);
			}
		}
		
		public function testAnyDeepLvl()
		{
			$scope = array(
				'foo' => array('foo' => array('foo' => '@bar')),
				'bar' => array('@bar' => array('bar' => "foo[]я")),
				'fo' => array(
					array('o', 'ba', 'r'),
				)
			);
			
			$this->assertEquals(
				array(
					'foo[foo][foo]' => '@bar',
					'bar[@bar][bar]' => 'foo[]я',
					'fo[0][0]' => 'o',
					'fo[0][1]' => 'ba',
					'fo[0][2]' => 'r',
				),
				UrlParamsUtils::toParamsList($scope)
			);
			
			$this->assertEquals(
				'foo[foo][foo]=%40bar&bar[%40bar][bar]=foo%5B%5D%D1%8F&fo[0][0]=o&fo[0][1]=ba&fo[0][2]=r',
				UrlParamsUtils::toString($scope)
			);
		}
	}
?>
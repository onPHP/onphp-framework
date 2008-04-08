<?php
	/* $Id$ */
	
	final class ApplicationUrlTest extends TestCase
	{
		public function testHref()
		{
			$global = Scope::create()->
				setScopeVar('sid', '42');
			
			$request = Scope::create()->
				setScopeVar('area', 'profile');
				
			$url = ApplicationUrl::create()->
				setRewriter(
					HttpRewriter::create(
						HttpUrl::create('http://example.com/path/')
					)
				)->
				setApplicationScope($global)->
				setRequestScope($request);
			
			$refUrl = $url->currentHref(array('login' => 'username'));
			
			$newUrl = $url->scopeHref(array('login' => 'username'));
			$newSameUrl = $url->href('?login=username');
			
			$this->assertEquals(
				'?area=profile&sid=42',
				$url->toString()
			);
			
			$this->assertEquals(
				'?login=username&sid=42',
				$newUrl->toString()
			);
			
			$this->assertEquals(
				'?login=username&sid=42',
				$newSameUrl->toString()
			);
			
			$this->assertEquals(
				'?area=profile&login=username&sid=42',
				$refUrl->toString()
			);
		}
	}
?>
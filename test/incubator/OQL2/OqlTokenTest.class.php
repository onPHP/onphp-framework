<?php
	/* $Id$ */
	
	final class OqlTokenTest extends TestCase
	{
		public function testInstances()
		{
			$this->assertSame(
				OqlToken::create(OqlTokenType::UNKNOWN, 'value'),
				OqlToken::create(OqlTokenType::UNKNOWN, 'value')
			);
			
			$this->assertNotEquals(
				OqlToken::create(OqlTokenType::UNKNOWN, 'value1'),
				OqlToken::create(OqlTokenType::UNKNOWN, 'value2')
			);
			
			$this->assertNotEquals(
				OqlToken::create(OqlTokenType::UNKNOWN, 'value'),
				OqlToken::create(OqlTokenType::KEYWORD, 'value')
			);
		}
	}
?>
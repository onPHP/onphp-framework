<?php
	/* $Id$ */
	
	final class OqlTokenTest extends TestCase
	{
		public function testInstances()
		{
			$this->assertSame(
				OqlToken::create('value', 'value', OqlTokenType::UNKNOWN),
				OqlToken::create('value', 'value', OqlTokenType::UNKNOWN)
			);
			
			$this->assertSame(
				OqlToken::create('value1', 'value', OqlTokenType::UNKNOWN),
				OqlToken::create('value2', 'value', OqlTokenType::UNKNOWN)
			);
			
			$this->assertNotEquals(
				OqlToken::create('value', 'value1', OqlTokenType::UNKNOWN),
				OqlToken::create('value', 'value2', OqlTokenType::UNKNOWN)
			);
			
			$this->assertNotEquals(
				OqlToken::create('value', 'value', OqlTokenType::UNKNOWN),
				OqlToken::create('value', 'value', OqlTokenType::KEYWORD)
			);
		}
	}
?>
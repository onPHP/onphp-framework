<?php
	/* $Id$ */
	
	final class FiltersTest extends UnitTestCase
	{
		public function testTrim()
		{
			$filter = TrimFilter::create();
			$text = ' qq ';
			
			$this->assertEqual(
				$filter->apply($text),
				'qq'
			);
			
			$this->assertEqual(
				$filter->setLeft()->apply($text),
				'qq '
			);
			
			$this->assertEqual(
				$filter->setRight()->apply($text),
				' qq'
			);
			
			$this->assertEqual(
				$filter->setBoth()->apply($text),
				'qq'
			);
		}
		
		public function testUu()
		{
			$text = 'foo und bar';
			
			$this->assertEqual(
				Filter::uudecode()->apply(Filter::uuencode()->apply($text)),
				$text
			);
		}
		
		public function testNewLines()
		{
			$this->assertEqual(
				Filter::nl2br()->apply("strange\nthings\nhappens"),
				"strange<br />\nthings<br />\nhappens"
			);
		}
		
		public function testRussianTypograf()
		{
			$filter = RussianTypograph::me();
			$values = array(null, '', false, 0);
			foreach ($values as $value) {
				$this->assertEqual($value, $filter->apply($value));
			}
		}
		
	}
?>
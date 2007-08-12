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
			$emptyValues = array(null, '', false, 0, '  ', "\n");
			
			foreach ($emptyValues as $value) {
				$this->assertEqual(null, $filter->apply($value));
			}
		}
	}
?>
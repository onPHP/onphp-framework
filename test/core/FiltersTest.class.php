<?php
	/* $Id$ */
	
	final class FiltersTest extends TestCase
	{
		public function testTrim()
		{
			$filter = TrimFilter::create();
			$text = ' qq ';
			
			$this->assertEquals(
				$filter->apply($text),
				'qq'
			);
			
			$this->assertEquals(
				$filter->setLeft()->apply($text),
				'qq '
			);
			
			$this->assertEquals(
				$filter->setRight()->apply($text),
				' qq'
			);
			
			$this->assertEquals(
				$filter->setBoth()->apply($text),
				'qq'
			);
		}
		
		public function testUu()
		{
			$text = 'foo und bar';
			
			$this->assertEquals(
				Filter::uudecode()->apply(Filter::uuencode()->apply($text)),
				$text
			);
		}
		
		public function testNewLines()
		{
			$this->assertEquals(
				Filter::nl2br()->apply("strange\nthings\nhappens"),
				"strange<br />\nthings<br />\nhappens"
			);
		}
		
		public function testRussianTypograf()
		{
			$filter = RussianTypograph::me();
			$emptyValues = array(null, '', false, 0, '  ', "\n");
			
			foreach ($emptyValues as $value) {
				$this->assertEquals(null, $filter->apply($value));
			}
			
			$this->assertEquals(
				$filter->apply(' 1/4'),
				'&frac14;'
			);
			
			$this->assertEquals(
				$filter->apply('1/2 '),
				'&frac12;'
			);
			
			$this->assertEquals(
				$filter->apply(' 3/4'),
				'&frac34;'
			);
			
			$this->assertEquals(
				$filter->apply(' 1/4 1/2 3/4 '),
				'&frac14; &frac12; &frac34;'
			);
			
			$link = '<a href="http://site.ru/21/43/41/21">http://test/21/43/41/21</a>';
			$this->assertEquals(
				$filter->apply($link),
				$link
			);
			
			$link = '<a href="http://site.ru/1/4/3/4/1/2">http://test/1/4/3/4/1/2</a>';
			$this->assertEquals(
				$filter->apply($link),
				$link
			);
			
			$img = '<img src="http://site.ru/img.gif" />';
			$this->assertEquals(
				$filter->apply($img),
				$img
			);
			
			$this->assertEquals(
				$filter->apply('арбайтен   ---   gut'),
				'арбайтен&nbsp;&#151; gut'
			);
			
			$this->assertEquals(
				$filter->apply('р  ра  раз,д  дв   два   три'),
				'р&nbsp;ра&nbsp;раз,д&nbsp;дв&nbsp;два три'
			);
			
			$this->assertEquals(
				$filter->apply('рок\'н\'ролл'),
				'рок&#146;н&#146;ролл'
			);
			
			$this->assertEquals(
				$filter->apply(
					'Работает   и   с   "unicode-строками (\'utf-8\')"'
					.' - не только с ansi, и это радует'
				),
				'Работает и&nbsp;с&nbsp;&laquo;unicode-строками (&#146;utf-8&#146;)&raquo;'
				.'&nbsp;&#151; не&nbsp;только с&nbsp;ansi, и&nbsp;это радует'
			);
			
			$this->assertEquals(
				$filter->apply('""text" text"'),
				'&laquo;&#132;text&#147; text&raquo;'
			);
		}
		
		public function testCrop()
		{
			$this->assertEquals(
				CropFilter::create()->
				setLength(128)->
				apply(urldecode('Mozilla/5.0 (SymbianOS/9.2; U; Series60/3.1 Nokia6120c/4.21; Profile/MIDP-2.0 Configuration/CLDC-1.1 ) AppleWebKit/413 (KHTML, like Gecko) Safari/413')),
				'Mozilla/5.0 (SymbianOS/9.2; U; Series60/3.1 Nokia6120c/4.21; Profile/MIDP-2.0 Configuration/CLDC-1.1 ) AppleWebKit/413 (KHTML, l'
			);
		}
		
		public function testCallChain()
		{
			$chain =
				CallChain::create()->
				add(Filter::nl2br())->
				add(Filter::htmlSpecialChars());
			
			$text = "foo\nbar&";
			
			$this->assertEquals(
				$chain->call('apply', $text),
				$chain->apply($text)
			);
			
			try {
				$chain->undefinedMethod();
			} catch (BaseException $e) {
				// passed
			}
			
			try {
				CallChain::create()->call('foo', 'bar');
			} catch (WrongStateException $e) {
				// passed
			}
			
			try {
				CallChain::create()->add(
					IdentifiableObject::wrap(112233)
				)->
				undefinedMethod();
			} catch (BaseException $e) {
				// passed;
			}
			
			try {
				CallChain::create()->add(
					IdentifiableObject::wrap(332211)
				)->
				call('undefinedMethod');
			} catch (BaseException $e) {
				// passed;
			}
		}
		
		public function testJsonFilters()
		{
			if (!function_exists('json_encode'))
				$this->markTestSkipped(
					'dude, you should compile php with json support'
					.' in order to complete this test'
				);

			$data = array('the keeey' => "that's value");
			
			$encoded = JsonEncoderFilter::me()->apply($data);
			
			$decoded = JsonDecoderFilter::me()->setAssoc()->apply($encoded);
			
			$this->assertEquals($decoded, $data);
		}
	}
?>
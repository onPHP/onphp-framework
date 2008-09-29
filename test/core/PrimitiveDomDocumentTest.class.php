<?php
	/* $Id$ */
	
	final class PrimitiveDomDocumentTest extends TestCase
	{
		private $schema = null;
		
		public function __construct()
		{
			$this->schema =
				ONPHP_TEST_PATH.'misc'.DIRECTORY_SEPARATOR
				.'text.xsd';
		}
		
		public function testGoodDocument()
		{
			$raw = <<<EOF
<body>
	<p>
	Hello! <a href="http://example.com/">world!</a>
	</p>

	<p>
	<em>em em em</em>
	
	normal text <a href="urn:project:href?area=profile&amp;arg1=value1">profile link</a>

	<strong>strong strong</strong>
	</p>
</body>
EOF;
			
			$prm = $this->makePrimitive();
			
			$this->assertTrue($prm->importValue($raw));
			
			$this->assertNull($prm->getError());
			
			Assert::isInstance($prm->getValue(), 'DomDocument');
		}
		
		public function testInvalidDocument()
		{
			$raw = <<<EOF
<body>
	<p>
		<a href=http://example.org/test>href attribute without quotes</a>
	</p>
</body>
EOF;
			
			$prm = $this->makePrimitive();
			
			$this->assertFalse($prm->importValue($raw));
			
			$this->assertNull($prm->getValue());
		}

		public function testBadDocument()
		{
			$raw = <<<EOF
<body>
	<b>not allowed tag</b>
	<a href="http://example.org/"><em>tags are not allowed inside A</em></a>
	<br/>
	line breaks are not allowed, use paragraphs
</body>
EOF;
			
			$prm = $this->makePrimitive();
			
			$this->assertFalse($prm->importValue($raw));
			
			$this->assertNull($prm->getValue());
			
			$this->assertEquals(
				$prm->getError(),
				PrimitiveDomDocument::ERROR_VALIDATION_FAILED
			);
		}
		
		public function testFormErrors()
		{
			$raw = '<body><b>not allowed tag</b></body>';
			
			$form = Form::create()->
				add($this->makePrimitive());
				
			$form->import(array('dom' => $raw));
			
			$this->assertNotNull($form->getErrors());
			
			$this->assertNull($form->getValue('dom'));
			
			$this->assertEquals(
				$form->getPrimitiveError('dom'),
				BasePrimitive::WRONG
			);
			
			$this->assertNotNull($form->get('dom')->getErrorLabel());
			
			$this->assertEquals($form->getTextualErrors(), array());
			$this->assertNull($form->getTextualErrorFor('dom'));
		}
		
		
		public function testInvalidSchemaPath()
		{
			$this->setExpectedException('IOException');
			
			Primitive::domDocument('dom')->
			ofXsd('http://nonexistenturl/');
		}
		
		public function testBadSchemaFormat()
		{
			try {
				$prm =
					Primitive::domDocument('dom')->
					ofXsd('http://example.com/');
			} catch (IOException $e) {
				return $this->markTestSkipped('no network available');
			}
			
			$this->assertFalse($prm->importValue('<body />'));
			
			$this->assertNull($prm->getValue());
			
			$this->assertEquals(
				$prm->getError(),
				PrimitiveDomDocument::ERROR_VALIDATION_FAILED
			);
		}
		
		private function makePrimitive()
		{
			return
				Primitive::domDocument('dom')->
				ofXsd($this->schema);
		}
	}
?>
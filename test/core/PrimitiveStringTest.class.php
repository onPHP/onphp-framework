<?php

	final class PrimitiveStringTest extends TestCase
	{
		public function testImport()
		{
			$prm = Primitive::string('name');
			
			$nullValues = array(null, '');
			
			foreach ($nullValues as $value)
				$this->assertNull($prm->importValue($value));

			$prm->clean();
			$falseValues = array(array(), true, false, $prm);
			
			foreach ($falseValues as $value)
				$this->assertFalse($prm->importValue($value));

			$prm->clean();
			$trueValues = array('some string', -100500, 2011.09);
			
			foreach ($trueValues as $value)
				$this->assertTrue($prm->importValue($value));


			$prm->setAllowedPattern(
				PrimitiveString::MAIL_PATTERN
			);

			$prm->clean();
			$trueValues = array('me@example.com', 'example@example.com', 'i.ivanov@example.com');

			foreach ($trueValues as $value)
				$this->assertTrue($prm->importValue($value));

			$prm->clean();
			$falseValues = array('example.com', 100500, 2012.04);

			foreach ($falseValues as $value)
				$this->assertFalse($prm->importValue($value));



		}
	}
?>
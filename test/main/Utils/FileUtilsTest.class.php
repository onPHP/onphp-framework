<?php
	namespace Onphp\Test;

	final class FileUtilsTest extends TestCase
	{
		public function testUniqueNames()
		{
			$uniqueName = \Onphp\FileUtils::makeUniqueName('Chasey Lain.jpeg');
			
			$this->assertEquals(
				substr($uniqueName, strpos($uniqueName, '.')),
				'.jpeg'
			);

			$uniqueName = \Onphp\FileUtils::makeUniqueName('Елена Беркова.jpeg');
			
			$this->assertEquals(
				substr($uniqueName, strpos($uniqueName, '.')),
				'.jpeg'
			);

			$uniqueName = \Onphp\FileUtils::makeUniqueLatinName('Елена Беркова.gif'); //animated gif ;)
			
			$this->assertEquals(
				substr($uniqueName, strpos($uniqueName, '.')),
				'.gif'
			);
		}
	}
?>
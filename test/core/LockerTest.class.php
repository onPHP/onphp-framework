<?php
	/* $Id$ */
	
	namespace Onphp\Test;

	final class LockerTest extends TestCase
	{
		public function testFileLocker()
		{
			$directory = ONPHP_TEMP_PATH.'file-locking/';
			
			$locker = new \Onphp\FileLocker('file-locking/');
			
			$this->assertTrue($locker->get('test'));
			
			$this->assertTrue(file_exists($directory.'test'));
			
			$this->assertTrue($locker->free('test'));
			
			$this->assertTrue(file_exists($directory.'test'));
			
			$this->assertTrue($locker->clean());
			
			$this->assertFalse(file_exists($directory.'test'));
		}
	}
?>
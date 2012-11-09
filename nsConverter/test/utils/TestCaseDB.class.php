<?php

	namespace Onphp\NsConverter\Test;
	
	abstract class TestCaseDB extends TestCase
	{
		public function setUp() {
			$this->setUpDB();
		}
		
		protected function setUpDB() {
			//implement me
		}
	}
?>
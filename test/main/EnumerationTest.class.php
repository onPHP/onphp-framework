<?php
	/* $Id$ */
	
	final class EnumerationTest extends UnitTestCase
	{
		public function testAnyId()
		{
			foreach (get_declared_classes() as $className) {
				if (is_subclass_of($className, 'Enumeration')) {
					try {
						$enum = new $className(
							call_user_func(
								array($className, 'getAnyId')
							)
						);
						
						$this->pass();
					} catch (MissingElementException $e) {
						$this->fail($className);
					}
				}
			}
		}
	}
?>
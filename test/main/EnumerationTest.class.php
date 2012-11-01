<?php
	/* $Id$ */
	
	namespace Onphp\Test;

	final class EnumerationTest extends TestCase
	{
		public function testAnyId()
		{
			foreach (get_declared_classes() as $className) {
				if (is_subclass_of($className, '\Onphp\Enumeration')) {
					try {
						$enum = new $className(
							call_user_func(
								array($className, 'getAnyId')
							)
						);
						
						/* pass */
					} catch (\Onphp\MissingElementException $e) {
						$this->fail($className);
					}
				} elseif(is_subclass_of($className, '\Onphp\Enum')) {
					try {
						$enum = new $className(
							\Onphp\ClassUtils::callStaticMethod($className.'::getAnyId')
						);

						/* pass */
					} catch (\Onphp\MissingElementException $e) {
						$this->fail($className);
					}
				}
			}
		}
	}
?>
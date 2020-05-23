<?php
	
namespace OnPHP\Tests\Main;

use OnPHP\Core\Base\Enumeration;
use OnPHP\Core\Exception\MissingElementException;
use OnPHP\Main\Util\ClassUtils;
use OnPHP\Tests\TestEnvironment\TestCase;
	
/**
 * @group main
 */
final class EnumerationTest extends TestCase
{
	/**
	* @doesNotPerformAssertions
	*/
	public function testAnyId()
	{
		foreach (get_declared_classes() as $className) {
			if (is_subclass_of($className, Enumeration::class)) {
				try {
					$enum = new $className(
						call_user_func(
							array($className, 'getAnyId')
						)
					);

					/* pass */
				} catch (MissingElementException $e) {
					$this->fail($className);
				}
			} elseif(is_subclass_of($className, 'Enum')) {
				try {
					$enum = new $className(
						ClassUtils::callStaticMethod($className.'::getAnyId')
					);

					/* pass */
				} catch (MissingElementException $e) {
					$this->fail($className);
				}
			}
		}
	}
}
?>
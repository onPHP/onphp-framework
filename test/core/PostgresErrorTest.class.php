<?php
	final class PostgresErrorTest extends TestCase
	{
		public function testCode()
		{
			$objectList = PostgresError::getList(
				new PostgresError(PostgresError::getAnyId())
			);
			
			foreach ($objectList as $object) {
				$code = $object->toCode();
				
				$this->assertEquals(
					PostgresError::getByCode($object->toCode())->getId(),
					$object->getId()
				);
				
				$this->assertTrue(
					PostgresError::checkCode($object->getId(), $code)
				);
			}
		}
	}
?>
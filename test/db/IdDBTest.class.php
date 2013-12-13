<?php
	namespace Onphp\Test;

	class IdDBTest extends TestCaseDAO
	{
		public function testGetByEmptyId()
		{
			foreach (DBTestPool::me()->getPool() as $db) {
				\Onphp\DBPool::me()->setDefault($db);
				$this->getByEmptyIdTest(0, true);
				$this->getByEmptyIdTest(null);
				$this->getByEmptyIdTest('');
				$this->getByEmptyIdTest('0', true);
				$this->getByEmptyIdTest(false);

				$empty = TestLazy::create();

				$this->assertNull($empty->getCity());
				$this->assertNull($empty->getCityOptional());
				$this->assertNull($empty->getEnum());
				$this->assertNull($empty->getStaticEnum());
			}
		}

		public function testStringIdentifier()
		{
			$identifier =
				TestStringIdentifier::proto()->getPropertyByName('id');

			$this->assertEquals($identifier->getType(), 'scalarIdentifier');

			$identifier =
				TestStringIdentifierRelated::proto()->getPropertyByName('test');

			$this->assertEquals($identifier->getType(), 'scalarIdentifier');
		}
		
		private function getByEmptyIdTest($id, $isCorrect = false)
		{
			try {
				TestUser::dao()->getById($id);
				$this->fail('expects to get exception');
			} catch (\Onphp\WrongArgumentException $e) {
				if ($isCorrect) {
					$this->fail('expects to get ObjectNotFoundException');
				}
			} catch (\Onphp\ObjectNotFoundException $e) {
				if (!$isCorrect) {
					$this->fail('expects to get WrongArgumentException');
				}
			}
		}
	}
?>
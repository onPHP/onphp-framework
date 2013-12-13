<?php
	namespace Onphp\Test;

	class DataDBTest extends TestCaseDAO
	{
		public function testData()
		{
			foreach (DBTestPool::me()->iterator() as $db) {
				$this->getDBCreator()->fillDB($this);
				
				$this->getSome(); // 41!
				\Onphp\Cache::me()->clean();
				$this->getSome();
				
				$this->nonIntegerIdentifier();
				
				$this->racySave();
				$this->binaryTest();
				$this->lazyTest();
			}
		}
		
		public function testBoolean()
		{
			foreach (DBTestPool::me()->iterator() as $db) {
				//creating moscow
				$moscow = TestCity::create()->setName('Moscow');
				$moscow = $moscow->dao()->add($moscow);
				$moscowId = $moscow->getId();
				/* @var $moscow \Onphp\Test\TestCity */
				
				//now moscow capital
				$moscow->dao()->merge($moscow->setCapital(true));
				TestCity::dao()->dropIdentityMap();
				
				\Onphp\Criteria::create(TestCity::dao())->
					setSilent(false)->
					add(\Onphp\Expression::isTrue('capital'))->
					get();
				TestCity::dao()->dropIdentityMap();
				
				$moscow = \Onphp\Criteria::create(TestCity::dao())->
					setSilent(false)->
					add(\Onphp\Expression::isNull('large'))->
					get();
				TestCity::dao()->dropIdentityMap();
				
				//now moscow large
				$moscow = $moscow->dao()->merge($moscow->setLarge(true));
				
				TestCity::dao()->dropIdentityMap();
				$moscow = TestCity::dao()->getById($moscowId);
				$this->assertTrue($moscow->getCapital());
				$this->assertTrue($moscow->getLarge());
				
				\Onphp\Criteria::create(TestCity::dao())->
					setSilent(false)->
					add(\Onphp\Expression::not(\Onphp\Expression::isFalse('large')))->
					get();
				TestCity::dao()->dropIdentityMap();
			}
		}
		
		/**
		 * this method used in DBTestCreator::fill
		 */
		public function getListByIdsTest()
		{
			$first = TestUser::dao()->getById(1);
			
			TestUser::dao()->dropIdentityMap();
			
			$list = TestUser::dao()->getListByIds(array(1, 3, 2, 1, 1, 1));
			
			$this->assertEquals(count($list), 5);
			
			$this->assertEquals($list[0]->getId(), 1);
			$this->assertEquals($list[1]->getId(), 2);
			$this->assertEquals($list[2]->getId(), 1);
			$this->assertEquals($list[3]->getId(), 1);
			$this->assertEquals($list[4]->getId(), 1);
			
			$this->assertEquals($list[0], $first);
			
			$this->assertEquals(
				array(),
				TestUser::dao()->getListByIds(array(42, 42, 1738))
			);
		}
		
		private function getSome()
		{
			for ($i = 1; $i < 3; ++$i) {
				$this->assertTrue(
					TestUser::dao()->getByLogic(
						\Onphp\Expression::eq('city_id', $i)
					)
					== TestUser::dao()->getById($i)
				);
			}
			
			$this->assertEquals(
				count(TestUser::dao()->getPlainList()),
				count(TestCity::dao()->getPlainList())
			);
		}

		private function nonIntegerIdentifier()
		{
			$id = 'non-integer-one';
			$binaryData = "\0!bbq!\0";
			
			$bin =
				TestBinaryStuff::create()->
				setId($id)->
				setData($binaryData);
			
			try {
				TestBinaryStuff::dao()->import($bin);
			} catch (\Onphp\DatabaseException $e) {
				return $this->fail($e->getMessage());
			}
			
			TestBinaryStuff::dao()->dropIdentityMap();
			\Onphp\Cache::me()->clean();
			
			$prm = \Onphp\Primitive::prototypedIdentifier('\Onphp\Test\TestBinaryStuff', 'id');
			
			$this->assertTrue($prm->import(array('id' => $id)));
			$this->assertSame($prm->getValue()->getId(), $id);
			
			$binLoaded = TestBinaryStuff::dao()->getById($id);
			$this->assertEquals($binLoaded, $bin);
			$this->assertEquals($binLoaded->getData(), $binaryData);
			$this->assertEquals(TestBinaryStuff::dao()->dropById($id), 1);
			
			$integerIdPrimitive = \Onphp\Primitive::prototypedIdentifier('\Onphp\Test\TestUser');
			try {
				$integerIdPrimitive->import(array('id' => 'string-instead-of-integer'));
			} catch (\Onphp\DatabaseException $e) {
				return $this->fail($e->getMessage());
			}
		}
		
		private function racySave()
		{
			$lost =
				TestCity::create()->
				setId(424242)->
				setName('inexistant city');
			
			try {
				TestCity::dao()->save($lost);
				
				$this->fail();
			} catch (\Onphp\WrongStateException $e) {
				/* pass */
			}
		}
		
		private function binaryTest()
		{
			$data = null;
			
			for ($i = 0; $i < 256; ++$i)
				$data .= chr($i);
			
			$id = sha1('all sessions are evil');
			
			$stuff =
				TestBinaryStuff::create()->
				setId($id)->
				setData($data);
			
			$stuff = $stuff->dao()->import($stuff);
			
			\Onphp\Cache::me()->clean();
			
			$this->assertEquals(
				TestBinaryStuff::dao()->getById($id)->getData(),
				$data
			);
			
			TestBinaryStuff::dao()->dropById($id);
		}
		
		private function lazyTest()
		{
			$city = TestCity::dao()->getById(1);
			
			$object = TestLazy::dao()->add(
				TestLazy::create()->
					setCity($city)->
					setCityOptional($city)->
					setEnum(
						new \Onphp\ImageType(\Onphp\ImageType::getAnyId())
					)->setStaticEnum(
						new \Onphp\MimeType(\Onphp\MimeType::getAnyId())
					)
			);
			
			\Onphp\Cache::me()->clean();
			
			$form = TestLazy::proto()->makeForm();
			$form->import(
				array('id' => $object->getId())
			);
			
			$this->assertNotNull($form->getValue('id'));
			
			\Onphp\FormUtils::object2form($object, $form);
			
			foreach ($object->proto()->getPropertyList() as $name => $property) {
				if (
					$property->getRelationId() == \Onphp\MetaRelation::ONE_TO_ONE
					&& $property->getFetchStrategyId() == \Onphp\FetchStrategy::LAZY
				) {
					$this->assertEquals(
						$object->{$property->getGetter()}(),
						$form->getValue($name)
					);
				}
			}
		}
	}
?>
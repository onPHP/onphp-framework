<?php
	/* $Id$ */
	
	class DAOTest extends TestTables
	{
		public function create()
		{
			/**
			 * @see testRecursionObjects() and meta
			 * for TestParentObject and TestChildObject
			**/
			$this->schema->
				getTableByName('test_parent_object')->
				getColumnByName('root_id')->
				dropReference();
			
			return parent::create();
		}
		
		public function testSchema()
		{
			return $this->create()->drop();
		}
		
		public function testData()
		{
			$this->create();
			
			foreach (DBTestPool::me()->getPool() as $connector => $db) {
				DBPool::me()->setDefault($db);
				$this->fill();
				
				$this->getSome(); // 41!
				Cache::me()->clean();
				$this->getSome();
				
				$this->nonIntegerIdentifier();
				
				$this->racySave();
				$this->binaryTest();
				$this->lazyTest();
			}
			
			$this->drop();
		}
		
		public function testUnified()
		{
			$this->create();
			
			foreach (DBTestPool::me()->getPool() as $connector => $db) {
				DBPool::me()->setDefault($db);
				$this->fill();
				
				$this->unified();
				
				Cache::me()->clean();
			}
			
			$this->deletedCount();
			
			$this->drop();
		}
		
		public function testCount()
		{
			$this->create();
			
			foreach (DBTestPool::me()->getPool() as $connector => $db) {
				DBPool::me()->setDefault($db);
				
				$this->fill();
				
				$count = TestUser::dao()->getTotalCount();
				
				$this->assertGreaterThan(1, $count);
				
				$city =
					TestCity::create()->
					setId(1);
				
				$newUser =
					TestUser::create()->
					setCity($city)->
					setCredentials(
						Credentials::create()->
						setNickname('newuser')->
						setPassword(sha1('newuser'))
					)->
					setLastLogin(
						Timestamp::create(time())
					)->
					setRegistered(
						Timestamp::create(time())
					);
				
				TestUser::dao()->add($newUser);
				
				$newCount = TestUser::dao()->getTotalCount();
				
				$this->assertEquals($count + 1, $newCount);
			}
			
			$this->drop();
		}
		
		public function testGetByEmptyId()
		{
			$this->create();
			
			$this->getByEmptyIdTest(0);
			$this->getByEmptyIdTest(null);
			$this->getByEmptyIdTest('');
			$this->getByEmptyIdTest('0');
			$this->getByEmptyIdTest(false);
			
			$empty = TestLazy::create();
			
			$this->assertNull($empty->getCity());
			$this->assertNull($empty->getCityOptional());
			$this->assertNull($empty->getEnum());
			
			$this->drop();
		}
		
		public function deletedCount()
		{
			TestUser::dao()->dropById(1);
			
			try {
				TestUser::dao()->dropByIds(array(1, 2));
				$this->fail();
			} catch (WrongStateException $e) {
				// ok
			}
		}
		
		public function fill($assertions = true)
		{
			$moscow =
				TestCity::create()->
				setName('Moscow');
			
			$piter =
				TestCity::create()->
				setName('Saint-Peterburg');
			
			$mysqler =
				TestUser::create()->
				setCity($moscow)->
				setCredentials(
					Credentials::create()->
					setNickname('mysqler')->
					setPassword(sha1('mysqler'))
				)->
				setLastLogin(
					Timestamp::create(time())
				)->
				setRegistered(
					Timestamp::create(time())->modify('-1 day')
				);
			
			$postgreser = clone $mysqler;
			
			$postgreser->
				setCredentials(
					Credentials::create()->
					setNickName('postgreser')->
					setPassword(sha1('postgreser'))
				)->
				setCity($piter)->
				setUrl(HttpUrl::create()->parse('http://postgresql.org/'));
			
			$piter = TestCity::dao()->add($piter);
			$moscow = TestCity::dao()->add($moscow);
			
			if ($assertions) {
				$this->assertEquals($piter->getId(), 1);
				$this->assertEquals($moscow->getId(), 2);
			}
			
			$postgreser = TestUser::dao()->add($postgreser);
			
			for ($i = 0; $i < 10; $i++) {
				$encapsulant = TestEncapsulant::dao()->add(
					TestEncapsulant::create()->
					setName($i)
				);
				
				$encapsulant->getCities()->
					fetch()->
					setList(
						array($piter, $moscow)
					)->
					save();
			}
			
			$mysqler = TestUser::dao()->add($mysqler);
			
			if ($assertions) {
				$this->assertEquals($postgreser->getId(), 1);
				$this->assertEquals($mysqler->getId(), 2);
			}
			
			if ($assertions) {
				// put them in cache now
				TestUser::dao()->dropIdentityMap();
				
				TestUser::dao()->getById(1);
				TestUser::dao()->getById(2);
				
				$this->getListByIdsTest();
				
				Cache::me()->clean();
				
				$this->assertTrue(
					($postgreser == TestUser::dao()->getById(1))
				);
				
				$this->assertTrue(
					($mysqler == TestUser::dao()->getById(2))
				);
			}
			
			$firstClone = clone $postgreser;
			$secondClone = clone $mysqler;
			
			$firstCount = TestUser::dao()->dropById($postgreser->getId());
			$secondCount = TestUser::dao()->dropByIds(array($mysqler->getId()));
			
			if ($assertions) {
				$this->assertEquals($firstCount, 1);
				$this->assertEquals($secondCount, 1);
				
				try {
					TestUser::dao()->getById(1);
					$this->fail();
				} catch (ObjectNotFoundException $e) {
					/* pass */
				}
				
				$result =
					Criteria::create(TestUser::dao())->
					add(Expression::eq(1, 2))->
					getResult();
				
				$this->assertEquals($result->getCount(), 0);
				$this->assertEquals($result->getList(), array());
			}
			
			TestUser::dao()->import($firstClone);
			TestUser::dao()->import($secondClone);
			
			if ($assertions) {
				// cache multi-get
				$this->getListByIdsTest();
				$this->getListByIdsTest();
			}
		}
		
		public function unified()
		{
			$user = TestUser::dao()->getById(1);
			
			$encapsulant = TestEncapsulant::dao()->getPlainList();
			
			$collectionDao = $user->getEncapsulants();
			
			$collectionDao->fetch()->setList($encapsulant);
			
			$collectionDao->save();
			
			unset($collectionDao);
			
			// fetch
			$encapsulantsList = $user->getEncapsulants()->getList();
			
			$piter = TestCity::dao()->getById(1);
			$moscow = TestCity::dao()->getById(2);
			
			for ($i = 0; $i < 10; $i++) {
				$this->assertEquals($encapsulantsList[$i]->getId(), $i + 1);
				$this->assertEquals($encapsulantsList[$i]->getName(), $i);
				
				$cityList = $encapsulantsList[$i]->getCities()->getList();
				
				$this->assertEquals($cityList[0], $piter);
				$this->assertEquals($cityList[1], $moscow);
			}
			
			unset($encapsulantsList);
			
			// lazy fetch
			$encapsulantsList = $user->getEncapsulants(true)->getList();
			
			for ($i = 1; $i < 11; $i++)
				$this->assertEquals($encapsulantsList[$i], $i);
			
			// count
			$user->getEncapsulants()->clean();
			
			$this->assertEquals($user->getEncapsulants()->getCount(), 10);
			
			$criteria = Criteria::create(TestEncapsulant::dao())->
				add(
					Expression::in(
						'cities.id',
						array($piter->getId(), $moscow->getId())
					)
				);
			
			$user->getEncapsulants()->setCriteria($criteria);
			
			$this->assertEquals($user->getEncapsulants()->getCount(), 20);
			
			// distinct count
			$user->getEncapsulants()->clean();
			
			$user->getEncapsulants()->setCriteria(
				$criteria->
					setDistinct(true)
			);
			
			if (DBPool::me()->getLink() instanceof SQLite)
				// TODO: sqlite does not support such queries yet
				return null;
			
			$this->assertEquals($user->getEncapsulants()->getCount(), 10);
		}
		
		public function testWorkingWithCache()
		{
			$this->create();
			
			foreach (DBTestPool::me()->getPool() as $connector => $db) {
				DBPool::me()->setDefault($db);
				
				$item =
					TestItem::create()->
					setName('testItem1');
				
				TestItem::dao()->add($item);
				
				$encapsulant =
					TestEncapsulant::create()->
					setName('testEncapsulant1');
				
				TestEncapsulant::dao()->add($encapsulant);
				
				$subItem1 =
					TestSubItem::create()->
					setName('testSubItem1')->
					setEncapsulant($encapsulant)->
					setItem($item);
				
				$subItem2 =
					TestSubItem::create()->
					setName('testSubItem2')->
					setEncapsulant($encapsulant)->
					setItem($item);
				
				TestSubItem::dao()->add($subItem1);
				TestSubItem::dao()->add($subItem2);
				
				$items =
					Criteria::create(TestItem::dao())->
					getList();
				
				foreach ($items as $item) {
					foreach ($item->getSubItems()->getList() as $subItem) {
						$this->assertEquals(
							$subItem->getEncapsulant()->getName(),
							'testEncapsulant1'
						);
					}
				}
				
				$encapsulant = TestEncapsulant::dao()->getById(1);
				
				$encapsulant->setName('testEncapsulant1_changed');
				
				TestEncapsulant::dao()->save($encapsulant);
				
				// drop identityMap
				TestEncapsulant::dao()->dropIdentityMap();
				TestSubItem::dao()->dropIdentityMap();
				TestItem::dao()->dropIdentityMap();
				
				$items =
					Criteria::create(TestItem::dao())->
					getList();
				
				foreach ($items as $item) {
					foreach ($item->getSubItems()->getList() as $subItem) {
						$this->assertEquals(
							$subItem->getEncapsulant()->getName(),
							'testEncapsulant1_changed'
						);
					}
				}
				
				// drop identityMap
				TestEncapsulant::dao()->dropIdentityMap();
				TestSubItem::dao()->dropIdentityMap();
				TestItem::dao()->dropIdentityMap();
				
				$subItem = TestSubItem::dao()->getById(1);
				
				$this->assertEquals(
					$subItem->getEncapsulant()->getName(),
					'testEncapsulant1_changed'
				);
				
				// drop identityMap
				TestEncapsulant::dao()->dropIdentityMap();
				TestSubItem::dao()->dropIdentityMap();
				TestItem::dao()->dropIdentityMap();
				
				$subItems =
					Criteria::create(TestSubItem::dao())->
					getList();
				
				foreach ($subItems as $subItem) {
					$this->assertEquals(
						$subItem->getEncapsulant()->getName(),
						'testEncapsulant1_changed'
					);
				}
			}
			
			$this->drop();
		}
		
		/**
		 * Install hstore
		 * /usr/share/postgresql/contrib # cat hstore.sql | psql -U pgsql -d onphp
		**/
		public function testHstore()
		{
			$this->create();
			
			$properties = array(
				'age' => '23',
				'weight' => 80,
				'comment' => null,
			);
			
			$user =
				TestUser::create()->
				setCity(
					$moscow = TestCity::create()->
					setName('Moscow')
				)->
				setCredentials(
					Credentials::create()->
					setNickname('fake')->
					setPassword(sha1('passwd'))
				)->
				setLastLogin(
					Timestamp::create(time())
				)->
				setRegistered(
					Timestamp::create(time())->modify('-1 day')
				)->
				setProperties(Hstore::make($properties));
			
			$moscow = TestCity::dao()->add($moscow);
			
			$user = TestUser::dao()->add($user);
			
			Cache::me()->clean();
			TestUser::dao()->dropIdentityMap();
			
			$user = TestUser::dao()->getById('1');
			
			$this->assertType('Hstore', $user->getProperties());
			
			$this->assertEquals(
				$properties,
				$user->getProperties()->getList()
			);
			
			
			$form = TestUser::proto()->makeForm();
			
			$form->get('properties')->
				setFormMapping(
					array(
						Primitive::string('age'),
						Primitive::integer('weight'),
						Primitive::string('comment'),
					)
				);
			
			$form->import(
				array('id' => $user->getId())
			);
			
			$this->assertNotNull($form->getValue('id'));
			
			$object = $user;
			
			FormUtils::object2form($object, $form);
			
			$this->assertType('Hstore', $form->getValue('properties'));
			
			$this->assertEquals(
				array_filter($properties),
				$form->getValue('properties')->getList()
			);
			
			$subform = $form->get('properties')->getInnerForm();
			
			$this->assertEquals(
				$subform->getValue('age'),
				'23'
			);
			
			$this->assertEquals(
				$subform->getValue('weight'),
				80
			);
			
			$this->assertNull(
				$subform->getValue('comment')
			);
			
			$user = new TestUser();
			
			FormUtils::form2object($form, $user, false);
			
			$this->assertEquals(
				$user->getProperties()->getList(),
				array_filter($properties)
			);
			
			$this->drop();
		}
		
		/**
		 * @see http://lists.shadanakar.org/onphp-dev-ru/0811/0774.html
		**/
		public function testRecursiveContainers()
		{
			$this->markTestSkipped('wontfix');
			
			$this->create();
			
			TestObject::dao()->import(
				TestObject::create()->
				setId(1)->
				setName('test object')
			);
			
			TestType::dao()->import(
				TestType::create()->
				setId(1)->
				setName('test type')
			);
			
			$type = TestType::dao()->getById(1);
			
			$type->getObjects()->fetch()->setList(
				array(TestObject::dao()->getById(1))
			)->
			save();
			
			$object = TestObject::dao()->getById(1);
			
			TestObject::dao()->save($object->setName('test object modified'));
			
			$list = $type->getObjects()->getList();
			
			$modifiedObject = TestObject::dao()->getById(1);
			
			$this->assertEquals($list[0], $modifiedObject);
			
			$this->drop();
		}
		
		public function testRecursionObjects()
		{
			$this->create();

			$parentProperties =
				Singleton::getInstance('ProtoTestParentObject')->
				getPropertyList();

			$resultRoot = $parentProperties['root']->
				getFetchStrategyId() == FetchStrategy::LAZY;

			$childProperties =
				Singleton::getInstance('ProtoTestChildObject')->
				getPropertyList();

			$resultParent = $childProperties['parent']->
				getFetchStrategyId() == FetchStrategy::LAZY;

			$selfRecursiveProperties =
				Singleton::getInstance('ProtoTestSelfRecursion')->
				getPropertyList();

			$resultSelfRecursive = $selfRecursiveProperties['parent']->
				getFetchStrategyId() == FetchStrategy::LAZY;

			$this->drop();

			$this->assertTrue($resultRoot);
			$this->assertTrue($resultParent);
			$this->assertTrue($resultSelfRecursive);
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

		public function nonIntegerIdentifier()
		{
			$id = 'non-integer-one';
			
			$bin =
				TestBinaryStuff::create()->
				setId($id)->
				setData("\0!bbq!\0");
			
			try {
				TestBinaryStuff::dao()->import($bin);
			} catch (DatabaseException $e) {
				return $this->fail();
			}
			
			Cache::me()->clean();
			
			$prm = Primitive::prototypedIdentifier('TestBinaryStuff', 'id');
			
			$this->assertTrue($prm->import(array('id' => $id)));
			$this->assertSame($prm->getValue()->getId(), $id);
			
			$this->assertEquals(TestBinaryStuff::dao()->getById($id), $bin);
			$this->assertEquals(TestBinaryStuff::dao()->dropById($id), 1);
			
			$id = Primitive::prototypedIdentifier('TestUser');
			
			try {
				$id->import(array('id' => 'string-instead-of-integer'));
			} catch (DatabaseException $e) {
				return $this->fail();
			}
		}
		
		protected function getSome()
		{
			for ($i = 1; $i < 3; ++$i) {
				$this->assertTrue(
					TestUser::dao()->getByLogic(
						Expression::eq('city_id', $i)
					)
					== TestUser::dao()->getById($i)
				);
			}
			
			$this->assertEquals(
				count(TestUser::dao()->getPlainList()),
				count(TestCity::dao()->getPlainList())
			);
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
			} catch (WrongStateException $e) {
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
			
			Cache::me()->clean();
			
			$this->assertEquals(
				TestBinaryStuff::dao()->getById($id)->getData(),
				$data
			);
			
			TestBinaryStuff::dao()->dropById($id);
		}
		
		private function getListByIdsTest()
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
		
		private function lazyTest()
		{
			$city = TestCity::dao()->getById(1);
			
			$object = TestLazy::dao()->add(
				TestLazy::create()->
					setCity($city)->
					setCityOptional($city)->
					setEnum(
						new ImageType(ImageType::getAnyId())
					)
			);
			
			Cache::me()->clean();
			
			$form = TestLazy::proto()->makeForm();
			$form->import(
				array('id' => $object->getId())
			);
			
			$this->assertNotNull($form->getValue('id'));
			
			FormUtils::object2form($object, $form);
			
			foreach ($object->proto()->getPropertyList() as $name => $property) {
				if (
					$property->getRelationId() == MetaRelation::ONE_TO_ONE
					&& $property->getFetchStrategyId() == FetchStrategy::LAZY
				) {
					$this->assertEquals(
						$object->{$property->getGetter()}(),
						$form->getValue($name)
					);
				}
			}
		}
		
		private function getByEmptyIdTest($id)
		{
			try {
				TestUser::dao()->getById($id);
				$this->fail();
			} catch (WrongArgumentException $e) {
				// pass
			}
		}
	}
?>
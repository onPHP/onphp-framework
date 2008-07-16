<?php
	/* $Id$ */
	
	class DAOTest extends TestTables
	{
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
				
				$this->racySave();
				$this->binaryTest();
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
				setCity($piter);
			
			$piter = TestCity::dao()->add($piter);
			$moscow = TestCity::dao()->add($moscow);
			
			if ($assertions) {
				$this->assertEquals($piter->getId(), 1);
				$this->assertEquals($moscow->getId(), 2);
			}
			
			$postgreser = TestUser::dao()->add($postgreser);
			
			for ($i = 0; $i < 10; $i++) {
				TestIncapsulant::dao()->add(
					TestIncapsulant::create()->
					setName($i)
				);
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
			
			$incapsulant = TestIncapsulant::dao()->getPlainList();
			
			$collectionDao = $user->getIncapsulants();
			
			$collectionDao->fetch()->setList($incapsulant);
			
			$collectionDao->save();
			
			unset($collectionDao);
			
			// fetch
			$incapsulantsList = $user->getIncapsulants()->getList();
			
			for ($i = 0; $i < 10; $i++) {
				$this->assertEquals($incapsulantsList[$i]->getId(), $i + 1);
				$this->assertEquals($incapsulantsList[$i]->getName(), $i);
			}
			
			unset($incapsulantsList);
			
			// lazy fetch
			$incapsulantsList = $user->getIncapsulants(true)->getList();
			
			for ($i = 1; $i < 11; $i++)
				$this->assertEquals($incapsulantsList[$i], $i);
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
	}
?>
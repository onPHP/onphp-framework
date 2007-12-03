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
			}
			
			$this->drop();
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
				$this->assertEqual($piter->getId(), 1);
				$this->assertEqual($moscow->getId(), 2);
			}
			
			$postgreser = TestUser::dao()->add($postgreser);
			$mysqler = TestUser::dao()->add($mysqler);
			
			if ($assertions) {
				$this->assertEqual($postgreser->getId(), 1);
				$this->assertEqual($mysqler->getId(), 2);
			}
			
			if ($assertions) {
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
			
			TestUser::dao()->dropById($postgreser->getId());
			TestUser::dao()->dropByIds(array($mysqler->getId()));
			
			if ($assertions) {
				try {
					TestUser::dao()->getById(1);
					$this->fail();
				} catch (ObjectNotFoundException $e) {
					$this->pass();
				}
			}
			
			TestUser::dao()->import($firstClone);
			TestUser::dao()->import($secondClone);
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
			
			$this->assertEqual(
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
				$this->pass();
			}
		}
	}
?>
<?php
	/* $Id$ */
	
	class DAOTest extends TestTables
	{
		public function testSchema()
		{
			$this->create();
			$this->drop();
		}
		
		public function testData()
		{
			$this->create();
			
			foreach (DBTestPool::me()->getPool() as $connector => $db) {
				DBFactory::setDefaultInstance($db);
				$this->fill();
			}
			
			$this->drop();
		}
		
		protected function fill()
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
				setNickname('mysqler')->
				setPassword(sha1('mysqler'))->
				setLastLogin(
					Timestamp::create(time())
				)->
				setRegistered(
					Timestamp::create(time())->modify('-1 day')
				);
			
			$postgreser = clone $mysqler;
			
			$postgreser->
				setNickName('postgreser')->
				setPassword(sha1('postgreser'))->
				setCity($piter);
			
			$piter = TestCity::dao()->add($piter);
			$moscow = TestCity::dao()->add($moscow);
			
			$this->assertEqual($piter->getId(), 1);
			$this->assertEqual($moscow->getId(), 2);
			
//			$postgreser = TestUser::dao()->add($postgreser);
//			$mysqler = TestUser::dao()->add($mysqler);
			
//			$this->assertEqual($postgreser->getId(), 1);
//			$this->assertEqual($mysqler->getId(), 2);
		}
	}
?>
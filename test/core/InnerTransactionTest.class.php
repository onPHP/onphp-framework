<?php
	class InnerTransactionTest extends TestCase
	{
		public function testCommitExt()
		{
			//setup
			$db = $this->spawnDb(array(
				'begin' => 1,
				'commit' => 1,
			));
			
			//execute
			$transaction = InnerTransaction::begin($db);
			$transaction->commit();
			
			//test Exception on second commit
			try {
				$transaction->commit();
				$this->fail('expecting exception on second transaction commit');
			} catch (WrongStateException $e) {
				/* all ok */
			}
		}
		
		public function testRollbackExt()
		{
			//setup
			$db = $this->spawnDb(array(
				'begin' => 1,
				'rollback' => 1,
			));
			
			//execute
			$transaction = InnerTransaction::begin($db);
			$transaction->rollback();
			
			//test Exception on second commit
			try {
				$transaction->rollback();
				$this->fail('expecting exception on second transaction commit');
			} catch (WrongStateException $e) {
				/* all ok */
			}
		}
		
		public function testCommitInt()
		{
			//setup
			$db = $this->spawnDb(array(
				'queries' => array(
					'savepoint innerSavepoint',
					'release savepoint innerSavepoint',
				),
				'inTransaction' => true,
			));
			
			//execute
			$transaction = InnerTransaction::begin($db);
			$transaction->commit();
			
			//test Exception on second commit
			try {
				$transaction->rollback();
				$this->fail('expecting exception on second transaction commit');
			} catch (WrongStateException $e) {
				/* all ok */
			}
		}
		
		public function testRollbackInt()
		{
			//setup
			$db = $this->spawnDb(array(
				'queries' => array(
					'savepoint innerSavepoint',
					'rollback to savepoint innerSavepoint',
				),
				'inTransaction' => true,
			));
			
			//execute
			$transaction = InnerTransaction::begin($db);
			$transaction->rollback();
			
			//test Exception on second commit
			try {
				$transaction->commit();
				$this->fail('expecting exception on second transaction commit');
			} catch (WrongStateException $e) {
				/* all ok */
			}
		}
		
		public function testWrapCommit()
		{
			$db = $this->spawnDb(array(
				'begin' => 1,
				'commit' => 1,
			));
			
			$foo = 'foo';
			$bar = 'bar';
			$innerFunction = function($foo) use ($bar) {
				return $foo . $bar;
			};
			
			$wrapper = InnerTransactionWrapper::create()
				->setDB($db)
				->setFunction($innerFunction);
			
			$this->assertEquals($foo . $bar, $wrapper->run($foo));
		}
		
		public function testWrapRollbackByWrapException()
		{
			$db = $this->spawnDb(array(
				'begin' => 1,
				'rollback' => 1,
			));
			
			$foo = 'foo';
			$bar = 'bar';
			
			$wrapper = InnerTransactionWrapper::create()
				->setDB($db)
				->setFunction(array($this, 'wrapExceptionFunction'));
			
			$this->assertEquals($foo . $bar, $wrapper->run($foo, $bar));
		}
		
		public function testWrapRollbackByOtherException()
		{
			$db = $this->spawnDb(array(
				'begin' => 1,
				'rollback' => 1,
			));
			
			$exception = new DatabaseException('Some database exception');
			
			$function = function () use ($exception) {throw $exception;};
			
			$wrapper = InnerTransactionWrapper::create()
				->setDB($db)
				->setFunction($function);
			
			try {
				$wrapper->run();
			} catch (Exception $e) {
				$this->assertEquals($exception, $e);
			}
		}
		
		public function wrapExceptionFunction($foo, $bar)
		{
			throw InnerTransactionWrapperException::createValue($foo.$bar);
		}
		
		/**
		 * @param array $options 
		 * @return DB
		 */
		private function spawnDb($options = array())
		{
			$options += array(
				'begin' => 0,
				'commit' => 0,
				'rollback' => 0,
				'queries' => array(),
				'inTransaction' => false,
			);
			
			$mock = $this->getMock('DB');
			$mock->expects($this->exactly($options['begin']))->method('begin');
			$mock->expects($this->exactly($options['commit']))->method('commit');
			$mock->expects($this->exactly($options['rollback']))->method('rollback');
			$mock->expects($this->any())->method('inTransaction')->will($this->returnValue($options['inTransaction']));
			
			$this->applyQueryList($mock, $options['queries']);
			
			return $mock;
		}
		
		private function applyQueryList(
			PHPUnit_Framework_MockObject_MockObject $mock,
			$queryList
		)
		{
			$mock->expects($this->exactly(count($queryList)))->method('queryRaw');
			
			$self = $this;
			$i = 1;
			foreach ($queryList as $at => $expQuery) {
				$cb = function ($query) use ($self, $expQuery) {
					$self->assertStringStartsWith($expQuery, $query);
				};
				$mock->
					expects($this->at($i++))->
					method('queryRaw')->
					will($this->returnCallback($cb));
			}
		}
	}
?>
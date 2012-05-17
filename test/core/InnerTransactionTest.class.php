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
				'savepointBegin' => 1,
				'savepointRelease' => 1,
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
				'savepointBegin' => 1,
				'savepointRollback' => 1,
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
			
			$wrapper = InnerTransactionWrapper::create()->
				setDB($db)->
				setFunction($innerFunction);
			
			$this->assertEquals($foo . $bar, $wrapper->run($foo));
		}
		
		public function testWrapRollbackByException()
		{
			$db = $this->spawnDb(array(
				'begin' => 1,
				'rollback' => 1,
			));
			
			$foo = 'foo';
			$bar = 'bar';
			
			$catchExceptionFunc = function ($e, $foo, $bar) {
				return $e->getMessage().' '.$foo.$bar;
			};
			
			$wrapper = InnerTransactionWrapper::create()->
				setDB($db)->
				setFunction(array($this, 'wrapExceptionFunction'))->
				setExceptionFunction($catchExceptionFunc);
			
			$this->assertEquals('some unimplemented feature foobar', $wrapper->run($foo, $bar));
		}
		
		public function testWrapRollbackByOtherException()
		{
			$db = $this->spawnDb(array(
				'begin' => 1,
				'rollback' => 1,
			));
			
			$exception = new DatabaseException('Some database exception');
			
			$function = function () use ($exception) {throw $exception;};
			
			$wrapper = InnerTransactionWrapper::create()->
				setDB($db)->
				setFunction($function);
			
			try {
				$wrapper->run();
			} catch (Exception $e) {
				$this->assertEquals($exception, $e);
			}
		}
		
		public function wrapExceptionFunction($foo, $bar)
		{
			throw new UnimplementedFeatureException('some unimplemented feature');
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
				'savepointBegin' => 0,
				'savepointRelease' => 0,
				'savepointRollback' => 0,
				'inTransaction' => false,
			);
			
			$mock = $this->getMock('DB');
			$countMethods = array(
				'begin', 'commit', 'rollback',
				'savepointBegin', 'savepointRelease', 'savepointRollback'
			);
			foreach ($countMethods as $method)
				$mock->
					expects($this->exactly($options[$method]))->
					method($method)->
					will($this->returnSelf());
			
			$mock->
				expects($this->any())->
				method('inTransaction')->
				will($this->returnValue($options['inTransaction']));
			
			return $mock;
		}
	}
?>
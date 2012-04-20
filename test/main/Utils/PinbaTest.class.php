<?php
	
	final class PinbaTest extends TestCase
	{
		protected static $skipMessage = 'unknown error';
		protected static $skipped = false;
		
		public static function setUpBeforeClass()
		{
			if (!extension_loaded('pinba'))
				return self::skip('The pinba extension is not available.');
			
			if (!PinbaClient::isEnabled())
				return self::skip('The pinba is not enabled at php.ini (pinba.enabled=1).');
			
			if (!extension_loaded('runkit')) {
				return self::skip('The runkit extension is not available.');
			}
			
			if (!ini_get('runkit.internal_override'))
				return self::skip('The runkit.internal_override is not enabled (enabled it at php.ini).');
			
			runkit_function_rename('pinba_timer_start', 'pinba_timer_start_bak');
			runkit_function_rename('pinba_timer_stop', 'pinba_timer_stop_bak');
			
			runkit_function_rename('pinba_timer_start_callback', 'pinba_timer_start');
			runkit_function_rename('pinba_timer_stop_callback', 'pinba_timer_stop');
		}
		
		public static function tearDownAfterClass()
		{
			if (self::$skipped)
				return;
			
			runkit_function_rename('pinba_timer_start', 'pinba_timer_start_callback');
			runkit_function_rename('pinba_timer_stop', 'pinba_timer_stop_callback');
			
			runkit_function_rename('pinba_timer_start_bak', 'pinba_timer_start');
			runkit_function_rename('pinba_timer_stop_bak', 'pinba_timer_stop');
		}
		
		public function setUp(){
			if (self::$skipped) {
				$this->markTestSkipped(self::$skipMessage);
			}
		}
		
		public function testTreeLog()
		{
			PinbaClient::me()->setTreeLogEnabled();
			
			$this->assertEquals(count(PinbaClient::me()->getTreeQueue()), 0);
			
			PinbaClient::me()->timerStart(
				'test',
				array("test" => 'main')
			);
			
			$this->assertEquals(count(PinbaClient::me()->getTreeQueue()), 1);
			
			PinbaClient::me()->timerStart(
				'subtest',
				array("test" => 'submain')
			);
			
			$this->assertEquals(count(PinbaClient::me()->getTreeQueue()), 2);
			
			PinbaClient::me()->timerStop('subtest');
			
			$this->assertEquals(count(PinbaClient::me()->getTreeQueue()), 1);
			
			PinbaClient::me()->timerStop('test');
			
			$this->assertEquals(count(PinbaClient::me()->getTreeQueue()), 0);
			
		}
		
		protected static function skip($message)
		{
			self::$skipMessage = $message;
			self::$skipped = true;
		}
	}
	
	final class RunkitCallback
	{
		public static $queue = array();
		public static $log = array();
		
		public static function start($tags, $data = array())
		{
			self::$log[] = $tags;
			end(self::$log);
			
			if (!empty($tags['treeParentId']) && $tags['treeParentId'] != "root") {
				if ($tags['treeParentId'] != end(self::$queue)) {
					throw new Exception('Error generatin tree');
				}
			}
			
			if (!empty($tags['treeId'])) {
				self::$queue[] = $tags['treeId'];
			}
			
			return key(self::$log);
		}
		
		public static function stop($id)
		{
			$current = self::$log[$id];
			$tree_id = $current['treeId'];
			
			if (end(self::$queue) != $tree_id) {
				throw new Exception('Error generatin tree');
			}
			
			array_pop(self::$queue);
			unset(self::$log[$id]);
		}
	}
	
	function pinba_timer_start_callback ($tags, $data = array()) {
		return RunkitCallback::start($tags, $data);
	}

	function pinba_timer_stop_callback($id){
		return RunkitCallback::stop($id);
	}
?>
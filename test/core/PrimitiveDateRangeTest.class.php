<?php
	/* $Id$ */
	
	namespace Onphp\Test;

	final class PrimitiveDateRangeTest extends TestCase
	{
		public function testImport()
		{
			$start = '1985-01-03';
			$stop = '1985-01-03';
			$delimiter = ' - ';
			
			$prm = new \Onphp\PrimitiveDateRange('test');
			
			$array = array('test' => $start.$delimiter.$stop);
			
			$this->assertTrue($prm->import($array));
			$this->assertTrue(
				$prm->getValue()->getStart()->getYear() == 1985
			);
			
			$this->assertEquals(
				$prm->getValue()->toDateString(),
				$array['test']
			);
			
			
			$array = array('test' => $start);
			
			$this->assertTrue($prm->import($array));
			$this->assertEquals(
				$prm->getValue()->toDateString(),
				$start.$delimiter.$start
			);

			
			$array = array('test' => $start.$delimiter);
			
			$this->assertTrue($prm->import($array));
			$this->assertEquals(
				$prm->getValue()->toDateString(), $start
			);
			
			$array = array('test' => $delimiter.$start);
			
			$this->assertTrue($prm->import($array));
			$this->assertEquals(
				$prm->getValue()->toDateString(), $start
			);
			
			$array = array('test' => $delimiter);
			
			$this->assertTrue($prm->import($array));
			$this->assertEquals(
				$prm->getValue()->toDateString(), ''
			);
			
			
			$array = array('test' => '---');
			$this->assertFalse($prm->import($array));
		}
		
		public function testDottedImport()
		{
			$start = '1985-01-03';
			$stop = '1985-01-03';
			$delimiter = ' - ';
			
			$dottedStart = '1985.01.03';
			$dottedStop = '1985.01.03';
			
			$prm = new \Onphp\PrimitiveDateRange('test');
			
			$array = array('test' => $dottedStart.$delimiter.$dottedStop);
			
			$this->assertTrue($prm->import($array));
			$this->assertTrue(
				$prm->getValue()->getStart()->getYear() == 1985
			);
			
			$array = array('test' => $dottedStart);
			
			$this->assertTrue($prm->import($array));
			$this->assertEquals(
				$prm->getValue()->toDateString(),
				$start.$delimiter.$start
			);
			
			
			$date = '14.02';
			$array = array('test' => $date);
			
			$now = \Onphp\Date::makeToday();
			$result = $now->getYear().'-02-14';
			
			$this->assertTrue($prm->import($array));
			$this->assertEquals(
				$prm->getValue()->toDateString(),
				$result.$delimiter.$result
			);
			
			
			$date = '02';
			$array = array('test' => $date);
			
			$now = \Onphp\Date::makeToday();
			$result = $now->getYear().'-'.$now->getMonth().'-'.$date;
			
			$this->assertTrue($prm->import($array));
			$this->assertEquals(
				$prm->getValue()->toDateString(),
				$result.$delimiter.$result
			);
			
			$date = '14.02.07';
			$array = array('test' => $date);
			
			$result = '2007-02-14';
			
			$this->assertTrue($prm->import($array));
			$this->assertEquals(
				$prm->getValue()->toDateString(),
				$result.$delimiter.$result
			);
			
			
			$date = '14.02.2007';
			$result = '2007-02-14';
			$array = array('test' => $date);
			
			$this->assertTrue($prm->import($array));
			$this->assertEquals(
				$prm->getValue()->toDateString(),
				$result.$delimiter.$result
			);
			
			$date = '2007.02.14';
			$result = '2007-02-14';
			$array = array('test' => $date);
			
			$this->assertTrue($prm->import($array));
			$this->assertEquals(
				$prm->getValue()->toDateString(),
				$result.$delimiter.$result
			);
		}
		
		public function testUndelimitedDate()
		{
			$prm = new \Onphp\PrimitiveDateRange('test');
			
			$date = '1402';
			$array = array('test' => $date);
			
			$now = \Onphp\Date::makeToday();
			$result = $now->getYear().'-02-14';
			
			$this->assertTrue($prm->import($array));
			$this->assertEquals(
				$prm->getValue()->toDateString(),
				$result.' - '.$result
			);
		}
	}
?>
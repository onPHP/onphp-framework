<?php
	final class ArrayUtilsTest extends TestCase
	{
		/**
		 * @dataProvider sortedLists
		**/
		public function testMergeSortedLists($list1, $list2, $method, $result)
		{
			$this->assertEquals(
				$result,
				ArrayUtils::mergeSortedLists(
					$list1,
					$list2,
					DateObjectComparator::me(),
					$method
				)
			);
		}

		public static function sortedLists()
		{
			$today = Date::makeToday();

			return
				array(
					array(
						array(
							SortableObjectForTheTest::create()->setDate($today),
							SortableObjectForTheTest::create()->setDate($today->spawn('-1 day')),
							SortableObjectForTheTest::create()->setDate($today->spawn('-3 day'))
						),
						array(
							SortableObjectForTheTest::create()->setDate($today->spawn('-1 day')),
							SortableObjectForTheTest::create()->setDate($today->spawn('-2 day')),
							SortableObjectForTheTest::create()->setDate($today->spawn('-2 day'))
						),
						'getDate',
						array(
							SortableObjectForTheTest::create()->setDate($today),
							SortableObjectForTheTest::create()->setDate($today->spawn('-1 day')),
							SortableObjectForTheTest::create()->setDate($today->spawn('-1 day')),
							SortableObjectForTheTest::create()->setDate($today->spawn('-2 day')),
							SortableObjectForTheTest::create()->setDate($today->spawn('-2 day')),
							SortableObjectForTheTest::create()->setDate($today->spawn('-3 day'))
						)
					)
				);
		}
	}

	// for the test
	final class SortableObjectForTheTest
	{
		private $date = null;

		public static function create()
		{
			return new self;
		}

		public function setDate(Date $date)
		{
			$this->date = $date;
			
			return $this;
		}

		public function getDate()
		{
			return $this->date;
		}
	}
?>
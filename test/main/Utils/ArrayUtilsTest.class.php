<?php
	final class ArrayUtilsTest extends TestCase
	{
		/**
		 * @dataProvider sortedLists
		**/
		public function testMergeSortedLists($list1, $list2, $method, $result, $limit)
		{
			$this->assertEquals(
				$result,
				ArrayUtils::mergeSortedLists(
					$list1,
					$list2,
					DateObjectComparator::me(),
					$method,
					$limit
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
						),
						null
					),
					array(
						array(
							SortableObjectForTheTest::create()->setDate($today),
							SortableObjectForTheTest::create()->setDate($today->spawn('-1 day')),
							SortableObjectForTheTest::create()->setDate($today->spawn('-3 day'))
						),
						array(
							SortableObjectForTheTest::create()->setDate($today->spawn('-2 day')),
						),
						'getDate',
						array(
							SortableObjectForTheTest::create()->setDate($today),
							SortableObjectForTheTest::create()->setDate($today->spawn('-1 day')),
							SortableObjectForTheTest::create()->setDate($today->spawn('-2 day')),
							SortableObjectForTheTest::create()->setDate($today->spawn('-3 day'))
						),
						null
					),
					array(
						array(
							SortableObjectForTheTest::create()->setDate($today->spawn('-1 day')),
							SortableObjectForTheTest::create()->setDate($today->spawn('-3 day'))
						),
						array(
							SortableObjectForTheTest::create()->setDate($today),
							SortableObjectForTheTest::create()->setDate($today->spawn('-4 day')),
							SortableObjectForTheTest::create()->setDate($today->spawn('-6 day')),
						),
						'getDate',
						array(
							SortableObjectForTheTest::create()->setDate($today),
							SortableObjectForTheTest::create()->setDate($today->spawn('-1 day')),
							SortableObjectForTheTest::create()->setDate($today->spawn('-3 day')),
							SortableObjectForTheTest::create()->setDate($today->spawn('-4 day')),
							SortableObjectForTheTest::create()->setDate($today->spawn('-6 day'))
						),
						null
					),
					array(
						array(
							SortableObjectForTheTest::create()->setDate($today->spawn('-1 day')),
							SortableObjectForTheTest::create()->setDate($today->spawn('-3 day'))
						),
						array(
							SortableObjectForTheTest::create()->setDate($today),
							SortableObjectForTheTest::create()->setDate($today->spawn('-4 day')),
							SortableObjectForTheTest::create()->setDate($today->spawn('-6 day')),
						),
						'getDate',
						array(
							SortableObjectForTheTest::create()->setDate($today),
							SortableObjectForTheTest::create()->setDate($today->spawn('-1 day')),
						),
						2
					),
					array(
						array(
							SortableObjectForTheTest::create()->setDate($today->spawn('-1 day')),
						),
						array(
							SortableObjectForTheTest::create()->setDate($today),
							SortableObjectForTheTest::create()->setDate($today->spawn('-4 day')),
							SortableObjectForTheTest::create()->setDate($today->spawn('-6 day')),
						),
						'getDate',
						array(
							SortableObjectForTheTest::create()->setDate($today),
							SortableObjectForTheTest::create()->setDate($today->spawn('-1 day')),
							SortableObjectForTheTest::create()->setDate($today->spawn('-4 day'))
						),
						3
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
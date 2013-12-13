<?php
	namespace Onphp\Test;

	final class ArrayUtilsTest extends TestCase
	{
		/**
		 * @dataProvider dateObjectsSortedLists
		**/
		public function testMergeDateSortedLists($list1, $list2, $method, $result, $limit)
		{
			$this->assertEquals(
				$result,
				\Onphp\ArrayUtils::mergeSortedLists(
					$list1,
					$list2,
					\Onphp\DateObjectComparator::me(),
					$method,
					$limit
				)
			);
		}

		public static function dateObjectsSortedLists()
		{
			$today = \Onphp\Date::makeToday();

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
		
		/**
		 * @dataProvider textDataSortedLists
		**/
		public function testMergeTextDataSortedLists($list1, $list2, $method, $result, $limit)
		{
			$this->assertEquals(
				$result,
				\Onphp\ArrayUtils::mergeSortedLists(
					$list1,
					$list2,
					\Onphp\StandardComparator::me(),
					$method,
					$limit
				)
			);
		}
		
		public function testConvertObjectList()
		{
			$list =
				array(
					TestCity::create()->setId(42)->setName('Beldyazki'),
					TestCity::create()->setId(666)->setName('Moscow')
				);
			
			$this->assertEquals(array(42, 666), array_keys(\Onphp\ArrayUtils::convertObjectList($list)));
			$this->assertEquals(array('Beldyazki', 'Moscow'), array_keys(\Onphp\ArrayUtils::convertObjectList($list, 'getName')));
			
		}

		public static function textDataSortedLists()
		{
			return
				array(
					array(
						array(
							SortableTextDataObjectForTheTest::create()->setData('SIBN'),
							SortableTextDataObjectForTheTest::create()->setData('SBER03'),
							SortableTextDataObjectForTheTest::create()->setData('HYDR')
						),
						array(
							SortableTextDataObjectForTheTest::create()->setData('MTSI'),
							SortableTextDataObjectForTheTest::create()->setData('GAZP')
						),
						'getData',
						array(
							SortableTextDataObjectForTheTest::create()->setData('SIBN'),
							SortableTextDataObjectForTheTest::create()->setData('SBER03'),
							SortableTextDataObjectForTheTest::create()->setData('MTSI'),
							SortableTextDataObjectForTheTest::create()->setData('HYDR'),
							SortableTextDataObjectForTheTest::create()->setData('GAZP')
						),
						null
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

		public function setDate(\Onphp\Date $date)
		{
			$this->date = $date;
			
			return $this;
		}

		public function getDate()
		{
			return $this->date;
		}
	}
	
	final class SortableTextDataObjectForTheTest
	{
		private $data = null;

		public static function create()
		{
			return new self;
		}

		public function setData($data)
		{
			$this->data = $data;
			
			return $this;
		}

		public function getData()
		{
			return $this->data;
		}
	}
?>